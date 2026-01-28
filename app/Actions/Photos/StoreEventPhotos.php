<?php

namespace App\Actions\Photos;

use App\Models\Event;
use App\Models\EventPhoto;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

class StoreEventPhotos
{
    public function __construct(private StoreImageVariants $storeImageVariants)
    {
    }

    /**
     * Store story photos for an event.
     *
     * @param array<int, UploadedFile> $photos
     * @return Collection<int, EventPhoto>
     */
    public function handle(Event $event, User $user, array $photos): Collection
    {
        $storedPhotos = collect();

        foreach ($photos as $photo) {
            $paths = $this->storeImageVariants->handle(
                $photo,
                'events/' . $event->id . '/story',
                'story'
            );

            $storedPhotos->push(EventPhoto::create([
                'event_id' => $event->id,
                'uploaded_by' => $user->id,
                'original_path' => $paths['original'],
                'medium_path' => $paths['medium'],
                'thumb_path' => $paths['thumb'],
            ]));
        }

        return $storedPhotos;
    }
}
