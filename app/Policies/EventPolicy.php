<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;

class EventPolicy
{
    /**
     * Determine whether the user can view any events.
     */
    public function viewAny(User $user): bool
    {
        return $this->canManageEvents($user);
    }

    /**
     * Determine whether the user can view the event.
     */
    public function view(?User $user, Event $event): bool
    {
        if ($event->is_public && $event->status === 'published') {
            return true;
        }

        return $user?->id === $event->created_by;
    }

    /**
     * Determine whether the user can create events.
     */
    public function create(User $user): bool
    {
        return $user->hasTenantRole('admin');
    }

    /**
     * Determine whether the user can update the event.
     */
    public function update(User $user, Event $event): bool
    {
        return $this->canManageEvents($user);
    }

    /**
     * Determine whether the user can upload story photos.
     */
    public function addPhotos(User $user, Event $event): bool
    {
        if (! $this->canManageEvents($user)) {
            return false;
        }

        if (! $event->ends_at) {
            return false;
        }

        return $event->ends_at->isPast();
    }

    /**
     * Determine whether the user can delete the event.
     */
    public function delete(User $user, Event $event): bool
    {
        return $user->hasTenantRole('admin');
    }

    private function canManageEvents(User $user): bool
    {
        return $user->hasTenantRole(['admin', 'moderator']);
    }
}
