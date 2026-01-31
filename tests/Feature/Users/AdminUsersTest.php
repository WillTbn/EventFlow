<?php

namespace Tests\Feature\Users;

use App\Models\User;
use App\Notifications\SetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminUsersTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_all_other_users(): void
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
                ->has('users.data', 2)
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
        Notification::fake();

        $tenant = $this->createTenant();
        $admin = User::factory()->create();

        $this->attachUserToTenant($admin, $tenant, 'admin');

        $response = $this->actingAsTenant($admin, $tenant, 'admin')
            ->post(route('admin.usuarios.store', ['tenantSlug' => $tenant->slug]), [
                'name' => 'Moderator User',
                'email' => 'moderator@example.com',
                'role' => 'moderator',
            ]);

        $createdUser = User::query()->where('email', 'moderator@example.com')->first();

        $this->assertNotNull($createdUser);
        Notification::assertSentTo($createdUser, SetPasswordNotification::class);
        $this->assertDatabaseHas('tenant_users', [
            'tenant_id' => $tenant->id,
            'user_id' => $createdUser->id,
            'role' => 'moderator',
        ]);
        $response->assertRedirect(route('admin.usuarios.edit', ['tenantSlug' => $tenant->slug, 'user' => $createdUser]));
    }

    public function test_moderator_can_only_create_member_users(): void
    {
        Notification::fake();

        $tenant = $this->createTenant();
        $moderator = User::factory()->create();

        $this->attachUserToTenant($moderator, $tenant, 'moderator');

        $response = $this->actingAsTenant($moderator, $tenant, 'moderator')
            ->post(route('admin.usuarios.store', ['tenantSlug' => $tenant->slug]), [
                'name' => 'Admin Attempt',
                'email' => 'admin-attempt@example.com',
                'role' => 'admin',
            ]);

        $response->assertSessionHasErrors('role');
        $this->assertDatabaseMissing('users', [
            'email' => 'admin-attempt@example.com',
        ]);
    }

    public function test_moderator_can_create_member_user(): void
    {
        Notification::fake();

        $tenant = $this->createTenant();
        $moderator = User::factory()->create();

        $this->attachUserToTenant($moderator, $tenant, 'moderator');

        $response = $this->actingAsTenant($moderator, $tenant, 'moderator')
            ->post(route('admin.usuarios.store', ['tenantSlug' => $tenant->slug]), [
                'name' => 'Member User',
                'email' => 'member@example.com',
                'role' => 'member',
            ]);

        $createdUser = User::query()->where('email', 'member@example.com')->first();

        $this->assertNotNull($createdUser);
        Notification::assertSentTo($createdUser, SetPasswordNotification::class);
        $this->assertDatabaseHas('tenant_users', [
            'tenant_id' => $tenant->id,
            'user_id' => $createdUser->id,
            'role' => 'member',
        ]);
        $response->assertRedirect(route('admin.usuarios.edit', ['tenantSlug' => $tenant->slug, 'user' => $createdUser]));
    }

    public function test_admin_can_attach_existing_user_and_send_invite(): void
    {
        Notification::fake();

        $tenant = $this->createTenant();
        $admin = User::factory()->create();
        $existingUser = User::factory()->create([
            'email' => 'existing@example.com',
        ]);

        $this->attachUserToTenant($admin, $tenant, 'admin');

        $response = $this->actingAsTenant($admin, $tenant, 'admin')
            ->post(route('admin.usuarios.store', ['tenantSlug' => $tenant->slug]), [
                'name' => 'Existing User',
                'email' => 'existing@example.com',
                'role' => 'member',
            ]);

        $this->assertSame($existingUser->id, User::query()->where('email', 'existing@example.com')->value('id'));
        $this->assertDatabaseHas('tenant_users', [
            'tenant_id' => $tenant->id,
            'user_id' => $existingUser->id,
            'role' => 'member',
        ]);
        Notification::assertSentTo($existingUser, SetPasswordNotification::class);
        $response->assertRedirect(route('admin.usuarios.edit', ['tenantSlug' => $tenant->slug, 'user' => $existingUser]));
    }

    public function test_admin_cannot_access_user_from_other_tenant(): void
    {
        $tenantA = $this->createTenant();
        $tenantB = $this->createTenant();
        $admin = User::factory()->create();
        $outsider = User::factory()->create();

        $this->attachUserToTenant($admin, $tenantA, 'admin');
        $this->attachUserToTenant($outsider, $tenantB, 'member');

        $this->actingAsTenant($admin, $tenantA, 'admin')
            ->get(route('admin.usuarios.edit', [
                'tenantSlug' => $tenantA->slug,
                'user' => $outsider->hash_id,
            ]))
            ->assertNotFound();
    }

    public function test_admin_user_route_returns_404_for_invalid_hash(): void
    {
        $tenant = $this->createTenant();
        $admin = User::factory()->create();

        $this->attachUserToTenant($admin, $tenant, 'admin');

        $this->actingAsTenant($admin, $tenant, 'admin')
            ->get(route('admin.usuarios.edit', [
                'tenantSlug' => $tenant->slug,
                'user' => 'invalid-hash',
            ]))
            ->assertNotFound();
    }

    public function test_admin_can_resend_invite_for_unverified_user(): void
    {
        Notification::fake();

        $tenant = $this->createTenant();
        $admin = User::factory()->create();
        $member = User::factory()->unverified()->create();

        $this->attachUserToTenant($admin, $tenant, 'admin');
        $this->attachUserToTenant($member, $tenant, 'member');

        $response = $this->actingAsTenant($admin, $tenant, 'admin')
            ->post(route('admin.usuarios.resend-invite', [
                'tenantSlug' => $tenant->slug,
                'user' => $member->hash_id,
            ]));

        Notification::assertSentTo($member, SetPasswordNotification::class);
        $response->assertRedirect(route('admin.usuarios.edit', [
            'tenantSlug' => $tenant->slug,
            'user' => $member->hash_id,
        ]));
    }

    public function test_admin_cannot_resend_invite_for_verified_user(): void
    {
        Notification::fake();

        $tenant = $this->createTenant();
        $admin = User::factory()->create();
        $member = User::factory()->create();

        $this->attachUserToTenant($admin, $tenant, 'admin');
        $this->attachUserToTenant($member, $tenant, 'member');

        $response = $this->actingAsTenant($admin, $tenant, 'admin')
            ->post(route('admin.usuarios.resend-invite', [
                'tenantSlug' => $tenant->slug,
                'user' => $member->hash_id,
            ]));

        Notification::assertNotSentTo($member, SetPasswordNotification::class);
        $response->assertRedirect(route('admin.usuarios.edit', [
            'tenantSlug' => $tenant->slug,
            'user' => $member->hash_id,
        ]));
    }
}
