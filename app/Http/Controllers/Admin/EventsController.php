<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Events\CreateEvent;
use App\Actions\Events\UpdateEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreEventRequest;
use App\Http\Requests\Admin\UpdateEventRequest;
use App\Models\Event;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EventsController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        // Map resource abilities to policy methods.
        $this->authorizeResource(Event::class, 'event');
    }

    /**
     * Display a listing of the events.
     */
    public function index(Request $request): Response
    {
        $events = Event::query()
            ->where('created_by', $request->user()->id)
            ->latest('starts_at')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (Event $event) => [
                'id' => $event->id,
                'title' => $event->title,
                'status' => $event->status,
                'is_public' => $event->is_public,
                'starts_at' => $event->starts_at?->format('Y-m-d H:i'),
                'ends_at' => $event->ends_at?->format('Y-m-d H:i'),
            ]);

        return Inertia::render('admin/events/Index', [
            'events' => $events,
        ]);
    }

    /**
     * Show the form for creating a new event.
     */
    public function create(): Response
    {
        return Inertia::render('admin/events/Create');
    }

    /**
     * Store a newly created event in storage.
     */
    public function store(StoreEventRequest $request, CreateEvent $createEvent): RedirectResponse
    {
        $event = $createEvent->handle($request->user(), $request->validated());

        return to_route('admin.eventos.edit', $event);
    }

    /**
     * Show the form for editing the specified event.
     */
    public function edit(Event $event): Response
    {
        return Inertia::render('admin/events/Edit', [
            'event' => [
                'id' => $event->id,
                'title' => $event->title,
                'description' => $event->description,
                'location' => $event->location,
                'starts_at' => $event->starts_at?->format('Y-m-d\\TH:i'),
                'ends_at' => $event->ends_at?->format('Y-m-d\\TH:i'),
                'status' => $event->status,
                'is_public' => $event->is_public,
                'capacity' => $event->capacity,
            ],
        ]);
    }

    /**
     * Update the specified event in storage.
     */
    public function update(UpdateEventRequest $request, Event $event, UpdateEvent $updateEvent): RedirectResponse
    {
        $updateEvent->handle($event, $request->validated());

        return to_route('admin.eventos.edit', $event);
    }

    /**
     * Remove the specified event from storage.
     */
    public function destroy(Event $event): RedirectResponse
    {
        $event->delete();

        return to_route('admin.eventos.index');
    }
}
