<?php

namespace App\Support;

class DiditMotivoMapper
{
    public const MOTIVO_IMAGEN_ILEGIBLE = 'imagen_ilegible';

    public const MOTIVO_DOCUMENTO_INVALIDO = 'documento_invalido';

    public const MOTIVO_DNI_NO_COINCIDE = 'dni_no_coincide';

    public const MOTIVO_REVISION_MANUAL = 'revision_manual';

    /**
     * @var array<string, array{mensaje: string, campo: string, motivo: string, accion: string}>
     */
    private const WARNING_MAP = [
        'BARCODE_NOT_DETECTED' => [
            'mensaje' => 'No se pudo leer el código de barras del dorso. Enfocá bien el dorso, sin reflejos.',
            'campo' => 'dni_dorso',
            'motivo' => self::MOTIVO_IMAGEN_ILEGIBLE,
            'accion' => 'repetir_dorso',
        ],
        'BARCODE_VALIDATION_FAILED' => [
            'mensaje' => 'No se pudo leer el código de barras del dorso. Enfocá bien el dorso, sin reflejos.',
            'campo' => 'dni_dorso',
            'motivo' => self::MOTIVO_IMAGEN_ILEGIBLE,
            'accion' => 'repetir_dorso',
        ],
        'QR_NOT_DETECTED' => [
            'mensaje' => 'No se pudo leer el código de barras del dorso. Enfocá bien el dorso, sin reflejos.',
            'campo' => 'dni_dorso',
            'motivo' => self::MOTIVO_IMAGEN_ILEGIBLE,
            'accion' => 'repetir_dorso',
        ],
        'INVALID_MRZ' => [
            'mensaje' => 'No se pudo leer la zona mecanizada del dorso. Enfocá bien el dorso, sin reflejos.',
            'campo' => 'dni_dorso',
            'motivo' => self::MOTIVO_IMAGEN_ILEGIBLE,
            'accion' => 'repetir_dorso',
        ],
        'MRZ_NOT_DETECTED' => [
            'mensaje' => 'No se pudo leer el código del dorso. Enfocá bien el dorso, sin reflejos.',
            'campo' => 'dni_dorso',
            'motivo' => self::MOTIVO_IMAGEN_ILEGIBLE,
            'accion' => 'repetir_dorso',
        ],
        'DOCUMENT_NUMBER_NOT_DETECTED' => [
            'mensaje' => 'No se leyó bien el frente. Encuadrá todo el DNI con las cuatro esquinas visibles.',
            'campo' => 'dni_frente',
            'motivo' => self::MOTIVO_IMAGEN_ILEGIBLE,
            'accion' => 'repetir_frente',
        ],
        'NAME_NOT_DETECTED' => [
            'mensaje' => 'No se leyó bien el frente. Encuadrá todo el DNI con las cuatro esquinas visibles.',
            'campo' => 'dni_frente',
            'motivo' => self::MOTIVO_IMAGEN_ILEGIBLE,
            'accion' => 'repetir_frente',
        ],
        'DATE_OF_BIRTH_NOT_DETECTED' => [
            'mensaje' => 'No se leyó bien el frente. Encuadrá todo el DNI con las cuatro esquinas visibles.',
            'campo' => 'dni_frente',
            'motivo' => self::MOTIVO_IMAGEN_ILEGIBLE,
            'accion' => 'repetir_frente',
        ],
        'PORTRAIT_IMAGE_NOT_DETECTED' => [
            'mensaje' => 'No se leyó bien el frente. Encuadrá todo el DNI con las cuatro esquinas visibles.',
            'campo' => 'dni_frente',
            'motivo' => self::MOTIVO_IMAGEN_ILEGIBLE,
            'accion' => 'repetir_frente',
        ],
        'COULD_NOT_DETECT_DOCUMENT_TYPE' => [
            'mensaje' => 'No pudimos reconocer el documento. Fotografiá el DNI completo sobre una superficie lisa.',
            'campo' => 'ambos',
            'motivo' => self::MOTIVO_IMAGEN_ILEGIBLE,
            'accion' => 'repetir_ambos',
        ],
        'COULD_NOT_RECOGNIZE_DOCUMENT' => [
            'mensaje' => 'No pudimos reconocer el documento. Fotografiá el DNI completo sobre una superficie lisa.',
            'campo' => 'ambos',
            'motivo' => self::MOTIVO_IMAGEN_ILEGIBLE,
            'accion' => 'repetir_ambos',
        ],
        'PRINTED_COPY_DETECTED' => [
            'mensaje' => 'Necesitamos una foto del DNI físico, no de una pantalla ni de una copia.',
            'campo' => 'ambos',
            'motivo' => self::MOTIVO_DOCUMENTO_INVALIDO,
            'accion' => 'usar_dni_fisico',
        ],
        'PUBLIC_DOCUMENT_IMAGE_DETECTED' => [
            'mensaje' => 'Necesitamos una foto del DNI físico, no de una pantalla ni de una copia.',
            'campo' => 'ambos',
            'motivo' => self::MOTIVO_DOCUMENTO_INVALIDO,
            'accion' => 'usar_dni_fisico',
        ],
        'SCREEN_CAPTURE_DETECTED' => [
            'mensaje' => 'Necesitamos una foto del DNI físico, no de una pantalla ni de una copia.',
            'campo' => 'ambos',
            'motivo' => self::MOTIVO_DOCUMENTO_INVALIDO,
            'accion' => 'usar_dni_fisico',
        ],
        'DOCUMENT_LIVENESS_FAILED' => [
            'mensaje' => 'Necesitamos una foto del DNI físico, no de una pantalla ni de una copia.',
            'campo' => 'ambos',
            'motivo' => self::MOTIVO_DOCUMENTO_INVALIDO,
            'accion' => 'usar_dni_fisico',
        ],
        'DOCUMENT_EXPIRED' => [
            'mensaje' => 'El DNI fotografiado figura vencido. Utilizá tu ejemplar vigente.',
            'campo' => 'dni_frente',
            'motivo' => self::MOTIVO_DOCUMENTO_INVALIDO,
            'accion' => 'usar_dni_vigente',
        ],
    ];

    /**
     * @param  array{
     *     warnings?: list<array{risk: string, log_type?: ?string, short_description?: ?string, long_description?: ?string}>,
     *     calidad_frente?: ?array{focus: ?float, brightness: ?float, resolution: ?float, overall: ?float, is_document_fully_visible?: ?bool, brightness_issue?: ?string},
     *     calidad_dorso?: ?array{focus: ?float, brightness: ?float, resolution: ?float, overall: ?float, is_document_fully_visible?: ?bool, brightness_issue?: ?string},
     *     error_code?: ?string
     * }  $resultado
     * @return array{
     *     motivo: string,
     *     campo: string,
     *     accion: string,
     *     mensajes: list<string>,
     *     calidad: array{frente: ?array<string, float|bool|string|null>, dorso: ?array<string, float|bool|string|null>}
     * }
     */
    public function mapear(array $resultado): array
    {
        $warnings = $resultado['warnings'] ?? [];
        $calidadFrente = $resultado['calidad_frente'] ?? null;
        $calidadDorso = $resultado['calidad_dorso'] ?? null;

        $mensajes = [];
        $campos = [];
        $motivo = self::MOTIVO_IMAGEN_ILEGIBLE;
        $accion = 'repetir_ambos';

        foreach ($warnings as $warning) {
            $risk = strtoupper(trim((string) ($warning['risk'] ?? '')));
            if ($risk === '') {
                continue;
            }

            if (isset(self::WARNING_MAP[$risk])) {
                $definicion = self::WARNING_MAP[$risk];
                $mensajes[] = $definicion['mensaje'];
                $campos[] = $definicion['campo'];

                if ($definicion['motivo'] === self::MOTIVO_DOCUMENTO_INVALIDO) {
                    $motivo = self::MOTIVO_DOCUMENTO_INVALIDO;
                    $accion = $definicion['accion'];
                } elseif ($motivo !== self::MOTIVO_DOCUMENTO_INVALIDO) {
                    $accion = $definicion['accion'];
                }
            }
        }

        $calidadFrenteMensajes = $this->evaluarCalidadLado($calidadFrente, 'frente');
        if ($calidadFrenteMensajes !== []) {
            array_push($mensajes, ...$calidadFrenteMensajes);
            $campos[] = 'dni_frente';
        }

        $calidadDorsoMensajes = $this->evaluarCalidadLado($calidadDorso, 'dorso');
        if ($calidadDorsoMensajes !== []) {
            array_push($mensajes, ...$calidadDorsoMensajes);
            $campos[] = 'dni_dorso';
        }

        $mensajes = array_values(array_unique($mensajes));

        if ($mensajes === []) {
            $mensajes[] = 'No pudimos validar las fotos del DNI. Asegurate de que ambas caras estén bien enfocadas, iluminadas y con las cuatro esquinas visibles.';
            $campos[] = 'ambos';
        }

        $campoFinal = $this->resolverCampo($campos);
        if ($motivo === self::MOTIVO_IMAGEN_ILEGIBLE) {
            $accion = match ($campoFinal) {
                'dni_frente' => 'repetir_frente',
                'dni_dorso' => 'repetir_dorso',
                default => 'repetir_ambos',
            };
        }

        return [
            'motivo' => $motivo,
            'campo' => $campoFinal,
            'accion' => $accion,
            'mensajes' => $mensajes,
            'calidad' => [
                'frente' => $calidadFrente,
                'dorso' => $calidadDorso,
            ],
        ];
    }

    /**
     * @param  ?array{focus: ?float, brightness: ?float, resolution: ?float, overall: ?float, is_document_fully_visible?: ?bool, brightness_issue?: ?string}  $calidad
     * @return list<string>
     */
    private function evaluarCalidadLado(?array $calidad, string $etiquetaLado): array
    {
        if ($calidad === null) {
            return [];
        }

        $umbralFocus = (float) config('services.didit.quality_thresholds.focus', 60);
        $umbralBrightness = (float) config('services.didit.quality_thresholds.brightness', 50);
        $umbralResolution = (float) config('services.didit.quality_thresholds.resolution', 60);
        $umbralOverall = (float) config('services.didit.quality_thresholds.overall', 60);

        $mensajes = [];

        if (($calidad['is_document_fully_visible'] ?? null) === false) {
            $mensajes[] = "El {$etiquetaLado} del DNI está cortado. Encuadrá las cuatro esquinas.";
        }

        if ($calidad['focus'] !== null && $calidad['focus'] < $umbralFocus) {
            $mensajes[] = "Foto del {$etiquetaLado} borrosa, tocá la pantalla para enfocar.";
        }

        if ($calidad['brightness'] !== null && $calidad['brightness'] < $umbralBrightness) {
            $mensajes[] = "Poca luz en la foto del {$etiquetaLado}. Buscá un lugar mejor iluminado sin reflejos.";
        } elseif (($calidad['brightness_issue'] ?? null) === 'too_bright') {
            $mensajes[] = "Demasiado brillo o reflejo en el {$etiquetaLado}. Evitá el flash directo.";
        }

        if ($calidad['resolution'] !== null && $calidad['resolution'] < $umbralResolution) {
            $mensajes[] = "Baja resolución en el {$etiquetaLado}. Acercate un poco más al DNI.";
        }

        if ($mensajes === [] && $calidad['overall'] !== null && $calidad['overall'] < $umbralOverall) {
            $mensajes[] = "La calidad de la foto del {$etiquetaLado} es baja. Tomala nuevamente enfocando bien.";
        }

        return $mensajes;
    }

    /**
     * @param  list<string>  $campos
     */
    private function resolverCampo(array $campos): string
    {
        $unicos = array_values(array_unique($campos));

        if (in_array('ambos', $unicos, true) || (in_array('dni_frente', $unicos, true) && in_array('dni_dorso', $unicos, true))) {
            return 'ambos';
        }

        if (in_array('dni_dorso', $unicos, true)) {
            return 'dni_dorso';
        }

        if (in_array('dni_frente', $unicos, true)) {
            return 'dni_frente';
        }

        return 'ambos';
    }
}

