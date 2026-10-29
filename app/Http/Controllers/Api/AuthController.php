<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
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

        return response()->json(['token' => $token]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json(['message' => 'Sesión cerrada']);
    }
}

