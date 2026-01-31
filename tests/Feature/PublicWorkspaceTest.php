<?php

namespace Tests\Feature;

use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PublicWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_workspace_shows_only_published_public_events(): void
    {
        $tenant = $this->createTenant();

        $publicPublished = Event::factory()->published()->public()->create([
            'tenant_id' => $tenant->id,
        ]);
        $publicDraft = Event::factory()->public()->create([
            'tenant_id' => $tenant->id,
        ]);
        $privatePublished = Event::factory()->published()->create([
            'tenant_id' => $tenant->id,
        ]);

        $this->withTenantContext($tenant)
            ->get(route('workspace.show', ['tenantSlug' => $tenant->slug]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('public/workspace/Show')
                ->where('workspace.name', $tenant->name)
                ->has('events.data', 1)
                ->where('events.data.0.id', $publicPublished->id)
            );

        $this->assertDatabaseHas('events', [
            'id' => $publicDraft->id,
        ]);
        $this->assertDatabaseHas('events', [
            'id' => $privatePublished->id,
        ]);
    }
}
