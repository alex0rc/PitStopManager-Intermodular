<?php

namespace App\Http\Requests\Result;

use Illuminate\Foundation\Http\FormRequest;

class UpdateResultRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id'       => ['sometimes', 'exists:users,id'],
            'position'      => ['nullable', 'integer', 'min:1'],
            'best_lap_time' => ['nullable', 'string', 'max:20'],
            'total_time'    => ['nullable', 'string', 'max:20'],
            'points'        => ['sometimes', 'integer', 'min:0'],
            'dnf'           => ['sometimes', 'boolean'],
            'dsq'           => ['sometimes', 'boolean'],
            'notes'         => ['nullable', 'string'],
        ];
    }
}
