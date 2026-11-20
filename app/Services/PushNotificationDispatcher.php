<?php

namespace App\Services;

use App\Models\PushNotification;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PushNotificationDispatcher
{
    public function __construct(
        private readonly FirebaseNotificationService $firebaseService,
    ) {}

    public function dispatchNow(PushNotification $notification): PushNotification
    {
        $recipientIds = is_array($notification->recipients) ? $notification->recipients : [];

        $users = User::whereIn('id', $recipientIds)
            ->whereNotNull('fcm_token')
            ->get();

        $fullImageUrl = $notification->image_path
            ? url(Storage::url($notification->image_path))
            : null;

        $payloadData = [
            'notification_id' => (string) $notification->id,
            'reason' => (string) $notification->reason,
            'sender' => (string) $notification->sender,
            'priority' => (string) $notification->priority,
            'notification_date' => (string) ($notification->notification_date
                ? $notification->notification_date->format('Y-m-d')
                : now()->format('Y-m-d')),
        ];

        if ($fullImageUrl !== null) {
            $payloadData['image'] = $fullImageUrl;
        }

        if (! empty($notification->link_url)) {
            $payloadData['link'] = (string) $notification->link_url;
            $payloadData['url'] = (string) $notification->link_url;
        }

        if (! empty($notification->action_text)) {
            $payloadData['action_text'] = (string) $notification->action_text;
        }

        $responses = [];
        $hasErrors = false;

        foreach ($users as $user) {
            $response = $this->firebaseService->sendToDevice(
                (string) $user->fcm_token,
                (string) $notification->title,
                (string) $notification->body,
                $fullImageUrl,
                $payloadData,
                (string) $notification->priority,
            );

            $responses[$user->id] = $response;

            if (! ($response['success'] ?? false)) {
                $hasErrors = true;
            }
        }

        $status = $users->isEmpty()
            ? PushNotification::STATUS_SIN_DISPOSITIVOS
            : ($hasErrors ? PushNotification::STATUS_ENVIADO_ERRORES : PushNotification::STATUS_ENVIADO);

        $notification->update([
            'status' => $status,
            'firebase_responses' => $responses,
            'sent_at' => now(),
        ]);

        return $notification->refresh();
    }

    public function claimAndDispatchScheduled(PushNotification $notification): bool
    {
        $claimed = PushNotification::query()
            ->whereKey($notification->id)
            ->where('status', PushNotification::STATUS_PROGRAMADA)
            ->update(['status' => PushNotification::STATUS_PROCESANDO]);

        if ($claimed === 0) {
            return false;
        }

        try {
            $fresh = $notification->fresh();
            if ($fresh === null) {
                return false;
            }

            $this->dispatchNow($fresh);
        } catch (\Throwable $e) {
            Log::error('Error al despachar notificación programada', [
                'notification_id' => $notification->id,
                'error' => $e->getMessage(),
            ]);

            PushNotification::query()->whereKey($notification->id)->update([
                'status' => PushNotification::STATUS_ENVIADO_ERRORES,
                'sent_at' => now(),
                'firebase_responses' => ['error' => $e->getMessage()],
            ]);

            throw $e;
        }

        return true;
    }
}
