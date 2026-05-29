<?php

namespace App\Http\Requests\Inscription;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateInscriptionRacesRequest extends FormRequest
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
            $inscription = $this->route('inscription');
            $championship = $inscription?->championship;
            if (!$championship?->usesOwnKarts()) {
                return;
            }

            $kartInfo = $this->has('kart_info')
                ? $this->input('kart_info')
                : $inscription?->kart_info;

            if (!filled($kartInfo)) {
                $validator->errors()->add(
                    'kart_info',
                    'Indica el modelo de tu kart (chasis y motor).'
                );
            }
        });
    }
}
