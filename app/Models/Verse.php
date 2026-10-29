<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['sadr', 'ajuz', 'occasion', 'sort_order'])]
class Verse extends Model
{
    public function work(): BelongsTo
    {
        return $this->belongsTo(Work::class);
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }

    /**
     * The verse as quote text: صدر on the first line, عجز on the second.
     */
    public function asText(): string
    {
        return $this->sadr."\n".$this->ajuz;
    }

    /**
     * Search the hemistichs and the poet's name.
     *
     * @param  Builder<Verse>  $query
     */
    public function scopeSearch(Builder $query, string $term): void
    {
        $like = '%'.$term.'%';

        $query->where(fn (Builder $matches) => $matches
            ->where('sadr', 'like', $like)
            ->orWhere('ajuz', 'like', $like)
            ->orWhereHas('work', fn (Builder $work) => $work
                ->where('title', 'like', $like)
                ->orWhereHas('poet', fn (Builder $poet) => $poet->where('name', 'like', $like))));
    }
}
