<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\GoogleOAuthController;
use App\Http\Controllers\Auth\OnboardingController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RequiredPasswordChangeController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');
});

// Google OAuth routes are outside the 'guest' group so the callback
// is still reachable after the user is authenticated during the flow.
Route::get('auth/google/redirect', [GoogleOAuthController::class, 'redirect'])
    ->name('google.redirect');

Route::get('auth/google/callback', [GoogleOAuthController::class, 'callback'])
    ->name('google.callback');

Route::middleware(['auth', 'force-password-change'])->group(function () {
    Route::get('password/change-required', [RequiredPasswordChangeController::class, 'edit'])
        ->name('password.change-required');

    Route::put('password/change-required', [RequiredPasswordChangeController::class, 'update'])
        ->name('password.change-required.update');

    // Onboarding: municipality & cooperative selection (after signup)
    Route::get('onboarding', [OnboardingController::class, 'show'])
        ->name('onboarding.show');

    Route::post('onboarding', [OnboardingController::class, 'store'])
        ->name('onboarding.store');

    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
