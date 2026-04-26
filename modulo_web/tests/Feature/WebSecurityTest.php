<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
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

    #[\PHPUnit\Framework\Attributes\Test]
    #[\PHPUnit\Framework\Attributes\DataProvider('protectedAdminRoutesProvider')]
    public function admin_routes_redirect_to_login_without_authentication(string $uri)
    {
        $response = $this->get($uri);

        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function login_page_is_accessible_without_authentication()
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
    }
}
