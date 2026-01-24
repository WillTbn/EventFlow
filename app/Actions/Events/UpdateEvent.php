<?php

namespace App\Actions\Events;

use App\Models\Event;
use Illuminate\Support\Str;

class UpdateEvent
{
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
        $baseSlug = Str::slug($title);
        $slug = $baseSlug;
        $counter = 1;

        while (
            Event::query()
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
