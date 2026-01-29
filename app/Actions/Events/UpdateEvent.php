<?php

namespace App\Actions\Events;

use App\Models\Event;
use App\Services\TenantContext;
use Illuminate\Support\Str;

class UpdateEvent
{
    public function __construct(private TenantContext $tenantContext)
    {
    }

    /**
     * Update the event with the provided data.
     *
     * @param array<string, mixed>
     */
    public function handle(Event $event, array $attributes): Event
    {
        $event->fill($attributes);

        if ($event->isDirty('title')) {
            $event->slug = $this->makeUniqueSlug($event->title, $event->id);
        }

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
