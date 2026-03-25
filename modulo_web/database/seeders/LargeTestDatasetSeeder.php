<?php

namespace Database\Seeders;

use App\Models\Cattle;
use App\Models\User;
use App\Models\Vaccine;
use App\Models\Workstation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LargeTestDatasetSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Limpar dados existentes para garantir os números exatos
        // Usamos delete() em vez de truncate() para evitar problemas com chaves estrangeiras em alguns drivers
        Vaccine::query()->delete();
        Cattle::query()->delete();
        Workstation::query()->delete();
        // Remove apenas veterinários de teste para não quebrar o admin principal se existir
        User::where('is_veterinarian', true)->delete();

        // 2. Criar 2 Estações de Trabalho
        $workstations = Workstation::factory()->count(2)->create();

        // 3. Criar 6 Veterinários
        $vets = User::factory()->count(6)->veterinarian()->create();

        // 4. Criar 50 Cabeças de Gado distribuídas entre os veterinários
        $cattleCount = 50;
        $cattlePerVet = (int) ($cattleCount / $vets->count());
        $extraCattle = $cattleCount % $vets->count();

        $allCattle = collect();

        foreach ($vets as $index => $vet) {
            $count = $cattlePerVet + ($index < $extraCattle ? 1 : 0);
            
            $vetCattle = Cattle::factory()->count($count)->create([
                'user_id' => $vet->id,
            ]);
            
            $allCattle = $allCattle->concat($vetCattle);
        }

        // 5. Criar Vacinas para cada gado
        foreach ($allCattle as $animal) {
            // Cada animal recebe de 1 a 2 vacinas
            $vaccineCount = rand(1, 2);
            
            Vaccine::factory()->count($vaccineCount)->create([
                'rfid_tag' => $animal->rfid_tag,
                'current_weight' => $animal->weight + rand(-5, 20), // Pequena variação de peso
                'user_id' => $vets->random()->id, // Veterinário aleatório aplica a vacina
                'workstation_id' => $workstations->random()->id, // Estação aleatória
                'vaccination_date' => now()->subDays(rand(0, 30))->toDateString(),
            ]);
        }
    }
}
