<?php

namespace Tests\Feature;

use App\Models\Legajo;
use App\Models\Registro;
use App\Models\User;
use Database\Seeders\LegajoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}

