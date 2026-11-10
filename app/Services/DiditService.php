<?php

namespace App\Services;

use App\Exceptions\DiditUnavailableException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Throwable;

class DiditService
{
    /**
     * @return array{
     *     request_id: ?string,
     *     status: string,
     *     warnings: list<array{risk: string, log_type: ?string, short_description: ?string, long_description: ?string}>,
     *     calidad_frente: ?array{focus: ?float, brightness: ?float, resolution: ?float, overall: ?float, is_document_fully_visible: ?bool, brightness_issue: ?string},
     *     calidad_dorso: ?array{focus: ?float, brightness: ?float, resolution: ?float, overall: ?float, is_document_fully_visible: ?bool, brightness_issue: ?string},
     *     numero_documento: ?string,
     *     error_code: ?string,
     *     http_status: int,
     *     raw: array<string, mixed>
     * }
     */
    public function verificarDocumento(UploadedFile $frente, UploadedFile $dorso, string $vendorData): array
    {
        $apiKey = trim((string) config('services.didit.api_key'));
        $baseUrl = rtrim((string) config('services.didit.base_url', 'https://verification.didit.me/v3'), '/');
        $timeout = (int) config('services.didit.timeout', 30);
        $connectTimeout = (int) config('services.didit.connect_timeout', 10);

        if ($apiKey === '') {
            throw new DiditUnavailableException('El servicio de verificación de identidad no está disponible temporalmente. Reintentá en unos minutos.');
        }

        $frenteBytes = file_get_contents($frente->getRealPath());
        $dorsoBytes = file_get_contents($dorso->getRealPath());

        if ($frenteBytes === false || $dorsoBytes === false) {
            throw new DiditUnavailableException('No se pudieron leer las imágenes cargadas. Reintentá en unos minutos.');
        }

        $frenteNombre = $frente->getClientOriginalName() ?: 'front.jpg';
        $dorsoNombre = $dorso->getClientOriginalName() ?: 'back.jpg';
        $frenteMime = $frente->getMimeType() ?: ($frente->getClientMimeType() ?: 'image/jpeg');
        $dorsoMime = $dorso->getMimeType() ?: ($dorso->getClientMimeType() ?: 'image/jpeg');

        try {
            $response = Http::withHeaders(['x-api-key' => $apiKey])
                ->timeout($timeout)
                ->connectTimeout($connectTimeout)
                ->retry(
                    2,
                    250,
                    function (Throwable $exception): bool {
                        if ($exception instanceof ConnectionException) {
                            return true;
                        }

                        if ($exception instanceof RequestException) {
                            return $exception->response->serverError();
                        }

                        return false;
                    },
                    throw: false
                )
                ->attach('front_image', $frenteBytes, $frenteNombre, ['Content-Type' => $frenteMime])
                ->attach('back_image', $dorsoBytes, $dorsoNombre, ['Content-Type' => $dorsoMime])
                ->post("{$baseUrl}/id-verification/", [
                    'save_api_request' => 'true',
                    'vendor_data' => $vendorData,
                ]);
        } catch (Throwable $e) {
            throw new DiditUnavailableException(
                'El servicio de verificación de identidad no está disponible temporalmente. Reintentá en unos minutos.'
            );
        }

        if ($response->serverError()) {
            throw new DiditUnavailableException(
                'El servicio de verificación de identidad no está disponible temporalmente. Reintentá en unos minutos.'
            );
        }

        /** @var array<string, mixed> $payload */
        $payload = is_array($response->json()) ? $response->json() : [];

        return $this->normalizarRespuesta($payload, $response->status());
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{
     *     request_id: ?string,
     *     status: string,
     *     warnings: list<array{risk: string, log_type: ?string, short_description: ?string, long_description: ?string}>,
     *     calidad_frente: ?array{focus: ?float, brightness: ?float, resolution: ?float, overall: ?float, is_document_fully_visible: ?bool, brightness_issue: ?string},
     *     calidad_dorso: ?array{focus: ?float, brightness: ?float, resolution: ?float, overall: ?float, is_document_fully_visible: ?bool, brightness_issue: ?string},
     *     numero_documento: ?string,
     *     error_code: ?string,
     *     http_status: int,
     *     raw: array<string, mixed>
     * }
     */
    private function normalizarRespuesta(array $payload, int $httpStatus): array
    {
        $verificacion = is_array($payload['id_verification'] ?? null) ? $payload['id_verification'] : [];

        $errorCode = $this->extraerErrorCode($payload, $verificacion, $httpStatus);
        $status = (string) ($verificacion['status'] ?? ($httpStatus >= 400 ? 'Declined' : 'Unknown'));

        $rawWarnings = $verificacion['warnings'] ?? $payload['warnings'] ?? [];
        $warnings = [];
        if (is_array($rawWarnings)) {
            foreach ($rawWarnings as $item) {
                if (is_array($item) && isset($item['risk'])) {
                    $warnings[] = [
                        'risk' => strtoupper(trim((string) $item['risk'])),
                        'log_type' => isset($item['log_type']) ? (string) $item['log_type'] : null,
                        'short_description' => isset($item['short_description']) ? (string) $item['short_description'] : null,
                        'long_description' => isset($item['long_description']) ? (string) $item['long_description'] : null,
                    ];
                } elseif (is_string($item) && trim($item) !== '') {
                    $warnings[] = [
                        'risk' => strtoupper(trim($item)),
                        'log_type' => 'error',
                        'short_description' => null,
                        'long_description' => null,
                    ];
                }
            }
        }

        if ($errorCode !== null && ! collect($warnings)->contains('risk', $errorCode)) {
            $warnings[] = [
                'risk' => $errorCode,
                'log_type' => 'error',
                'short_description' => isset($payload['detail']) && is_string($payload['detail']) ? $payload['detail'] : null,
                'long_description' => null,
            ];
        }

        $frenteScoreRaw = $verificacion['front_image_quality_score'] ?? $payload['front_image_quality_score'] ?? null;
        $dorsoScoreRaw = $verificacion['back_image_quality_score'] ?? $payload['back_image_quality_score'] ?? null;

        $docNum = $verificacion['document_number']
            ?? $verificacion['personal_number']
            ?? $payload['document_number']
            ?? $payload['personal_number']
            ?? null;

        $numeroDocumento = is_scalar($docNum) && trim((string) $docNum) !== '' ? trim((string) $docNum) : null;

        $requestId = $payload['request_id'] ?? $verificacion['request_id'] ?? null;

        return [
            'request_id' => is_scalar($requestId) && (string) $requestId !== '' ? (string) $requestId : null,
            'status' => $status,
            'warnings' => $warnings,
            'calidad_frente' => $this->normalizarCalidad($frenteScoreRaw),
            'calidad_dorso' => $this->normalizarCalidad($dorsoScoreRaw),
            'numero_documento' => $numeroDocumento,
            'error_code' => $errorCode,
            'http_status' => $httpStatus,
            'raw' => $payload,
        ];
    }

    /**
     * @return ?array{focus: ?float, brightness: ?float, resolution: ?float, overall: ?float, is_document_fully_visible: ?bool, brightness_issue: ?string}
     */
    private function normalizarCalidad(mixed $score): ?array
    {
        if (is_numeric($score)) {
            return [
                'focus' => null,
                'brightness' => null,
                'resolution' => null,
                'overall' => (float) $score,
                'is_document_fully_visible' => null,
                'brightness_issue' => null,
            ];
        }

        if (! is_array($score) || $score === []) {
            return null;
        }

        $focus = $score['focus'] ?? $score['focus_score'] ?? $score['sharpness'] ?? null;
        $brightness = $score['brightness'] ?? $score['brightness_score'] ?? null;
        $resolution = $score['resolution'] ?? $score['resolution_score'] ?? null;
        $overall = $score['overall'] ?? $score['overall_score'] ?? null;
        $fullyVisible = $score['is_document_fully_visible'] ?? $score['fully_visible'] ?? null;
        $brightnessIssue = $score['brightness_issue'] ?? null;

        return [
            'focus' => is_numeric($focus) ? (float) $focus : null,
            'brightness' => is_numeric($brightness) ? (float) $brightness : null,
            'resolution' => is_numeric($resolution) ? (float) $resolution : null,
            'overall' => is_numeric($overall) ? (float) $overall : null,
            'is_document_fully_visible' => is_bool($fullyVisible) ? $fullyVisible : null,
            'brightness_issue' => is_string($brightnessIssue) ? $brightnessIssue : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $verificacion
     */
    private function extraerErrorCode(array $payload, array $verificacion, int $httpStatus): ?string
    {
        $candidates = [
            $payload['error_code'] ?? null,
            $payload['code'] ?? null,
            is_array($payload['error'] ?? null) ? ($payload['error']['code'] ?? $payload['error']['type'] ?? null) : ($payload['error'] ?? null),
            $verificacion['error_code'] ?? null,
            $payload['detail'] ?? null,
        ];

        foreach ($candidates as $candidate) {
            if (! is_string($candidate) || trim($candidate) === '') {
                continue;
            }

            $trimmed = trim($candidate);
            if (preg_match('/^[A-Z0-9_]{4,}$/', strtoupper($trimmed)) === 1) {
                return strtoupper($trimmed);
            }

            if (preg_match('/\b([A-Z][A-Z0-9_]{4,})\b/', $trimmed, $m) === 1) {
                return $m[1];
            }
        }

        if ($httpStatus >= 400 && $httpStatus < 500) {
            return 'COULD_NOT_RECOGNIZE_DOCUMENT';
        }

        return null;
    }
}
