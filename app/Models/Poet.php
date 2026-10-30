<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

#[Fillable(['name', 'slug', 'era', 'years', 'region', 'bio', 'is_peninsula', 'color', 'sort_order', 'wikisource_url', 'aldiwan_url'])]
class Poet extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['is_peninsula' => 'boolean'];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function works(): HasMany
    {
        return $this->hasMany(Work::class)->orderBy('sort_order');
    }

    /**
     * Readers who added this poet to شعرائي.
     */
    public function fans(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function verses(): HasManyThrough
    {
        return $this->hasManyThrough(Verse::class, Work::class);
    }

    public function eraLabel(): string
    {
        return config('library.eras.'.$this->era, '');
    }
}
