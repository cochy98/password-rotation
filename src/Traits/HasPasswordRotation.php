<?php

namespace Cosmoferrigno\PasswordRotation\Traits;

use Carbon\Carbon;
use Cosmoferrigno\PasswordRotation\Services\PasswordRotationService;

trait HasPasswordRotation
{
    /**
     * Aggiunge password_expires_at ai cast e al fillable al momento dell'inizializzazione.
     */
    public function initializeHasPasswordRotation(): void
    {
        $this->casts['password_expires_at'] = 'datetime';
        $this->fillable[]                   = 'password_expires_at';
    }

    /**
     * Indica se la password necessita di rinnovo.
     */
    public function needsPasswordRotation(): bool
    {
        return app(PasswordRotationService::class)->needsRotation($this);
    }

    /**
     * Giorni rimanenti prima della scadenza (PHP_INT_MAX = rotazione disabilitata).
     */
    public function daysUntilPasswordExpiry(): int
    {
        return app(PasswordRotationService::class)->daysUntilExpiry($this);
    }

    /**
     * Data di scadenza effettiva della password.
     */
    public function passwordExpiresAt(): Carbon
    {
        return app(PasswordRotationService::class)->resolveExpiresAt($this);
    }

    /**
     * Intercetta qualsiasi salvataggio del modello: se il campo `password` è dirty
     * (cambio avvenuto tramite qualsiasi canale), aggiorna password_expires_at
     * nello stesso write, senza un ulteriore save().
     */
    public static function bootHasPasswordRotation(): void
    {
        static::saving(function ($user): void {
            if (! $user->isDirty('password')) {
                return;
            }

            $service      = app(PasswordRotationService::class);
            $rotationDays = $service->getRotationDays();

            $user->password_expires_at = $rotationDays > 0
                ? now()->addDays($rotationDays)
                : null;
        });
    }

    /**
     * Calcola e salva la nuova password_expires_at.
     * Utile per reset espliciti (es. admin che forza il rinnovo) indipendentemente
     * da un cambio password.
     */
    public function markPasswordChanged(): void
    {
        app(PasswordRotationService::class)->markPasswordChanged($this);
    }
}
