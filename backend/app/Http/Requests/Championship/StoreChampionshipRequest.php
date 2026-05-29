<?php

namespace App\Http\Requests\Championship;

use Illuminate\Foundation\Http\FormRequest;

class StoreChampionshipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'kart_modality' => ['nullable', 'in:rental,own'],
            'engine_class' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string'],
            'season_year' => ['required', 'integer', 'min:2020', 'max:2030'],
            'start_date'  => ['nullable', 'date'],
            'end_date'    => ['nullable', 'date', 'after_or_equal:start_date'],
            'venue_country' => ['nullable', 'string', 'max:255'],
            'venue_province' => ['nullable', 'string', 'max:255'],
            'venue_city' => ['nullable', 'string', 'max:255'],
            'venue_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'venue_longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }
}
