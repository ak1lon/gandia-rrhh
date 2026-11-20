<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Registro;
use App\Support\UserPayload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Usuario no autenticado.'], 401);
        }

        return response()->json(UserPayload::format($user));
    }

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

        return response()->json([
            'message' => 'Perfil actualizado correctamente',
            'user' => UserPayload::format($user),
        ]);
    }

    public function updateFotoPerfil(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Usuario no autenticado.'], 401);
        }

        $request->validate([
            'foto' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $file = $request->file('foto');
        if (! $file instanceof UploadedFile) {
            throw ValidationException::withMessages([
                'foto' => 'No se recibió una imagen válida.',
            ]);
        }

        $ext = $file->extension() ?: 'jpg';
        $filename = "{$user->id}_" . now()->timestamp . '_' . bin2hex(random_bytes(4)) . ".{$ext}";
        $newPath = Storage::disk('local')->putFileAs('perfiles', $file, $filename);

        if ($newPath === false) {
            return response()->json(['message' => 'No se pudo almacenar la foto de perfil.'], 500);
        }

        $oldPath = $user->foto_perfil_path;

        $user->forceFill([
            'foto_perfil_path' => $newPath,
            'foto_perfil_updated_at' => now(),
        ])->save();

        if (! empty($oldPath) && $oldPath !== $newPath) {
            Storage::disk('local')->delete($oldPath);
        }

        $user->refresh();

        return response()->json([
            'message' => 'Foto de perfil actualizada correctamente',
            'user' => UserPayload::format($user),
        ]);
    }

    public function showFotoPerfil(Request $request): StreamedResponse|JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Usuario no autenticado.'], 401);
        }

        $path = $user->foto_perfil_path;
        if (empty($path) || ! Storage::disk('local')->exists($path)) {
            return response()->json(['message' => 'Foto de perfil no encontrada.'], 404);
        }

        $mimeType = Storage::disk('local')->mimeType($path) ?: 'image/jpeg';

        return Storage::disk('local')->response($path, null, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'private, max-age=86400',
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
