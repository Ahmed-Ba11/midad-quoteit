<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['sadr', 'ajuz', 'note'])]
class DiwanVerse extends Model
{
    public function diwan(): BelongsTo
    {
        return $this->belongsTo(Diwan::class);
    }

    /**
     * The verse as quote text: صدر on the first line, عجز (if any) on the second.
     */
    public function asText(): string
    {
        return trim($this->sadr."\n".$this->ajuz);
    }
}
