<?php

namespace Cosmoferrigno\PasswordRotation\Services;

use Carbon\Carbon;
use Illuminate\Contracts\Auth\Authenticatable;

class PasswordRotationService
{
    /**
     * Verifica se l'utente deve cambiare la password.
     */
    public function needsRotation(Authenticatable $user): bool
    {
        $rotationDays = $this->getRotationDays();

        // 0 = rotazione disabilitata
        if ($rotationDays === 0) {
            return false;
        }

        return $this->resolveExpiresAt($user)->isPast();
    }

    /**
     * Giorni rimanenti prima della scadenza (0 = scaduta, PHP_INT_MAX = disabilitata).
     */
    public function daysUntilExpiry(Authenticatable $user): int
    {
        if ($this->getRotationDays() === 0) {
            return PHP_INT_MAX;
        }

        return max(0, (int) now()->diffInDays($this->resolveExpiresAt($user), false));
    }

    /**
     * Risolve la data di scadenza effettiva per l'utente.
     *
     * - Se password_expires_at è valorizzata, la usa direttamente.
     * - Altrimenti fallback: created_at + rotation_days.
     */
    public function resolveExpiresAt(Authenticatable $user): Carbon
    {
        $expiresAt = $user->password_expires_at;

        if ($expiresAt) {
            return $expiresAt instanceof Carbon ? $expiresAt : Carbon::parse($expiresAt);
        }

        return $user->created_at->copy()->addDays($this->getRotationDays());
    }

    /**
     * Legge il numero di giorni di rotazione dalla configurazione.
     */
    public function getRotationDays(): int
    {
        return (int) config('password-rotation.days', 90);
    }

    /**
     * Aggiorna password_expires_at calcolando la nuova scadenza da oggi.
     * Da chiamare dopo ogni cambio password riuscito.
     */
    public function markPasswordChanged(Authenticatable $user): void
    {
        $rotationDays = $this->getRotationDays();
        $expiresAt    = $rotationDays > 0 ? now()->addDays($rotationDays) : null;

        $user->forceFill(['password_expires_at' => $expiresAt])->save();
    }
}
