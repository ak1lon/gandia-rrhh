<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PushNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = \App\Models\User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([]);
        }

        $notifications = PushNotification::whereIn('status', ['Enviado correctamente', 'Enviado con errores'])
            ->whereJsonContains('recipients', (int) $user->id)
            ->select('title', 'body', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($notifications);
    }
}
