<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\VaccineType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VaccineTypeTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function vaccine_type_list_loads_with_data()
    {
        $admin = User::factory()->create();
        VaccineType::factory()->create(['name' => 'Febre Aftosa', 'interval_days' => 180]);

        $response = $this->actingAs($admin)->get(route('admin.vaccine-types.index'));

        $response->assertStatus(200);
        $response->assertSee('Febre Aftosa');
        $response->assertSee('180 dias');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function vaccine_type_can_be_created()
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.vaccine-types.store'), [
            'name'          => 'Raiva',
            'description'   => 'Vacina antirrábica bovina',
            'interval_days' => 365,
            'season_months' => [1, 2, 11, 12],
        ]);

        $response->assertRedirect(route('admin.vaccine-types.index'));
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('vaccine_types', [
            'name'          => 'Raiva',
            'interval_days' => 365,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function vaccine_type_name_must_be_unique_on_create()
    {
        $admin = User::factory()->create();
        VaccineType::factory()->create(['name' => 'Brucelose']);

        $response = $this->actingAs($admin)->post(route('admin.vaccine-types.store'), [
            'name' => 'Brucelose',
        ]);

        $response->assertSessionHasErrors('name');
        $this->assertDatabaseCount('vaccine_types', 1);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function vaccine_type_name_is_required()
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.vaccine-types.store'), [
            'name' => '',
        ]);

        $response->assertSessionHasErrors('name');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function vaccine_type_can_be_updated()
    {
        $admin = User::factory()->create();
        $type  = VaccineType::factory()->create(['name' => 'Clostridiose', 'interval_days' => 365]);

        $response = $this->actingAs($admin)->put(route('admin.vaccine-types.update', $type->id), [
            'name'          => 'Clostridiose Atualizada',
            'interval_days' => 180,
        ]);

        $response->assertRedirect(route('admin.vaccine-types.index'));
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('vaccine_types', [
            'id'            => $type->id,
            'name'          => 'Clostridiose Atualizada',
            'interval_days' => 180,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function vaccine_type_update_unique_rule_ignores_self()
    {
        $admin = User::factory()->create();
        $type  = VaccineType::factory()->create(['name' => 'Leptospirose', 'interval_days' => 180]);

        $response = $this->actingAs($admin)->put(route('admin.vaccine-types.update', $type->id), [
            'name'          => 'Leptospirose',
            'interval_days' => 365,
        ]);

        $response->assertRedirect(route('admin.vaccine-types.index'));
        $response->assertSessionHasNoErrors();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function vaccine_types_api_returns_authenticated_list()
    {
        $token = User::factory()->create()->createToken('test')->plainTextToken;
        VaccineType::factory()->create(['name' => 'IBR/BVD', 'interval_days' => 365]);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson('/api/desktop/vaccine-types');

        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => [['id', 'name', 'interval_days']]]);
        $response->assertJsonFragment(['name' => 'IBR/BVD']);
    }
}
