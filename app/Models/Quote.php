<?php

namespace App\Models;

use Database\Factories\QuoteFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'content', 'category_id', 'author', 'book', 'story',
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

    /**
     * Split the text into words (on whitespace) and merge neighbouring words that share the same
     * highlight state, so adjacent highlighted words render as ONE continuous highlight.
     *
     * @return list<array{text: string, highlighted: bool}>
     */
    public function contentPieces(): array
    {
        $words = preg_split('/\s+/u', trim((string) $this->content), -1, PREG_SPLIT_NO_EMPTY);
        $highlighted = array_flip($this->highlights ?? []);
        $pieces = [];

        foreach ($words as $index => $word) {
            $isHighlighted = isset($highlighted[$index]);
            $last = array_key_last($pieces);

            if ($last !== null && $pieces[$last]['highlighted'] === $isHighlighted) {
                $pieces[$last]['text'] .= ' '.$word;
            } else {
                $pieces[] = ['text' => $word, 'highlighted' => $isHighlighted];
            }
        }

        return $pieces;
    }
}
