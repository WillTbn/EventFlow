<?php

namespace Tests\Feature\Users;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminUsersTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_all_users(): void
    {
        $tenant = $this->createTenant();
        $admin = User::factory()->create();
        $moderator = User::factory()->create();
        $member = User::factory()->create();

        $this->attachUserToTenant($admin, $tenant, 'admin');
        $this->attachUserToTenant($moderator, $tenant, 'moderator');
        $this->attachUserToTenant($member, $tenant, 'member');

        $this->actingAsTenant($admin, $tenant, 'admin')
            ->get(route('admin.usuarios.index', ['tenantSlug' => $tenant->slug]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/users/Index')
                ->has('users.data', 3)
            );
    }

    public function test_moderator_sees_only_members(): void
    {
        $tenant = $this->createTenant();
        $admin = User::factory()->create();
        $moderator = User::factory()->create();
        $memberOne = User::factory()->create();
        $memberTwo = User::factory()->create();

        $this->attachUserToTenant($admin, $tenant, 'admin');
        $this->attachUserToTenant($moderator, $tenant, 'moderator');
        $this->attachUserToTenant($memberOne, $tenant, 'member');
        $this->attachUserToTenant($memberTwo, $tenant, 'member');

        $this->actingAsTenant($moderator, $tenant, 'moderator')
            ->get(route('admin.usuarios.index', ['tenantSlug' => $tenant->slug]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/users/Index')
                ->has('users.data', 2)
            );
    }

    public function test_moderator_cannot_edit_admin(): void
    {
        $tenant = $this->createTenant();
        $admin = User::factory()->create();
        $moderator = User::factory()->create();

        $this->attachUserToTenant($admin, $tenant, 'admin');
        $this->attachUserToTenant($moderator, $tenant, 'moderator');

        $this->actingAsTenant($moderator, $tenant, 'moderator')
            ->get(route('admin.usuarios.edit', ['tenantSlug' => $tenant->slug, 'user' => $admin]))
            ->assertForbidden();
    }

    public function test_admin_can_create_moderator_user(): void
    {
        $tenant = $this->createTenant();
        $admin = User::factory()->create();

        $this->attachUserToTenant($admin, $tenant, 'admin');

        $response = $this->actingAsTenant($admin, $tenant, 'admin')
            ->post(route('admin.usuarios.store', ['tenantSlug' => $tenant->slug]), [
                'name' => 'Moderator User',
                'email' => 'moderator@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => 'moderator',
            ]);

        $createdUser = User::query()->where('email', 'moderator@example.com')->first();

        $this->assertNotNull($createdUser);
        $this->assertDatabaseHas('tenant_users', [
            'tenant_id' => $tenant->id,
            'user_id' => $createdUser->id,
            'role' => 'moderator',
        ]);
        $response->assertRedirect(route('admin.usuarios.edit', ['tenantSlug' => $tenant->slug, 'user' => $createdUser]));
    }

    public function test_moderator_can_only_create_member_users(): void
    {
        $tenant = $this->createTenant();
        $moderator = User::factory()->create();

        $this->attachUserToTenant($moderator, $tenant, 'moderator');

        $response = $this->actingAsTenant($moderator, $tenant, 'moderator')
            ->post(route('admin.usuarios.store', ['tenantSlug' => $tenant->slug]), [
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
        $tenant = $this->createTenant();
        $moderator = User::factory()->create();

        $this->attachUserToTenant($moderator, $tenant, 'moderator');

        $response = $this->actingAsTenant($moderator, $tenant, 'moderator')
            ->post(route('admin.usuarios.store', ['tenantSlug' => $tenant->slug]), [
                'name' => 'Member User',
                'email' => 'member@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => 'member',
            ]);

        $createdUser = User::query()->where('email', 'member@example.com')->first();

        $this->assertNotNull($createdUser);
        $this->assertDatabaseHas('tenant_users', [
            'tenant_id' => $tenant->id,
            'user_id' => $createdUser->id,
            'role' => 'member',
        ]);
        $response->assertRedirect(route('admin.usuarios.edit', ['tenantSlug' => $tenant->slug, 'user' => $createdUser]));
    }
}
