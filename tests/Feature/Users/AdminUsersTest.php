<?php

namespace Tests\Feature\Users;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminUsersTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_all_users(): void
    {
        $admin = User::factory()->create();
        $this->assignRole($admin, 'admin');

        $moderator = User::factory()->create();
        $this->assignRole($moderator, 'moderator');

        $member = User::factory()->create();
        $this->assignRole($member, 'member');

        $this->actingAs($admin)
            ->get(route('admin.usuarios.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/users/Index')
                ->has('users.data', 3)
            );
    }

    public function test_moderator_sees_only_members(): void
    {
        $admin = User::factory()->create();
        $this->assignRole($admin, 'admin');

        $moderator = User::factory()->create();
        $this->assignRole($moderator, 'moderator');

        $memberOne = User::factory()->create();
        $this->assignRole($memberOne, 'member');

        $memberTwo = User::factory()->create();
        $this->assignRole($memberTwo, 'member');

        $this->actingAs($moderator)
            ->get(route('admin.usuarios.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/users/Index')
                ->has('users.data', 2)
            );
    }

    public function test_moderator_cannot_edit_admin(): void
    {
        $admin = User::factory()->create();
        $this->assignRole($admin, 'admin');

        $moderator = User::factory()->create();
        $this->assignRole($moderator, 'moderator');

        $this->actingAs($moderator)
            ->get(route('admin.usuarios.edit', $admin))
            ->assertForbidden();
    }

    public function test_admin_can_create_moderator_user(): void
    {
        $admin = User::factory()->create();
        $this->assignRole($admin, 'admin');

        $response = $this->actingAs($admin)
            ->post(route('admin.usuarios.store'), [
                'name' => 'Moderator User',
                'email' => 'moderator@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => 'moderator',
            ]);

        $createdUser = User::query()->where('email', 'moderator@example.com')->first();

        $this->assertNotNull($createdUser);
        $this->assertTrue($createdUser->hasRole('moderator'));
        $response->assertRedirect(route('admin.usuarios.edit', $createdUser));
    }

    public function test_moderator_can_only_create_member_users(): void
    {
        $moderator = User::factory()->create();
        $this->assignRole($moderator, 'moderator');

        $response = $this->actingAs($moderator)
            ->post(route('admin.usuarios.store'), [
                'name' => 'Admin Attempt',
                'email' => 'admin-attempt@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => 'admin',
            ]);

        $response->assertSessionHasErrors('role');
        $this->assertDatabaseMissing('users', [
            'email' => 'admin-attempt@example.com',
        ]);
    }

    public function test_moderator_can_create_member_user(): void
    {
        $moderator = User::factory()->create();
        $this->assignRole($moderator, 'moderator');

        $response = $this->actingAs($moderator)
            ->post(route('admin.usuarios.store'), [
                'name' => 'Member User',
                'email' => 'member@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => 'member',
            ]);

        $createdUser = User::query()->where('email', 'member@example.com')->first();

        $this->assertNotNull($createdUser);
        $this->assertTrue($createdUser->hasRole('member'));
        $response->assertRedirect(route('admin.usuarios.edit', $createdUser));
    }

    private function assignRole(User $user, string $role): void
    {
        Role::firstOrCreate(['name' => $role]);
        $user->assignRole($role);
    }
}
