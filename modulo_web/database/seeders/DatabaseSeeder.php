<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(VaccineTypeSeeder::class);

        User::create([
            'name' => 'Sistema Admin',
            'email' => 'admin@cattlerfid.com',
            'password' => Hash::make(config('app.admin_password')),
            'is_veterinarian' => false,
        ]);

        $this->call(LargeTestDatasetSeeder::class);
    }
}
