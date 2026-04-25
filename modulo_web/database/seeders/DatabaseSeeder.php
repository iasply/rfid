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
        // Admin
        User::create([
            'name' => 'Sistema Admin',
            'email' => 'admin@cattlerfid.com',
            'password' => Hash::make('admin123'),
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
            'Mimosa',
            'Estrela',
            'Bruna',
            'Belinha',
            'Geraldão',
            'Malhada',
            'Pintadinha',
            'Realeza',
        ];

        $vaccineTypes = [
            'Febre Aftosa',
            'Brucelose',
            'Raiva',
            'Clostridiose',
            'Botulismo',
            'Leptospirose',
            'IBR/BVD',
            'Carbúnculo',
        ];

        foreach ($cattleNames as $index => $name) {
            $vet = $vets[$index % 2]; // Distributed between the 2 vets
            $vaccineType = $vaccineTypes[$index % count($vaccineTypes)];

            $animal = Cattle::create([
                'name' => $name,
                'weight' => rand(400, 600),
                'rfid_tag' => RfidGenerator::generateCattleTag(),
                'registration_date' => now()->toDateString(),
                'user_id' => $vet->id,
            ]);

            // Create vaccination for each cattle
            Vaccine::create([
                'rfid_tag' => $animal->rfid_tag,
                'vaccine_type' => $vaccineType,
                'current_weight' => $animal->weight,
                'vaccination_date' => now()->toDateString(),
                'user_id' => $vet->id,
            ]);
        }

        // $this->call(IntegrationTestDataSeeder::class);
         $this->call(LargeTestDatasetSeeder::class);
    }
}
