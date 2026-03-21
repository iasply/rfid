<?php

namespace Database\Factories;

use App\Models\Workstation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class WorkstationFactory extends Factory
{
    protected $model = Workstation::class;

    public function definition(): array
    {
        return [
            'hash' => 'WS-' . strtoupper(Str::random(8)),
            'desc' => 'Workstation ' . fake()->city(),
        ];
    }
}
