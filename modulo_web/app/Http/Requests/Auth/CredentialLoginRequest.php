<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class CredentialLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'identity' => 'required|string',
            'password' => 'required|string',
            'device_name' => 'required|string',
        ];
    }
}
