<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Garante que 419 (CSRF token mismatch) nunca chegue como tela de erro ao usuário.
 *
 * Contexto: o Laravel converte TokenMismatchException → HttpException(419) antes
 * dos render callbacks. Nosso handler precisa interceptar isso e redirecionar
 * para o login com a sessão limpa.
 */
class CsrfTokenMismatchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Route::post('/test-csrf-mismatch', function () {
            throw new TokenMismatchException('CSRF token mismatch.');
        })->middleware('web');
    }

    #[Test]
    public function token_mismatch_redirects_to_login_not_419()
    {
        $response = $this->post('/test-csrf-mismatch');

        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function token_mismatch_flashes_friendly_error_message()
    {
        $response = $this->post('/test-csrf-mismatch');

        $response->assertSessionHas('error');
        $this->assertStringContainsString('sessão', strtolower(session('error') ?? ''));
    }

    #[Test]
    public function token_mismatch_logs_out_authenticated_user()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/test-csrf-mismatch')
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    #[Test]
    public function token_mismatch_on_json_request_returns_419_not_redirect()
    {
        $response = $this->withHeaders(['Accept' => 'application/json'])
            ->post('/test-csrf-mismatch');

        $response->assertStatus(419);
    }

    #[Test]
    public function other_419_errors_are_not_intercepted_as_csrf()
    {
        Route::post('/test-other-419', function () {
            abort(419, 'Outro motivo qualquer');
        })->middleware('web');

        $response = $this->post('/test-other-419');

        // Sem TokenMismatchException como previous, o handler deixa o 419 padrão passar
        $response->assertStatus(419);
    }

    #[Test]
    public function authenticated_pages_set_no_store_cache_control()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/admin/dashboard');

        $response->assertHeader('Cache-Control');
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
    }
}
