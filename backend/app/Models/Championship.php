<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Championship extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'kart_modality',
        'engine_class',
        'name',
        'description',
        'image',
        'venue_country',
        'venue_province',
        'venue_city',
        'venue_latitude',
        'venue_longitude',
        'season_year',
        'status',
        'start_date',
        'end_date',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'venue_latitude' => 'decimal:8',
            'venue_longitude' => 'decimal:8',
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

    public function races(): HasMany
    {
        return $this->hasMany(Race::class);
    }

    public function inscriptions(): HasMany
    {
        return $this->hasMany(Inscription::class);
    }

    public function usesRentalKarts(): bool
    {
        return $this->kart_modality !== 'own';
    }

    public function usesOwnKarts(): bool
    {
        return $this->kart_modality === 'own';
    }
}
