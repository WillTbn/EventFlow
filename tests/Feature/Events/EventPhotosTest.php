<?php

namespace Tests\Feature\Events;

use App\Models\Event;
use App\Models\EventPhoto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EventPhotosTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_upload_main_photo(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $this->assignRole($user, 'admin');

        $event = Event::factory()->create(['created_by' => $user->id]);
        $photo = UploadedFile::fake()->image('main.jpg', 1600, 1200);

        $response = $this->actingAs($user)->post(route('admin.eventos.foto-principal', $event), [
            'main_photo' => $photo,
        ]);

        $response->assertRedirect();

        $event->refresh();

        $this->assertNotNull($event->main_photo_path);
        Storage::disk('public')->assertExists($event->main_photo_path);
        Storage::disk('public')->assertExists($event->main_photo_medium_path);
        Storage::disk('public')->assertExists($event->main_photo_thumb_path);
    }

    public function test_admin_cannot_upload_story_photos_before_event_ends(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $this->assignRole($user, 'admin');

        $event = Event::factory()->create([
            'created_by' => $user->id,
            'ends_at' => now()->addDay(),
        ]);

        $photo = UploadedFile::fake()->image('story.jpg', 1200, 800);

        $response = $this->actingAs($user)->post(route('admin.eventos.fotos', $event), [
            'photos' => [$photo],
        ]);

        $response->assertForbidden();
        $this->assertDatabaseCount('event_photos', 0);
    }

    public function test_admin_can_upload_story_photos_after_event_ends(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $this->assignRole($user, 'admin');

        $event = Event::factory()->create([
            'created_by' => $user->id,
            'ends_at' => now()->subDay(),
        ]);

        $photos = [
            UploadedFile::fake()->image('story-1.jpg', 1200, 800),
            UploadedFile::fake()->image('story-2.jpg', 1200, 800),
        ];

        $response = $this->actingAs($user)->post(route('admin.eventos.fotos', $event), [
            'photos' => $photos,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseCount('event_photos', 2);

        $storedPhoto = EventPhoto::firstOrFail();
        Storage::disk('public')->assertExists($storedPhoto->original_path);
        Storage::disk('public')->assertExists($storedPhoto->medium_path);
        Storage::disk('public')->assertExists($storedPhoto->thumb_path);
    }

    private function assignRole(User $user, string $role): void
    {
        Role::firstOrCreate(['name' => $role]);
        $user->assignRole($role);
    }
}
