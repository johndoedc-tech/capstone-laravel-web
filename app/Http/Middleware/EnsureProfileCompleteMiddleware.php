<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfileCompleteMiddleware
{
    /**
     * Routes that remain accessible while profile completion is pending.
     *
     * @var array<int, string>
     */
    private array $allowedRoutes = [
        'onboarding.show',
        'onboarding.store',
        'password.change-required',
        'password.change-required.update',
        'logout',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Not authenticated or is admin — skip this check.
        if (! $user || $user->isAdmin()) {
            return $next($request);
        }

        // Profile is already complete — carry on.
        if ($user->hasCompletedOnboarding()) {
            return $next($request);
        }

        // Allow onboarding-related and essential routes through.
        if ($request->routeIs($this->allowedRoutes)) {
            return $next($request);
        }

        return redirect()->route('onboarding.show');
    }
}
