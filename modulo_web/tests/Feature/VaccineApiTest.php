<?php

namespace Tests\Feature;

use App\Models\Cattle;
use App\Models\User;
use App\Models\Vaccine;
use App\Models\VaccineType;
use App\Models\Workstation;
use App\Support\RfidGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class VaccineApiTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function vaccine_can_be_registered_via_api_and_associates_with_workstation()
    {
        $workstation = Workstation::create([
            'hash' => 'WS-API-TEST',
            'desc' => 'API Test Station',
        ]);

        $user = User::factory()->create(['is_veterinarian' => true]);

        $vetTag = RfidGenerator::generateVetTag();
        $user->update(['vet_rfid' => $vetTag]);

        $loginResponse = $this->postJson('/api/desktop/login', [
            'workstation' => 'WS-API-TEST',
            'tag' => $vetTag,
        ]);

        $loginResponse->assertStatus(200);
        $token = $loginResponse->json('access_token');

        $tag1 = RfidGenerator::generateCattleTag();

        Cattle::create([
            'rfid_tag' => $tag1,
            'name' => 'Test Cow',
            'weight' => 500.00,
            'registration_date' => now(),
        ]);

        $vaccineType = VaccineType::factory()->create(['name' => 'Aftosa Test']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/desktop/vaccines', [
            'rfid_tag' => $tag1,
            'vaccine_type_id' => $vaccineType->id,
            'current_weight' => 510.50,
            'vaccination_date' => now()->format('Y-m-d'),
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('vaccines', [
            'rfid_tag' => $tag1,
            'vaccine_type_id' => $vaccineType->id,
            'current_weight' => 510.50,
            'user_id' => $user->id,
            'workstation_id' => $workstation->id,
        ]);

        $this->assertEquals(510.50, Cattle::where('rfid_tag', $tag1)->first()->weight);
    }

    #[Test]
    public function vaccine_registered_without_workstation_token_has_null_workstation_id()
    {
        $user = User::factory()->create(['is_veterinarian' => true]);
        $token = $user->createToken('normal-token')->plainTextToken;

        $tag2 = RfidGenerator::generateCattleTag();

        Cattle::create([
            'rfid_tag' => $tag2,
            'name' => 'Another Cow',
            'weight' => 400.00,
            'registration_date' => now(),
        ]);

        $vaccineType = VaccineType::factory()->create(['name' => 'Brucelose Test']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/desktop/vaccines', [
            'rfid_tag' => $tag2,
            'vaccine_type_id' => $vaccineType->id,
            'current_weight' => 410.00,
            'vaccination_date' => now()->format('Y-m-d'),
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('vaccines', [
            'rfid_tag' => $tag2,
            'workstation_id' => null,
        ]);
    }

    #[Test]
    public function user_can_filter_vaccines_by_rfid_tag()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $tagA = RfidGenerator::generateCattleTag();
        $tagB = RfidGenerator::generateCattleTag();

        Cattle::create(['rfid_tag' => $tagA, 'name' => 'Cow A', 'weight' => 100, 'registration_date' => now()]);
        Cattle::create(['rfid_tag' => $tagB, 'name' => 'Cow B', 'weight' => 100, 'registration_date' => now()]);

        $vt1 = VaccineType::factory()->create(['name' => 'Type 1']);
        $vt2 = VaccineType::factory()->create(['name' => 'Type 2']);
        $vt3 = VaccineType::factory()->create(['name' => 'Type 3']);

        Vaccine::create(['rfid_tag' => $tagA, 'vaccine_type_id' => $vt1->id, 'current_weight' => 105, 'vaccination_date' => now()]);
        Vaccine::create(['rfid_tag' => $tagA, 'vaccine_type_id' => $vt2->id, 'current_weight' => 110, 'vaccination_date' => now()]);
        Vaccine::create(['rfid_tag' => $tagB, 'vaccine_type_id' => $vt3->id, 'current_weight' => 105, 'vaccination_date' => now()]);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson("/api/desktop/vaccines?rfid_tag={$tagA}");

        $response->assertStatus(200)->assertJsonCount(2, 'data');

        foreach ($response->json('data') as $v) {
            $this->assertEquals($tagA, $v['rfid_tag']);
        }
    }
}
