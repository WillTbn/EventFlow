<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEventRsvpRequest;
use App\Models\Event;
use App\Models\EventPhoto;
use App\Models\EventRsvp;
use App\Services\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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
                'hash_id' => $event->hash_id,
                'title' => $event->title,
                'description' => $event->description,
                'location' => $event->location,
                'starts_at' => $event->starts_at?->format('Y-m-d H:i'),
                'ends_at' => $event->ends_at?->format('Y-m-d H:i'),
                'status' => $event->status,
                'is_public' => $event->is_public,
                'capacity' => $event->capacity,
                'rsvp_count' => EventRsvp::query()
                    ->where('event_id', $event->id)
                    ->count(),
                'cover_url' => $event->main_photo_medium_path
                    ? Storage::disk('public')->url($event->main_photo_medium_path)
                    : null,
            ],
            'photos' => $photos,
        ]);
    }

    /**
     * Store or update a public RSVP for the event.
     */
    public function rsvp(StoreEventRsvpRequest $request, string $tenantSlug, Event $event): JsonResponse
    {
        if (! $event->is_public || $event->status !== 'published') {
            abort(404);
        }

        $tenant = app(TenantContext::class)->get();

        if (! $tenant || $tenant->id !== $event->tenant_id) {
            abort(404);
        }

        $data = $request->validated();
        $email = Str::lower($data['email']);

        $rsvp = EventRsvp::query()->updateOrCreate(
            [
                'event_id' => $event->id,
                'email' => $email,
            ],
            [
                'workspace_id' => $tenant->id,
                'name' => $data['name'],
                'phone' => $data['phone_whatsapp'] ?? null,
                'communication_preference' => $data['communication_preference'],
                'notifications_scope' => $data['notifications_scope'],
                'status' => 'going',
                'source' => 'public_page',
            ],
        );

        return response()->json([
            'status' => $rsvp->wasRecentlyCreated ? 'created' : 'updated',
        ]);
    }

    /**
     * Return an ICS calendar file for the event.
     */
    public function calendar(string $tenantSlug, Event $event)
    {
        if (! $event->is_public || $event->status !== 'published') {
            abort(404);
        }

        $tenant = app(TenantContext::class)->get();

        if (! $tenant || $tenant->id !== $event->tenant_id) {
            abort(404);
        }

        $start = $event->starts_at;
        $end = $event->ends_at ?? $event->starts_at?->copy()->addHours(2);
        $eventUrl = route('eventos.show', [
            'tenantSlug' => $tenant->slug,
            'event' => $event->hash_id,
        ]);

        $uidHost = parse_url(config('app.url'), PHP_URL_HOST) ?? 'eventflow.local';
        $uid = $event->id.'@'.$uidHost;

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//EventFlow//Public Events//PT',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'BEGIN:VEVENT',
            'UID:'.$uid,
            'SUMMARY:'.self::escapeIcsText($event->title),
            $start ? 'DTSTART:'.$start->clone()->utc()->format('Ymd\THis\Z') : null,
            $end ? 'DTEND:'.$end->clone()->utc()->format('Ymd\THis\Z') : null,
            'DESCRIPTION:'.self::escapeIcsText(trim(($event->description ?? '')."\n\n".$eventUrl)),
            'LOCATION:'.self::escapeIcsText($event->location ?? ''),
            'URL:'.$eventUrl,
            'ORGANIZER:'.self::escapeIcsText($tenant->name ?? 'Workspace'),
            'END:VEVENT',
            'END:VCALENDAR',
        ];

        $content = implode("\r\n", array_values(array_filter($lines)));

        return response($content, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="evento-'.$event->hash_id.'.ics"',
        ]);
    }

    private static function escapeIcsText(string $value): string
    {
        $value = str_replace(["\r\n", "\n", "\r"], '\\n', $value);
        return str_replace([',', ';'], ['\\,', '\\;'], $value);
    }
}
