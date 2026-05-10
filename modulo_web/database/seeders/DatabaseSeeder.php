<?php

namespace Database\Seeders;

use App\Models\Cattle;
use App\Models\User;
use App\Models\Vaccine;
use App\Models\VaccineType;
use App\Support\RfidGenerator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Vaccine types must exist before any vaccine records are seeded
        $this->call(VaccineTypeSeeder::class);

        // Admin
        User::create([
            'name' => 'Sistema Admin',
            'email' => 'admin@cattlerfid.com',
            'password' => Hash::make(config('app.admin_password')),
            'is_veterinarian' => false,
        ]);

        // Specific Veterinarians
        $vets = [];
        $vets[] = User::create([
            'name' => 'Alexandre',
            'email' => 'alexandre@vet.com',
            'password' => Hash::make('password'),
            'is_veterinarian' => true,
        ]);

        $vets[] = User::create([
            'name' => 'Iury',
            'email' => 'iury@vet.com',
            'password' => Hash::make('password'),
            'is_veterinarian' => true,
        ]);

        $cattleNames = [
            'Mimosa', 'Estrela', 'Bruna', 'Belinha',
            'Geraldão', 'Malhada', 'Pintadinha', 'Realeza',
        ];

        $vaccineTypeIds = VaccineType::pluck('id')->all();

        foreach ($cattleNames as $index => $name) {
            $vet = $vets[$index % 2];
            $vaccineTypeId = $vaccineTypeIds[$index % count($vaccineTypeIds)];

            $animal = Cattle::create([
                'name' => $name,
                'weight' => rand(400, 600),
                'rfid_tag' => RfidGenerator::generateCattleTag(),
                'registration_date' => now()->toDateString(),
                'user_id' => $vet->id,
            ]);

            Vaccine::create([
                'rfid_tag' => $animal->rfid_tag,
                'vaccine_type_id' => $vaccineTypeId,
                'current_weight' => $animal->weight,
                'vaccination_date' => now()->toDateString(),
                'user_id' => $vet->id,
            ]);
        }

        $this->call(LargeTestDatasetSeeder::class);
    }
}
