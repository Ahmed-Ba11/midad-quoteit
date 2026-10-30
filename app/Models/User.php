<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'bio', 'accent', 'badge', 'show_poets'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'show_poets' => 'boolean',
        ];
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Reply::class);
    }

    public function likedQuotes(): BelongsToMany
    {
        return $this->belongsToMany(Quote::class, 'likes')->withTimestamps();
    }

    /**
     * The reader's shelf (كتبي) with the reading status of each book.
     */
    public function books(): BelongsToMany
    {
        return $this->belongsToMany(Book::class)->withPivot('status')->withTimestamps();
    }

    public function bookmarkedQuotes(): BelongsToMany
    {
        return $this->belongsToMany(Quote::class, 'bookmarks')->withTimestamps();
    }

    /**
     * The one quote shown large at the top of ركني.
     */
    public function pinnedQuote(): BelongsTo
    {
        return $this->belongsTo(Quote::class, 'pinned_quote_id');
    }

    /**
     * مختاراتي: the reader's named selections of quotes.
     */
    public function anthologies(): HasMany
    {
        return $this->hasMany(Anthology::class);
    }

    /**
     * شعرائي: library poets the reader keeps close.
     */
    public function favoritePoets(): BelongsToMany
    {
        return $this->belongsToMany(Poet::class)->withTimestamps();
    }

    /**
     * دواويني: poets and verses the reader copied out with their source.
     */
    public function diwans(): HasMany
    {
        return $this->hasMany(Diwan::class);
    }

    /**
     * The reader's chosen palette for ركني (config/quotes.php), or the default sand.
     *
     * @return array{name: string, bg: string, text: string, highlight: string}
     */
    public function accentColors(): array
    {
        return config('quotes.palettes.'.$this->accent, config('quotes.palettes.sand'));
    }
}
