<?php

namespace App\Models;

use Database\Factories\AnthologyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * مختارات: a reader's named selection of quotes (their own or anyone's), public or private.
 */
#[Fillable(['title', 'description', 'palette', 'is_public'])]
class Anthology extends Model
{
    /** @use HasFactory<AnthologyFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['is_public' => 'boolean'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function quotes(): BelongsToMany
    {
        return $this->belongsToMany(Quote::class)->withTimestamps();
    }

    /**
     * Anthologies the viewer may see: every public one, plus their own private ones.
     *
     * @param  Builder<Anthology>  $query
     */
    public function scopeVisibleTo(Builder $query, ?User $viewer): void
    {
        $query->where(fn (Builder $visible) => $visible->where('is_public', true)
            ->when($viewer, fn (Builder $own) => $own->orWhere('user_id', $viewer->id)));
    }

    /**
     * @return array{name: string, bg: string, text: string, highlight: string}
     */
    public function colors(): array
    {
        return config('quotes.palettes.'.$this->palette, config('quotes.palettes.sand'));
    }
}
