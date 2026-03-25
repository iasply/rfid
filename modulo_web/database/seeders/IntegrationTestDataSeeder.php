<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Workstation;
use App\Support\RfidGenerator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class IntegrationTestDataSeeder extends Seeder
{
    public function run(): void
    {
        $vetRfid = 'V000002';
        if (!RfidGenerator::isValid($vetRfid)) {
            $vetRfid = RfidGenerator::generateVetTag();
        }

        User::updateOrCreate(
            ['vet_rfid' => $vetRfid],
            [
                'name' => 'Vet Integration Test',
                'email' => 'vet2@cattlerfid.com',
                'password' => Hash::make('password123'),
                'is_veterinarian' => true,
                'tag_hash' => hash('sha256', $vetRfid . config('app.tag_salt')),
            ]
        );

        Workstation::updateOrCreate(
            ['hash' => 'WS-XTYBQRG6'],
            ['desc' => 'Workstation Integration Test']
        );

        $cattleRfid = 'C000002';
        if (!RfidGenerator::isValid($cattleRfid)) {
            $cattleRfid = RfidGenerator::generateCattleTag();
        }

        User::where('vet_rfid', $vetRfid)->first()->cattle()->updateOrCreate(
            ['rfid_tag' => $cattleRfid],
            [
                'name' => 'Cattle Integration Test',
                'weight' => 200.0,
                'registration_date' => now()->format('Y-m-d'),
            ]
        );
    }
}
