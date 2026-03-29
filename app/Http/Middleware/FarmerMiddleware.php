<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class FarmerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please login to access this page.');
        }

        if (!Auth::user()->isFarmer()) {
            $redirectRoute = Auth::user()->isAdmin() ? 'admin.dashboard' : 'dashboard';

            return redirect()->route($redirectRoute)->with('error', 'You do not have permission to access the farmer area.');
        }

        return $next($request);
    }
}
