<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Contracts\User as SocialiteUserContract;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Tests\TestCase;

class GoogleAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_access_google_redirect_route(): void
    {
        $provider = Mockery::mock(Provider::class);
        Socialite::shouldReceive('driver')->once()->with('google')->andReturn($provider);
        $provider->shouldReceive('redirect')->once()->andReturn(new RedirectResponse('https://accounts.google.com/o/oauth2/auth'));

        $response = $this->get(route('google.redirect'));

        $response->assertRedirect('https://accounts.google.com/o/oauth2/auth');
    }

    public function test_new_google_user_is_created_as_a_farmer(): void
    {
        $this->mockGoogleUser(
            id: 'google-new-user',
            email: 'farmer@example.com',
            name: 'Farmer New',
            avatar: 'https://example.com/avatar.png',
        );

        $response = $this->get(route('google.callback'));

        $user = User::where('email', 'farmer@example.com')->first();

        $this->assertNotNull($user);
        $this->assertSame('farmer', $user->role);
        $this->assertSame('google-new-user', $user->google_id);
        $this->assertSame('https://example.com/avatar.png', $user->avatar);
        $this->assertNotNull($user->email_verified_at);
        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_existing_farmer_is_auto_linked_by_email_without_duplicate_accounts(): void
    {
        $user = User::factory()->unverified()->create([
            'email' => 'existing-farmer@example.com',
            'role' => 'farmer',
            'google_id' => null,
            'avatar' => null,
        ]);

        $this->mockGoogleUser(
            id: 'google-existing-user',
            email: 'existing-farmer@example.com',
            name: 'Existing Farmer',
            avatar: 'https://example.com/linked-avatar.png',
        );

        $response = $this->get(route('google.callback'));

        $user->refresh();

        $this->assertSame(1, User::where('email', 'existing-farmer@example.com')->count());
        $this->assertSame('google-existing-user', $user->google_id);
        $this->assertSame('https://example.com/linked-avatar.png', $user->avatar);
        $this->assertNotNull($user->email_verified_at);
        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_existing_google_linked_farmer_can_sign_in_by_google_id(): void
    {
        $user = User::factory()->create([
            'email' => 'linked-farmer@example.com',
            'role' => 'farmer',
            'google_id' => 'google-linked-user',
            'avatar' => 'https://example.com/old-avatar.png',
        ]);

        $this->mockGoogleUser(
            id: 'google-linked-user',
            email: 'different-email@example.com',
            name: 'Linked Farmer',
            avatar: 'https://example.com/new-avatar.png',
        );

        $response = $this->get(route('google.callback'));

        $user->refresh();

        $this->assertSame('linked-farmer@example.com', $user->email);
        $this->assertSame('https://example.com/new-avatar.png', $user->avatar);
        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_existing_admin_with_matching_email_is_denied_google_sign_in(): void
    {
        User::factory()->create([
            'email' => 'admin@example.com',
            'role' => 'admin',
            'google_id' => null,
        ]);

        $this->mockGoogleUser(
            id: 'google-admin-user',
            email: 'admin@example.com',
            name: 'Admin User',
            avatar: 'https://example.com/admin.png',
        );

        $response = $this->get(route('google.callback'));

        $this->assertGuest();
        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error', 'Google sign-in is only available for farmer accounts.');
    }

    public function test_callback_failure_redirects_back_with_a_friendly_error(): void
    {
        $provider = Mockery::mock(Provider::class);
        Socialite::shouldReceive('driver')->once()->with('google')->andReturn($provider);
        $provider->shouldReceive('user')->once()->andThrow(new Exception('OAuth failed'));

        $response = $this->get(route('google.callback'));

        $this->assertGuest();
        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error', 'Google sign-in could not be completed. Please try again.');
    }

    public function test_auth_pages_render_google_button_and_error_feedback(): void
    {
        $this->withSession(['error' => 'Google sign-in failed.'])
            ->get(route('login'))
            ->assertOk()
            ->assertSee('Continue with Google')
            ->assertSee('Google sign-in failed.');

        $this->withSession(['error' => 'Google sign-in failed.'])
            ->get(route('register'))
            ->assertOk()
            ->assertSee('Continue with Google')
            ->assertSee('Google sign-in failed.');
    }

    private function mockGoogleUser(string $id, string $email, string $name, ?string $avatar = null): void
    {
        $provider = Mockery::mock(Provider::class);
        $googleUser = Mockery::mock(SocialiteUserContract::class);

        Socialite::shouldReceive('driver')->once()->with('google')->andReturn($provider);

        $provider->shouldReceive('user')->once()->andReturn($googleUser);
        $googleUser->shouldReceive('getId')->andReturn($id);
        $googleUser->shouldReceive('getEmail')->andReturn($email);
        $googleUser->shouldReceive('getName')->andReturn($name);
        $googleUser->shouldReceive('getAvatar')->andReturn($avatar);
    }
}
