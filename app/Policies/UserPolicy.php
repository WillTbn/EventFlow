<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Before checks for admin users.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($this->roleFor($user) === 'admin') {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view any users.
     */
    public function viewAny(User $user): bool
    {
        return $this->roleFor($user) === 'moderator';
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        return $this->roleFor($user) === 'moderator'
            && $this->roleFor($model) === 'member';
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $this->roleFor($user) === 'moderator';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        return $this->roleFor($user) === 'moderator'
            && $this->roleFor($model) === 'member';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        return $this->roleFor($user) === 'moderator'
            && $this->roleFor($model) === 'member';
    }

    private function roleFor(User $user): ?string
    {
        return $user->tenantRole();
    }
}
