<?php

namespace App\Http\Requests\Cattle;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCattleRequest extends FormRequest
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
        ];
    }
}
