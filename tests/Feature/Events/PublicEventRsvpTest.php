<?php

namespace Tests\Feature\Events;

use App\Models\Event;
use App\Models\EventRsvp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicEventRsvpTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_rsvp_creates_a_record(): void
    {
        $tenant = $this->createTenant();
        $event = Event::factory()->published()->public()->create([
            'tenant_id' => $tenant->id,
            'starts_at' => now()->addDay(),
        ]);

        $payload = [
            'name' => 'Maria Silva',
            'email' => 'maria@example.com',
            'phone_whatsapp' => '+5511999999999',
            'communication_preference' => 'whatsapp',
            'notifications_scope' => 'event_only',
        ];

        $response = $this->withTenantContext($tenant)
            ->postJson(route('eventos.rsvp', ['tenantSlug' => $tenant->slug, 'event' => $event->hash_id]), $payload);

        $response->assertOk();

        $this->assertDatabaseHas('event_rsvps', [
            'event_id' => $event->id,
            'workspace_id' => $tenant->id,
            'email' => 'maria@example.com',
        ]);
    }

    public function test_public_rsvp_updates_existing_record(): void
    {
        $tenant = $this->createTenant();
        $event = Event::factory()->published()->public()->create([
            'tenant_id' => $tenant->id,
            'starts_at' => now()->addDay(),
        ]);

        EventRsvp::query()->create([
            'workspace_id' => $tenant->id,
            'event_id' => $event->id,
            'name' => 'Old Name',
            'email' => 'maria@example.com',
            'phone' => null,
            'communication_preference' => 'email',
            'notifications_scope' => 'event_only',
            'status' => 'going',
            'source' => 'public_page',
        ]);

        $payload = [
            'name' => 'Maria Atualizada',
            'email' => 'maria@example.com',
            'phone_whatsapp' => null,
            'communication_preference' => 'email',
            'notifications_scope' => 'workspace',
        ];

        $response = $this->withTenantContext($tenant)
            ->postJson(route('eventos.rsvp', ['tenantSlug' => $tenant->slug, 'event' => $event->hash_id]), $payload);

        $response->assertOk();

        $this->assertSame(1, EventRsvp::query()->count());
        $this->assertDatabaseHas('event_rsvps', [
            'event_id' => $event->id,
            'workspace_id' => $tenant->id,
            'name' => 'Maria Atualizada',
            'notifications_scope' => 'workspace',
        ]);
    }

    public function test_public_rsvp_requires_published_public_event(): void
    {
        $tenant = $this->createTenant();
        $event = Event::factory()->create([
            'tenant_id' => $tenant->id,
            'status' => 'draft',
            'is_public' => false,
        ]);

        $payload = [
            'name' => 'Joao',
            'email' => 'joao@example.com',
            'communication_preference' => 'email',
            'notifications_scope' => 'event_only',
        ];

        $this->withTenantContext($tenant)
            ->postJson(route('eventos.rsvp', ['tenantSlug' => $tenant->slug, 'event' => $event->hash_id]), $payload)
            ->assertNotFound();
    }

    public function test_public_calendar_ics_returns_event_details(): void
    {
        $tenant = $this->createTenant();
        $event = Event::factory()->published()->public()->create([
            'tenant_id' => $tenant->id,
            'title' => 'Evento Publico',
            'starts_at' => now()->addDay(),
        ]);

        $response = $this->withTenantContext($tenant)
            ->get(route('eventos.calendar', ['tenantSlug' => $tenant->slug, 'event' => $event->hash_id]));

        $response->assertOk()
            ->assertHeader('Content-Type', 'text/calendar; charset=utf-8')
            ->assertSee('SUMMARY:Evento Publico');
    }
}
