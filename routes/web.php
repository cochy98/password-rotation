<?php

use Cosmoferrigno\PasswordRotation\Http\Controllers\PasswordChangeController;
use Illuminate\Support\Facades\Route;

Route::middleware(config('password-rotation.routes.middleware', ['web', 'auth']))
    ->prefix(config('password-rotation.routes.prefix', ''))
    ->group(function () {
        Route::get('/password/change', [PasswordChangeController::class, 'show'])
            ->name('password.change');

        Route::post('/password/change', [PasswordChangeController::class, 'update'])
            ->name('password.change.update');
    });
