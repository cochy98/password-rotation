<?php

namespace Cosmoferrigno\PasswordRotation\Http\Controllers;

use Cosmoferrigno\PasswordRotation\Services\PasswordRotationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;

class UserPasswordStatusController extends Controller
{
    public function __construct(
        protected PasswordRotationService $service,
    ) {}

    public function index(Request $request): mixed
    {
        Gate::authorize(config('password-rotation.gate', 'manage-password-rotation'));

        /** @var class-string<\Illuminate\Database\Eloquent\Model> $userModel */
        $userModel = config('auth.providers.users.model', 'App\\Models\\User');

        $rotationDays = $this->service->getRotationDays();

        $colName  = config('password-rotation.user_columns.name', 'name');
        $colEmail = config('password-rotation.user_columns.email', 'email');
        $sortBy   = (array) config('password-rotation.user_columns.sort_by', []);

        $query = $userModel::query();
        foreach ($sortBy as $col) {
            $query->orderBy($col);
        }

        $users = $query
            ->get()
            ->map(function ($user) use ($rotationDays, $colName, $colEmail) {
                $expiresAt       = $this->service->resolveExpiresAt($user);
                $daysUntilExpiry = $this->service->daysUntilExpiry($user);
                $needsRotation   = $this->service->needsRotation($user);

                return [
                    'id'                => $user->getKey(),
                    'name'              => $user->getAttribute($colName) ?? '',
                    'email'             => $user->getAttribute($colEmail) ?? '',
                    'expires_at'        => $expiresAt->toIso8601String(),
                    'days_until_expiry' => $daysUntilExpiry === PHP_INT_MAX ? null : $daysUntilExpiry,
                    'needs_rotation'    => $needsRotation,
                    'rotation_disabled' => $rotationDays === 0,
                ];
            });

        $component = config('password-rotation.admin_routes.inertia_component', 'PasswordRotation/Users');

        if (class_exists(\Inertia\Inertia::class)) {
            return \Inertia\Inertia::render($component, [
                'users'        => $users,
                'rotationDays' => $rotationDays,
            ]);
        }

        return view('password-rotation::users', [
            'users'        => $users,
            'rotationDays' => $rotationDays,
        ]);
    }

    public function forceReset(Request $request, int|string $userId): RedirectResponse
    {
        Gate::authorize(config('password-rotation.gate', 'manage-password-rotation'));

        /** @var class-string<\Illuminate\Database\Eloquent\Model> $userModel */
        $userModel = config('auth.providers.users.model', 'App\\Models\\User');

        $user = $userModel::findOrFail($userId);

        // Imposta la scadenza a ieri per forzare il cambio al prossimo login.
        $user->forceFill(['password_expires_at' => now()->subDay()])->save();

        $nameCol = config('password-rotation.user_columns.name', 'name');

        return back()->with('status', 'Cambio password forzato per ' . ($user->getAttribute($nameCol) ?? $user->getKey()) . '.');
    }
}
