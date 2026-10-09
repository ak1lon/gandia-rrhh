<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\FcmTokenController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    //Route::post('/fcm-token', [FcmTokenController::class, 'store']);
});

Route::post('/fcm-token', [FcmTokenController::class, 'store']);
Route::get('/notifications', [\App\Http\Controllers\Api\NotificationController::class, 'index']);
