<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminWorkspaceSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_workspace_settings(): void
    {
        $tenant = $this->createTenant();
        $user = User::factory()->create();

        $this->actingAsTenant($user, $tenant, 'admin')
            ->get(route('admin.workspace.edit', ['tenantSlug' => $tenant->slug]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/workspace/Edit')
                ->where('workspace.name', $tenant->name)
            );
    }

    public function test_admin_can_update_workspace_name_and_slug(): void
    {
        $tenant = $this->createTenant(['slug' => 'alpha']);
        $otherTenant = $this->createTenant(['slug' => 'my-workspace']);
        $user = User::factory()->create();

        $this->actingAsTenant($user, $tenant, 'admin')
            ->put(route('admin.workspace.update', ['tenantSlug' => $tenant->slug]), [
                'name' => 'My Workspace',
            ])
            ->assertRedirect('/t/my-workspace-1/admin/workspace');

        $this->assertDatabaseHas('tenants', [
            'id' => $tenant->id,
            'name' => 'My Workspace',
            'slug' => 'my-workspace-1',
        ]);
        $this->assertDatabaseHas('tenants', [
            'id' => $otherTenant->id,
            'slug' => 'my-workspace',
        ]);
    }

    public function test_moderator_cannot_access_workspace_settings(): void
    {
        $tenant = $this->createTenant();
        $user = User::factory()->create();

        $this->actingAsTenant($user, $tenant, 'moderator')
            ->get(route('admin.workspace.edit', ['tenantSlug' => $tenant->slug]))
            ->assertForbidden();
    }
}
