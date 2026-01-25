<?php

namespace Tests\Feature\Authorization;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_dashboard(): void
    {
        $admin = User::factory()->create();
        $this->assignRole($admin, 'admin');

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk();
    }

    public function test_moderator_can_access_dashboard(): void
    {
        $moderator = User::factory()->create();
        $this->assignRole($moderator, 'moderator');

        $this->actingAs($moderator)
            ->get(route('dashboard'))
            ->assertOk();
    }

    public function test_member_cannot_access_dashboard(): void
    {
        $member = User::factory()->create();
        $this->assignRole($member, 'member');

        $this->actingAs($member)
            ->get(route('dashboard'))
            ->assertForbidden();
    }

    public function test_member_cannot_access_settings(): void
    {
        $member = User::factory()->create();
        $this->assignRole($member, 'member');

        $this->actingAs($member)
            ->get(route('profile.edit'))
            ->assertForbidden();
    }

    public function test_roles_and_admin_seeder_creates_admin_user(): void
    {
        $this->seed(\Database\Seeders\RolesAndAdminSeeder::class);

        $admin = User::query()->where('email', 'admin@example.com')->first();

        $this->assertNotNull($admin);
        $this->assertTrue($admin->hasRole('admin'));
    }

    private function assignRole(User $user, string $role): void
    {
        Role::firstOrCreate(['name' => $role]);
        $user->assignRole($role);
    }
}
