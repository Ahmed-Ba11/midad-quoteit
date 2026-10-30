<?php

namespace App\Policies;

use App\Models\Diwan;
use App\Models\User;

class DiwanPolicy
{
    /**
     * Anyone can see a public reader's diwan; a private one is its owner's only.
     */
    public function view(?User $user, Diwan $diwan): bool
    {
        return $diwan->is_public || $user?->id === $diwan->user_id;
    }

    /**
     * Determine whether the user can update the reader's diwan (owner only).
     */
    public function update(User $user, Diwan $diwan): bool
    {
        return $user->id === $diwan->user_id;
    }

    /**
     * Determine whether the user can delete the reader's diwan (owner only).
     */
    public function delete(User $user, Diwan $diwan): bool
    {
        return $user->id === $diwan->user_id;
    }
}
