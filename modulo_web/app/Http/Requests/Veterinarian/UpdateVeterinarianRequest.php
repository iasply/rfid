<?php

namespace App\Http\Requests\Veterinarian;

use App\Rules\ValidVetRfidTag;
use Illuminate\Foundation\Http\FormRequest;

class UpdateVeterinarianRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = auth()->user();
        $veterinarian = $this->route('veterinarian');

        if ($user && $user->is_veterinarian && $veterinarian && $user->id !== $veterinarian->id) {
            return false;
        }

        return auth()->check();
    }

    public function rules(): array
    {
        $veterinarianId = $this->route('veterinarian')?->id;

        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $veterinarianId,
            'vet_rfid' => ['nullable', 'string', 'unique:users,vet_rfid,' . $veterinarianId, new ValidVetRfidTag],
            'password' => 'nullable|min:6',
        ];
    }
}
