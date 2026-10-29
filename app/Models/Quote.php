<?php

namespace App\Models;

use Database\Factories\QuoteFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'content', 'category_id', 'verse_id', 'saying_id', 'author', 'book', 'source', 'story',
    'font', 'bg_color', 'text_color', 'highlight_color', 'highlight_style', 'highlights',
    'texture', 'ratio', 'font_size',
])]
class Quote extends Model
{
    /** @use HasFactory<QuoteFactory> */
    use HasFactory;

    /**
     * Same defaults as the migration, so an unsaved quote (e.g. in رواق الحرف) renders correctly.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'font' => 'amiri',
        'bg_color' => '#E8E1D5',
        'text_color' => '#1E1A17',
        'highlight_color' => '#8A3324',
        'highlight_style' => 'mark',
        'texture' => true,
        'ratio' => 'wide',
        'font_size' => 34,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'highlights' => 'array',
            'texture' => 'boolean',
            'font_size' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function verse(): BelongsTo
    {
        return $this->belongsTo(Verse::class);
    }

    public function saying(): BelongsTo
    {
        return $this->belongsTo(Saying::class);
    }

    /**
     * The source as a safe link when it is a web address (http/https only); otherwise null.
     */
    public function sourceUrl(): ?string
    {
        $source = trim((string) $this->source);

        return filter_var($source, FILTER_VALIDATE_URL) && preg_match('#^https?://#i', $source) ? $source : null;
    }

    /**
     * Text taken from خزانة الأدب is published as it is and cannot be edited.
     */
    public function isFromLibrary(): bool
    {
        return $this->verse_id !== null || $this->saying_id !== null;
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Reply::class);
    }

    public function likers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'likes')->withTimestamps();
    }

    public function bookmarkers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'bookmarks')->withTimestamps();
    }

    /**
     * Eager-load what a quote card needs: sharer, category, like/reply counts,
     * and whether the current user has liked or saved it.
     *
     * @param  Builder<Quote>  $query
     */
    public function scopeWithCardData(Builder $query, ?User $viewer): void
    {
        $query->with(['user', 'category'])
            ->withCount(['likers as likes_count', 'replies']);

        if ($viewer) {
            $query->withExists([
                'likers as liked_by_viewer' => fn (Builder $likers) => $likers->whereKey($viewer->id),
                'bookmarkers as saved_by_viewer' => fn (Builder $savers) => $savers->whereKey($viewer->id),
            ]);
        }
    }

    /**
     * Search the text, the author (القائل) and the book.
     *
     * @param  Builder<Quote>  $query
     */
    public function scopeSearch(Builder $query, ?string $term): void
    {
        $term = trim((string) $term);

        if ($term === '') {
            return;
        }

        $like = '%'.$term.'%';

        $query->where(fn (Builder $matches) => $matches
            ->where('content', 'like', $like)
            ->orWhere('author', 'like', $like)
            ->orWhere('book', 'like', $like));
    }

    /**
     * latest (default) · likes (most liked) · replies (most discussed). Needs withCardData() for the counts.
     *
     * @param  Builder<Quote>  $query
     */
    public function scopeSortBy(Builder $query, ?string $sort): void
    {
        match ($sort) {
            'likes' => $query->orderByDesc('likes_count')->latest(),
            'replies' => $query->orderByDesc('replies_count')->latest(),
            default => $query->latest(),
        };
    }

    /**
     * The same quote for everyone all day, changing at midnight.
     */
    public static function ofTheDay(): ?self
    {
        $count = static::count();

        if ($count === 0) {
            return null;
        }

        $offset = crc32(today()->toDateString()) % $count;

        return static::with(['user', 'category'])->orderBy('id')->skip($offset)->first();
    }

    /**
     * The text as lines (a verse has صدر and عجز on separate lines), each split into words on
     * whitespace. Word indexes run across all lines; neighbouring words in a line that share the
     * same highlight state are merged, so adjacent highlighted words render as ONE highlight.
     *
     * @return list<list<array{text: string, highlighted: bool}>>
     */
    public function contentLines(): array
    {
        $highlighted = array_flip($this->highlights ?? []);
        $index = 0;
        $lines = [];

        foreach (preg_split('/\R/u', trim((string) $this->content)) as $line) {
            $pieces = [];

            foreach (preg_split('/\s+/u', trim($line), -1, PREG_SPLIT_NO_EMPTY) as $word) {
                $isHighlighted = isset($highlighted[$index++]);
                $last = array_key_last($pieces);

                if ($last !== null && $pieces[$last]['highlighted'] === $isHighlighted) {
                    $pieces[$last]['text'] .= ' '.$word;
                } else {
                    $pieces[] = ['text' => $word, 'highlighted' => $isHighlighted];
                }
            }

            if ($pieces !== []) {
                $lines[] = $pieces;
            }
        }

        return $lines;
    }

    /**
     * All pieces of a one-line text (see contentLines()).
     *
     * @return list<array{text: string, highlighted: bool}>
     */
    public function contentPieces(): array
    {
        return array_merge(...$this->contentLines() ?: [[]]);
    }
}
