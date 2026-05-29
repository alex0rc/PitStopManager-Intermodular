<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChampionshipResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'category_id' => $this->category_id,
            'kart_modality' => $this->kart_modality ?? 'rental',
            'engine_class' => $this->engine_class,
            'name' => $this->name,
            'description' => $this->description,
            'image' => $this->image ? url('storage/' . $this->image) : null,
            'venue_country' => $this->venue_country,
            'venue_province' => $this->venue_province,
            'venue_city' => $this->venue_city,
            'venue_latitude' => $this->venue_latitude,
            'venue_longitude' => $this->venue_longitude,
            'season_year' => $this->season_year,
            'status' => $this->status,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'user' => new UserResource($this->whenLoaded('user')),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'races' => RaceResource::collection($this->whenLoaded('races')),
            'races_count' => $this->whenCounted('races'),
        ];
    }
}
