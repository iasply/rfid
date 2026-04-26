<?php

namespace App\DTOs\Request\Veterinarian;

use App\Support\RfidGenerator;
use Illuminate\Foundation\Http\FormRequest;

class StoreVeterinarianRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'vet_rfid' => [
                'nullable',
                'string',
                'unique:users,vet_rfid',
                function ($attribute, $value, $fail) {
                    if (!RfidGenerator::isVetTag($value)) {
                        $fail(__('A tag RFID do veterinário é inválida ou não possui o prefixo esperado (V).'));
                    }
                },
            ],
            'password' => 'required|min:6',
        ];
    }
}
