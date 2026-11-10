<?php

namespace App\Exceptions;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use RuntimeException;

class DiditVerificationException extends RuntimeException implements Responsable
{
    /**
     * @param  list<string>  $mensajes
     * @param  array<string, array<string, float|bool|string|null>|null>  $calidad
     */
    public function __construct(
        public readonly string $motivo,
        public readonly string $campo,
        public readonly array $mensajes,
        public readonly array $calidad = ['frente' => null, 'dorso' => null],
        public readonly bool $reintentar = true,
        public readonly ?string $accion = null,
    ) {
        parent::__construct($mensajes[0] ?? 'No se pudo verificar el documento.', 422);
    }

    public function toResponse($request): JsonResponse
    {
        $mensajePrincipal = $this->getMessage();

        $errors = match ($this->campo) {
            'dni_dorso' => ['dni_dorso' => [$mensajePrincipal], 'dni_frente' => [$mensajePrincipal]],
            'ambos' => ['dni_frente' => [$mensajePrincipal], 'dni_dorso' => [$mensajePrincipal]],
            default => ['dni_frente' => [$mensajePrincipal]],
        };

        return response()->json([
            'message' => $mensajePrincipal,
            'errors' => $errors,
            'verificado' => false,
            'motivo' => $this->motivo,
            'campo' => $this->campo,
            'accion' => $this->accion,
            'mensajes' => $this->mensajes,
            'calidad' => $this->calidad,
            'reintentar' => $this->reintentar,
        ], 422);
    }
}

