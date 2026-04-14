<?php

namespace Cosmoferrigno\PasswordRotation\Http\Controllers;

use Cosmoferrigno\PasswordRotation\Http\Requests\ChangePasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;

class PasswordChangeController extends Controller
{
    public function show(Request $request): mixed
    {
        $daysUntilExpiry = $request->user()->daysUntilPasswordExpiry();

        if (class_exists(\Inertia\Inertia::class)) {
            return \Inertia\Inertia::render('Auth/ChangePassword', [
                'daysUntilExpiry' => $daysUntilExpiry,
            ]);
        }

        return view('password-rotation::change-password', [
            'daysUntilExpiry' => $daysUntilExpiry,
        ]);
    }

    public function update(ChangePasswordRequest $request): RedirectResponse
    {
        $user = $request->user();

        $user->forceFill([
            'password' => Hash::make($request->validated()['password']),
        ])->save();

        $user->markPasswordChanged();

        return redirect()->intended(config('password-rotation.redirect_after_change', '/'))
            ->with('status', 'Password aggiornata con successo.');
    }
}
