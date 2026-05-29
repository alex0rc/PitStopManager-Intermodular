<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResultResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'race_id' => $this->race_id,
            'user_id' => $this->user_id,
            'position' => $this->position,
            'best_lap_time' => $this->best_lap_time,
            'total_time' => $this->total_time,
            'points' => $this->points,
            'dnf' => $this->dnf,
            'dsq' => $this->dsq,
            'notes' => $this->notes,
            'user' => new UserResource($this->whenLoaded('user')),
            'race' => new RaceResource($this->whenLoaded('race')),
        ];
    }
}
