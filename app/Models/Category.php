<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    protected $fillable = [
        'name',
        'description',
    ];

    public function monsters(): BelongsToMany
    {
        return $this->belongsToMany(
            Monster::class,
            'category_monster'
        );
    }
}