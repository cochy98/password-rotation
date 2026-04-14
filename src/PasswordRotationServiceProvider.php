<?php

namespace Cosmoferrigno\PasswordRotation;

use Cosmoferrigno\PasswordRotation\Middleware\CheckPasswordRotation;
use Cosmoferrigno\PasswordRotation\Services\PasswordRotationService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

/**
 * @property \Illuminate\Foundation\Application $app
 */
class PasswordRotationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/password-rotation.php',
            'password-rotation'
        );

        // Registra con alias stringa per un binding più esplicito e testabile.
        // La Facade usa questo stesso alias come accessor.
        $this->app->singleton('password-rotation', PasswordRotationService::class);
    }

    public function boot(): void
    {
        // Pubblica la configurazione
        $this->publishes([
            __DIR__.'/../config/password-rotation.php' => config_path('password-rotation.php'),
        ], 'password-rotation-config');

        // Pubblica la migration
        $this->publishes([
            __DIR__.'/../stubs/add_password_expires_at_to_users_table.php.stub' 
                => database_path('migrations/' . date('Y_m_d_His') . '_add_password_expires_at_to_users_table.php'),
        ], 'password-rotation-migrations');

        // Pubblica il componente React/Inertia per il cambio password (utente)
        $this->publishes([
            __DIR__.'/../stubs/ChangePassword.tsx.stub' => resource_path('js/Pages/Auth/ChangePassword.tsx'),
        ], 'password-rotation-react');

        // Pubblica il componente React/Inertia per la gestione admin
        $this->publishes([
            __DIR__.'/../stubs/PasswordRotationUsers.tsx.stub' => resource_path('js/Pages/PasswordRotation/Users.tsx'),
        ], 'password-rotation-react');

        // Pubblica la Blade view (override opzionale)
        $this->publishes([
            __DIR__.'/../resources/views/' => resource_path('views/vendor/password-rotation'),
        ], 'password-rotation-views');

        // Registra le view del package con namespace 'password-rotation::'
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'password-rotation');

        // Registra le route del package (disabilitabile via config)
        if (config('password-rotation.routes.enabled', true)) {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        }

        // Registra l'alias del middleware
        $router = $this->app->make(\Illuminate\Routing\Router::class);
        $router->aliasMiddleware('password.rotation', CheckPasswordRotation::class);

        // Definisce il Gate di gestione admin (l'host app può ridefinirlo).
        // Default: ammette utenti con campo is_admin === true.
        $gateName = config('password-rotation.gate', 'manage-password-rotation');
        if (! Gate::has($gateName)) {
            Gate::define($gateName, static function ($user) use ($gateName): bool {
                if(method_exists($user, 'hasPermission')) {
                    return $user->hasPermission($gateName);
                }
                return false;
            });
        }
    }
}
