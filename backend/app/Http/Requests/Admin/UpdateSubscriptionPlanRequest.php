<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSubscriptionPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'              => ['sometimes', 'string', 'max:255'],
            'slug'              => ['sometimes', 'string', 'max:255', Rule::unique('subscription_plans', 'slug')->ignore($this->route('plan'))],
            'description'       => ['nullable', 'string'],
            'price'             => ['sometimes', 'numeric', 'min:0'],
            'duration_days'     => ['sometimes', 'integer', 'min:1'],
            'max_championships' => ['sometimes', 'integer', 'min:1'],
            'is_active'         => ['sometimes', 'boolean'],
        ];
    }
}
