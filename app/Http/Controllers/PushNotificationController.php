<?php

namespace App\Http\Controllers;

use App\Models\PushNotification;
use App\Models\User;
use App\Services\FirebaseNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PushNotificationController extends Controller
{
    public function index(Request $request)
    {
        $query = PushNotification::query();

        if ($request->filled('q_id')) {
            $query->where('id', $request->q_id);
        }

        if ($request->filled('q_date')) {
            $query->whereDate('notification_date', $request->q_date)
                  ->orWhereDate('created_at', $request->q_date);
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

    public function store(Request $request, FirebaseNotificationService $firebaseService)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'body' => 'required|string',
            'reason' => 'required|string|max:255',
            'sender' => 'required|string|max:255',
            'sender_show_section' => 'nullable|boolean',
            'priority' => 'required|in:baja,normal,alta',
            'notification_date' => 'nullable|date',
            'recipients' => 'required|array|min:1',
            'recipients.*' => 'exists:users,id',
            'image' => 'nullable|image|max:10240',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('notifications', 'public');
        }

        $title = !empty($validated['title']) ? $validated['title'] : $validated['reason'];

        $notification = PushNotification::create([
            'title' => $title,
            'body' => $validated['body'],
            'sender' => $validated['sender'],
            'sender_show_section' => $request->boolean('sender_show_section'),
            'reason' => $validated['reason'],
            'priority' => $validated['priority'],
            'notification_date' => $validated['notification_date'] ?? now()->toDateString(),
            'image_path' => $imagePath,
            'recipients' => array_values(array_map('intval', $validated['recipients'])),
            'status' => 'Procesando',
        ]);

        $users = User::whereIn('id', $notification->recipients)
            ->whereNotNull('fcm_token')
            ->get();

        $fullImageUrl = $imagePath ? url(Storage::url($imagePath)) : null;

        $payloadData = [
            'notification_id' => (string) $notification->id,
            'reason' => (string) $notification->reason,
            'sender' => (string) $notification->sender,
            'priority' => (string) $notification->priority,
            'notification_date' => (string) ($notification->notification_date ? $notification->notification_date->format('Y-m-d') : date('Y-m-d')),
        ];

        if ($fullImageUrl) {
            $payloadData['image'] = $fullImageUrl;
        }

        $responses = [];
        $hasErrors = false;

        foreach ($users as $user) {
            $response = $firebaseService->sendToDevice(
                $user->fcm_token,
                $title,
                $validated['body'],
                $fullImageUrl,
                $payloadData
            );

            $responses[$user->id] = $response;

            if (!$response['success']) {
                $hasErrors = true;
            }
        }

        $notification->update([
            'status' => count($users) === 0 ? 'Sin dispositivos registrados' : ($hasErrors ? 'Enviado con errores' : 'Enviado correctamente'),
            'firebase_responses' => $responses,
        ]);

        return redirect()->route('cola-revision.index')->with('success', 'Notificación push procesada correctamente.');
    }
}
