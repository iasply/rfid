<?php

namespace Tests\Feature;

use App\Models\Cattle;
use App\Models\User;
use App\Support\RfidGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CattleApiTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function user_can_lookup_cattle_by_rfid_tag()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $tag = RfidGenerator::generateCattleTag();

        Cattle::create([
            'rfid_tag' => $tag,
            'name' => 'Boi Teste',
            'weight' => 450.50,
            'registration_date' => '2023-01-01',
        ]);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson("/api/desktop/cattle/{$tag}");

        $response->assertStatus(200)
            ->assertJson([
                'rfid_tag' => $tag,
                'name' => 'Boi Teste',
                'weight' => 450.50,
            ]);
    }

    #[Test]
    public function lookup_returns_404_for_non_existent_tag()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson('/api/desktop/cattle/NON-EXISTENT');

        $response->assertStatus(404)
            ->assertJson(['message' => 'Animal não encontrado.']);
    }

    #[Test]
    public function user_can_list_all_cattle()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        Cattle::create(['rfid_tag' => RfidGenerator::generateCattleTag(), 'name' => 'A', 'weight' => 100, 'registration_date' => now()]);
        Cattle::create(['rfid_tag' => RfidGenerator::generateCattleTag(), 'name' => 'B', 'weight' => 200, 'registration_date' => now()]);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson('/api/desktop/cattle');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }
}
