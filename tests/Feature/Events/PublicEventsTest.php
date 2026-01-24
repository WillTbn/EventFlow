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
        $publicPublished = Event::factory()->published()->public()->create();
        $publicDraft = Event::factory()->public()->create();
        $privatePublished = Event::factory()->published()->create();

        $this->get(route('eventos.index'))
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
        $event = Event::factory()->create();

        $this->get(route('eventos.show', $event->slug))
            ->assertForbidden();
    }

    public function test_public_show_renders_published_event()
    {
        $event = Event::factory()->published()->public()->create();

        $this->get(route('eventos.show', $event->slug))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('public/events/Show')
                ->where('event.id', $event->id)
            );
    }
}
