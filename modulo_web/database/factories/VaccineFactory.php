<?php

namespace Database\Factories;

use App\Models\Cattle;
use App\Models\User;
use App\Models\Vaccine;
use App\Models\Workstation;
use Illuminate\Database\Eloquent\Factories\Factory;

class VaccineFactory extends Factory
{
    protected $model = Vaccine::class;

    public function definition(): array
    {
        return [
            'rfid_tag' => Cattle::factory(),
            'vaccine_type' => fake()->randomElement(['Febre Aftosa', 'Brucelose', 'Raiva', 'Clostridiose', 'Botulismo']),
            'current_weight' => fake()->randomFloat(2, 100, 800),
            'vaccination_date' => now()->toDateString(),
            'user_id' => User::factory(),
            'workstation_id' => Workstation::factory(),
        ];
    }
}
