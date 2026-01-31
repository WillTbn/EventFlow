<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
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
            'events' => $events,
        ]);
    }

    /**
     * Display the specified event.
     */
    public function show(string $tenantSlug, Event $event): Response
    {
        Gate::authorize('view', $event);

        return Inertia::render('public/events/Show', [
            'event' => [
                'id' => $event->id,
                'title' => $event->title,
                'description' => $event->description,
                'location' => $event->location,
                'starts_at' => $event->starts_at?->format('Y-m-d H:i'),
                'ends_at' => $event->ends_at?->format('Y-m-d H:i'),
                'status' => $event->status,
                'is_public' => $event->is_public,
            ],
        ]);
    }
}
