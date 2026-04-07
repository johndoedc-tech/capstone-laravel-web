<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    public function test_password_help_screen_can_be_rendered(): void
    {
        $response = $this->get(route('password.request'));

        $response
            ->assertOk()
            ->assertSee('Password recovery is handled by an administrator.')
            ->assertDontSee('Email Password Reset Link');
    }

    public function test_password_help_post_route_is_not_available(): void
    {
        $response = $this->post('/forgot-password', [
            'email' => 'farmer@example.com',
        ]);

        $response->assertStatus(405);
    }
}
