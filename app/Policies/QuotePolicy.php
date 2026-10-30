<?php

namespace App\Policies;

use App\Models\Quote;
use App\Models\User;

class QuotePolicy
{
    /**
     * A public quote is everyone's to read and share; a private one exists only in its owner's corner.
     * This also guards liking, saving, replying and adding to anthologies.
     */
    public function view(?User $user, Quote $quote): bool
    {
        return $quote->is_public || $user?->id === $quote->user_id;
    }

    /**
     * Determine whether the user can update the quote (owner only).
     */
    public function update(User $user, Quote $quote): bool
    {
        return $user->id === $quote->user_id;
    }

    /**
     * Determine whether the user can delete the quote (owner only).
     */
    public function delete(User $user, Quote $quote): bool
    {
        return $user->id === $quote->user_id;
    }
}
