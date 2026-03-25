<?php

namespace Database\Factories;

use App\Models\Cattle;
use App\Models\User;
use App\Support\RfidGenerator;
use Illuminate\Database\Eloquent\Factories\Factory;

class CattleFactory extends Factory
{
    protected $model = Cattle::class;

    public function definition(): array
    {
        return [
            'rfid_tag' => RfidGenerator::generateCattleTag(),
            'name' => fake()->firstName(),
            'weight' => fake()->randomFloat(2, 100, 800),
            'registration_date' => now()->subDays(rand(0, 365))->toDateString(),
            'user_id' => User::factory(),
        ];
    }
}
