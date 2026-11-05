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

        $this->withToken($token)->getJson('/api/user')->assertOk()->assertJson(['leg_numero' => 1001]);
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
                    'id_verification' => ['status' => 'Approved', 'document_number' => '99999999'],
                ], 200)
                ->push([
                    'request_id' => 'req-456',
                    'id_verification' => ['status' => 'Approved', 'document_number' => '30.123.456'],
                ], 200),
        ]);

        $this->postJson("/api/v1/registro/{$registro->id}/verificar-dni", [
            'dni_frente' => UploadedFile::fake()->image('frente.jpg'),
            'dni_dorso' => UploadedFile::fake()->image('dorso.jpg'),
        ])->assertUnprocessable()->assertJsonValidationErrors('dni_frente');

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
}

