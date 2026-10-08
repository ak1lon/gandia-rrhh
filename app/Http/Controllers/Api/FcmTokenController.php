<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FcmTokenController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string',
            'email' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();
        
        if ($user) {
            $user->update([
                'fcm_token' => $request->fcm_token,
            ]);

            return response()->json(['message' => 'Token actualizado correctamente.']);
        }

        return response()->json(['message' => 'Usuario no autenticado.'], 401);
    }
}
