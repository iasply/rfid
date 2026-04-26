<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ApiSecurityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Data provider for protected routes and their methods.
     */
    public static function protectedRoutesProvider(): array
    {
        return [
            'Logout' => ['POST', '/api/desktop/logout'],
            'Cattle with Vaccines' => ['GET', '/api/desktop/cattle-with-vaccines'],
            'Cattle Index' => ['GET', '/api/desktop/cattle'],
            'Cattle Store' => ['POST', '/api/desktop/cattle'],
            'Cattle Update' => ['PUT', '/api/desktop/cattle/1'],
            'Cattle Show' => ['GET', '/api/desktop/cattle/TAG123'],
            'Vaccines Index' => ['GET', '/api/desktop/vaccines'],
            'Vaccines Store' => ['POST', '/api/desktop/vaccines'],
            'Vaccine Types' => ['GET', '/api/desktop/vaccine-types'],
            'User Profile' => ['GET', '/api/desktop/user'],
        ];
    }

    #[Test]
    #[DataProvider('protectedRoutesProvider')]
    public function protected_endpoints_return_unauthorized_without_token(string $method, string $uri)
    {
        $response = $this->json($method, $uri);

        $response->assertStatus(401);
    }

    #[Test]
    public function login_endpoint_is_accessible_without_token()
    {
        // We don't provide a tag/workstation, so it should fail with 422 (Validation), NOT 401
        $response = $this->postJson('/api/desktop/login', []);

        $this->assertNotEquals(401, $response->getStatusCode());
    }
}
