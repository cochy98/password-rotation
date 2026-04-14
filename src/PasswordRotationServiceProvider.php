<?php

namespace Cosmoferrigno\PasswordRotation;

use Cosmoferrigno\PasswordRotation\Console\Commands\InstallCommand;
use Cosmoferrigno\PasswordRotation\Middleware\CheckPasswordRotation;
use Cosmoferrigno\PasswordRotation\Services\PasswordRotationService;
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

        // Pubblica gli stub delle migration (con timestamp dinamico via InstallCommand)
        $this->publishes([
            __DIR__.'/../stubs/' => $this->app->basePath('stubs/vendor/password-rotation'),
        ], 'password-rotation-stubs');

        // Registra i comandi Artisan
        if ($this->app->runningInConsole()) {
            $this->commands([InstallCommand::class]);
        }

        // Registra l'alias del middleware
        $router = $this->app->make(\Illuminate\Routing\Router::class);
        $router->aliasMiddleware('password.rotation', CheckPasswordRotation::class);
    }
}
