<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Result extends Model
{
    use HasFactory;

    protected $fillable = [
        'race_id',
        'user_id',
        'position',
        'best_lap_time',
        'total_time',
        'points',
        'dnf',
        'dsq',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'dnf' => 'boolean',
            'dsq' => 'boolean',
        ];
    }

    public function race(): BelongsTo
    {
        return $this->belongsTo(Race::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
