<?php

namespace App\Console\Commands;

use App\Models\PushNotification;
use App\Services\PushNotificationDispatcher;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DispatchScheduledNotificationsCommand extends Command
{
    protected $signature = 'notifications:dispatch-scheduled';

    protected $description = 'Envía las notificaciones push programadas vencidas';

    public function handle(PushNotificationDispatcher $dispatcher): int
    {
        $pendientes = PushNotification::pendientesDeEnvio()
            ->orderBy('scheduled_at')
            ->get();

        $procesadas = 0;

        foreach ($pendientes as $notification) {
            try {
                if ($dispatcher->claimAndDispatchScheduled($notification)) {
                    $procesadas++;
                }
            } catch (\Throwable $e) {
                Log::error('Fallo al procesar notificación programada en cron', [
                    'notification_id' => $notification->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Notificaciones programadas procesadas: {$procesadas}");

        return self::SUCCESS;
    }
}
