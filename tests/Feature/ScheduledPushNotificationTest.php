<?php

namespace Tests\Feature;

use App\Models\PushNotification;
use App\Models\User;
use App\Services\FirebaseNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class ScheduledPushNotificationTest extends TestCase
{
    use RefreshDatabase;

    private function crearAdminYEmpleado(): array
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email' => 'rrhh@cyre.com.ar',
        ]);

        $empleado = User::factory()->create([
            'role' => 'empleado',
            'email' => 'empleado1@cyre.com.ar',
            'fcm_token' => 'fcm-token-empleado-1',
        ]);

        return [$admin, $empleado];
    }

    public function test_envio_inmediato_con_link_envia_por_firebase_y_persiste_sent_at(): void
    {
        [$admin, $empleado] = $this->crearAdminYEmpleado();

        $mock = Mockery::mock(FirebaseNotificationService::class);
        $mock->shouldReceive('sendToDevice')
            ->once()
            ->withArgs(function (
                string $token,
                string $title,
                string $body,
                ?string $imageUrl,
                array $data,
                string $priority,
            ) {
                return $token === 'fcm-token-empleado-1'
                    && $title === 'CAPACITACIÓN'
                    && $body === 'Revisar el nuevo instructivo'
                    && $imageUrl === null
                    && ($data['link'] ?? null) === 'https://cyre.ddns.net/manual'
                    && ($data['url'] ?? null) === 'https://cyre.ddns.net/manual'
                    && ($data['action_text'] ?? null) === 'Ver manual'
                    && $priority === 'alta';
            })
            ->andReturn(['success' => true]);

        $this->app->instance(FirebaseNotificationService::class, $mock);

        $this->actingAs($admin)
            ->post('/cola-revision', [
                'reason' => 'CAPACITACIÓN',
                'body' => 'Revisar el nuevo instructivo',
                'sender' => 'RRHH Gandia',
                'priority' => 'alta',
                'send_mode' => 'now',
                'link_url' => 'https://cyre.ddns.net/manual',
                'action_text' => 'Ver manual',
                'recipients' => [$empleado->id],
            ])
            ->assertRedirect(route('cola-revision.index'));

        $notification = PushNotification::query()->firstOrFail();
        $this->assertSame(PushNotification::STATUS_ENVIADO, $notification->status);
        $this->assertNotNull($notification->sent_at);
        $this->assertNull($notification->scheduled_at);
        $this->assertSame('https://cyre.ddns.net/manual', $notification->link_url);
        $this->assertSame('Ver manual', $notification->action_text);
    }

    public function test_programar_notificacion_no_envia_inmediatamente_y_guarda_estado_programada(): void
    {
        [$admin, $empleado] = $this->crearAdminYEmpleado();

        $mock = Mockery::mock(FirebaseNotificationService::class);
        $mock->shouldNotReceive('sendToDevice');
        $this->app->instance(FirebaseNotificationService::class, $mock);

        $fechaFutura = now()->addHours(3)->format('Y-m-d\TH:i');

        $this->actingAs($admin)
            ->post('/cola-revision', [
                'reason' => 'CAMBIO DE HORARIO',
                'body' => 'Nuevo horario de apertura',
                'sender' => 'RRHH Gandia',
                'priority' => 'normal',
                'send_mode' => 'schedule',
                'scheduled_at' => $fechaFutura,
                'link_url' => 'https://cyre.ddns.net/horarios',
                'action_text' => 'Consultar horarios',
                'recipients' => [$empleado->id],
            ])
            ->assertRedirect(route('cola-revision.index'))
            ->assertSessionHas('success');

        $notification = PushNotification::query()->firstOrFail();
        $this->assertSame(PushNotification::STATUS_PROGRAMADA, $notification->status);
        $this->assertNotNull($notification->scheduled_at);
        $this->assertNull($notification->sent_at);
        $this->assertSame('https://cyre.ddns.net/horarios', $notification->link_url);
        $this->assertSame('Consultar horarios', $notification->action_text);
    }

    public function test_validaciones_de_programacion_y_link_invalido(): void
    {
        [$admin, $empleado] = $this->crearAdminYEmpleado();

        $this->actingAs($admin)
            ->post('/cola-revision', [
                'reason' => 'AVISO',
                'body' => 'Texto',
                'sender' => 'RRHH Gandia',
                'priority' => 'normal',
                'send_mode' => 'schedule',
                'scheduled_at' => now()->subMinutes(15)->format('Y-m-d\TH:i'),
                'recipients' => [$empleado->id],
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('scheduled_at');

        $this->actingAs($admin)
            ->post('/cola-revision', [
                'reason' => 'AVISO',
                'body' => 'Texto',
                'sender' => 'RRHH Gandia',
                'priority' => 'normal',
                'send_mode' => 'schedule',
                'recipients' => [$empleado->id],
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('scheduled_at');

        $this->actingAs($admin)
            ->post('/cola-revision', [
                'reason' => 'AVISO',
                'body' => 'Texto',
                'sender' => 'RRHH Gandia',
                'priority' => 'normal',
                'send_mode' => 'now',
                'link_url' => 'javascript:alert(1)',
                'recipients' => [$empleado->id],
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('link_url');

        $this->assertDatabaseCount('push_notifications', 0);
    }

    public function test_comando_envia_solo_programadas_vencidas_y_es_idempotente(): void
    {
        [, $empleado] = $this->crearAdminYEmpleado();

        $vencida = PushNotification::create([
            'title' => 'VENCIDA',
            'body' => 'Debe enviarse ahora',
            'sender' => 'RRHH Gandia',
            'reason' => 'VENCIDA',
            'priority' => 'normal',
            'notification_date' => now()->toDateString(),
            'scheduled_at' => now()->subMinutes(5),
            'link_url' => 'https://cyre.ddns.net/aviso',
            'recipients' => [$empleado->id],
            'status' => PushNotification::STATUS_PROGRAMADA,
        ]);

        $futura = PushNotification::create([
            'title' => 'FUTURA',
            'body' => 'Todavía no',
            'sender' => 'RRHH Gandia',
            'reason' => 'FUTURA',
            'priority' => 'normal',
            'notification_date' => now()->addDay()->toDateString(),
            'scheduled_at' => now()->addDay(),
            'recipients' => [$empleado->id],
            'status' => PushNotification::STATUS_PROGRAMADA,
        ]);

        $cancelada = PushNotification::create([
            'title' => 'CANCELADA',
            'body' => 'No debe enviarse',
            'sender' => 'RRHH Gandia',
            'reason' => 'CANCELADA',
            'priority' => 'normal',
            'notification_date' => now()->toDateString(),
            'scheduled_at' => now()->subMinutes(10),
            'cancelled_at' => now()->subMinutes(8),
            'recipients' => [$empleado->id],
            'status' => PushNotification::STATUS_CANCELADA,
        ]);

        $mock = Mockery::mock(FirebaseNotificationService::class);
        $mock->shouldReceive('sendToDevice')
            ->once()
            ->withArgs(function (
                string $token,
                string $title,
                string $body,
                ?string $imageUrl,
                array $data,
            ) use ($vencida) {
                return $token === 'fcm-token-empleado-1'
                    && $title === 'VENCIDA'
                    && ($data['notification_id'] ?? null) === (string) $vencida->id
                    && ($data['link'] ?? null) === 'https://cyre.ddns.net/aviso';
            })
            ->andReturn(['success' => true]);

        $this->app->instance(FirebaseNotificationService::class, $mock);

        $this->artisan('notifications:dispatch-scheduled')->assertSuccessful();

        $vencida->refresh();
        $futura->refresh();
        $cancelada->refresh();

        $this->assertSame(PushNotification::STATUS_ENVIADO, $vencida->status);
        $this->assertNotNull($vencida->sent_at);
        $this->assertSame(PushNotification::STATUS_PROGRAMADA, $futura->status);
        $this->assertNull($futura->sent_at);
        $this->assertSame(PushNotification::STATUS_CANCELADA, $cancelada->status);

        // Segunda ejecución no debe volver a enviar
        $this->artisan('notifications:dispatch-scheduled')->assertSuccessful();
    }

    public function test_acciones_cancelar_reprogramar_y_enviar_ahora(): void
    {
        [$admin, $empleado] = $this->crearAdminYEmpleado();

        $notifParaReprogramarYEnviar = PushNotification::create([
            'title' => 'A REPROGRAMAR',
            'body' => 'Mensaje',
            'sender' => 'RRHH Gandia',
            'reason' => 'A REPROGRAMAR',
            'priority' => 'normal',
            'notification_date' => now()->addDay()->toDateString(),
            'scheduled_at' => now()->addDay(),
            'recipients' => [$empleado->id],
            'status' => PushNotification::STATUS_PROGRAMADA,
        ]);

        $nuevaFecha = now()->addDays(2)->format('Y-m-d\TH:i');
        $this->actingAs($admin)
            ->patch(route('cola-revision.reschedule', $notifParaReprogramarYEnviar), [
                'scheduled_at' => $nuevaFecha,
            ])
            ->assertRedirect(route('cola-revision.index'))
            ->assertSessionHas('success');

        $this->assertSame(
            now()->addDays(2)->format('Y-m-d H:i'),
            $notifParaReprogramarYEnviar->fresh()?->scheduled_at?->format('Y-m-d H:i')
        );

        $this->actingAs($admin)
            ->patch(route('cola-revision.reschedule', $notifParaReprogramarYEnviar), [
                'scheduled_at' => now()->subHour()->format('Y-m-d\TH:i'),
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('scheduled_at');

        $mock = Mockery::mock(FirebaseNotificationService::class);
        $mock->shouldReceive('sendToDevice')->once()->andReturn(['success' => true]);
        $this->app->instance(FirebaseNotificationService::class, $mock);

        $this->actingAs($admin)
            ->post(route('cola-revision.send-now', $notifParaReprogramarYEnviar))
            ->assertRedirect(route('cola-revision.index'))
            ->assertSessionHas('success');

        $notifParaReprogramarYEnviar->refresh();
        $this->assertSame(PushNotification::STATUS_ENVIADO, $notifParaReprogramarYEnviar->status);
        $this->assertNotNull($notifParaReprogramarYEnviar->sent_at);

        // Repetir "Enviar ahora" no vuelve a disparar Firebase
        $this->actingAs($admin)
            ->post(route('cola-revision.send-now', $notifParaReprogramarYEnviar))
            ->assertRedirect(route('cola-revision.index'))
            ->assertSessionHas('error');

        // No se puede cancelar una ya enviada
        $this->actingAs($admin)
            ->patch(route('cola-revision.cancel', $notifParaReprogramarYEnviar))
            ->assertRedirect(route('cola-revision.index'))
            ->assertSessionHas('error');

        // Sí se puede cancelar una programada
        $notifParaCancelar = PushNotification::create([
            'title' => 'A CANCELAR',
            'body' => 'Mensaje',
            'sender' => 'RRHH Gandia',
            'reason' => 'A CANCELAR',
            'priority' => 'normal',
            'notification_date' => now()->addDay()->toDateString(),
            'scheduled_at' => now()->addDay(),
            'recipients' => [$empleado->id],
            'status' => PushNotification::STATUS_PROGRAMADA,
        ]);

        $this->actingAs($admin)
            ->patch(route('cola-revision.cancel', $notifParaCancelar))
            ->assertRedirect(route('cola-revision.index'))
            ->assertSessionHas('success');

        $notifParaCancelar->refresh();
        $this->assertSame(PushNotification::STATUS_CANCELADA, $notifParaCancelar->status);
        $this->assertNotNull($notifParaCancelar->cancelled_at);
    }

    public function test_api_movil_oculta_programadas_y_canceladas_y_ordena_por_sent_at(): void
    {
        [, $empleado] = $this->crearAdminYEmpleado();

        $programada = PushNotification::create([
            'title' => 'PROGRAMADA',
            'body' => 'No visible aún',
            'sender' => 'RRHH Gandia',
            'reason' => 'PROGRAMADA',
            'priority' => 'normal',
            'scheduled_at' => now()->addHour(),
            'recipients' => [$empleado->id],
            'status' => PushNotification::STATUS_PROGRAMADA,
        ]);

        $cancelada = PushNotification::create([
            'title' => 'CANCELADA',
            'body' => 'Cancelada',
            'sender' => 'RRHH Gandia',
            'reason' => 'CANCELADA',
            'priority' => 'normal',
            'cancelled_at' => now(),
            'recipients' => [$empleado->id],
            'status' => PushNotification::STATUS_CANCELADA,
        ]);

        $enviadaAyer = PushNotification::create([
            'title' => 'AYER',
            'body' => 'Creada y enviada ayer',
            'sender' => 'RRHH Gandia',
            'reason' => 'AYER',
            'priority' => 'normal',
            'sent_at' => now()->subDay(),
            'recipients' => [$empleado->id],
            'status' => PushNotification::STATUS_ENVIADO,
        ]);
        $enviadaAyer->forceFill(['created_at' => now()->subDay()])->save();

        $programadaHace2DiasEnviadaHoy = PushNotification::create([
            'title' => 'PROGRAMADA ENVIADA HOY',
            'body' => 'Con link',
            'sender' => 'RRHH Gandia',
            'reason' => 'PROGRAMADA ENVIADA HOY',
            'priority' => 'alta',
            'link_url' => 'https://cyre.ddns.net/encuesta',
            'action_text' => 'Responder encuesta',
            'sent_at' => now(),
            'recipients' => [$empleado->id],
            'status' => PushNotification::STATUS_ENVIADO,
        ]);
        $programadaHace2DiasEnviadaHoy->forceFill(['created_at' => now()->subDays(2)])->save();

        $response = $this->getJson('/api/notifications?email=empleado1@cyre.com.ar')
            ->assertOk();

        $items = $response->json();
        $this->assertCount(2, $items);

        $ids = array_column($items, 'id');
        $this->assertNotContains($programada->id, $ids);
        $this->assertNotContains($cancelada->id, $ids);

        $this->assertSame($programadaHace2DiasEnviadaHoy->id, $items[0]['id']);
        $this->assertSame('https://cyre.ddns.net/encuesta', $items[0]['link']);
        $this->assertSame('https://cyre.ddns.net/encuesta', $items[0]['link_url']);
        $this->assertSame('Responder encuesta', $items[0]['action_text']);
        $this->assertSame($enviadaAyer->id, $items[1]['id']);
    }

    public function test_limpia_fcm_token_solo_cuando_firebase_reporta_token_invalido(): void
    {
        [$admin, $empleadoInvalido] = $this->crearAdminYEmpleado();
        $empleadoTemporal = User::factory()->create([
            'role' => 'empleado',
            'email' => 'empleado2@cyre.com.ar',
            'fcm_token' => 'fcm-token-empleado-2',
        ]);

        $mock = Mockery::mock(FirebaseNotificationService::class);
        $mock->shouldReceive('sendToDevice')
            ->once()
            ->with('fcm-token-empleado-1', Mockery::any(), Mockery::any(), Mockery::any(), Mockery::any(), Mockery::any())
            ->andReturn([
                'success' => false,
                'error' => 'NotRegistered',
                'invalid_token' => true,
            ]);
        $mock->shouldReceive('sendToDevice')
            ->once()
            ->with('fcm-token-empleado-2', Mockery::any(), Mockery::any(), Mockery::any(), Mockery::any(), Mockery::any())
            ->andReturn([
                'success' => false,
                'error' => 'Temporary server error',
                'invalid_token' => false,
            ]);

        $this->app->instance(FirebaseNotificationService::class, $mock);

        $this->actingAs($admin)
            ->post('/cola-revision', [
                'reason' => 'AVISO',
                'body' => 'Prueba token inválido',
                'sender' => 'RRHH Gandia',
                'priority' => 'normal',
                'send_mode' => 'now',
                'recipients' => [$empleadoInvalido->id, $empleadoTemporal->id],
            ])
            ->assertRedirect(route('cola-revision.index'));

        $notification = PushNotification::query()->firstOrFail();
        $this->assertSame(PushNotification::STATUS_ENVIADO_ERRORES, $notification->status);
        $this->assertNull($empleadoInvalido->fresh()?->fcm_token);
        $this->assertSame('fcm-token-empleado-2', $empleadoTemporal->fresh()?->fcm_token);
    }
}
