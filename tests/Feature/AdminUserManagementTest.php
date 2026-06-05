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
}
