<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfilePhotoTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_y_user_incluyen_campos_de_foto_en_false_por_defecto(): void
    {
        $user = User::factory()->create([
            'email' => 'empleado@cyre.com.ar',
            'password' => 'secreto123',
        ]);

        $loginResponse = $this->postJson('/api/login', [
            'email' => 'empleado@cyre.com.ar',
            'password' => 'secreto123',
        ])->assertOk()
            ->assertJsonPath('user.tiene_foto_perfil', false)
            ->assertJsonPath('user.foto_perfil_url', null);

        $token = (string) $loginResponse->json('token');

        $this->withToken($token)
            ->getJson('/api/user')
            ->assertOk()
            ->assertJsonPath('id', $user->id)
            ->assertJsonPath('tiene_foto_perfil', false)
            ->assertJsonPath('foto_perfil_url', null);
    }

    public function test_endpoints_foto_perfil_requieren_autenticacion(): void
    {
        $this->getJson('/api/user/foto-perfil')->assertUnauthorized();
        $this->postJson('/api/user/foto-perfil')->assertUnauthorized();
    }

    public function test_get_foto_perfil_devuelve_404_si_usuario_no_tiene_foto(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/user/foto-perfil')
            ->assertNotFound();
    }

    public function test_post_foto_perfil_rechaza_archivo_no_imagen(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/user/foto-perfil', [
                'foto' => UploadedFile::fake()->create('documento.pdf', 100, 'application/pdf'),
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('foto');

        $this->assertNull($user->fresh()?->foto_perfil_path);
    }

    public function test_subir_y_obtener_foto_perfil_y_reemplazar_borra_anterior(): void
    {
        Storage::fake('local');
        $user = User::factory()->create([
            'email' => 'foto@cyre.com.ar',
            'celular' => '5493511234567',
        ]);

        $upload1 = $this->actingAs($user, 'sanctum')
            ->postJson('/api/user/foto-perfil', [
                'foto' => UploadedFile::fake()->image('selfie1.jpg', 600, 600),
            ])
            ->assertOk()
            ->assertJsonPath('user.tiene_foto_perfil', true);

        $url1 = (string) $upload1->json('user.foto_perfil_url');
        $this->assertStringStartsWith('/user/foto-perfil?v=', $url1);

        $user->refresh();
        $firstPath = (string) $user->foto_perfil_path;
        $this->assertNotEmpty($firstPath);
        $this->assertNotNull($user->foto_perfil_updated_at);
        Storage::disk('local')->assertExists($firstPath);

        $getResponse = $this->actingAs($user, 'sanctum')
            ->get('/api/user/foto-perfil')
            ->assertOk();
        $this->assertStringStartsWith('image/', (string) $getResponse->headers->get('Content-Type'));

        $this->actingAs($user, 'sanctum')
            ->putJson('/api/user/profile', [
                'email' => 'foto@cyre.com.ar',
                'celular' => '5493517654321',
            ])
            ->assertOk()
            ->assertJsonPath('user.tiene_foto_perfil', true)
            ->assertJsonPath('user.foto_perfil_url', $url1);

        $this->travel(2)->seconds();

        $upload2 = $this->actingAs($user, 'sanctum')
            ->postJson('/api/user/foto-perfil', [
                'foto' => UploadedFile::fake()->image('selfie2.jpg', 600, 600),
            ])
            ->assertOk()
            ->assertJsonPath('user.tiene_foto_perfil', true);

        $url2 = (string) $upload2->json('user.foto_perfil_url');
        $this->assertStringStartsWith('/user/foto-perfil?v=', $url2);
        $this->assertNotSame($url1, $url2);

        $user->refresh();
        $secondPath = (string) $user->foto_perfil_path;
        $this->assertNotSame($firstPath, $secondPath);
        Storage::disk('local')->assertMissing($firstPath);
        Storage::disk('local')->assertExists($secondPath);
    }
}
