<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\FcmTokenController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\V1\RegistroController;

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');

Route::prefix('v1/registro')->middleware('throttle:20,1')->group(function () {
    Route::post('/verificar', [RegistroController::class, 'verificar']);
    Route::post('/iniciar', [RegistroController::class, 'iniciar']);
    Route::put('/{registro}/datos-personales', [RegistroController::class, 'datosPersonales'])->whereNumber('registro');
    Route::post('/{registro}/confirmar', [RegistroController::class, 'confirmar'])->whereNumber('registro');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    //Route::post('/fcm-token', [FcmTokenController::class, 'store']);
});

Route::post('/fcm-token', [FcmTokenController::class, 'store']);
Route::get('/notifications', [\App\Http\Controllers\Api\NotificationController::class, 'index']);
