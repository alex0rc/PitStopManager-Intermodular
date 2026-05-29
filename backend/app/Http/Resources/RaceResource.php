<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RaceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'championship_id' => $this->championship_id,
            'circuit_id' => $this->circuit_id,
            'name' => $this->name,
            'scheduled_at' => $this->scheduled_at,
            'total_laps' => $this->total_laps,
            'status' => $this->status,
            'notes' => $this->notes,
            'championship' => new ChampionshipResource($this->whenLoaded('championship')),
            'circuit' => new CircuitResource($this->whenLoaded('circuit')),
        ];
    }
}
