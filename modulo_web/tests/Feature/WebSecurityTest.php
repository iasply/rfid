<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class WebSecurityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Data provider for protected admin routes.
     */
    public static function protectedAdminRoutesProvider(): array
    {
        return [
            'Dashboard' => ['/admin/dashboard'],
            'Veterinarians' => ['/admin/veterinarians'],
            'Cattle' => ['/admin/cattle'],
            'Vaccines' => ['/admin/vaccines'],
            'Workstations' => ['/admin/workstations'],
            'Vaccine Types' => ['/admin/vaccine-types'],
            'Root Redirect' => ['/'],
        ];
    }

    #[Test]
    #[DataProvider('protectedAdminRoutesProvider')]
    public function admin_routes_redirect_to_login_without_authentication(string $uri)
    {
        $response = $this->get($uri);

        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function login_page_is_accessible_without_authentication()
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
    }

    #[Test]
    public function vet_cannot_open_edit_form_of_another_vet()
    {
        $vet = User::factory()->create(['is_veterinarian' => true]);
        $other = User::factory()->create(['is_veterinarian' => true]);

        $this->actingAs($vet)
            ->get(route('admin.veterinarians.edit', $other->id))
            ->assertStatus(403);
    }

    #[Test]
    public function vet_cannot_update_another_vet()
    {
        $vet = User::factory()->create(['is_veterinarian' => true]);
        $other = User::factory()->create(['is_veterinarian' => true]);

        $this->actingAs($vet)
            ->put(route('admin.veterinarians.update', $other->id), [
                'name'  => 'Hacker',
                'email' => $other->email,
            ])
            ->assertStatus(403);
    }

    #[Test]
    public function vet_can_open_own_edit_form()
    {
        $vet = User::factory()->create(['is_veterinarian' => true]);

        $this->actingAs($vet)
            ->get(route('admin.veterinarians.edit', $vet->id))
            ->assertStatus(200);
    }

    #[Test]
    public function vet_can_update_own_profile()
    {
        $vet = User::factory()->create(['is_veterinarian' => true]);

        $this->actingAs($vet)
            ->put(route('admin.veterinarians.update', $vet->id), [
                'name'  => 'Nome Atualizado',
                'email' => $vet->email,
            ])
            ->assertRedirect(route('admin.veterinarians.index'));
    }

    #[Test]
    public function admin_can_edit_any_vet()
    {
        $admin = User::factory()->create(['is_veterinarian' => false]);
        $vet   = User::factory()->create(['is_veterinarian' => true]);

        $this->actingAs($admin)
            ->get(route('admin.veterinarians.edit', $vet->id))
            ->assertStatus(200);
    }

    #[Test]
    public function admin_can_update_any_vet()
    {
        $admin = User::factory()->create(['is_veterinarian' => false]);
        $vet   = User::factory()->create(['is_veterinarian' => true]);

        $this->actingAs($admin)
            ->put(route('admin.veterinarians.update', $vet->id), [
                'name'  => 'Nome pelo Admin',
                'email' => $vet->email,
            ])
            ->assertRedirect(route('admin.veterinarians.index'));
    }
}
