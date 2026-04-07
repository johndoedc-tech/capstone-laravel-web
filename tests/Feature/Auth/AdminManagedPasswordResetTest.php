<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminManagedPasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_reset_another_farmers_password_and_invalidate_sessions(): void
    {
        Config::set('session.driver', 'database');
        Config::set('session.table', 'sessions');

        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $farmer = User::factory()->create([
            'role' => 'farmer',
        ]);

        $oldRememberToken = $farmer->remember_token;

        DB::table('sessions')->insert([
            'id' => 'target-session',
            'user_id' => $farmer->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'payload' => 'payload',
            'last_activity' => now()->timestamp,
        ]);

        $response = $this->actingAs($admin)->put(route('admin.users.password.reset', $farmer), [
            'new_password' => 'TempPassword123!',
            'new_password_confirmation' => 'TempPassword123!',
        ]);

        $farmer->refresh();

        $response->assertRedirect(route('admin.users.index'));
        $this->assertTrue(Hash::check('TempPassword123!', $farmer->password));
        $this->assertTrue($farmer->must_change_password);
        $this->assertNotSame($oldRememberToken, $farmer->remember_token);
        $this->assertDatabaseMissing('sessions', ['id' => 'target-session']);
        $this->assertDatabaseHas('admin_activity_logs', [
            'actor_id' => $admin->id,
            'subject_user_id' => $farmer->id,
            'action' => 'password_reset',
        ]);
    }

    public function test_admin_can_reset_another_admins_password(): void
    {
        $superAdmin = User::factory()->create([
            'role' => 'admin',
        ]);

        $otherAdmin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($superAdmin)->put(route('admin.users.password.reset', $otherAdmin), [
            'new_password' => 'AdminTemp123!',
            'new_password_confirmation' => 'AdminTemp123!',
        ]);

        $otherAdmin->refresh();

        $response->assertRedirect(route('admin.users.index'));
        $this->assertTrue(Hash::check('AdminTemp123!', $otherAdmin->password));
        $this->assertTrue($otherAdmin->must_change_password);
    }

    public function test_logged_in_admin_cannot_reset_their_own_password_through_admin_route(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $oldPasswordHash = $admin->password;

        $response = $this->actingAs($admin)->put(route('admin.users.password.reset', $admin), [
            'new_password' => 'NewOwnPassword123!',
            'new_password_confirmation' => 'NewOwnPassword123!',
        ]);

        $admin->refresh();

        $response->assertRedirect(route('admin.users.index'));
        $this->assertSame($oldPasswordHash, $admin->password);
        $this->assertFalse($admin->must_change_password);
        $this->assertDatabaseCount('admin_activity_logs', 0);
    }

    public function test_flagged_user_is_redirected_to_required_password_change_on_login(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('TempPassword123!'),
            'must_change_password' => true,
        ]);

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'TempPassword123!',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('password.change-required'));
    }

    public function test_flagged_user_cannot_access_protected_routes_until_password_is_changed(): void
    {
        $user = User::factory()->create([
            'must_change_password' => true,
        ]);

        $response = $this->actingAs($user)->get(route('profile.edit'));

        $response->assertRedirect(route('password.change-required'));
    }

    public function test_completing_required_password_change_clears_flag_and_restores_normal_redirect(): void
    {
        $user = User::factory()->create([
            'role' => 'farmer',
            'must_change_password' => true,
            'remember_token' => 'old-token',
        ]);

        $response = $this->actingAs($user)->put(route('password.change-required.update'), [
            'password' => 'FreshPassword123!',
            'password_confirmation' => 'FreshPassword123!',
        ]);

        $user->refresh();

        $response->assertRedirect(route('dashboard'));
        $this->assertFalse($user->must_change_password);
        $this->assertTrue(Hash::check('FreshPassword123!', $user->password));
        $this->assertNotSame('old-token', $user->remember_token);
    }

    public function test_admin_activity_feed_displays_password_reset_events(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $targetUser = User::factory()->create([
            'name' => 'Reset Target',
        ]);

        $this->actingAs($admin)->put(route('admin.users.password.reset', $targetUser), [
            'new_password' => 'TempPassword123!',
            'new_password_confirmation' => 'TempPassword123!',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.activities.index', ['activity_type' => 'admin_actions']));

        $response
            ->assertOk()
            ->assertSee('Admin Actions')
            ->assertSee("Reset a user's password")
            ->assertSee('Reset password for Reset Target');
    }
}
