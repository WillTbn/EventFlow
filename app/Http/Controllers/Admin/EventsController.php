<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Events\CreateEvent;
use App\Actions\Events\UpdateEvent;
use App\Actions\Photos\StoreEventMainPhoto;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreEventRequest;
use App\Http\Requests\Admin\UpdateEventRequest;
use App\Models\Event;
use App\Models\EventPhoto;
use App\Models\Tenant;
use App\Services\PlanService;
use App\Services\TenantContext;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class EventsController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private TenantContext $tenantContext,
        private PlanService $planService
    )
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

        $tenant = $this->tenantContext->get();

        return Inertia::render('admin/events/Index', [
            'events' => $events,
            'eventQuota' => $this->eventQuotaPayload($tenant),
        ]);
    }

    /**
     * Show the form for creating a new event.
     */
    public function create(): Response
    {
        $tenant = $this->tenantContext->get();

        return Inertia::render('admin/events/Create', [
            'eventQuota' => $this->eventQuotaPayload($tenant),
        ]);
    }

    /**
     * Store a newly created event in storage.
     */
    public function store(
        StoreEventRequest $request,
        CreateEvent $createEvent,
        StoreEventMainPhoto $storeEventMainPhoto
    ): RedirectResponse {
        $event = $createEvent->handle($request->user(), $request->validated());

        if ($request->hasFile('main_photo')) {
            $storeEventMainPhoto->handle($event, $request->file('main_photo'));
        }

        return to_route('admin.eventos.edit', [
            'tenantSlug' => $this->tenantContext->get()?->slug,
            'event' => $event,
        ]);
    }

    /**
     * Show the form for editing the specified event.
     */
    public function edit(string $tenantSlug, Event $event): Response
    {
        $photos = $event->photos()
            ->latest()
            ->take(12)
            ->get()
            ->map(fn (EventPhoto $photo) => [
                'id' => $photo->id,
                'thumb_url' => Storage::disk('public')->url($photo->thumb_path),
            ]);

        return Inertia::render('admin/events/Edit', [
            'event' => [
                'id' => $event->id,
                'title' => $event->title,
                'description' => $event->description,
                'location' => $event->location,
                'starts_at' => $event->starts_at?->format('Y-m-d\TH:i'),
                'ends_at' => $event->ends_at?->format('Y-m-d\TH:i'),
                'status' => $event->status,
                'is_public' => $event->is_public,
                'capacity' => $event->capacity,
                'main_photo_url' => $event->main_photo_thumb_path
                    ? Storage::disk('public')->url($event->main_photo_thumb_path)
                    : null,
                'can_add_photos' => $event->ends_at?->isPast() ?? false,
            ],
            'photos' => $photos,
        ]);
    }

    /**
     * Update the specified event in storage.
     */
    public function update(UpdateEventRequest $request, string $tenantSlug, Event $event, UpdateEvent $updateEvent): RedirectResponse
    {
        $updateEvent->handle($event, $request->validated());

        return to_route('admin.eventos.edit', [
            'tenantSlug' => $this->tenantContext->get()?->slug,
            'event' => $event,
        ]);
    }

    /**
     * Remove the specified event from storage.
     */
    public function destroy(string $tenantSlug, Event $event): RedirectResponse
    {
        $event->delete();

        return to_route('admin.eventos.index', [
            'tenantSlug' => $this->tenantContext->get()?->slug,
        ]);
    }

    /**
     * Build the event quota payload for the UI.
     *
     * @return array<string, mixed>
     */
    protected function eventQuotaPayload(?Tenant $tenant): array
    {
        if (! $tenant) {
            return [
                'plan' => null,
                'events_this_month' => 0,
                'events_limit' => null,
                'can_create_event' => false,
            ];
        }

        return [
            'plan' => $this->planService->planLabel($tenant),
            'events_this_month' => $this->planService->eventsCreatedThisMonth($tenant),
            'events_limit' => $this->planService->eventLimitPerMonth($tenant),
            'can_create_event' => $this->planService->canCreateEvent($tenant),
        ];
    }
}


