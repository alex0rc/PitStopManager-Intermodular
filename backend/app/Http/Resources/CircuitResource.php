<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CircuitResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'name' => $this->name,
            'location' => $this->location,
            'city' => $this->city,
            'province' => $this->province,
            'country' => $this->country,
            'status' => $this->status,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'length_meters' => $this->length_meters,
            'image' => $this->image ? url('storage/' . $this->image) : null,
            'description' => $this->description,
            'user' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
