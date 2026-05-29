<?php

namespace App\Http\Requests\Race;

use Illuminate\Foundation\Http\FormRequest;

class StoreRaceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'circuit_id'   => ['required', 'exists:circuits,id'],
            'name'         => ['required', 'string', 'max:255'],
            'scheduled_at' => ['required', 'date'],
            'total_laps'   => ['nullable', 'integer', 'min:1'],
            'notes'        => ['nullable', 'string'],
        ];
    }
}
