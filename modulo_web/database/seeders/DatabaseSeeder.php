<?php

namespace Database\Seeders;

use App\Models\Cattle;
use App\Models\User;
use App\Models\Vaccine;
use App\Support\RfidGenerator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Sistema Admin',
            'email' => 'admin@cattlerfid.com',
            'password' => Hash::make('admin123'),
            'is_veterinarian' => false,
        ]);

        $vet = User::create([
            'name' => 'Dr. Ricardo Vet',
            'email' => 'ricardo@vet.com',
            'password' => Hash::make('vet123'),
            'is_veterinarian' => true,
        ]);

        $rfid = 'C1234567894';
        if (!RfidGenerator::isValid($rfid)) {
            $rfid = RfidGenerator::generateCattleTag();
        }

        $animal = Cattle::create([
            'name' => 'Mimosa',
            'weight' => 450.00,
            'rfid_tag' => $rfid,
            'registration_date' => now()->toDateString(),
        ]);

        Vaccine::create([
            'rfid_tag' => $animal->rfid_tag,
            'vaccine_type' => 'Febre Aftosa',
            'current_weight' => 450.00,
            'vaccination_date' => now()->toDateString(),
            'user_id' => $vet->id,
        ]);

        $this->call(IntegrationTestDataSeeder::class);
    }
}
