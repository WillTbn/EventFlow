<?php

namespace App\Actions\Events;

use App\Models\Event;
use App\Models\User;
use App\Services\TenantContext;
use Illuminate\Support\Str;

class CreateEvent
{
    public function __construct(private TenantContext $tenantContext)
    {
    }

    /**
     * Create a new event for the given user.
     *
     * @param array<string, mixed>
     */
    public function handle(User $user, array $attributes): Event
    {
        $event = new Event();
        $event->fill($attributes);
        $event->created_by = $user->id;
        $event->slug = $this->makeUniqueSlug($event->title);
        $event->save();

        return $event;
    }

    protected function makeUniqueSlug(string $title, ?int $exceptId = null): string
    {
        $tenantId = $this->tenantContext->id();
        $baseSlug = Str::slug($title);
        $slug = $baseSlug;
        $counter = 1;

        while (
            Event::query()
                ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
                ->where('slug', $slug)
                ->when($exceptId, fn ($query) => $query->whereKeyNot($exceptId))
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
