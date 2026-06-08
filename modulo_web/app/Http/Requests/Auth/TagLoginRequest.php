<?php

namespace App\Http\Requests\Auth;

use App\Rules\ValidVetRfidTag;
use Illuminate\Foundation\Http\FormRequest;

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
            'tag' => ['required', 'string', new ValidVetRfidTag],
        ];
    }
}
