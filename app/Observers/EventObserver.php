<?php

namespace App\Observers;

use App\Models\Event;
use Vinkla\Hashids\Facades\Hashids;
class EventObserver
{

    /**
     * Handle the Event "created" event.
     */
    public function creating(Event $event): void
    {
        if ($event->hash_id) {
            return;
        }

        $event->hash_id = Hashids::encode(now()->timestamp);

    }
}
