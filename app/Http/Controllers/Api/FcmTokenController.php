<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class FcmTokenController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string|max:500',
        ]);

        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Usuario no autenticado.'], 401);
        }

        $user->update([
            'fcm_token' => $request->string('fcm_token')->toString(),
        ]);

        return response()->json(['message' => 'Token actualizado correctamente.']);
    }
}
