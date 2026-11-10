<?php

namespace App\Services;

use App\Exceptions\DiditUnavailableException;
use App\Exceptions\DiditVerificationException;
use App\Models\Legajo;
use App\Models\Registro;
use App\Models\User;
use App\Support\CatalogoBejerman;
use App\Support\DiditMotivoMapper;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class RegistroService
{
    public function __construct(
        private readonly DiditService $diditService,
        private readonly DiditMotivoMapper $motivoMapper,
    ) {}

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

        $rateKey = "didit-dni:{$registro->id}";
        $maxAttempts = (int) config('services.didit.rate_limit_per_hour', 5);

        if (RateLimiter::tooManyAttempts($rateKey, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($rateKey);
            $minutos = max(1, (int) ceil($seconds / 60));

            throw new HttpResponseException(response()->json([
                'message' => "Superaste el límite de {$maxAttempts} intentos de verificación por hora. Volvé a intentar en {$minutos} min.",
                'verificado' => false,
                'motivo' => 'limite_intentos',
                'reintentar' => false,
                'retry_after_seconds' => $seconds,
            ], 429));
        }

        $dir = "registros/{$registro->id}";
        $frentePath = $frente->store($dir, 'local');
        $dorsoPath = $dorso->store($dir, 'local');

        try {
            $resultado = $this->diditService->verificarDocumento(
                $frente,
                $dorso,
                "registro:{$registro->id}:legajo:{$registro->leg_numero}",
            );
        } catch (DiditUnavailableException $e) {
            Log::warning('Didit no disponible al verificar DNI', [
                'registro_id' => $registro->id,
                'mensaje' => $e->getMessage(),
            ]);

            throw new HttpResponseException(response()->json([
                'message' => $e->getMessage(),
                'errors' => ['dni_frente' => [$e->getMessage()]],
                'verificado' => false,
                'motivo' => 'servicio_no_disponible',
                'reintentar' => true,
            ], 503));
        }

        RateLimiter::hit($rateKey, 3600);

        $status = $resultado['status'];
        $calidad = [
            'frente' => $resultado['calidad_frente'],
            'dorso' => $resultado['calidad_dorso'],
        ];

        $registro->fill([
            'dni_frente_path' => $frentePath ?: null,
            'dni_dorso_path' => $dorsoPath ?: null,
            'didit_request_id' => $resultado['request_id'],
            'didit_status' => $status,
            'didit_payload' => $resultado['raw'],
        ])->save();

        Log::info('Resultado de verificación de DNI con Didit', [
            'registro_id' => $registro->id,
            'request_id' => $resultado['request_id'],
            'status' => $status,
            'http_status' => $resultado['http_status'],
            'error_code' => $resultado['error_code'],
            'warnings' => array_map(
                static fn (array $w): array => [
                    'risk' => $w['risk'],
                    'log_type' => $w['log_type'],
                    'short_description' => $w['short_description'],
                ],
                $resultado['warnings'],
            ),
            'calidad_frente' => $resultado['calidad_frente'],
            'calidad_dorso' => $resultado['calidad_dorso'],
        ]);

        if ($status === 'In Review') {
            $registro->forceFill(['estado' => Registro::ESTADO_REVISION_PENDIENTE])->save();

            throw new DiditVerificationException(
                motivo: DiditMotivoMapper::MOTIVO_REVISION_MANUAL,
                campo: 'ambos',
                mensajes: ['Tu documento quedó pendiente de revisión manual por RRHH. Te notificaremos cuando sea validado.'],
                calidad: $calidad,
                reintentar: false,
                accion: 'esperar_revision',
            );
        }

        if ($status !== 'Approved') {
            $mapeado = $this->motivoMapper->mapear($resultado);

            throw new DiditVerificationException(
                motivo: $mapeado['motivo'],
                campo: $mapeado['campo'],
                mensajes: $mapeado['mensajes'],
                calidad: $mapeado['calidad'],
                reintentar: true,
                accion: $mapeado['accion'],
            );
        }

        $dniExtraido = $this->normalizarDni($resultado['numero_documento']);
        if ($dniExtraido === '' || ! hash_equals($this->normalizarDni($registro->dni), $dniExtraido)) {
            throw new DiditVerificationException(
                motivo: DiditMotivoMapper::MOTIVO_DNI_NO_COINCIDE,
                campo: 'dni_frente',
                mensajes: ['El número de DNI de la foto no coincide con el titular del legajo.'],
                calidad: $calidad,
                reintentar: true,
                accion: 'verificar_titular',
            );
        }

        $registro->forceFill([
            'estado' => Registro::ESTADO_EN_CURSO,
            'paso_actual' => 4,
        ])->save();

        return $registro;
    }

    /**
     * @return array<string, mixed>
     */
    public function estadoDni(Registro $registro): array
    {
        return [
            'registro_id' => $registro->id,
            'paso_actual' => $registro->paso_actual,
            'estado' => $registro->estado,
            'didit_status' => $registro->didit_status,
            'verificado' => $registro->didit_status === 'Approved' && $registro->paso_actual >= 4,
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
