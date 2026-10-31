<?php

namespace App\Models;

use App\Models\Concerns\NormalizesArabic;
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
    use HasFactory, NormalizesArabic;

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
     * A book is seen once someone quoted it publicly; a book known only from private quotes is seen
     * only by a reader who quoted it or has it on their shelf (a private quote never reveals a title).
     */
    public function isVisibleTo(?User $user): bool
    {
        return $this->quotes()->public()->exists()
            || ($user !== null && ($this->quotes()->whereBelongsTo($user)->exists() || $user->books()->whereKey($this->id)->exists()));
    }

    /**
     * The same book written two ways ("الأيام" / "الايام", with or without tashkeel) is one book.
     */
    public static function matchKey(string $title, ?string $author): string
    {
        return mb_substr(static::normalizeArabic($title).'|'.static::normalizeArabic($author), 0, 255);
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
