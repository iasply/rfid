<?php

namespace App\Http\Requests\Veterinarian;

use App\Rules\ValidVetRfidTag;
use Illuminate\Foundation\Http\FormRequest;

class StoreVeterinarianRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'vet_rfid' => ['nullable', 'string', 'unique:users,vet_rfid', new ValidVetRfidTag],
            'password' => 'required|min:6',
        ];
    }
}
