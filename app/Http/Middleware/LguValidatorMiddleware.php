<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class LguValidatorMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return redirect()->route('login')->with('error', 'Please login to access this page.');
        }

        $user = Auth::user();

        if (! $user->isLguValidator()) {
            $route = $user->isAdmin() ? 'admin.dashboard' : 'dashboard';

            return redirect()->route($route)->with('error', 'You do not have permission to access the LGU validation area.');
        }

        if (! $user->is_active) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('error', 'Your LGU validator account is inactive. Please contact the DA administrator.');
        }

        return $next($request);
    }
}
