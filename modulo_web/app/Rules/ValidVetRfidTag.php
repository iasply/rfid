<?php

namespace App\Rules;

use App\Support\RfidGenerator;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidVetRfidTag implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!RfidGenerator::isVetTag($value)) {
            $fail(__('A tag RFID do veterinário é inválida ou não possui o prefixo esperado (V).'));
        }
    }
}
