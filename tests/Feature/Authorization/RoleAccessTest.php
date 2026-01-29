<?php

namespace Tests\Feature\Authorization;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_dashboard(): void
    {
        $tenant = $this->createTenant();
        $admin = User::factory()->create();

        $this->actingAsTenant($admin, $tenant, 'admin')
            ->get(route('admin.dashboard', ['tenantSlug' => $tenant->slug]))
            ->assertOk();
    }

    public function test_moderator_can_access_dashboard(): void
    {
        $tenant = $this->createTenant();
        $moderator = User::factory()->create();

        $this->actingAsTenant($moderator, $tenant, 'moderator')
            ->get(route('admin.dashboard', ['tenantSlug' => $tenant->slug]))
            ->assertOk();
    }

    public function test_member_cannot_access_dashboard(): void
    {
        $tenant = $this->createTenant();
        $member = User::factory()->create();

        $this->actingAsTenant($member, $tenant, 'member')
            ->get(route('admin.dashboard', ['tenantSlug' => $tenant->slug]))
            ->assertForbidden();
    }

    public function test_member_cannot_access_settings(): void
    {
        $tenant = $this->createTenant();
        $member = User::factory()->create();

        $this->actingAsTenant($member, $tenant, 'member')
            ->get(route('profile.edit', ['tenantSlug' => $tenant->slug]))
            ->assertForbidden();
    }

    public function test_tenant_seeder_creates_admin_user_and_membership(): void
    {
        $this->seed(\Database\Seeders\TenantSeeder::class);

        $tenant = Tenant::query()->where('slug', 'demo-workspace')->first();
        $admin = User::query()->where('email', 'admin@example.com')->first();

        $this->assertNotNull($tenant);
        $this->assertNotNull($admin);
        $this->assertDatabaseHas('tenant_users', [
            'tenant_id' => $tenant->id,
            'user_id' => $admin->id,
            'role' => 'admin',
        ]);
    }
}
