<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CattleResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'rfid_tag' => $this->rfid_tag,
            'name' => $this->name,
            'weight' => (float)$this->weight,
            'registration_date' => $this->registration_date,
            'user_name' => $this->whenLoaded('user', fn() => $this->user->name),
            'vaccines_count' => $this->when(isset($this->vaccines_count), fn() => (int)$this->vaccines_count),
        ];
    }
}
