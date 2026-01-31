<?php

namespace Tests\Feature\Events;

use App\Models\Event;
use App\Models\EventPhoto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminEventsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_index_requires_authentication()
    {
        $tenant = $this->createTenant();

        $this->get(route('admin.eventos.index', ['tenantSlug' => $tenant->slug]))
            ->assertRedirect(route('login'));
    }

    public function test_admin_index_shows_all_events_for_tenant()
    {
        $tenant = $this->createTenant();
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $this->attachUserToTenant($user, $tenant, 'admin');
        $this->attachUserToTenant($otherUser, $tenant, 'admin');

        $ownEvent = Event::factory()->create([
            'tenant_id' => $tenant->id,
            'created_by' => $user->id,
        ]);
        Event::factory()->create([
            'tenant_id' => $tenant->id,
            'created_by' => $otherUser->id,
        ]);

        $this->actingAsTenant($user, $tenant, 'admin')
            ->get(route('admin.eventos.index', ['tenantSlug' => $tenant->slug]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/events/Index')
                ->has('events.data', 2)
                ->where('events.data', fn ($events) => collect($events)
                    ->pluck('id')
                    ->contains($ownEvent->id))
            );
    }

    public function test_admin_index_isolated_by_tenant()
    {
        $tenantA = $this->createTenant();
        $tenantB = $this->createTenant();
        $user = User::factory()->create();

        $this->attachUserToTenant($user, $tenantA, 'admin');
        $this->attachUserToTenant($user, $tenantB, 'admin');

        $eventA = Event::factory()->create([
            'tenant_id' => $tenantA->id,
            'created_by' => $user->id,
        ]);
        Event::factory()->create([
            'tenant_id' => $tenantB->id,
            'created_by' => $user->id,
        ]);

        $this->actingAsTenant($user, $tenantA, 'admin')
            ->get(route('admin.eventos.index', ['tenantSlug' => $tenantA->slug]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/events/Index')
                ->has('events.data', 1)
                ->where('events.data.0.id', $eventA->id)
            );
    }

    public function test_event_hash_id_is_generated_on_create(): void
    {
        $tenant = $this->createTenant();
        $user = User::factory()->create();

        $this->attachUserToTenant($user, $tenant, 'admin');

        $event = Event::factory()->create([
            'tenant_id' => $tenant->id,
            'created_by' => $user->id,
        ]);

        $event->refresh();

        $this->assertNotNull($event->hash_id);
        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'hash_id' => $event->hash_id,
        ]);
    }

    public function test_admin_can_create_event()
    {
        $tenant = $this->createTenant();
        $user = User::factory()->create();
        $this->attachUserToTenant($user, $tenant, 'admin');

        $startsAt = now()->addDays(2)->setTime(10, 0);
        $endsAt = (clone $startsAt)->addHours(2);

        $response = $this->actingAsTenant($user, $tenant, 'admin')
            ->post(route('admin.eventos.store', ['tenantSlug' => $tenant->slug]), [
                'title' => 'Evento de teste',
                'description' => 'Descricao do evento',
                'location' => 'Sao Paulo',
                'starts_at' => $startsAt->format('Y-m-d H:i:s'),
                'ends_at' => $endsAt->format('Y-m-d H:i:s'),
                'status' => 'draft',
                'is_public' => true,
                'capacity' => 100,
            ]);

        $event = Event::firstOrFail();

        $response->assertRedirect(route('admin.eventos.edit', [
            'tenantSlug' => $tenant->slug,
            'event' => $event,
        ]));
        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'tenant_id' => $tenant->id,
            'created_by' => $user->id,
            'title' => 'Evento de teste',
            'is_public' => 1,
        ]);
    }

    public function test_admin_can_create_event_with_main_photo(): void
    {
        Storage::fake('public');

        $tenant = $this->createTenant();
        $user = User::factory()->create();
        $this->attachUserToTenant($user, $tenant, 'admin');

        $startsAt = now()->addDays(2)->setTime(10, 0);
        $endsAt = (clone $startsAt)->addHours(2);

        $response = $this->actingAsTenant($user, $tenant, 'admin')
            ->post(route('admin.eventos.store', ['tenantSlug' => $tenant->slug]), [
                'title' => 'Evento com foto',
                'description' => 'Descricao do evento',
                'location' => 'Sao Paulo',
                'starts_at' => $startsAt->format('Y-m-d H:i:s'),
                'ends_at' => $endsAt->format('Y-m-d H:i:s'),
                'status' => 'draft',
                'is_public' => true,
                'capacity' => 100,
                'main_photo' => UploadedFile::fake()->image('main.jpg', 1600, 1200),
            ]);

        $event = Event::firstOrFail();

        $response->assertRedirect(route('admin.eventos.edit', [
            'tenantSlug' => $tenant->slug,
            'event' => $event,
        ]));
        $event->refresh();

        $this->assertNotNull($event->main_photo_path);
        Storage::disk('public')->assertExists($event->main_photo_path);
        Storage::disk('public')->assertExists($event->main_photo_medium_path);
        Storage::disk('public')->assertExists($event->main_photo_thumb_path);
    }

    public function test_free_plan_blocks_second_event_in_month(): void
    {
        $tenant = $this->createTenant(['plan' => 'free']);
        $user = User::factory()->create();
        $this->attachUserToTenant($user, $tenant, 'admin');

        Event::factory()->create([
            'tenant_id' => $tenant->id,
            'created_by' => $user->id,
            'created_at' => now()->startOfMonth()->addDay(),
        ]);

        $startsAt = now()->addDays(2)->setTime(10, 0);
        $endsAt = (clone $startsAt)->addHours(2);

        $response = $this->actingAsTenant($user, $tenant, 'admin')
            ->postJson(route('admin.eventos.store', ['tenantSlug' => $tenant->slug]), [
                'title' => 'Segundo evento',
                'description' => 'Descricao do evento',
                'location' => 'Sao Paulo',
                'starts_at' => $startsAt->format('Y-m-d H:i:s'),
                'ends_at' => $endsAt->format('Y-m-d H:i:s'),
                'status' => 'draft',
                'is_public' => true,
                'capacity' => 100,
            ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['event']);
    }

    public function test_admin_can_edit_other_users_event()
    {
        $tenant = $this->createTenant();
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $this->attachUserToTenant($user, $tenant, 'admin');
        $this->attachUserToTenant($otherUser, $tenant, 'admin');

        $event = Event::factory()->create([
            'tenant_id' => $tenant->id,
            'created_by' => $otherUser->id,
        ]);

        $this->actingAsTenant($user, $tenant, 'admin')
            ->get(route('admin.eventos.edit', [
                'tenantSlug' => $tenant->slug,
                'event' => $event,
            ]))
            ->assertOk();
    }

    public function test_admin_can_update_own_event()
    {
        $tenant = $this->createTenant();
        $user = User::factory()->create();

        $this->attachUserToTenant($user, $tenant, 'admin');

        $event = Event::factory()->create([
            'tenant_id' => $tenant->id,
            'created_by' => $user->id,
        ]);
        $startsAt = now()->addDays(3)->setTime(12, 0);
        $endsAt = (clone $startsAt)->addHours(3);

        $response = $this->actingAsTenant($user, $tenant, 'admin')
            ->put(route('admin.eventos.update', [
                'tenantSlug' => $tenant->slug,
                'event' => $event,
            ]), [
                'title' => 'Evento atualizado',
                'description' => 'Descricao atualizada',
                'location' => 'Rio de Janeiro',
                'starts_at' => $startsAt->format('Y-m-d H:i:s'),
                'ends_at' => $endsAt->format('Y-m-d H:i:s'),
                'status' => 'published',
                'is_public' => true,
                'capacity' => 200,
            ]);

        $response->assertRedirect(route('admin.eventos.edit', [
            'tenantSlug' => $tenant->slug,
            'event' => $event,
        ]));
        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'title' => 'Evento atualizado',
            'status' => 'published',
            'is_public' => 1,
        ]);
    }

    public function test_admin_can_delete_own_event()
    {
        $tenant = $this->createTenant();
        $user = User::factory()->create();

        $this->attachUserToTenant($user, $tenant, 'admin');

        $event = Event::factory()->create([
            'tenant_id' => $tenant->id,
            'created_by' => $user->id,
        ]);

        $response = $this->actingAsTenant($user, $tenant, 'admin')
            ->delete(route('admin.eventos.destroy', [
                'tenantSlug' => $tenant->slug,
                'event' => $event,
            ]));

        $response->assertRedirect(route('admin.eventos.index', ['tenantSlug' => $tenant->slug]));
        $this->assertDatabaseMissing('events', [
            'id' => $event->id,
        ]);
    }

    public function test_moderator_cannot_create_event(): void
    {
        $tenant = $this->createTenant();
        $moderator = User::factory()->create();
        $this->attachUserToTenant($moderator, $tenant, 'moderator');

        $startsAt = now()->addDays(2)->setTime(10, 0);
        $endsAt = (clone $startsAt)->addHours(2);

        $this->actingAsTenant($moderator, $tenant, 'moderator')
            ->post(route('admin.eventos.store', ['tenantSlug' => $tenant->slug]), [
                'title' => 'Evento do moderador',
                'description' => 'Descricao do evento',
                'location' => 'Sao Paulo',
                'starts_at' => $startsAt->format('Y-m-d H:i:s'),
                'ends_at' => $endsAt->format('Y-m-d H:i:s'),
                'status' => 'draft',
                'is_public' => true,
                'capacity' => 100,
            ])
            ->assertForbidden();
    }

    public function test_moderator_can_update_event_from_other_user(): void
    {
        $tenant = $this->createTenant();
        $moderator = User::factory()->create();
        $admin = User::factory()->create();

        $this->attachUserToTenant($moderator, $tenant, 'moderator');
        $this->attachUserToTenant($admin, $tenant, 'admin');

        $event = Event::factory()->create([
            'tenant_id' => $tenant->id,
            'created_by' => $admin->id,
            'title' => 'Evento original',
        ]);

        $response = $this->actingAsTenant($moderator, $tenant, 'moderator')
            ->put(route('admin.eventos.update', [
                'tenantSlug' => $tenant->slug,
                'event' => $event->hash_id,
            ]), [
                'title' => 'Evento atualizado',
                'description' => $event->description,
                'location' => $event->location,
                'starts_at' => $event->starts_at?->format('Y-m-d H:i:s'),
                'ends_at' => $event->ends_at?->format('Y-m-d H:i:s'),
                'status' => $event->status,
                'is_public' => $event->is_public,
                'capacity' => $event->capacity,
            ]);

        $response->assertRedirect(route('admin.eventos.edit', [
            'tenantSlug' => $tenant->slug,
            'event' => $event->hash_id,
        ]));

        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'title' => 'Evento atualizado',
        ]);
    }
}
