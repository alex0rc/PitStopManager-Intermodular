<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Inscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'championship_id',
        'status',
        'car_number',
        'kart_info',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function championship(): BelongsTo
    {
        return $this->belongsTo(Championship::class);
    }

    public function races(): BelongsToMany
    {
        return $this->belongsToMany(Race::class, 'inscription_race');
    }

    public function isRegisteredForRace(int $raceId): bool
    {
        return $this->races()->where('races.id', $raceId)->exists();
    }
}
