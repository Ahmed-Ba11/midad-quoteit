<?php

namespace App\Models;

use Database\Factories\BookFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['title', 'author', 'genre', 'match_key'])]
class Book extends Model
{
    /** @use HasFactory<BookFactory> */
    use HasFactory;

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }

    /**
     * Readers who put this book on their shelf (كتبي), with their reading status.
     */
    public function readers(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('status')->withTimestamps();
    }

    /**
     * The same book written two ways ("الأيام" / "الايام", with or without tashkeel) is one book.
     */
    public static function matchKey(string $title, ?string $author): string
    {
        $normalise = fn (?string $text) => trim(preg_replace('/\s+/u', ' ', strtr(
            preg_replace('/[\x{0610}-\x{061A}\x{064B}-\x{065F}\x{0670}\x{0640}]/u', '', mb_strtolower((string) $text)),
            ['أ' => 'ا', 'إ' => 'ا', 'آ' => 'ا', 'ٱ' => 'ا', 'ة' => 'ه', 'ى' => 'ي', 'ؤ' => 'و', 'ئ' => 'ي'],
        )));

        return mb_substr($normalise($title).'|'.$normalise($author), 0, 255);
    }

    /**
     * The book a reader names when quoting: reuse it if it exists, otherwise add it.
     */
    public static function findOrAdd(string $title, ?string $author, ?User $addedBy): self
    {
        $book = static::firstOrNew(['match_key' => static::matchKey($title, $author)]);

        if (! $book->exists) {
            $book->fill(['title' => trim($title), 'author' => $author ? trim($author) : null]);
            $book->added_by = $addedBy?->id;
            $book->save();
        }

        return $book;
    }

    /**
     * A fixed cover colour for the generated cover, picked from the card palettes by the title.
     *
     * @return array{bg: string, text: string, highlight: string}
     */
    public function coverColors(): array
    {
        $palettes = array_values(config('quotes.palettes'));

        return $palettes[crc32($this->match_key ?? $this->title) % count($palettes)];
    }

    public function genreLabel(): ?string
    {
        return $this->genre ? config('quotes.book_genres.'.$this->genre) : null;
    }
}
