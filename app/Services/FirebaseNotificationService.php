<?php

namespace App\Services;

use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class FirebaseNotificationService
{
    protected Messaging $messaging;

    public function __construct(Messaging $messaging)
    {
        $this->messaging = $messaging;
    }

    /**
     * @param string $token
     * @param string $title
     * @param string $body
     * @param string|null $imageUrl
     * @param array $data
     * @return array
     */
    public function sendToDevice(string $token, string $title, string $body, ?string $imageUrl = null, array $data = [])
    {
        $notification = Notification::create($title, $body, $imageUrl);

        if ($imageUrl && !isset($data['image'])) {
            $data['image'] = $imageUrl;
        }

        // Aseguramos que todos los valores en $data sean strings para compatibilidad FCM
        $dataFormatted = array_map(fn($val) => is_scalar($val) ? (string)$val : json_encode($val), $data);

        $message = CloudMessage::new()
            ->withNotification($notification)
            ->withData($dataFormatted)
            ->toToken($token);

        try {
            $this->messaging->send($message);
            return ['success' => true];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
