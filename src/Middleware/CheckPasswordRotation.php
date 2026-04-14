<?php

namespace Cosmoferrigno\PasswordRotation\Middleware;

use Closure;
use Cosmoferrigno\PasswordRotation\Services\PasswordRotationService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPasswordRotation
{
    public function __construct(
        protected PasswordRotationService $service
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !config('password-rotation.enabled', true)) {
            return $next($request);
        }

        if ($this->isExcluded($request)) {
            return $next($request);
        }

        if ($this->service->needsRotation($user)) {
            $changeRoute = config('password-rotation.change_route', 'password.change');

            // Inertia XHR: restituisce 409 con X-Inertia-Location per forzare
            // una navigazione full-page verso la pagina di cambio password.
            // Per le request Blade classiche basta il redirect 302.
            if ($request->header('X-Inertia')) {
                return response('', 409)
                    ->header('X-Inertia-Location', route($changeRoute));
            }

            return redirect()->route($changeRoute);
        }

        return $next($request);
    }

    protected function isExcluded(Request $request): bool
    {
        $excludedRoutes = config('password-rotation.excluded_routes', []);
        $currentRoute   = $request->route()?->getName();

        return $currentRoute !== null && in_array($currentRoute, $excludedRoutes, true);
    }
}
