<?php

namespace Tests\Feature;

use App\Models\Cattle;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EntityTagTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function cattle_is_created_with_automatic_rfid_tag_and_registration_date()
    {
        $response = $this->post(route('admin.cattle.store'), [
            'name' => 'Boi Barnabé',
            'weight' => 250.5,
        ]);

        $response->assertRedirect(route('admin.cattle.index'));

        $cattle = Cattle::first();
        $this->assertNotNull($cattle->rfid_tag);
        $this->assertStringStartsWith('C', $cattle->rfid_tag);
        $this->assertNotNull($cattle->registration_date);
        $this->assertEquals(now()->format('Y-m-d'), $cattle->registration_date);
        $this->assertEquals(auth()->id(), $cattle->user_id);
    }

    #[Test]
    public function veterinarian_is_created_with_automatic_username_tag()
    {
        $response = $this->post(route('admin.veterinarians.store'), [
            'name' => 'Dr. Wilson',
            'email' => 'wilson@vet.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('admin.veterinarians.index'));

        $vet = User::where('is_veterinarian', true)->first();
        $this->assertNotNull($vet->vet_rfid);
        $this->assertStringStartsWith('V', $vet->vet_rfid);
    }

    protected function setUp(): void
    {
        parent::setUp();
        // Create an admin user for authenticated requests
        $this->actingAs(User::factory()->create());
    }
}
