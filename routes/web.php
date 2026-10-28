<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('cola-revision.index');
    }
    return view('login');
})->name('login');

Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();
        return redirect()->intended(route('cola-revision.index'));
    }

    return back()->withErrors([
        'email' => 'Las credenciales proporcionadas no coinciden.',
    ])->onlyInput('email');
});

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
})->name('logout');

Route::get('/dashboard', function () {
    return redirect()->route('cola-revision.index');
})->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::get('/cola-revision', [\App\Http\Controllers\PushNotificationController::class, 'index'])->name('cola-revision.index');
    Route::post('/cola-revision', [\App\Http\Controllers\PushNotificationController::class, 'store'])->name('cola-revision.store');
});
