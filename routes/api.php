<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\FcmTokenController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\V1\RegistroController;
use App\Models\Legajo;
use App\Models\Registro;

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');

Route::prefix('v1/registro')->middleware('throttle:20,1')->group(function () {
    Route::post('/verificar', [RegistroController::class, 'verificar']);
    Route::post('/iniciar', [RegistroController::class, 'iniciar']);
    Route::put('/{registro}/datos-personales', [RegistroController::class, 'datosPersonales'])->whereNumber('registro');
    Route::post('/{registro}/verificar-dni', [RegistroController::class, 'verificarDocumento'])->whereNumber('registro');
    Route::post('/{registro}/dni', [RegistroController::class, 'verificarDocumento'])->whereNumber('registro');
    Route::get('/{registro}/dni/estado', [RegistroController::class, 'estadoDni'])->whereNumber('registro');
    Route::post('/{registro}/biometria', [RegistroController::class, 'biometria'])->whereNumber('registro');
    Route::post('/{registro}/confirmar', [RegistroController::class, 'confirmar'])->whereNumber('registro');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/fcm-token', [FcmTokenController::class, 'store']);
    Route::put('/user/profile', [ProfileController::class, 'update']);
    Route::post('/user/rubrica', [ProfileController::class, 'updateRubrica']);

    Route::get('/user', function (Request $request) {
        $user = $request->user();
        $legajo = $user->leg_numero ? Legajo::find($user->leg_numero) : null;
        $registro = Registro::where('user_id', $user->id)->latest()->first();

        $domicilioPartes = array_filter([
            $registro?->domicilio ?? $legajo?->leg_domic,
            $registro?->localidad ?? $legajo?->leg_locali,
            $registro?->provincia,
        ]);
        $domicilio = !empty($domicilioPartes) ? implode(', ', $domicilioPartes) : '';

        return response()->json([
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
        ]);
    });
});

Route::get('/notifications', [\App\Http\Controllers\Api\NotificationController::class, 'index']);
