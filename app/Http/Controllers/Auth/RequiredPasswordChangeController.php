<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RequiredPasswordChangeController extends Controller
{
    /**
     * Display the forced password change form.
     */
    public function edit(Request $request): View|RedirectResponse
    {
        if (! $request->user()?->must_change_password) {
            return $this->redirectFor($request->user());
        }

        return view('auth.password-change-required');
    }

    /**
     * Update the password for a user who is required to change it.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $user->forceFill([
            'password' => $validated['password'],
            'must_change_password' => false,
            'remember_token' => Str::random(60),
        ])->save();

        $request->session()->regenerate();

        return $this->redirectFor($user)->with('status', 'password-updated');
    }

    /**
     * Redirect to the appropriate home route for the user.
     */
    private function redirectFor(?object $user): RedirectResponse
    {
        if ($user && method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('dashboard');
    }
}
