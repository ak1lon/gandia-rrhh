<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PushNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
            ->select('id', 'title', 'body', 'reason', 'sender', 'priority', 'image_path', 'notification_date', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn (PushNotification $n) => [
                'id' => $n->id,
                'title' => $n->title,
                'body' => $n->body,
                'reason' => $n->reason,
                'sender' => $n->sender,
                'priority' => $n->priority,
                'image' => $n->image_path ? url(Storage::url($n->image_path)) : null,
                'notification_date' => $n->notification_date?->format('Y-m-d'),
                'created_at' => $n->created_at,
            ]);

        return response()->json($notifications);
    }
}
