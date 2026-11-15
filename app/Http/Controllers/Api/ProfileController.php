<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Legajo;
use App\Models\Registro;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Usuario no autenticado.'], 401);
        }

        if ($request->filled('email')) {
            $request->merge(['email' => mb_strtolower(trim((string) $request->input('email')))]);
        }

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'celular' => ['required', 'string', 'max:30'],
            'domicilio' => ['nullable', 'string', 'max:255'],
            'localidad' => ['nullable', 'string', 'max:60'],
            'provincia' => ['nullable', 'string', 'max:60'],
        ]);

        DB::transaction(function () use ($user, $validated): void {
            $user->forceFill([
                'email' => $validated['email'],
                'celular' => trim($validated['celular']),
            ])->save();

            $registro = Registro::where('user_id', $user->id)->latest()->first();
            if ($registro) {
                $registro->email = $validated['email'];
                $registro->telefono = trim($validated['celular']);

                if (array_key_exists('domicilio', $validated)) {
                    $registro->domicilio = $validated['domicilio'];
                }
                if (array_key_exists('localidad', $validated)) {
                    $registro->localidad = $validated['localidad'];
                }
                if (array_key_exists('provincia', $validated)) {
                    $registro->provincia = $validated['provincia'];
                }

                $registro->save();
            }
        });

        $user->refresh();
        $legajo = $user->leg_numero ? Legajo::find($user->leg_numero) : null;
        $registro = Registro::where('user_id', $user->id)->latest()->first();

        $domicilioPartes = array_filter([
            $registro?->domicilio ?? $legajo?->leg_domic,
            $registro?->localidad ?? $legajo?->leg_locali,
            $registro?->provincia,
        ]);
        $domicilio = ! empty($domicilioPartes) ? implode(', ', $domicilioPartes) : '';

        return response()->json([
            'message' => 'Perfil actualizado correctamente',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'legajo' => $user->leg_numero ? (string) $user->leg_numero : ($user->legajo ?? ''),
                'dni' => $user->dni ?? $legajo?->leg_numdoc ?? '',
                'cuil' => $legajo?->leg_cuil ?? '',
                'celular' => $user->celular ?? $registro?->telefono ?? $legajo?->leg_telefono ?? '',
                'cargo' => $user->cargo_sap ?? '',
                'seccion' => $user->seccion ?? '',
                'domicilio' => $domicilio,
                'fecha_ingreso' => $legajo?->leg_fecing?->format('d/m/Y') ?? '',
                'tiene_rubrica' => ! empty($user->rubrica_path),
                'tiene_clave_firma' => ! empty($user->clave_firma),
            ],
        ]);
    }

    public function updateRubrica(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Usuario no autenticado.'], 401);
        }

        $validated = $request->validate([
            'rubrica' => ['required', 'string', 'max:2000000'],
            'clave_firma' => ['nullable', 'string', 'regex:/^\d{6}$/'],
        ]);

        $clean = preg_replace('/^data:image\/png;base64,/i', '', trim((string) $validated['rubrica'])) ?? '';
        $bytes = base64_decode($clean, true);

        if ($bytes === false || ! str_starts_with($bytes, "\x89PNG\r\n\x1a\n")) {
            throw ValidationException::withMessages([
                'rubrica' => 'La rúbrica enviada no es una imagen PNG válida.',
            ]);
        }

        $rubricaPath = "firmas/{$user->id}.png";
        Storage::disk('local')->put($rubricaPath, $bytes);

        $updates = ['rubrica_path' => $rubricaPath];
        if (! empty($validated['clave_firma'])) {
            $updates['clave_firma'] = $validated['clave_firma'];
        }

        $user->forceFill($updates)->save();

        return response()->json([
            'message' => 'Rúbrica guardada correctamente',
            'tiene_rubrica' => true,
            'tiene_clave_firma' => ! empty($user->clave_firma),
        ]);
    }
}
