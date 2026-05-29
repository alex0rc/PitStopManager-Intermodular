<?php

namespace App\Http\Requests\Circuit;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCircuitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'          => ['sometimes', 'string', 'max:255'],
            'location'      => ['sometimes', 'string', 'max:255'],
            'city'          => ['nullable', 'string', 'max:255'],
            'province'      => ['nullable', 'string', 'max:255'],
            'country'       => ['nullable', 'string', 'max:255'],
            'status'        => ['sometimes', 'in:pending,approved,rejected'],
            'latitude'      => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'     => ['nullable', 'numeric', 'between:-180,180'],
            'length_meters' => ['nullable', 'integer', 'min:0'],
            'description'   => ['nullable', 'string'],
        ];
    }
}
