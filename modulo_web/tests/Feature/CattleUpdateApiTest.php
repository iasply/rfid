<?php

namespace Tests\Feature;

use App\Models\Cattle;
use App\Models\User;
use App\Support\RfidGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CattleUpdateApiTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function user_can_update_existing_cattle_without_rfid_collision()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $tag = RfidGenerator::generateCattleTag();

        $cattle = Cattle::create([
            'rfid_tag' => $tag,
            'name' => 'Original Name',
            'weight' => 200.00,
            'registration_date' => '2024-01-01',
        ]);

        $updateData = [
            'name' => 'Updated Name',
            'weight' => 250.00,
        ];

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->putJson("/api/desktop/cattle/{$cattle->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'cattle' => [
                    'id' => $cattle->id,
                    'name' => 'Updated Name',
                    'weight' => 250.00,
                    'rfid_tag' => $tag, // RFID should remain same
                ]
            ]);

        $this->assertEquals('Updated Name', $cattle->fresh()->name);
        $this->assertEquals(250.00, $cattle->fresh()->weight);
    }

    #[Test]
    public function updating_via_post_to_store_endpoint_fails_with_422_due_to_duplicate_rfid()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $tag = RfidGenerator::generateCattleTag();

        Cattle::create([
            'rfid_tag' => $tag,
            'name' => 'Boi 1',
            'weight' => 100,
            'registration_date' => now(),
        ]);

        $duplicateData = [
            'rfid_tag' => $tag,
            'name' => 'Attempt Duplicate',
            'weight' => 200,
        ];

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/desktop/cattle', $duplicateData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['rfid_tag']);
    }
}
