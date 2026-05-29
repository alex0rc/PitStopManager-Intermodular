<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubscriptionPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'              => ['required', 'string', 'max:255'],
            'slug'              => ['required', 'string', 'max:255', 'unique:subscription_plans,slug'],
            'description'       => ['nullable', 'string'],
            'price'             => ['required', 'numeric', 'min:0'],
            'duration_days'     => ['required', 'integer', 'min:1'],
            'max_championships' => ['required', 'integer', 'min:1'],
            'is_active'         => ['sometimes', 'boolean'],
        ];
    }
}
