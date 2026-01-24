<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
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
                'slug' => $event->slug,
                'location' => $event->location,
                'starts_at' => $event->starts_at?->format('Y-m-d H:i'),
                'ends_at' => $event->ends_at?->format('Y-m-d H:i'),
            ]);

        return Inertia::render('public/events/Index', [
            'events' => $events,
        ]);
    }

    /**
     * Display the specified event.
     */
    public function show(Event $event): Response
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
