<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['title', 'slug', 'type', 'color', 'ink', 'sort_order'])]
class Work extends Model
{
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function poet(): BelongsTo
    {
        return $this->belongsTo(Poet::class);
    }

    public function verses(): HasMany
    {
        return $this->hasMany(Verse::class)->orderBy('sort_order');
    }

    public function typeLabel(): string
    {
        return config('library.work_types.'.$this->type, '');
    }
}
