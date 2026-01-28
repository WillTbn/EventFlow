<?php

namespace Tests\Feature\Events;

use App\Models\Event;
use App\Models\EventPhoto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminEventsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_index_requires_authentication()
    {
        $this->get(route('admin.eventos.index'))
            ->assertRedirect(route('login'));
    }

    public function test_admin_index_shows_only_own_events()
    {
        $user = User::factory()->create();
        $this->assignRole($user, 'admin');

        $otherUser = User::factory()->create();
        $this->assignRole($otherUser, 'admin');

        $ownEvent = Event::factory()->create(['created_by' => $user->id]);
        Event::factory()->create(['created_by' => $otherUser->id]);

        $this->actingAs($user)
            ->get(route('admin.eventos.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/events/Index')
                ->has('events.data', 1)
                ->where('events.data.0.id', $ownEvent->id)
            );
    }

    public function test_admin_can_create_event()
    {
        $user = User::factory()->create();
        $this->assignRole($user, 'admin');

        $startsAt = now()->addDays(2)->setTime(10, 0);
        $endsAt = (clone $startsAt)->addHours(2);

        $response = $this->actingAs($user)
            ->post(route('admin.eventos.store'), [
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

        $response->assertRedirect(route('admin.eventos.edit', $event));
        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'created_by' => $user->id,
            'title' => 'Evento de teste',
            'is_public' => 1,
        ]);
    }

    public function test_admin_can_create_event_with_main_photo(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $this->assignRole($user, 'admin');

        $startsAt = now()->addDays(2)->setTime(10, 0);
        $endsAt = (clone $startsAt)->addHours(2);

        $response = $this->actingAs($user)
            ->post(route('admin.eventos.store'), [
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

        $response->assertRedirect(route('admin.eventos.edit', $event));
        $event->refresh();

        $this->assertNotNull($event->main_photo_path);
        Storage::disk('public')->assertExists($event->main_photo_path);
        Storage::disk('public')->assertExists($event->main_photo_medium_path);
        Storage::disk('public')->assertExists($event->main_photo_thumb_path);
    }

    public function test_admin_cannot_edit_other_users_event()
    {
        $user = User::factory()->create();
        $this->assignRole($user, 'admin');

        $otherUser = User::factory()->create();
        $this->assignRole($otherUser, 'admin');

        $event = Event::factory()->create(['created_by' => $otherUser->id]);

        $this->actingAs($user)
            ->get(route('admin.eventos.edit', $event))
            ->assertForbidden();
    }

    public function test_admin_can_update_own_event()
    {
        $user = User::factory()->create();
        $this->assignRole($user, 'admin');

        $event = Event::factory()->create(['created_by' => $user->id]);
        $startsAt = now()->addDays(3)->setTime(12, 0);
        $endsAt = (clone $startsAt)->addHours(3);

        $response = $this->actingAs($user)
            ->put(route('admin.eventos.update', $event), [
                'title' => 'Evento atualizado',
                'description' => 'Descricao atualizada',
                'location' => 'Rio de Janeiro',
                'starts_at' => $startsAt->format('Y-m-d H:i:s'),
                'ends_at' => $endsAt->format('Y-m-d H:i:s'),
                'status' => 'published',
                'is_public' => true,
                'capacity' => 200,
            ]);

        $response->assertRedirect(route('admin.eventos.edit', $event));
        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'title' => 'Evento atualizado',
            'status' => 'published',
            'is_public' => 1,
        ]);
    }

    public function test_admin_can_delete_own_event()
    {
        $user = User::factory()->create();
        $this->assignRole($user, 'admin');

        $event = Event::factory()->create(['created_by' => $user->id]);

        $response = $this->actingAs($user)
            ->delete(route('admin.eventos.destroy', $event));

        $response->assertRedirect(route('admin.eventos.index'));
        $this->assertDatabaseMissing('events', [
            'id' => $event->id,
        ]);
    }

    private function assignRole(User $user, string $role): void
    {
        Role::firstOrCreate(['name' => $role]);
        $user->assignRole($role);
    }
}
