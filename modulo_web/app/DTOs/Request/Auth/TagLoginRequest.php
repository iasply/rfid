<?php

namespace App\DTOs\Request\Auth;

use App\Support\RfidGenerator;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Login via workstation hash + raw RFID tag (desktop/IoT flow).
 */
class TagLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'workstation' => 'required|string',
            'tag' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if (!RfidGenerator::isVetTag($value)) {
                        $fail(__('A tag RFID do veterinário é inválida ou não possui o prefixo esperado (V).'));
                    }
                },
            ],
        ];
    }
}
