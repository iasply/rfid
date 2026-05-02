<?php

namespace App\Services;

use App\Models\Cattle;
use App\Models\Vaccine;
use Illuminate\Support\Facades\DB;

class VaccineService
{
    public function recordVaccination(array $data, int $userId, ?int $workstationId): Vaccine
    {
        return DB::transaction(function () use ($data, $userId, $workstationId) {
            $vaccine = Vaccine::create(array_merge($data, [
                'user_id'        => $userId,
                'workstation_id' => $workstationId,
            ]));

            Cattle::where('rfid_tag', $data['rfid_tag'])
                ->update(['weight' => $data['current_weight']]);

            return $vaccine;
        });
    }
}
