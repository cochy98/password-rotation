<?php

namespace Cosmoferrigno\PasswordRotation\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static bool needsRotation(\Illuminate\Contracts\Auth\Authenticatable $user)
 * @method static int daysUntilExpiry(\Illuminate\Contracts\Auth\Authenticatable $user)
 * @method static \Carbon\Carbon resolveExpiresAt(\Illuminate\Contracts\Auth\Authenticatable $user)
 * @method static int getRotationDays()
 * @method static void markPasswordChanged(\Illuminate\Contracts\Auth\Authenticatable $user)
 *
 * @see \Cosmoferrigno\PasswordRotation\Services\PasswordRotationService
 */
class PasswordRotation extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'password-rotation';
    }
}
