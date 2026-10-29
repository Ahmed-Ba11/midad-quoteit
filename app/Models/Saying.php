<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['text', 'source'])]
class Saying extends Model
{
    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }
}
