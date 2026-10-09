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

        $notifications = PushNotification::where('status', 'enviado')
            ->whereJsonContains('recipients', $request->email)
            ->select('title', 'body as contenido', 'created_at as fecha')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($notifications);
    }
}
