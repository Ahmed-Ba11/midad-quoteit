<?php

namespace App\Policies;

use App\Models\Quote;
use App\Models\User;

class QuotePolicy
{
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
