<?php

namespace App\DTOs\Request\Vaccine;

use Illuminate\Foundation\Http\FormRequest;

class StoreVaccineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->is_veterinarian;
    }

    public function rules(): array
    {
        return [
            'rfid_tag' => [
                'required',
                'exists:cattle,rfid_tag',
                function ($attribute, $value, $fail) {
                    if (!\App\Support\RfidGenerator::isCattleTag($value)) {
                        $fail(__('A tag RFID do animal é inválida ou não possui o prefixo esperado (C).'));
                    }
                },
            ],
            'vaccine_type_id'  => 'required|exists:vaccine_types,id',
            'current_weight'   => 'required|numeric|min:0',
            'vaccination_date' => 'required|date',
        ];
    }
}
