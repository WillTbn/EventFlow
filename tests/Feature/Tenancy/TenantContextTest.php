<?php

namespace Tests\Feature\Tenancy;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantContextTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_member_cannot_access_tenant_ping(): void
    {
        $tenant = $this->createTenant();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('tenant.ping', ['tenantSlug' => $tenant->slug]))
            ->assertForbidden();
    }

    public function test_member_can_access_tenant_ping_and_receives_tenant(): void
    {
        $tenant = $this->createTenant();
        $user = User::factory()->create();

        $this->attachUserToTenant($user, $tenant, 'member');

        $response = $this->actingAs($user)
            ->get(route('tenant.ping', ['tenantSlug' => $tenant->slug]));

        $response->assertOk();
        $response->assertJsonPath('tenant.id', $tenant->id);
        $response->assertJsonPath('tenant.slug', $tenant->slug);
        $response->assertJsonPath('user.id', $user->id);
    }

    public function test_member_cannot_access_admin_only_routes(): void
    {
        $tenant = $this->createTenant();
        $user = User::factory()->create();

        $this->attachUserToTenant($user, $tenant, 'member');

        $this->actingAs($user)
            ->get(route('admin.dashboard', ['tenantSlug' => $tenant->slug]))
            ->assertForbidden();
    }
}
