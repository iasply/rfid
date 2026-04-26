<?php

namespace App\DTOs\Request\Veterinarian;

use App\Support\RfidGenerator;
use Illuminate\Foundation\Http\FormRequest;

class UpdateVeterinarianRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $veterinarianId = $this->route('veterinarian')?->id;

        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $veterinarianId,
            'vet_rfid' => [
                'nullable',
                'string',
                'unique:users,vet_rfid,' . $veterinarianId,
                function ($attribute, $value, $fail) {
                    if (!RfidGenerator::isVetTag($value)) {
                        $fail(__('A tag RFID do veterinário é inválida ou não possui o prefixo esperado (V).'));
                    }
                },
            ],
            'password' => 'nullable|min:6',
        ];
    }
}
