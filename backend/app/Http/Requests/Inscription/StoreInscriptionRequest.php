<?php

namespace App\Http\Requests\Inscription;

use App\Services\CategoryEligibilityService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreInscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'car_number' => ['nullable', 'integer', 'min:1'],
            'kart_info' => ['nullable', 'string', 'max:500'],
            'race_ids' => ['nullable', 'array'],
            'race_ids.*' => ['integer'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $championship = $this->route('championship');
            if (!$championship?->usesOwnKarts()) {
                return;
            }

            if (!filled($this->input('kart_info'))) {
                $validator->errors()->add(
                    'kart_info',
                    'Indica el modelo de tu kart (chasis y motor).'
                );
            }

            $category = $championship->category;
            if ($category) {
                $message = app(CategoryEligibilityService::class)
                    ->validateForCategory($this->user(), $category);

                if ($message) {
                    $validator->errors()->add('category', $message);
                }
            }
        });
    }
}
