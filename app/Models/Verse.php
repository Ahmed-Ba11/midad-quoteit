<?php

namespace App\Models;

use App\Models\Concerns\NormalizesArabic;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['sadr', 'ajuz', 'occasion', 'sort_order', 'source_url', 'explanation', 'glossary'])]
class Verse extends Model
{
    use NormalizesArabic;

    /**
     * Store the verse, its diwan and poet as normalised search text (see NormalizesArabic).
     */
    public function refreshSearchText(): void
    {
        $this->loadMissing('work.poet');
        $this->search_text = static::normalizeArabic(implode(' ', [$this->sadr, $this->ajuz, $this->work->title, $this->work->poet->name]));
        $this->saveQuietly();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['glossary' => 'array'];
    }

    /**
     * The verse's words without diacritics, for looking each one up in لسان العرب.
     *
     * @return list<string>
     */
    public function lookupWords(): array
    {
        $plain = preg_replace('/[\x{0610}-\x{061A}\x{064B}-\x{065F}\x{0670}\x{0640}]/u', '', $this->sadr.' '.$this->ajuz);

        return array_values(array_unique(array_filter(
            preg_split('/\s+/u', $plain, -1, PREG_SPLIT_NO_EMPTY),
            fn (string $word) => mb_strlen($word) > 2,
        )));
    }

    public function work(): BelongsTo
    {
        return $this->belongsTo(Work::class);
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }

    /**
     * بيت اليوم: one verified verse, the same all day, a different one tomorrow.
     */
    public static function ofTheDay(): ?self
    {
        $count = static::count();

        return $count === 0 ? null
            : static::with('work.poet')->orderBy('id')->skip(crc32(today()->toDateString()) % $count)->first();
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
        $query->where('search_text', 'like', '%'.static::normalizeArabic($term).'%');
    }
}
