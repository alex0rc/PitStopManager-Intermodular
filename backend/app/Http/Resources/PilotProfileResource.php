<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PilotProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'nickname' => $this->nickname,
            'birth_date' => $this->birth_date,
            'license_number' => $this->license_number,
            'bio' => $this->bio,
        ];
    }
}
