<?php

use Cosmoferrigno\PasswordRotation\Http\Controllers\PasswordChangeController;
use Cosmoferrigno\PasswordRotation\Http\Controllers\UserPasswordStatusController;
use Illuminate\Support\Facades\Route;

// Rotte per il cambio password (utente autenticato)
Route::middleware(config('password-rotation.routes.middleware', ['web', 'auth']))
    ->prefix(config('password-rotation.routes.prefix', ''))
    ->group(function () {
        Route::get('/password/change', [PasswordChangeController::class, 'show'])
            ->name('password.change');

        Route::post('/password/change', [PasswordChangeController::class, 'update'])
            ->name('password.change.update');
    });

// Rotte di amministrazione (lista utenti + force reset)
if (config('password-rotation.admin_routes.enabled', true)) {
    Route::middleware(['web', 'auth'])->group(function () {
        Route::get('/password-rotation/users', [UserPasswordStatusController::class, 'index'])
            ->name('password-rotation.users.index');

        Route::post('/password-rotation/users/{user}/force-reset', [UserPasswordStatusController::class, 'forceReset'])
            ->name('password-rotation.users.force-reset');
    });
}
