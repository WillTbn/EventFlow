<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Services\TenantContext;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class WorkspaceController extends Controller
{
    /**
     * Display the workspace public landing page.
     */
    public function show(): Response
    {
        $tenant = app(TenantContext::class)->get();

        $events = Event::query()
            ->where('is_public', true)
            ->where('status', 'published')
            ->orderBy('starts_at')
            ->paginate(6)
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

        return Inertia::render('public/workspace/Show', [
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
}
