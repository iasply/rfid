<?php

namespace App\Http\Requests\Vaccine;

use App\Rules\ValidCattleRfidTag;
use Illuminate\Foundation\Http\FormRequest;

class StoreVaccineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->is_veterinarian;
    }

    public function rules(): array
    {
        return [
            'rfid_tag' => ['required', 'exists:cattle,rfid_tag', new ValidCattleRfidTag],
            'vaccine_type_id' => 'required|exists:vaccine_types,id',
            'current_weight' => 'required|numeric|min:0',
            'vaccination_date' => 'required|date',
        ];
    }
}
