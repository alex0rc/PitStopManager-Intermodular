<?php

namespace App\Http\Requests\Race;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRaceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'circuit_id'   => ['sometimes', 'exists:circuits,id'],
            'name'         => ['sometimes', 'string', 'max:255'],
            'scheduled_at' => ['sometimes', 'date'],
            'total_laps'   => ['nullable', 'integer', 'min:1'],
            'notes'        => ['nullable', 'string'],
        ];
    }
}
