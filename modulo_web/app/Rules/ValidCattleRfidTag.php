<?php

namespace App\Rules;

use App\Support\RfidGenerator;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidCattleRfidTag implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!RfidGenerator::isCattleTag($value)) {
            $fail(__('A tag RFID do animal é inválida ou não possui o prefixo esperado (C).'));
        }
    }
}
