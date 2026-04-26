<?php

namespace App\DTOs\Request\Cattle;

use App\Support\RfidGenerator;
use Illuminate\Foundation\Http\FormRequest;

class StoreCattleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'weight' => 'required|numeric|min:0',
            'rfid_tag' => [
                'nullable',
                'string',
                'unique:cattle,rfid_tag',
                function ($attribute, $value, $fail) {
                    if (!RfidGenerator::isCattleTag($value)) {
                        $fail(__('A tag RFID do animal é inválida ou não possui o prefixo esperado (C).'));
                    }
                },
            ],
        ];
    }
}
