<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InscriptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'championship_id' => $this->championship_id,
            'status' => $this->status,
            'car_number' => $this->car_number,
            'kart_info' => $this->kart_info,
            'user' => new UserResource($this->whenLoaded('user')),
            'championship' => new ChampionshipResource($this->whenLoaded('championship')),
            'races' => $this->whenLoaded(
                'races',
                fn () => $this->races->map(fn ($race) => (new RaceResource($race))->resolve())->values()->all(),
            ),
            'created_at' => $this->created_at,
        ];
    }
}
