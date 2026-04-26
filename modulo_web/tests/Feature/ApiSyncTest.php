<?php

namespace Tests\Feature;

use App\Models\Cattle;
use App\Models\User;
use App\Models\VaccineType;
use App\Support\RfidGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ApiSyncTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function can_save_vaccine_via_api_with_valid_token()
    {
        $vet = User::factory()->create([
            'is_veterinarian' => true,
        ]);

        $cattle = Cattle::create([
            'rfid_tag' => RfidGenerator::generateCattleTag(),
            'registration_date' => now()->toDateString(),
            'name' => 'Mimosa API',
            'weight' => 300.0,
        ]);

        $vaccineType = VaccineType::factory()->create(['name' => 'Anti-Rábica']);

        $token = $vet->createToken('Desktop')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/desktop/vaccines', [
            'rfid_tag' => $cattle->rfid_tag,
            'vaccine_type_id' => $vaccineType->id,
            'current_weight' => 310.5,
            'vaccination_date' => now()->toDateString(),
            'vaccinator_username' => $vet->username,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('vaccines', [
            'rfid_tag' => $cattle->rfid_tag,
            'vaccine_type_id' => $vaccineType->id,
        ]);

        // Verify cattle weight was updated
        $this->assertEquals(310.5, $cattle->fresh()->weight);
    }
}
