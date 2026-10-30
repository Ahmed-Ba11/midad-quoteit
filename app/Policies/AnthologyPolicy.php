<?php

namespace App\Policies;

use App\Models\Anthology;
use App\Models\User;

class AnthologyPolicy
{
    /**
     * Anyone can see a public anthology (مختارات); a private one is its owner's only.
     */
    public function view(?User $user, Anthology $anthology): bool
    {
        return $anthology->is_public || $user?->id === $anthology->user_id;
    }

    /**
     * Determine whether the user can update the anthology (مختارات) (owner only).
     */
    public function update(User $user, Anthology $anthology): bool
    {
        return $user->id === $anthology->user_id;
    }

    /**
     * Determine whether the user can delete the anthology (مختارات) (owner only).
     */
    public function delete(User $user, Anthology $anthology): bool
    {
        return $user->id === $anthology->user_id;
    }
}
