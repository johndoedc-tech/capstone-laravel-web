<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleOAuthController extends Controller
{
    /**
     * Redirect the user to Google's OAuth page.
     */
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')
            ->with(['prompt' => 'select_account'])
            ->redirect();
    }

    /**
     * Handle the Google OAuth callback.
     */
    public function callback(Request $request): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable) {
            return redirect()->route('login')->with('error', 'Google sign-in could not be completed. Please try again.');
        }

        $email = $googleUser->getEmail();

        if (! $email) {
            return redirect()->route('login')->with('error', 'Google did not provide an email address for this account.');
        }

        $user = User::where('google_id', $googleUser->getId())->first()
            ?? User::where('email', $email)->first();

        if ($user && $user->isAdmin()) {
            return redirect()->route('login')->with('error', 'Google sign-in is only available for farmer accounts.');
        }

        if ($user) {
            $user->forceFill([
                'google_id' => $user->google_id ?: $googleUser->getId(),
                'avatar' => $googleUser->getAvatar() ?: $user->avatar,
                'email_verified_at' => $user->email_verified_at ?? now(),
            ])->save();
        } else {
            $user = User::forceCreate([
                'name' => $googleUser->getName() ?: Str::before($email, '@'),
                'email' => $email,
                'password' => Str::random(40),
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
                'role' => 'farmer',
                'email_verified_at' => now(),
            ]);
        }

        Auth::login($user);
        $request->session()->regenerate();

        if ($user->must_change_password) {
            return redirect()->route('password.change-required');
        }

        return redirect()->intended(route('onboarding.show', absolute: false));
    }
}
