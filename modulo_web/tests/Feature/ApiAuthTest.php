<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Workstation;
use App\Support\RfidGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ApiAuthTest extends TestCase
{
    use RefreshDatabase;


    #[Test]
    public function user_can_login_via_api_using_workstation_and_tag()
    {
        $workstation = Workstation::create([
            'hash' => 'WS-HASH-123',
            'desc' => 'Main Lab',
        ]);

        $user = User::factory()->create([
            'is_veterinarian' => true,
        ]);

        $response = $this->postJson('/api/desktop/login', [
            'workstation' => 'WS-HASH-123',
            'tag' => $user->vet_rfid, // Use the automatically generated RFID
            'device_name' => 'DesktopClient',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['access_token', 'token_type', 'user', 'workstation'])
            ->assertJsonPath('user.id', $user->id)
            ->assertJsonPath('workstation.id', $workstation->id);

        // Verify workstation_id is stored in the password_access_tokens table
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'workstation_id' => $workstation->id,
        ]);
    }

    #[Test]
    public function login_fails_with_invalid_tag()
    {
        Workstation::create([
            'hash' => 'WS-HASH-123',
            'desc' => 'Main Lab',
        ]);

        $response = $this->postJson('/api/desktop/login', [
            'workstation' => 'WS-HASH-123',
            'tag' => 'WRONG-TAG',
            'device_name' => 'DesktopClient',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['tag']);
    }

    #[Test]
    public function login_fails_if_user_is_not_a_veterinarian_even_with_valid_tag()
    {
        Workstation::create([
            'hash' => 'WS-HASH-123',
            'desc' => 'Main Lab',
        ]);

        $rawTag = RfidGenerator::generateVetTag();
        $hashedTag = hash('sha256', $rawTag . config('app.tag_salt'));

        User::factory()->create([
            'tag_hash' => $hashedTag,
            'is_veterinarian' => false,
        ]);

        $response = $this->postJson('/api/desktop/login', [
            'workstation' => 'WS-HASH-123',
            'tag' => $rawTag,
            'device_name' => 'DesktopClient',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['tag']);
    }

    #[Test]
    public function authenticated_user_can_access_protected_cattle_endpoint()
    {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/desktop/cattle');

        $response->assertStatus(200);
    }

    #[Test]
    public function api_login_is_rate_limited_after_too_many_attempts()
    {
        Workstation::create([
            'hash' => 'WS-HASH-LIMIT',
            'desc' => 'Limit Lab',
        ]);

        // Attempt 5 times (limit)
        for ($i = 0; $i < 5; $i++) {
            $response = $this->postJson('/api/desktop/login', [
                'workstation' => 'WS-HASH-LIMIT',
                'tag' => 'WRONG-TAG',
                'device_name' => 'DesktopClient',
            ]);
            $response->assertStatus(422)
                ->assertJsonValidationErrors(['tag']);
        }

        // 6th attempt should be rate limited
        $response = $this->postJson('/api/desktop/login', [
            'workstation' => 'WS-HASH-LIMIT',
            'tag' => 'WRONG-TAG',
            'device_name' => 'DesktopClient',
        ]);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['workstation']);
    }
}

