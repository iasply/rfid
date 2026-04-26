<?php

namespace Tests\Feature;

use App\Models\Cattle;
use App\Models\User;
use App\Models\Vaccine;
use App\Models\VaccineType;
use App\Models\Workstation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSearchFilterTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create();
    }

    // ── Cattle ───────────────────────────────────────────────────────────────────

    #[\PHPUnit\Framework\Attributes\Test]
    public function cattle_filter_by_name_returns_matching_records()
    {
        Cattle::factory()->create(['name' => 'Mimosa', 'rfid_tag' => 'TAG-MIM-01']);
        Cattle::factory()->create(['name' => 'Boiadeiro', 'rfid_tag' => 'TAG-BOI-02']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.cattle.index', ['q' => 'Mimosa', 'col' => 'name']));

        $response->assertStatus(200);
        $response->assertSee('Mimosa');
        $response->assertDontSee('Boiadeiro');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function cattle_filter_by_rfid_tag_returns_matching_records()
    {
        Cattle::factory()->create(['name' => 'Mimosa', 'rfid_tag' => 'TAG-MIM-01']);
        Cattle::factory()->create(['name' => 'Boiadeiro', 'rfid_tag' => 'TAG-BOI-02']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.cattle.index', ['q' => 'TAG-MIM', 'col' => 'rfid_tag']));

        $response->assertStatus(200);
        $response->assertSee('TAG-MIM-01');
        $response->assertDontSee('Boiadeiro');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function cattle_default_search_matches_name_and_rfid()
    {
        Cattle::factory()->create(['name' => 'Mimosa', 'rfid_tag' => 'TAG-MIM-01']);
        Cattle::factory()->create(['name' => 'Boiadeiro', 'rfid_tag' => 'TAG-BOI-02']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.cattle.index', ['q' => 'Mimosa']));

        $response->assertStatus(200);
        $response->assertSee('Mimosa');
        $response->assertDontSee('Boiadeiro');
    }

    // ── Vaccines ──────────────────────────────────────────────────────────────────

    #[\PHPUnit\Framework\Attributes\Test]
    public function vaccines_filter_by_vaccine_type_returns_matching_records()
    {
        $ws  = Workstation::factory()->create();
        $vt1 = VaccineType::factory()->create(['name' => 'Febre Aftosa']);
        $vt2 = VaccineType::factory()->create(['name' => 'Brucelose']);
        $c1  = Cattle::factory()->create(['rfid_tag' => 'TAG-VT-001']);
        $c2  = Cattle::factory()->create(['rfid_tag' => 'TAG-VT-002']);

        $this->createVaccine($c1->rfid_tag, $vt1->id, $ws->id);
        $this->createVaccine($c2->rfid_tag, $vt2->id, $ws->id);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.vaccines.index', ['q' => 'Febre', 'col' => 'vaccine_type']));

        $response->assertStatus(200);
        $response->assertSee('Febre Aftosa');
        $response->assertDontSee('Brucelose');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function vaccines_filter_by_rfid_tag_returns_matching_records()
    {
        $ws = Workstation::factory()->create();
        $vt = VaccineType::factory()->create();
        $c1 = Cattle::factory()->create(['rfid_tag' => 'TAG-RFID-AAA']);
        $c2 = Cattle::factory()->create(['rfid_tag' => 'TAG-RFID-BBB']);

        $this->createVaccine($c1->rfid_tag, $vt->id, $ws->id);
        $this->createVaccine($c2->rfid_tag, $vt->id, $ws->id);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.vaccines.index', ['q' => 'TAG-RFID-AAA', 'col' => 'rfid_tag']));

        $response->assertStatus(200);
        $response->assertSee('TAG-RFID-AAA');
        $response->assertDontSee('TAG-RFID-BBB');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function vaccines_filter_by_animal_name_returns_matching_records()
    {
        $ws = Workstation::factory()->create();
        $vt = VaccineType::factory()->create();
        $c1 = Cattle::factory()->create(['name' => 'Mimosa', 'rfid_tag' => 'TAG-AN-001']);
        $c2 = Cattle::factory()->create(['name' => 'Boiadeiro', 'rfid_tag' => 'TAG-AN-002']);

        $this->createVaccine($c1->rfid_tag, $vt->id, $ws->id);
        $this->createVaccine($c2->rfid_tag, $vt->id, $ws->id);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.vaccines.index', ['q' => 'Mimosa', 'col' => 'animal']));

        $response->assertStatus(200);
        $response->assertSee('Mimosa');
        $response->assertDontSee('Boiadeiro');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function vaccines_default_search_matches_across_type_rfid_and_animal()
    {
        $ws  = Workstation::factory()->create();
        $vt1 = VaccineType::factory()->create(['name' => 'Febre Aftosa']);
        $vt2 = VaccineType::factory()->create(['name' => 'Brucelose']);
        $c1  = Cattle::factory()->create(['name' => 'Mimosa', 'rfid_tag' => 'TAG-DEF-001']);
        $c2  = Cattle::factory()->create(['name' => 'Boiadeiro', 'rfid_tag' => 'TAG-DEF-002']);

        $this->createVaccine($c1->rfid_tag, $vt1->id, $ws->id);
        $this->createVaccine($c2->rfid_tag, $vt2->id, $ws->id);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.vaccines.index', ['q' => 'Febre Aftosa']));

        $response->assertStatus(200);
        $response->assertSee('Febre Aftosa');
        $response->assertDontSee('Brucelose');
    }

    // ── Veterinarians ─────────────────────────────────────────────────────────────

    #[\PHPUnit\Framework\Attributes\Test]
    public function veterinarians_filter_by_name_returns_matching_records()
    {
        User::factory()->veterinarian()->create(['name' => 'Dr. Fulano', 'email' => 'fulano@example.com']);
        User::factory()->veterinarian()->create(['name' => 'Dr. Sicrano', 'email' => 'sicrano@example.com']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.veterinarians.index', ['q' => 'Fulano', 'col' => 'name']));

        $response->assertStatus(200);
        $response->assertSee('Dr. Fulano');
        $response->assertDontSee('Dr. Sicrano');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function veterinarians_filter_by_email_returns_matching_records()
    {
        User::factory()->veterinarian()->create(['name' => 'Dr. Fulano', 'email' => 'fulano@example.com']);
        User::factory()->veterinarian()->create(['name' => 'Dr. Sicrano', 'email' => 'sicrano@example.com']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.veterinarians.index', ['q' => 'fulano@', 'col' => 'email']));

        $response->assertStatus(200);
        $response->assertSee('fulano@example.com');
        $response->assertDontSee('sicrano@example.com');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function veterinarians_default_search_matches_name_and_email()
    {
        User::factory()->veterinarian()->create(['name' => 'Dr. Fulano', 'email' => 'fulano@example.com']);
        User::factory()->veterinarian()->create(['name' => 'Dr. Sicrano', 'email' => 'sicrano@example.com']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.veterinarians.index', ['q' => 'Dr. Fulano']));

        $response->assertStatus(200);
        $response->assertSee('Dr. Fulano');
        $response->assertDontSee('Dr. Sicrano');
    }

    // ── Workstations ──────────────────────────────────────────────────────────────

    #[\PHPUnit\Framework\Attributes\Test]
    public function workstations_filter_by_desc_returns_matching_records()
    {
        Workstation::factory()->create(['desc' => 'Curral Norte', 'hash' => 'WS-NORTE-01']);
        Workstation::factory()->create(['desc' => 'Curral Sul', 'hash' => 'WS-SUL-0002']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.workstations.index', ['q' => 'Norte', 'col' => 'desc']));

        $response->assertStatus(200);
        $response->assertSee('Curral Norte');
        $response->assertDontSee('Curral Sul');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function workstations_filter_by_hash_returns_matching_records()
    {
        Workstation::factory()->create(['desc' => 'Curral Norte', 'hash' => 'WS-NORTE-01']);
        Workstation::factory()->create(['desc' => 'Curral Sul', 'hash' => 'WS-SUL-0002']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.workstations.index', ['q' => 'WS-NORTE', 'col' => 'hash']));

        $response->assertStatus(200);
        $response->assertSee('WS-NORTE-01');
        $response->assertDontSee('WS-SUL-0002');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function workstations_default_search_matches_desc_and_hash()
    {
        Workstation::factory()->create(['desc' => 'Curral Norte', 'hash' => 'WS-NORTE-01']);
        Workstation::factory()->create(['desc' => 'Curral Sul', 'hash' => 'WS-SUL-0002']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.workstations.index', ['q' => 'Curral Norte']));

        $response->assertStatus(200);
        $response->assertSee('Curral Norte');
        $response->assertDontSee('Curral Sul');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────────

    private function createVaccine(string $rfidTag, int $vaccineTypeId, int $workstationId): void
    {
        Vaccine::create([
            'rfid_tag'         => $rfidTag,
            'vaccine_type_id'  => $vaccineTypeId,
            'current_weight'   => 350.0,
            'vaccination_date' => now()->toDateString(),
            'user_id'          => $this->admin->id,
            'workstation_id'   => $workstationId,
        ]);
    }
}
