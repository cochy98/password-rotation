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
     * Calcola e salva la nuova password_expires_at. Da chiamare dopo il cambio password.
     */
    public function markPasswordChanged(): void
    {
        app(PasswordRotationService::class)->markPasswordChanged($this);
    }
}
