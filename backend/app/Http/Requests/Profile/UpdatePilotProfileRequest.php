<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePilotProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nickname'       => ['nullable', 'string', 'max:255'],
            'birth_date'     => ['nullable', 'date'],
            'license_number' => ['nullable', 'string', 'max:255'],
            'bio'            => ['nullable', 'string'],
        ];
    }
}
