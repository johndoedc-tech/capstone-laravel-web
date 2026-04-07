<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChangeMiddleware
{
    /**
     * The route names that remain accessible while a forced password change is pending.
     *
     * @var array<int, string>
     */
    private array $allowedRoutes = [
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

        if (! $user || ! $user->must_change_password) {
            return $next($request);
        }

        if ($request->routeIs($this->allowedRoutes)) {
            return $next($request);
        }

        return redirect()->route('password.change-required');
    }
}
