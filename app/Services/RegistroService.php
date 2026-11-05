<?php

namespace App\Services;

use App\Models\Legajo;
use App\Models\Registro;
use App\Models\User;
use App\Support\CatalogoBejerman;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RegistroService
{
    public function __construct(private readonly DiditService $diditService) {}
    public function buscarLegajo(int $legNumero): ?Legajo
    {
        return Legajo::query()->find($legNumero);
    }

    public function legajoYaRegistrado(int $legNumero): bool
    {
        return User::query()->where('leg_numero', $legNumero)->exists();
    }

    public function normalizarDni(?string $dni): string
    {
        return ltrim(preg_replace('/\D/', '', (string) $dni) ?? '', '0');
    }

    public function iniciar(int $legNumero, string $dni): Registro
    {
        $legajo = $this->legajoHabilitado($legNumero);

        $dniLegajo = $this->normalizarDni($legajo->leg_numdoc);
        if ($dniLegajo === '' || ! hash_equals($dniLegajo, $this->normalizarDni($dni))) {
            throw ValidationException::withMessages(['dni' => 'El DNI ingresado no coincide con el legajo.']);
        }

        return Registro::query()->updateOrCreate(
            ['leg_numero' => $legNumero, 'estado' => Registro::ESTADO_EN_CURSO],
            ['dni' => $dniLegajo, 'paso_actual' => 2],
        );
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    public function guardarDatosPersonales(Registro $registro, array $datos): Registro
    {
        $this->asegurarEnCurso($registro);

        $registro->fill([
            'genero' => $datos['genero'] ?? null,
            'domicilio' => $datos['domicilio'] ?? null,
            'localidad' => $datos['localidad'] ?? null,
            'provincia' => $datos['provincia'] ?? null,
            'telefono' => $datos['telefono'],
            'fecha_nacimiento' => $datos['fecha_nacimiento'] ?? null,
            'email' => mb_strtolower($datos['email']),
            'paso_actual' => 3,
        ])->save();

        return $registro;
    }

    public function verificarDocumentoDni(Registro $registro, UploadedFile $frente, UploadedFile $dorso): Registro
    {
        $this->asegurarEnCurso($registro);

        $dir = "registros/{$registro->id}";
        $frentePath = $frente->store($dir, 'local');
        $dorsoPath = $dorso->store($dir, 'local');

        $payload = $this->diditService->verificarDocumento($frente, $dorso, "registro:{$registro->id}:legajo:{$registro->leg_numero}");

        $verificacion = is_array($payload['id_verification'] ?? null) ? $payload['id_verification'] : [];
        $status = (string) ($verificacion['status'] ?? 'Unknown');
        $dniExtraido = $this->normalizarDni((string) ($verificacion['document_number'] ?? $verificacion['personal_number'] ?? ''));

        $registro->fill([
            'dni_frente_path' => $frentePath ?: null,
            'dni_dorso_path' => $dorsoPath ?: null,
            'didit_request_id' => isset($payload['request_id']) ? (string) $payload['request_id'] : null,
            'didit_status' => $status,
            'didit_payload' => $payload,
        ])->save();

        if ($status !== 'Approved') {
            throw ValidationException::withMessages([
                'dni_frente' => 'El documento no pudo ser validado por Didit. Asegurate de que ambas caras sean legibles y sin reflejos.',
            ]);
        }

        if ($dniExtraido === '' || ! hash_equals($this->normalizarDni($registro->dni), $dniExtraido)) {
            throw ValidationException::withMessages([
                'dni_frente' => 'El número de DNI de la foto no coincide con el titular del legajo.',
            ]);
        }

        $registro->forceFill(['paso_actual' => 4])->save();

        return $registro;
    }

    public function estadoDni(Registro $registro): array
    {
        return [
            'registro_id' => $registro->id,
            'paso_actual' => $registro->paso_actual,
            'didit_status' => $registro->didit_status,
            'verificado' => $registro->didit_status === 'Approved',
            'dni_frente_cargado' => ! empty($registro->dni_frente_path),
            'dni_dorso_cargado' => ! empty($registro->dni_dorso_path),
        ];
    }

    public function confirmar(Registro $registro, string $email, string $password): User
    {
        return DB::transaction(function () use ($registro, $email, $password): User {
            $registro = Registro::query()->lockForUpdate()->findOrFail($registro->getKey());
            $this->asegurarEnCurso($registro);

            if ($registro->didit_status !== 'Approved') {
                throw ValidationException::withMessages([
                    'dni' => 'La validación de identidad con DNI debe estar aprobada antes de confirmar el registro.',
                ]);
            }

            $legajo = $this->legajoHabilitado($registro->leg_numero);

            $user = User::query()->create([
                'name' => $legajo->nombre_completo,
                'email' => mb_strtolower($email),
                'password' => $password,
                'role' => 'empleado',
                'legajo' => (string) $legajo->leg_numero,
                'leg_numero' => $legajo->leg_numero,
                'cargo_sap' => CatalogoBejerman::cargo($legajo->car_codigo),
                'seccion' => CatalogoBejerman::seccion($legajo->sec_codigo),
                'dni' => $registro->dni,
                'celular' => $registro->telefono,
            ]);

            $registro->forceFill([
                'email' => $user->email,
                'estado' => Registro::ESTADO_COMPLETADO,
                'user_id' => $user->id,
            ])->save();

            return $user;
        });
    }

    private function legajoHabilitado(int $legNumero): Legajo
    {
        $legajo = $this->buscarLegajo($legNumero);

        if ($legajo === null || ! $legajo->estaActivo()) {
            throw ValidationException::withMessages(['leg_numero' => 'El legajo no existe o no se encuentra activo.']);
        }

        if ($this->legajoYaRegistrado($legNumero)) {
            throw ValidationException::withMessages(['leg_numero' => 'Este legajo ya tiene un usuario creado.']);
        }

        return $legajo;
    }

    private function asegurarEnCurso(Registro $registro): void
    {
        if (! $registro->estaEnCurso()) {
            throw ValidationException::withMessages(['registro' => 'El registro ya fue completado.']);
        }
    }
}

