<?php

namespace App\Http\Requests\Cattle;

use App\Rules\ValidCattleRfidTag;
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
            'name'     => 'required|string|max:255',
            'weight'   => 'required|numeric|min:0',
            'rfid_tag' => ['nullable', 'string', 'unique:cattle,rfid_tag', new ValidCattleRfidTag],
        ];
    }
}
