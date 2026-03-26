<?php

namespace App\Services;

use App\Models\Cattle;
use Illuminate\Support\Facades\DB;

class CattleService
{
    public function createCattle(array $data, ?int $userId): Cattle
    {
        return DB::transaction(function () use ($data, $userId) {
            $data['user_id'] = $userId;

            if (empty($data['rfid_tag']) || $data['rfid_tag'] === 'C') {
                $data['rfid_tag'] = \App\Support\RfidGenerator::generateCattleTag();
            }

            if (empty($data['registration_date'])) {
                $data['registration_date'] = now()->toDateString();
            }

            return Cattle::create($data);
        });
    }

    public function updateCattle(Cattle $cattle, array $data): Cattle
    {
        return DB::transaction(function () use ($cattle, $data) {
            $cattle->update($data);
            return $cattle;
        });
    }
}
