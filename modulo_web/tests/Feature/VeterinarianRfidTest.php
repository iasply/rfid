<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class VeterinarianRfidTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function creating_a_veterinarian_automatically_generates_vet_rfid_and_tag_hash()
    {
        $vet = User::create([
            'name' => 'Dr. Smith',
            'email' => 'smith@vet.com',
            'password' => bcrypt('password'),
            'is_veterinarian' => true,
        ]);

        $this->assertNotNull($vet->vet_rfid);
        $this->assertStringStartsWith('V', $vet->vet_rfid);

        $expectedHash = hash('sha256', $vet->vet_rfid . config('app.tag_salt'));
        $this->assertEquals($expectedHash, $vet->tag_hash);
        $this->assertNotNull($vet->tag_hash);
    }

    #[Test]
    public function creating_a_non_veterinarian_still_generates_a_tag_hash()
    {
        $user = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'is_veterinarian' => false,
        ]);

        $this->assertNotNull($user->vet_rfid);
        $this->assertStringStartsWith('USER-', $user->vet_rfid);

        $expectedHash = hash('sha256', $user->vet_rfid . config('app.tag_salt'));
        $this->assertEquals($expectedHash, $user->tag_hash);
    }

    #[Test]
    public function vet_rfid_is_not_overwritten_if_provided()
    {
        $customRfid = 'STATION-VET-001';
        $vet = User::create([
            'name' => 'Dr. Jones',
            'email' => 'jones@vet.com',
            'password' => bcrypt('password'),
            'is_veterinarian' => true,
            'vet_rfid' => $customRfid,
        ]);

        $this->assertEquals($customRfid, $vet->vet_rfid);
        $expectedHash = hash('sha256', $customRfid . config('app.tag_salt'));
        $this->assertEquals($expectedHash, $vet->tag_hash);
    }
}
