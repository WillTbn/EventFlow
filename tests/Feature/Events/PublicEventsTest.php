<?php

namespace Tests\Feature\Events;

use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PublicEventsTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_index_shows_only_published_public_events()
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
            ->get(route('eventos.index', ['tenantSlug' => $tenant->slug]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('public/events/Index')
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

    public function test_public_show_requires_event_to_be_visible()
    {
        $tenant = $this->createTenant();
        $event = Event::factory()->create([
            'tenant_id' => $tenant->id,
        ]);
        $event->refresh();

        $this->withTenantContext($tenant)
            ->get(route('eventos.show', ['tenantSlug' => $tenant->slug, 'event' => $event->hash_id]))
            ->assertForbidden();
    }

    public function test_public_show_renders_published_event()
    {
        $tenant = $this->createTenant();
        $event = Event::factory()->published()->public()->create([
            'tenant_id' => $tenant->id,
        ]);
        $event->refresh();

        $this->withTenantContext($tenant)
            ->get(route('eventos.show', ['tenantSlug' => $tenant->slug, 'event' => $event->hash_id]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('public/events/Show')
                ->where('event.id', $event->id)
            );
    }

    public function test_public_show_returns_404_for_wrong_tenant(): void
    {
        $tenantA = $this->createTenant();
        $tenantB = $this->createTenant();
        $event = Event::factory()->published()->public()->create([
            'tenant_id' => $tenantA->id,
        ]);
        $event->refresh();

        $this->withTenantContext($tenantB)
            ->get(route('eventos.show', ['tenantSlug' => $tenantB->slug, 'event' => $event->hash_id]))
            ->assertNotFound();
    }

    public function test_public_show_returns_404_for_invalid_hash(): void
    {
        $tenant = $this->createTenant();

        $this->withTenantContext($tenant)
            ->get(route('eventos.show', ['tenantSlug' => $tenant->slug, 'event' => 'invalid-hash']))
            ->assertNotFound();
    }
}
