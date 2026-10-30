<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['text', 'source', 'meaning', 'source_url'])]
class Saying extends Model
{
    /**
     * Who said it, without the book: "مثل عربي — مجمع الأمثال للميداني" → "مثل عربي".
     */
    public function attribution(): string
    {
        return trim(explode('—', $this->source)[0]);
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }
}
