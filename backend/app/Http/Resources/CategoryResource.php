<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'min_age' => $this->min_age,
            'max_age' => $this->max_age,
            'max_weight_kg' => $this->max_weight_kg,
        ];
    }
}
