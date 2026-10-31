<?php

namespace App\Models;

use App\Models\Concerns\NormalizesArabic;
use App\Support\CardDesign;
use App\Support\QuoteLayout;
use Database\Factories\QuoteFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'content', 'category_id', 'verse_id', 'saying_id', 'author', 'book', 'page', 'source', 'source_url', 'is_original', 'is_public', 'presentation', 'mark_style', 'layout', 'story',
    'font', 'bg_color', 'text_color', 'highlight_color', 'highlight_style', 'highlights',
    'texture', 'ratio', 'font_size', 'design',
])]
class Quote extends Model
{
    /** @use HasFactory<QuoteFactory> */
    use HasFactory, NormalizesArabic;

    protected static function booted(): void
    {
        static::saving(fn (Quote $quote) => $quote->search_text = $quote->buildSearchText());
    }

    /**
     * Everything a reader might search a quote by, normalised (see NormalizesArabic).
     */
    public function buildSearchText(): string
    {
        return static::normalizeArabic(implode(' ', array_filter([$this->content, $this->author, $this->book, $this->source])));
    }

    /**
     * Same defaults as the migration, so an unsaved quote (e.g. in مرسم الحروف) renders correctly.
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
        'is_public' => true,
        'is_original' => false,
        'presentation' => 'card',
        'mark_style' => 'guillemets',
        'layout' => 'auto',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'highlights' => 'array',
            'texture' => 'boolean',
            'is_original' => 'boolean',
            'is_public' => 'boolean',
            'font_size' => 'integer',
            'design' => 'array',
        ];
    }

    /**
     * The anthologies (مختارات) this quote was added to.
     */
    public function anthologies(): BelongsToMany
    {
        return $this->belongsToMany(Anthology::class)->withTimestamps();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function bookRecord(): BelongsTo
    {
        return $this->belongsTo(Book::class, 'book_id');
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
     * The "read on" link as a safe URL (http/https only); otherwise null.
     */
    public function sourceUrl(): ?string
    {
        $url = trim((string) $this->source_url);

        return filter_var($url, FILTER_VALIDATE_URL) && preg_match('#^https?://#i', $url) ? $url : null;
    }

    /**
     * What readers see instead of a raw link: the source's name, else the book, else the site's name.
     */
    public function sourceLabel(): ?string
    {
        if (filled($this->source)) {
            return $this->source;
        }

        if (filled($this->book)) {
            return $this->book;
        }

        $host = $this->sourceUrl() ? parse_url($this->sourceUrl(), PHP_URL_HOST) : null;

        return $host ? preg_replace('/^www\./i', '', $host) : null;
    }

    /**
     * Shown to everyone (home, books, search). Private quotes live only in their owner's corner.
     *
     * @param  Builder<Quote>  $query
     */
    public function scopePublic(Builder $query): void
    {
        $query->where('quotes.is_public', true);
    }

    /**
     * Public quotes, plus the viewer's own private ones.
     *
     * @param  Builder<Quote>  $query
     */
    public function scopeVisibleTo(Builder $query, ?User $viewer): void
    {
        $query->where(fn (Builder $visible) => $visible->where('quotes.is_public', true)
            ->when($viewer, fn (Builder $own) => $own->orWhere('quotes.user_id', $viewer->id)));
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

        $query->where('search_text', 'like', '%'.static::normalizeArabic($term).'%');
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
        $count = static::public()->count();

        if ($count === 0) {
            return null;
        }

        $offset = crc32(today()->toDateString()) % $count;

        return static::public()->with(['user', 'category'])->orderBy('id')->skip($offset)->first();
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
     * The words set as the reader asked (App\Support\QuoteLayout): verses of صدر and عجز, free lines,
     * or paragraphs of lines. Every hemistich, line and paragraph line comes as pieces (see
     * contentLines()); word indexes for highlights run through them in reading order, separators left out.
     *
     * @return array{mode: 'verse'|'lines'|'prose', verses: list<array{sadr: list<array{text: string, highlighted: bool}>, ajuz: list<array{text: string, highlighted: bool}>}>, lines: list<list<array{text: string, highlighted: bool}>>, paragraphs: list<list<list<array{text: string, highlighted: bool}>>>, attribution: ?string}
     */
    public function typeset(): array
    {
        $layout = QuoteLayout::parse((string) $this->content, $this->layout ?? 'auto');
        $highlighted = array_flip($this->highlights ?? []);
        $index = 0;

        $pieces = function (string $text) use (&$index, $highlighted): array {
            $pieces = [];

            foreach (preg_split('/\s+/u', trim($text), -1, PREG_SPLIT_NO_EMPTY) as $word) {
                $isHighlighted = isset($highlighted[$index++]);
                $last = array_key_last($pieces);

                if ($last !== null && $pieces[$last]['highlighted'] === $isHighlighted) {
                    $pieces[$last]['text'] .= ' '.$word;
                } else {
                    $pieces[] = ['text' => $word, 'highlighted' => $isHighlighted];
                }
            }

            return $pieces;
        };

        $verses = [];
        foreach ($layout['verses'] as $verse) {
            $sadr = $pieces($verse['sadr']);
            $verses[] = ['sadr' => $sadr, 'ajuz' => $pieces($verse['ajuz'])];
        }

        $lines = array_map($pieces, $layout['lines']);
        $paragraphs = array_map(fn (string $paragraph) => array_map($pieces, explode("\n", $paragraph)), $layout['paragraphs']);

        return [
            'mode' => $layout['mode'],
            'verses' => $verses,
            'lines' => $lines,
            'paragraphs' => $paragraphs,
            'attribution' => $layout['attribution'],
        ];
    }

    /**
     * The card's design (quotes.design) with every key present and valid. A card from before the studio
     * had settings keeps its paper texture as it was.
     *
     * @return array<string, mixed>
     */
    public function cardDesign(): array
    {
        return CardDesign::normalize($this->design ?? ['texture' => $this->texture ? 'paper' : 'none']);
    }

    /**
     * The words as plain text for titles, copying and sharing: a verse as «صدر … عجز» on its own
     * line, free verse line by line, prose as written — the separators the reader typed left out.
     */
    public function readableText(): string
    {
        $layout = QuoteLayout::parse((string) $this->content, $this->layout ?? 'auto');

        return match ($layout['mode']) {
            'verse' => collect($layout['verses'])->map(fn (array $verse) => trim($verse['sadr'].($verse['ajuz'] !== '' ? ' … '.$verse['ajuz'] : '')))->implode("\n"),
            'lines' => implode("\n", $layout['lines']),
            default => implode("\n\n", $layout['paragraphs']),
        };
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
