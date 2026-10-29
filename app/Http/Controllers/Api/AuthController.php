<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Legajo;
use App\Models\Registro;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::query()
            ->where('email', mb_strtolower((string) $request->validated('email')))
            ->first();

        if ($user === null || ! Hash::check((string) $request->validated('password'), $user->password)) {
            throw ValidationException::withMessages(['email' => 'Credenciales incorrectas.']);
        }

        $token = $user->createToken((string) ($request->validated('device_name') ?? 'gandia-app'))->plainTextToken;

        $legajo = $user->leg_numero ? Legajo::find($user->leg_numero) : null;
        $registro = $user->id ? Registro::where('user_id', $user->id)->latest()->first() : null;

        $domicilioPartes = array_filter([
            $registro?->domicilio ?? $legajo?->leg_domic,
            $registro?->localidad ?? $legajo?->leg_locali,
            $registro?->provincia,
        ]);
        $domicilio = !empty($domicilioPartes) ? implode(', ', $domicilioPartes) : null;

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'legajo' => $user->leg_numero ? (string) $user->leg_numero : ($user->legajo ?? ''),
                'dni' => $user->dni ?? $legajo?->leg_numdoc ?? '',
                'cuil' => $legajo?->leg_cuil ?? '',
                'celular' => $user->celular ?? $registro?->telefono ?? $legajo?->leg_telefono ?? '',
                'cargo' => $user->cargo_sap ?? 'Repositor de Salón',
                'seccion' => $user->seccion ?? 'Cordiez Villa Dolores (Suc. 14)',
                'domicilio' => $domicilio ?? 'Belgrano 245, Villa Dolores, Córdoba',
                'fecha_ingreso' => $legajo?->leg_fecing?->format('d/m/Y') ?? '14/03/2018',
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json(['message' => 'Sesión cerrada']);
    }
}
