<?php

namespace App\Http\Requests\VaccineType;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVaccineTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $vaccineTypeId = $this->route('vaccine_type')?->id;

        return [
            'name'              => 'required|string|max:100|unique:vaccine_types,name,' . $vaccineTypeId,
            'description'       => 'nullable|string|max:500',
            'interval_days'     => 'nullable|integer|min:1|max:3650',
            'season_months'     => 'nullable|array',
            'season_months.*'   => 'integer|between:1,12',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['season_months' => $this->season_months ?? null]);
    }
}
