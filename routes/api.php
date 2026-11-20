<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\FcmTokenController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\V1\RegistroController;

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
    Route::post('/user/foto-perfil', [ProfileController::class, 'updateFotoPerfil']);
    Route::get('/user/foto-perfil', [ProfileController::class, 'showFotoPerfil']);
    Route::get('/user', [ProfileController::class, 'show']);
});

Route::get('/notifications', [\App\Http\Controllers\Api\NotificationController::class, 'index']);
