<?php

namespace Database\Factories;

use App\Models\VaccineType;
use Illuminate\Database\Eloquent\Factories\Factory;

class VaccineTypeFactory extends Factory
{
    protected $model = VaccineType::class;

    public function definition(): array
    {
        return [
            'name'          => fake()->unique()->word() . ' Vaccine',
            'description'   => fake()->sentence(),
            'interval_days' => fake()->randomElement([120, 180, 365]),
            'season_months' => fake()->randomElements(range(1, 12), fake()->numberBetween(3, 12)),
        ];
    }
}
