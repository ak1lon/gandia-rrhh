<?php

namespace App\Http\Controllers;

use App\Models\PushNotification;
use App\Models\User;
use App\Services\PushNotificationDispatcher;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PushNotificationController extends Controller
{
    public function index(Request $request)
    {
        $query = PushNotification::query();

        if ($request->filled('q_id')) {
            $query->where('id', $request->q_id);
        }

        if ($request->filled('q_date')) {
            $query->where(function ($q) use ($request) {
                $q->whereDate('notification_date', $request->q_date)
                    ->orWhereDate('created_at', $request->q_date);
            });
        }

        if ($request->filled('q_reason')) {
            $query->where(function ($q) use ($request) {
                $q->where('reason', 'like', '%' . $request->q_reason . '%')
                    ->orWhere('title', 'like', '%' . $request->q_reason . '%');
            });
        }

        if ($request->filled('q_sender')) {
            $query->where('sender', 'like', '%' . $request->q_sender . '%');
        }

        if ($request->filled('q_status')) {
            match ($request->input('q_status')) {
                'programadas' => $query->where('status', PushNotification::STATUS_PROGRAMADA),
                'enviadas' => $query->whereIn('status', [
                    PushNotification::STATUS_ENVIADO,
                    PushNotification::STATUS_ENVIADO_ERRORES,
                    PushNotification::STATUS_SIN_DISPOSITIVOS,
                ]),
                'canceladas' => $query->where('status', PushNotification::STATUS_CANCELADA),
                default => null,
            };
        }

        $notifications = $query->orderBy('id', 'desc')->get();

        $employees = User::where('role', 'empleado')->orderBy('name')->get();

        $cargos = User::where('role', 'empleado')
            ->whereNotNull('cargo_sap')
            ->where('cargo_sap', '!=', '')
            ->distinct()
            ->orderBy('cargo_sap')
            ->pluck('cargo_sap');

        $secciones = User::where('role', 'empleado')
            ->whereNotNull('seccion')
            ->where('seccion', '!=', '')
            ->distinct()
            ->orderBy('seccion')
            ->pluck('seccion');

        $emisores = collect([
            'RRHH Gandia',
            'Gerencia de Operaciones',
            'Seguridad e Higiene',
            'Capacitación y Desarrollo',
            'Mantenimiento',
            'Administración',
        ])->merge(PushNotification::whereNotNull('sender')->distinct()->pluck('sender'))->unique()->values();

        $motivos = collect([
            'INVITACIÓN A CAPACITACIÓN',
            'FELICES FIESTAS',
            'ACTUALIZACION DE MANUAL DE CAJERO',
            '"CARNE MOLIDA SEGURA"',
            'PREVENCION DE TRIQUINOSIS',
            'GUARDIA MANTENIMIENTO',
            'COMUNICADO DE SEGURIDAD',
            'CAMBIO DE HORARIO',
        ])->merge(PushNotification::whereNotNull('reason')->distinct()->pluck('reason'))->unique()->values();

        return view('push-notifications.index', compact(
            'notifications',
            'employees',
            'cargos',
            'secciones',
            'emisores',
            'motivos'
        ));
    }

    public function store(Request $request, PushNotificationDispatcher $dispatcher): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'body' => 'required|string',
            'reason' => 'required|string|max:255',
            'sender' => 'required|string|max:255',
            'sender_show_section' => 'nullable|boolean',
            'priority' => 'required|in:baja,normal,alta',
            'notification_date' => 'nullable|date',
            'send_mode' => 'nullable|in:now,schedule',
            'scheduled_at' => 'required_if:send_mode,schedule|nullable|date|after:now',
            'link_url' => 'nullable|url:http,https|max:2048',
            'action_text' => 'nullable|string|max:60',
            'recipients' => 'required|array|min:1',
            'recipients.*' => 'exists:users,id',
            'image' => 'nullable|image|max:10240',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('notifications', 'public');
        }

        $title = ! empty($validated['title']) ? $validated['title'] : $validated['reason'];
        $linkUrl = ! empty($validated['link_url']) ? trim((string) $validated['link_url']) : null;
        $actionText = ($linkUrl !== null && ! empty($validated['action_text']))
            ? trim((string) $validated['action_text'])
            : null;
        $recipients = array_values(array_map('intval', $validated['recipients']));
        $sendMode = $validated['send_mode'] ?? 'now';

        if ($sendMode === 'schedule') {
            $scheduledAt = Carbon::parse((string) $validated['scheduled_at'], config('app.timezone'));

            PushNotification::create([
                'title' => $title,
                'body' => $validated['body'],
                'sender' => $validated['sender'],
                'sender_show_section' => $request->boolean('sender_show_section'),
                'reason' => $validated['reason'],
                'priority' => $validated['priority'],
                'notification_date' => $validated['notification_date'] ?? $scheduledAt->toDateString(),
                'scheduled_at' => $scheduledAt,
                'image_path' => $imagePath,
                'link_url' => $linkUrl,
                'action_text' => $actionText,
                'recipients' => $recipients,
                'status' => PushNotification::STATUS_PROGRAMADA,
            ]);

            return redirect()->route('cola-revision.index')->with(
                'success',
                'Notificación programada para el ' . $scheduledAt->format('d/m/Y H:i') . ' hs.'
            );
        }

        $notification = PushNotification::create([
            'title' => $title,
            'body' => $validated['body'],
            'sender' => $validated['sender'],
            'sender_show_section' => $request->boolean('sender_show_section'),
            'reason' => $validated['reason'],
            'priority' => $validated['priority'],
            'notification_date' => $validated['notification_date'] ?? now()->toDateString(),
            'image_path' => $imagePath,
            'link_url' => $linkUrl,
            'action_text' => $actionText,
            'recipients' => $recipients,
            'status' => PushNotification::STATUS_PROCESANDO,
        ]);

        $dispatcher->dispatchNow($notification);

        return redirect()->route('cola-revision.index')->with('success', 'Notificación push procesada correctamente.');
    }

    public function cancel(PushNotification $notification): RedirectResponse
    {
        $updated = PushNotification::query()
            ->whereKey($notification->id)
            ->where('status', PushNotification::STATUS_PROGRAMADA)
            ->update([
                'status' => PushNotification::STATUS_CANCELADA,
                'cancelled_at' => now(),
            ]);

        if ($updated === 0) {
            return redirect()->route('cola-revision.index')
                ->with('error', 'La notificación ya fue procesada o cancelada.');
        }

        return redirect()->route('cola-revision.index')
            ->with('success', 'Notificación programada cancelada.');
    }

    public function sendNow(PushNotification $notification, PushNotificationDispatcher $dispatcher): RedirectResponse
    {
        if (! $dispatcher->claimAndDispatchScheduled($notification)) {
            return redirect()->route('cola-revision.index')
                ->with('error', 'La notificación ya no está pendiente de envío.');
        }

        return redirect()->route('cola-revision.index')
            ->with('success', 'Notificación enviada inmediatamente.');
    }

    public function reschedule(Request $request, PushNotification $notification): RedirectResponse
    {
        $validated = $request->validate([
            'scheduled_at' => ['required', 'date', 'after:now'],
        ]);

        $newScheduledAt = Carbon::parse((string) $validated['scheduled_at'], config('app.timezone'));

        $updated = PushNotification::query()
            ->whereKey($notification->id)
            ->where('status', PushNotification::STATUS_PROGRAMADA)
            ->update([
                'scheduled_at' => $newScheduledAt,
                'notification_date' => $newScheduledAt->toDateString(),
            ]);

        if ($updated === 0) {
            return redirect()->route('cola-revision.index')
                ->with('error', 'Solo se pueden reprogramar notificaciones en estado Programada.');
        }

        return redirect()->route('cola-revision.index')
            ->with('success', 'Notificación reprogramada para el ' . $newScheduledAt->format('d/m/Y H:i') . ' hs.');
    }
}
