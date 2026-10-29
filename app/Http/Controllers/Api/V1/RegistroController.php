<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Registro\ConfirmarRegistroRequest;
use App\Http\Requests\Registro\DatosPersonalesRequest;
use App\Http\Requests\Registro\IniciarRegistroRequest;
use App\Http\Requests\Registro\VerificarLegajoRequest;
use App\Models\Legajo;
use App\Models\Registro;
use App\Services\RegistroService;
use Illuminate\Http\JsonResponse;

class RegistroController extends Controller
{
    public function __construct(private readonly RegistroService $registroService) {}

    public function verificar(VerificarLegajoRequest $request): JsonResponse
    {
        $legajo = $this->registroService->buscarLegajo($request->legNumero());

        if ($legajo === null) {
            return $this->respuestaVerificacion(false, false, 'No encontramos un empleado con ese número de legajo.');
        }

        if (! $legajo->estaActivo()) {
            return $this->respuestaVerificacion(false, false, 'El legajo no se encuentra activo. Comunicate con RRHH.');
        }

        if ($this->registroService->legajoYaRegistrado($legajo->leg_numero)) {
            return $this->respuestaVerificacion(true, true, 'Este legajo ya tiene un usuario creado. Iniciá sesión con tu email.');
        }

        return $this->respuestaVerificacion(true, false, null, $this->mapearLegajo($legajo));
    }

    public function iniciar(IniciarRegistroRequest $request): JsonResponse
    {
        $registro = $this->registroService->iniciar($request->legNumero(), (string) $request->validated('dni'));

        return response()->json([
            'registro_id' => $registro->id,
            'paso_actual' => $registro->paso_actual,
        ]);
    }

    public function datosPersonales(DatosPersonalesRequest $request, Registro $registro): JsonResponse
    {
        $registro = $this->registroService->guardarDatosPersonales($registro, $request->validated());

        return response()->json(['paso_actual' => $registro->paso_actual]);
    }

    public function confirmar(ConfirmarRegistroRequest $request, Registro $registro): JsonResponse
    {
        $this->registroService->confirmar(
            $registro,
            (string) $request->validated('email'),
            (string) $request->validated('password'),
        );

        return response()->json([
            'message' => 'Usuario creado y activado con éxito',
            'estado' => Registro::ESTADO_COMPLETADO,
        ], 201);
    }

    /**
     * @param  array<string, int|string>|null  $legajo
     */
    private function respuestaVerificacion(bool $encontrado, bool $yaRegistrado, ?string $mensaje, ?array $legajo = null): JsonResponse
    {
        return response()->json([
            'encontrado' => $encontrado,
            'ya_registrado' => $yaRegistrado,
            'mensaje' => $mensaje,
            'legajo' => $legajo,
        ]);
    }

    /**
     * @return array<string, int|string>
     */
    private function mapearLegajo(Legajo $legajo): array
    {
        return [
            'leg_numero' => $legajo->leg_numero,
            'leg_nombre' => (string) $legajo->leg_nombre,
            'leg_apellido' => (string) $legajo->leg_apellido,
            'leg_numdoc' => (string) $legajo->leg_numdoc,
            'leg_cuil' => (string) $legajo->leg_cuil,
            'leg_fecnac' => $legajo->leg_fecnac?->format('Y-m-d') ?? '',
            'leg_sexo' => (string) $legajo->leg_sexo,
            'leg_domic' => (string) $legajo->leg_domic,
            'leg_puerta' => (string) $legajo->leg_puerta,
            'leg_piso' => (string) $legajo->leg_piso,
            'leg_depto' => (string) $legajo->leg_depto,
            'leg_codpos' => (string) $legajo->leg_codpos,
            'leg_locali' => (string) $legajo->leg_locali,
            'leg_prvdom' => (string) $legajo->leg_prvdom,
            'Leg_celular' => (string) $legajo->Leg_celular,
            'Leg_mail' => (string) $legajo->Leg_mail,
        ];
    }
}

