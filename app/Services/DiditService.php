<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class DiditService
{
    /**
     * @return array<string, mixed>
     */
    public function verificarDocumento(UploadedFile $frente, UploadedFile $dorso, string $vendorData): array
    {
        $apiKey = (string) config('services.didit.api_key');
        $baseUrl = rtrim((string) config('services.didit.base_url'), '/');

        if ($apiKey === '') {
            throw ValidationException::withMessages([
                'dni_frente' => 'El servicio de verificación de identidad no está configurado.',
            ]);
        }

        $response = Http::withHeaders(['x-api-key' => $apiKey])
            ->timeout(30)
            ->attach('front_image', file_get_contents($frente->getRealPath()), $frente->getClientOriginalName() ?: 'front.jpg')
            ->attach('back_image', file_get_contents($dorso->getRealPath()), $dorso->getClientOriginalName() ?: 'back.jpg')
            ->post("{$baseUrl}/id-verification/", [
                'save_api_request' => 'true',
                'vendor_data' => $vendorData,
            ]);

        if (! $response->successful()) {
            throw ValidationException::withMessages([
                'dni_frente' => 'No se pudo procesar el documento en este momento. Intentá con una foto más clara.',
            ]);
        }

        /** @var array<string, mixed> $payload */
        $payload = $response->json() ?? [];

        return $payload;
    }
}

