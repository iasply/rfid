<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VaccineResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'rfid_tag'         => $this->rfid_tag,
            'vaccine_type_id'  => $this->vaccine_type_id,
            'vaccine_type_name' => $this->whenLoaded('vaccineType', fn () => $this->vaccineType?->name),
            'current_weight'   => (float) $this->current_weight,
            'vaccination_date' => $this->vaccination_date,
            'user_id'          => $this->user_id,
            'workstation_id'   => $this->workstation_id,
            'created_at'       => $this->created_at,
        ];
    }
}
