<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'min_age',
        'max_age',
        'max_weight_kg',
    ];

    public function championships(): HasMany
    {
        return $this->hasMany(Championship::class);
    }
}
