<?php

namespace App\Http\Controllers;

use App\Models\PushNotification;
use App\Models\User;
use Illuminate\Http\Request;

class PushNotificationController extends Controller
{
    public function index()
    {
        $notifications = PushNotification::latest()->get();
        $employees = User::where('role', 'empleado')->get();
        
        return view('push-notifications.index', compact('notifications', 'employees'));
    }

    public function store(Request $request, \App\Services\FirebaseNotificationService $firebaseService)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'recipients' => 'required|array',
            'recipients.*' => 'exists:users,id',
        ]);
        
        $notification = PushNotification::create([
            'title' => $validated['title'],
            'body' => $validated['body'],
            'recipients' => $validated['recipients'],
            'status' => 'Procesando'
        ]);

        $users = User::whereIn('id', $validated['recipients'])
            ->whereNotNull('fcm_token')
            ->get();

        $responses = [];
        $hasErrors = false;

        foreach ($users as $user) {
            $response = $firebaseService->sendToDevice(
                $user->fcm_token,
                $validated['title'],
                $validated['body']
            );
            
            $responses[$user->id] = $response;
            
            if (!$response['success']) {
                $hasErrors = true;
            }
        }

        $notification->update([
            'status' => count($users) == 0 ? 'Sin destinatarios válidos' : ($hasErrors ? 'Enviado con errores' : 'Enviado correctamente'),
            'firebase_responses' => $responses
        ]);

        return redirect()->route('cola-revision.index')->with('success', 'Notificación push enviada correctamente a los dispositivos registrados.');
    }
}
