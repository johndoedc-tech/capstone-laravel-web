<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        if (($_ENV['DB_CONNECTION'] ?? null) === 'sqlite' && ! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('The pdo_sqlite extension is not available in this PHP runtime.');
        }

        parent::setUp();
    }

    public function test_admin_can_view_general_user_management_page(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        User::factory()->create([
            'name' => 'Sample Farmer',
            'role' => User::ROLE_FARMER,
        ]);

        User::factory()->create([
            'name' => 'Sample Validator',
            'role' => User::ROLE_LGU_VALIDATOR,
            'lgu_municipality' => 'BUGUIAS',
            'lgu_barangay' => 'ABATAN',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.users.index'));

        $response->assertOk();
        $response->assertSee('User Management');
        $response->assertSee('LGU Validators');
        $response->assertSee('Sample Farmer');
        $response->assertSee('Sample Validator');
        $response->assertSee('Buguias');
    }

    public function test_admin_can_create_farmer_without_lgu_assignment(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Test Farmer Account',
            'email' => 'test.farmer@example.test',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => User::ROLE_FARMER,
        ]);

        $response->assertRedirect(route('admin.users.index'));

        $farmer = User::where('email', 'test.farmer@example.test')->firstOrFail();

        $this->assertSame(User::ROLE_FARMER, $farmer->role);
        $this->assertTrue(Hash::check('Password123!', $farmer->password));
        $this->assertTrue((bool) $farmer->is_active);
        $this->assertNull($farmer->lgu_municipality);
    }

    public function test_admin_can_create_lgu_validator_with_assignment(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Test LGU Validator',
            'email' => 'test.lgu@example.test',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => User::ROLE_LGU_VALIDATOR,
            'lgu_municipality' => 'Buguias',
            'lgu_barangay' => 'Abatan',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.users.index'));

        $validator = User::where('email', 'test.lgu@example.test')->firstOrFail();

        $this->assertSame(User::ROLE_LGU_VALIDATOR, $validator->role);
        $this->assertSame('BUGUIAS', $validator->lgu_municipality);
        $this->assertSame('ABATAN', $validator->lgu_barangay);
        $this->assertTrue($validator->is_active);
    }

    public function test_old_lgu_validator_pages_redirect_to_unified_users_page(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.lgu-validators.index'));

        $response->assertRedirect('/admin/users?role=lgu_validator');
    }

    public function test_admin_can_update_lgu_validator_through_users_page(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $validator = User::factory()->create([
            'name' => 'Existing LGU Validator',
            'email' => 'existing.lgu@example.test',
            'role' => User::ROLE_LGU_VALIDATOR,
            'lgu_municipality' => 'BUGUIAS',
            'lgu_barangay' => 'ABATAN',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->put(route('admin.users.update', $validator), [
            'name' => 'Updated LGU Validator',
            'email' => 'updated.lgu@example.test',
            'role' => User::ROLE_LGU_VALIDATOR,
            'lgu_municipality' => 'ATOK',
            'lgu_barangay' => 'CALIKING',
        ]);

        $response->assertRedirect(route('admin.users.index'));

        $validator->refresh();
        $this->assertSame('Updated LGU Validator', $validator->name);
        $this->assertSame('ATOK', $validator->lgu_municipality);
        $this->assertSame('CALIKING', $validator->lgu_barangay);
        $this->assertFalse($validator->is_active);
    }

    public function test_lgu_validator_barangay_must_match_municipality(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Invalid LGU Validator',
            'email' => 'invalid.lgu@example.test',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => User::ROLE_LGU_VALIDATOR,
            'lgu_municipality' => 'ATOK',
            'lgu_barangay' => 'ABATAN',
            'is_active' => '1',
        ]);

        $response->assertSessionHasErrors('lgu_barangay');
        $this->assertDatabaseMissing('users', [
            'email' => 'invalid.lgu@example.test',
        ]);
    }
}
