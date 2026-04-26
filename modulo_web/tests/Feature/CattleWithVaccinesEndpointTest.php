<?php

namespace Tests\Feature;

use App\Models\Cattle;
use App\Models\User;
use App\Models\Vaccine;
use App\Support\RfidGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CattleWithVaccinesEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_cattle_with_vaccines_endpoint_returns_correct_data(): void
    {
        $user = User::factory()->create();
        $tag1 = RfidGenerator::generateCattleTag();
        $tag2 = RfidGenerator::generateCattleTag();

        // Create cattle
        $cattle1 = Cattle::create([
            'rfid_tag' => $tag1,
            'name' => 'Boi 1',
            'weight' => 500,
            'registration_date' => '2023-10-01',
            'user_id' => $user->id,
        ]);

        $cattle2 = Cattle::create([
            'rfid_tag' => $tag2,
            'name' => 'Boi 2',
            'weight' => 600,
            'registration_date' => '2023-10-02',
            'user_id' => $user->id,
        ]);

        // Add 2 vaccines to cattle 1
        Vaccine::create([
            'rfid_tag' => $tag1,
            'vaccine_type' => 'Aftosa',
            'current_weight' => 500,
            'vaccination_date' => '2023-10-10',
            'user_id' => $user->id,
        ]);
        Vaccine::create([
            'rfid_tag' => $tag1,
            'vaccine_type' => 'Brucelose',
            'current_weight' => 510,
            'vaccination_date' => '2023-11-10',
            'user_id' => $user->id,
        ]);

        // Add 1 vaccine to cattle 2
        Vaccine::create([
            'rfid_tag' => $tag2,
            'vaccine_type' => 'Aftosa',
            'current_weight' => 600,
            'vaccination_date' => '2023-10-15',
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/desktop/cattle-with-vaccines');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'rfid_tag',
                    'name',
                    'weight',
                    'registration_date',
                    'vaccines_count'
                ]
            ]
        ]);

        // Assert data values are correct
        $data = collect($response->json('data'));

        $item1 = $data->firstWhere('rfid_tag', $tag1);
        $this->assertEquals(2, $item1['vaccines_count']);

        $item2 = $data->firstWhere('rfid_tag', $tag2);
        $this->assertEquals(1, $item2['vaccines_count']);
    }
}
