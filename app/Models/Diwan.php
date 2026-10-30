<?php

namespace App\Models;

use Database\Factories\DiwanFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * ديوان قارئ: a poet and the verses a reader copied out with their source. Not reviewed by مِداد,
 * so it lives apart from خزانة الأدب and is always labelled as a reader's contribution.
 */
#[Fillable(['poet_name', 'poet_death_year', 'title', 'about', 'source', 'color', 'is_public'])]
class Diwan extends Model
{
    /** @use HasFactory<DiwanFactory> */
    use HasFactory;

    /**
     * Saudi copyright law (Art. 19): a poet's work is protected for their life and 50 years after.
     */
    public const PROTECTION_YEARS = 50;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['is_public' => 'boolean', 'poet_death_year' => 'integer'];
    }

    /**
     * Only a poet who died more than 50 years ago (Gregorian) can be published for everyone.
     */
    public static function isInPublicDomain(?int $deathYear): bool
    {
        return $deathYear !== null && $deathYear + self::PROTECTION_YEARS < (int) now()->year;
    }

    public function canBePublic(): bool
    {
        return static::isInPublicDomain($this->poet_death_year);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function verses(): HasMany
    {
        return $this->hasMany(DiwanVerse::class)->orderBy('id');
    }

    /**
     * Diwans the viewer may see: public ones (still in the public domain), plus their own.
     *
     * @param  Builder<Diwan>  $query
     */
    public function scopeVisibleTo(Builder $query, ?User $viewer): void
    {
        $query->where(fn (Builder $visible) => $visible->where('is_public', true)
            ->when($viewer, fn (Builder $own) => $own->orWhere('user_id', $viewer->id)));
    }

    /**
     * The source is a link only when it is an http(s) URL; anything else is shown as plain text.
     */
    public function sourceUrl(): ?string
    {
        return preg_match('#^https?://#i', (string) $this->source) && filter_var($this->source, FILTER_VALIDATE_URL)
            ? $this->source
            : null;
    }

    /**
     * @return array{name: string, bg: string, text: string, highlight: string}
     */
    public function colors(): array
    {
        return config('quotes.palettes.'.$this->color, config('quotes.palettes.cocoa'));
    }
}
