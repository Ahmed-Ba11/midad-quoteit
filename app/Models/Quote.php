<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['content'])]
class Quote extends Model
{
    public function user() {
        return $this->belongsTo(User::class);
    }
}
