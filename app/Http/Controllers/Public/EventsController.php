<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventPhoto;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class EventsController extends Controller
{
    /**
     * Display a listing of public events.
     */
    public function index(Request $request): Response
    {
        $tenant = app(TenantContext::class)->get();

        $events = Event::query()
            ->where('is_public', true)
            ->where('status', 'published')
            ->orderBy('starts_at')
            ->paginate(12)
            ->withQueryString()
            ->through(fn (Event $event) => [
                'id' => $event->id,
                'title' => $event->title,
                'description' => $event->description,
                'hash_id' => $event->hash_id,
                'location' => $event->location,
                'starts_at' => $event->starts_at?->format('Y-m-d H:i'),
                'ends_at' => $event->ends_at?->format('Y-m-d H:i'),
                'main_photo_medium_path' => $event->main_photo_medium_path
                    ? Storage::disk('public')->url($event->main_photo_medium_path)
                    : null,
            ]);

        return Inertia::render('public/events/Index', [
            'workspace' => [
                'name' => $tenant?->name,
                'slug' => $tenant?->slug,
                'logo_url' => $tenant?->logo_medium_path
                    ? Storage::disk('public')->url($tenant->logo_medium_path)
                    : null,
                'logo_thumb_url' => $tenant?->logo_thumb_path
                    ? Storage::disk('public')->url($tenant->logo_thumb_path)
                    : null,
            ],
            'events' => $events,
        ]);
    }

    /**
     * Display the specified event.
     */
    public function show(string $tenantSlug, Event $event): Response
    {
        if (! $event->is_public || $event->status !== 'published') {
            abort(404);
        }

        $tenant = app(TenantContext::class)->get();
        $event->load(['photos']);
        $photos = $event->photos
            ->map(fn (EventPhoto $photo) => [
                'id' => $photo->id,
                'medium_url' => Storage::disk('public')->url($photo->medium_path),
                'thumb_url' => Storage::disk('public')->url($photo->thumb_path),
            ])
            ->values()
            ->all();

        return Inertia::render('public/events/Show', [
            'workspace' => [
                'name' => $tenant?->name,
                'slug' => $tenant?->slug,
                'logo_url' => $tenant?->logo_medium_path
                    ? Storage::disk('public')->url($tenant->logo_medium_path)
                    : null,
                'logo_thumb_url' => $tenant?->logo_thumb_path
                    ? Storage::disk('public')->url($tenant->logo_thumb_path)
                    : null,
            ],
            'event' => [
                'id' => $event->id,
                'title' => $event->title,
                'description' => $event->description,
                'location' => $event->location,
                'starts_at' => $event->starts_at?->format('Y-m-d H:i'),
                'ends_at' => $event->ends_at?->format('Y-m-d H:i'),
                'status' => $event->status,
                'is_public' => $event->is_public,
                'cover_url' => $event->main_photo_medium_path
                    ? Storage::disk('public')->url($event->main_photo_medium_path)
                    : null,
            ],
            'photos' => $photos,
        ]);
    }
}
