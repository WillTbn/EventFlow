<?php

namespace App\Observers;

use App\Models\User;
use Vinkla\Hashids\Facades\Hashids;

class UserObserver
{
    /**
     * Handle the User "creating" event.
     */
    public function creating(User $user): void
    {
        if ($user->hash_id) {
            return;
        }

        $user->hash_id = Hashids::encode(now()->timestamp, random_int(1, 999_999));
    }
}
