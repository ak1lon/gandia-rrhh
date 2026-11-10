<?php

namespace Tests\Feature;

use App\Models\Legajo;
use App\Models\Registro;
use App\Models\User;
use Database\Seeders\LegajoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RegistroFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(LegajoSeeder::class);
    }

    public function test_verificar_devuelve_legajo_activo(): void
    {
        $this->postJson('/api/v1/registro/verificar', ['legajo_o_dni' => '1001', 'leg_numero' => 1001])
            ->assertOk()
            ->assertJson([
                'encontrado' => true,
                'ya_registrado' => false,
                'mensaje' => null,
                'legajo' => [
                    'leg_numero' => 1001,
                    'leg_nombre' => 'Juan Martín',
                    'leg_apellido' => 'Pérez',
                    'leg_numdoc' => '30123456',
                    'leg_fecnac' => '1983-05-14',
                    'leg_piso' => '',
                ],
            ]);
    }

    public function test_verificar_rechaza_inexistente_y_dado_de_baja(): void
    {
        $this->postJson('/api/v1/registro/verificar', ['leg_numero' => 9999])
            ->assertOk()->assertJson(['encontrado' => false, 'legajo' => null]);

        $this->postJson('/api/v1/registro/verificar', ['leg_numero' => 1010])
            ->assertOk()->assertJson(['encontrado' => false, 'legajo' => null]);
    }

    public function test_iniciar_rechaza_dni_incorrecto(): void
    {
        $this->postJson('/api/v1/registro/iniciar', ['leg_numero' => 1001, 'dni' => '11111111'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('dni');

        $this->assertDatabaseCount('registros', 0);
    }

    public function test_flujo_completo_crea_usuario_y_permite_login(): void
    {
        $registroId = $this->postJson('/api/v1/registro/iniciar', [
            'legajo_o_dni' => '1001',
            'leg_numero' => 1001,
            'es_alta_nueva' => false,
            'dni' => '30.123.456',
        ])->assertOk()->assertJson(['paso_actual' => 2])->json('registro_id');

        $this->putJson("/api/v1/registro/{$registroId}/datos-personales", [
            'genero' => 'M',
            'domicilio' => 'Belgrano 245',
            'localidad' => 'Villa Dolores',
            'provincia' => 'Córdoba',
            'telefono' => '+54 9 3544 55-1234',
            'fecha_nacimiento' => '1983-05-14',
            'email' => 'JuanPerez@gmail.com',
        ])->assertOk()->assertJson(['paso_actual' => 3]);

        // Simular que completó la validación de DNI antes de confirmar
        Registro::query()->whereKey($registroId)->update([
            'didit_status' => 'Approved',
            'paso_actual' => 4,
        ]);

        $this->postJson("/api/v1/registro/{$registroId}/confirmar", [
            'acepta_declaracion' => true,
            'email' => 'juanperez@gmail.com',
            'password' => 'MiPasswordSegura123',
        ])->assertCreated()->assertJson(['estado' => 'completado']);

        $user = User::query()->where('leg_numero', 1001)->firstOrFail();
        $this->assertSame('juanperez@gmail.com', $user->email);
        $this->assertSame('30123456', $user->dni);
        $this->assertSame(Registro::ESTADO_COMPLETADO, Registro::query()->findOrFail($registroId)->estado);

        $this->postJson('/api/v1/registro/verificar', ['leg_numero' => 1001])
            ->assertJson(['encontrado' => true, 'ya_registrado' => true, 'legajo' => null]);

        $this->postJson("/api/v1/registro/{$registroId}/confirmar", [
            'acepta_declaracion' => true,
            'email' => 'otro@gmail.com',
            'password' => 'MiPasswordSegura123',
        ])->assertUnprocessable();

        $token = $this->postJson('/api/login', ['email' => 'juanperez@gmail.com', 'password' => 'MiPasswordSegura123'])
            ->assertOk()->json('token');
        $this->assertIsString($token);

        $this->withToken($token)->getJson('/api/user')->assertOk()->assertJson(['legajo' => '1001']);
    }

    public function test_login_con_password_incorrecta(): void
    {
        User::factory()->create(['email' => 'a@b.com', 'password' => 'correcta123']);

        $this->postJson('/api/login', ['email' => 'a@b.com', 'password' => 'mala'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function test_datos_personales_rechaza_email_en_uso(): void
    {
        User::factory()->create(['email' => 'usado@gmail.com']);
        $registro = Registro::query()->create(['leg_numero' => 1002, 'dni' => '32456789']);

        $this->putJson("/api/v1/registro/{$registro->id}/datos-personales", [
            'telefono' => '123',
            'email' => 'usado@gmail.com',
        ])->assertUnprocessable()->assertJsonValidationErrors('email');

        $this->assertNull($registro->fresh()?->email);
        $this->assertNotNull(Legajo::query()->find(1002));
    }

    public function test_verificar_dni_con_didit_aprueba_y_rechaza_dni_distinto(): void
    {
        Storage::fake('local');
        config()->set('services.didit.api_key', 'test-api-key');

        $registro = Registro::query()->create(['leg_numero' => 1001, 'dni' => '30123456', 'paso_actual' => 3]);

        Http::fake([
            '*/id-verification/' => Http::sequence()
                ->push([
                    'request_id' => 'req-123',
                    'id_verification' => [
                        'status' => 'Approved',
                        'document_number' => '99999999',
                        'full_name' => 'OTRO NOMBRE',
                    ],
                ], 200)
                ->push([
                    'request_id' => 'req-456',
                    'id_verification' => [
                        'status' => 'Approved',
                        'document_number' => '30.123.456',
                    ],
                ], 200),
        ]);

        $resRechazo = $this->postJson("/api/v1/registro/{$registro->id}/verificar-dni", [
            'dni_frente' => UploadedFile::fake()->image('frente.jpg'),
            'dni_dorso' => UploadedFile::fake()->image('dorso.jpg'),
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('dni_frente')
            ->assertJson([
                'verificado' => false,
                'motivo' => 'dni_no_coincide',
                'campo' => 'dni_frente',
                'reintentar' => true,
            ]);

        $this->assertArrayNotHasKey('didit_payload', $resRechazo->json());
        $this->assertStringNotContainsString('99999999', $resRechazo->getContent());
        $this->assertSame(3, $registro->fresh()?->paso_actual);

        $this->postJson("/api/v1/registro/{$registro->id}/verificar-dni", [
            'dni_frente' => UploadedFile::fake()->image('frente.jpg'),
            'dni_dorso' => UploadedFile::fake()->image('dorso.jpg'),
        ])->assertOk()->assertJson([
            'verificado' => true,
            'didit_status' => 'Approved',
            'paso_actual' => 4,
        ]);

        $actualizado = $registro->fresh();
        $this->assertSame('req-456', $actualizado?->didit_request_id);
        $this->assertNotNull($actualizado?->dni_frente_path);
        $this->assertArrayNotHasKey('didit_payload', $actualizado?->toArray() ?? []);
        Storage::disk('local')->assertExists((string) $actualizado?->dni_frente_path);

        $this->getJson("/api/v1/registro/{$registro->id}/dni/estado")
            ->assertOk()
            ->assertJson([
                'verificado' => true,
                'didit_status' => 'Approved',
                'dni_frente_cargado' => true,
                'dni_dorso_cargado' => true,
            ]);
    }

    public function test_verificar_dni_declined_con_warnings_de_codigo_de_barras(): void
    {
        Storage::fake('local');
        config()->set('services.didit.api_key', 'test-api-key');

        $registro = Registro::query()->create(['leg_numero' => 1001, 'dni' => '30123456', 'paso_actual' => 3]);

        Http::fake([
            '*/id-verification/' => Http::response([
                'request_id' => 'req-barcode',
                'id_verification' => [
                    'status' => 'Declined',
                    'warnings' => [
                        [
                            'risk' => 'BARCODE_NOT_DETECTED',
                            'log_type' => 'error',
                            'short_description' => 'Barcode not detected',
                            'long_description' => 'Could not decode PDF417 barcode on back side.',
                        ],
                    ],
                ],
            ], 200),
        ]);

        $this->postJson("/api/v1/registro/{$registro->id}/verificar-dni", [
            'dni_frente' => UploadedFile::fake()->image('frente.jpg'),
            'dni_dorso' => UploadedFile::fake()->image('dorso.jpg'),
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['dni_dorso', 'dni_frente'])
            ->assertJson([
                'verificado' => false,
                'motivo' => 'imagen_ilegible',
                'campo' => 'dni_dorso',
                'accion' => 'repetir_dorso',
                'mensajes' => [
                    'No se pudo leer el código de barras del dorso. Enfocá bien el dorso, sin reflejos.',
                ],
                'reintentar' => true,
            ]);

        $this->assertSame(3, $registro->fresh()?->paso_actual);
    }

    public function test_verificar_dni_declined_con_calidad_baja_y_copia_impresa(): void
    {
        Storage::fake('local');
        config()->set('services.didit.api_key', 'test-api-key');

        $registro = Registro::query()->create(['leg_numero' => 1001, 'dni' => '30123456', 'paso_actual' => 3]);

        Http::fake([
            '*/id-verification/' => Http::sequence()
                ->push([
                    'request_id' => 'req-low-quality',
                    'id_verification' => [
                        'status' => 'Declined',
                        'warnings' => [],
                        'front_image_quality_score' => [
                            'focus' => 35.0,
                            'brightness' => 40.0,
                            'resolution' => 50.0,
                            'overall' => 42.0,
                        ],
                        'back_image_quality_score' => [
                            'focus' => 85.0,
                            'brightness' => 80.0,
                            'resolution' => 88.0,
                            'overall' => 84.0,
                        ],
                    ],
                ], 200)
                ->push([
                    'request_id' => 'req-copy',
                    'id_verification' => [
                        'status' => 'Declined',
                        'warnings' => [
                            ['risk' => 'PRINTED_COPY_DETECTED', 'log_type' => 'error'],
                        ],
                    ],
                ], 200),
        ]);

        $response = $this->postJson("/api/v1/registro/{$registro->id}/verificar-dni", [
            'dni_frente' => UploadedFile::fake()->image('frente.jpg'),
            'dni_dorso' => UploadedFile::fake()->image('dorso.jpg'),
        ])->assertUnprocessable()
            ->assertJson([
                'verificado' => false,
                'motivo' => 'imagen_ilegible',
                'campo' => 'dni_frente',
                'accion' => 'repetir_frente',
                'reintentar' => true,
            ]);

        $mensajes = $response->json('mensajes');
        $this->assertContains('Foto del frente borrosa, tocá la pantalla para enfocar.', $mensajes);
        $this->assertContains('Poca luz en la foto del frente. Buscá un lugar mejor iluminado sin reflejos.', $mensajes);
        $this->assertContains('Baja resolución en el frente. Acercate un poco más al DNI.', $mensajes);
        $this->assertSame(35, (int) $response->json('calidad.frente.focus'));
        $this->assertSame(3, $registro->fresh()?->paso_actual);

        $this->postJson("/api/v1/registro/{$registro->id}/verificar-dni", [
            'dni_frente' => UploadedFile::fake()->image('frente.jpg'),
            'dni_dorso' => UploadedFile::fake()->image('dorso.jpg'),
        ])->assertUnprocessable()
            ->assertJson([
                'verificado' => false,
                'motivo' => 'documento_invalido',
                'campo' => 'ambos',
                'accion' => 'usar_dni_fisico',
                'reintentar' => true,
            ]);
    }

    public function test_verificar_dni_maneja_4xx_could_not_recognize_document(): void
    {
        Storage::fake('local');
        config()->set('services.didit.api_key', 'test-api-key');

        $registro = Registro::query()->create(['leg_numero' => 1001, 'dni' => '30123456', 'paso_actual' => 3]);

        Http::fake([
            '*/id-verification/' => Http::response([
                'code' => 'COULD_NOT_RECOGNIZE_DOCUMENT',
                'detail' => 'Could not recognize document from provided images.',
            ], 400),
        ]);

        $this->postJson("/api/v1/registro/{$registro->id}/verificar-dni", [
            'dni_frente' => UploadedFile::fake()->image('frente.jpg'),
            'dni_dorso' => UploadedFile::fake()->image('dorso.jpg'),
        ])->assertUnprocessable()
            ->assertJson([
                'verificado' => false,
                'motivo' => 'imagen_ilegible',
                'campo' => 'ambos',
                'accion' => 'repetir_ambos',
                'mensajes' => [
                    'No pudimos reconocer el documento. Fotografiá el DNI completo sobre una superficie lisa.',
                ],
                'reintentar' => true,
            ]);

        Http::assertSentCount(1);
        $this->assertSame(3, $registro->fresh()?->paso_actual);
    }

    public function test_verificar_dni_maneja_5xx_y_timeout_con_503_sin_consumir_intento(): void
    {
        Storage::fake('local');
        config()->set('services.didit.api_key', 'test-api-key');
        config()->set('services.didit.rate_limit_per_hour', 1);

        $registro = Registro::query()->create(['leg_numero' => 1001, 'dni' => '30123456', 'paso_actual' => 3]);

        Http::fake([
            '*/id-verification/' => Http::sequence()
                ->push(['error' => 'Internal Server Error'], 500)
                ->push(['error' => 'Bad Gateway'], 502)
                ->push([
                    'request_id' => 'req-ok',
                    'id_verification' => ['status' => 'Approved', 'document_number' => '30123456'],
                ], 200),
        ]);

        $this->postJson("/api/v1/registro/{$registro->id}/verificar-dni", [
            'dni_frente' => UploadedFile::fake()->image('frente.jpg'),
            'dni_dorso' => UploadedFile::fake()->image('dorso.jpg'),
        ])->assertStatus(503)
            ->assertJson([
                'verificado' => false,
                'motivo' => 'servicio_no_disponible',
                'reintentar' => true,
            ]);

        $this->assertSame(3, $registro->fresh()?->paso_actual);
        $this->assertNull($registro->fresh()?->didit_status);

        $this->postJson("/api/v1/registro/{$registro->id}/verificar-dni", [
            'dni_frente' => UploadedFile::fake()->image('frente.jpg'),
            'dni_dorso' => UploadedFile::fake()->image('dorso.jpg'),
        ])->assertOk()->assertJson([
            'verificado' => true,
            'paso_actual' => 4,
        ]);
    }

    public function test_verificar_dni_in_review_deja_pendiente_de_revision(): void
    {
        Storage::fake('local');
        config()->set('services.didit.api_key', 'test-api-key');

        $registro = Registro::query()->create(['leg_numero' => 1001, 'dni' => '30123456', 'paso_actual' => 3]);

        Http::fake([
            '*/id-verification/' => Http::response([
                'request_id' => 'req-review',
                'id_verification' => [
                    'status' => 'In Review',
                    'document_number' => '30123456',
                ],
            ], 200),
        ]);

        $this->postJson("/api/v1/registro/{$registro->id}/verificar-dni", [
            'dni_frente' => UploadedFile::fake()->image('frente.jpg'),
            'dni_dorso' => UploadedFile::fake()->image('dorso.jpg'),
        ])->assertUnprocessable()
            ->assertJson([
                'verificado' => false,
                'motivo' => 'revision_manual',
                'campo' => 'ambos',
                'accion' => 'esperar_revision',
                'reintentar' => false,
            ]);

        $actualizado = $registro->fresh();
        $this->assertSame(3, $actualizado?->paso_actual);
        $this->assertSame('In Review', $actualizado?->didit_status);
        $this->assertSame(Registro::ESTADO_REVISION_PENDIENTE, $actualizado?->estado);
    }

    public function test_verificar_dni_aplica_rate_limit_por_registro(): void
    {
        Storage::fake('local');
        config()->set('services.didit.api_key', 'test-api-key');
        config()->set('services.didit.rate_limit_per_hour', 2);

        $registro = Registro::query()->create(['leg_numero' => 1001, 'dni' => '30123456', 'paso_actual' => 3]);

        Http::fake([
            '*/id-verification/' => Http::response([
                'request_id' => 'req-declined',
                'id_verification' => ['status' => 'Declined', 'warnings' => []],
            ], 200),
        ]);

        for ($i = 0; $i < 2; $i++) {
            $this->postJson("/api/v1/registro/{$registro->id}/verificar-dni", [
                'dni_frente' => UploadedFile::fake()->image('frente.jpg'),
                'dni_dorso' => UploadedFile::fake()->image('dorso.jpg'),
            ])->assertUnprocessable();
        }

        $this->postJson("/api/v1/registro/{$registro->id}/verificar-dni", [
            'dni_frente' => UploadedFile::fake()->image('frente.jpg'),
            'dni_dorso' => UploadedFile::fake()->image('dorso.jpg'),
        ])->assertStatus(429)
            ->assertJson([
                'verificado' => false,
                'motivo' => 'limite_intentos',
                'reintentar' => false,
            ]);
    }
}

