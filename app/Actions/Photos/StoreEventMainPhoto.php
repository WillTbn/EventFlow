<?php

namespace App\Actions\Photos;

use App\Models\Event;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class StoreEventMainPhoto
{
    public function __construct(private StoreImageVariants $storeImageVariants)
    {
    }

    /**
     * Store the main photo for an event and generate variants.
     */
    public function handle(Event $event, UploadedFile $photo): Event
    {
        $oldPaths = array_filter([
            $event->main_photo_path,
            $event->main_photo_medium_path,
            $event->main_photo_thumb_path,
        ]);

        $paths = $this->storeImageVariants->handle(
            $photo,
            'events/' . $event->id . '/main',
            'main'
        );

        $event->forceFill([
            'main_photo_path' => $paths['original'],
            'main_photo_medium_path' => $paths['medium'],
            'main_photo_thumb_path' => $paths['thumb'],
        ])->save();

        Storage::disk('public')->delete($oldPaths);

        return $event;
    }
}
