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
    public function token_mismatch_keeps_authenticated_user_and_redirects_to_dashboard()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/test-csrf-mismatch')
            ->assertRedirect(route('admin.dashboard'));

        // Sessão preservada — 419 em form não é motivo pra deslogar usuário válido
        $this->assertAuthenticatedAs($user);
    }

    #[Test]
    public function token_mismatch_on_guest_redirects_to_login_with_error()
    {
        $this->post('/test-csrf-mismatch')
            ->assertRedirect(route('login'))
            ->assertSessionHas('error');

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

    /**
     * Reproduz o ciclo de 3 logins observado em prod:
     *   1. POST /login OK     → sessão regenerada, novo token
     *   2. POST /login 419    → browser re-submetendo com token velho
     *   3. Esperado: usuário continua autenticado, não cai pra login de novo
     */
    #[Test]
    public function authenticated_user_survives_repeated_csrf_mismatches_from_mobile_resubmit()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        // Simula 5 re-submits seguidos do mobile com tokens stale
        for ($i = 0; $i < 5; $i++) {
            $this->post('/test-csrf-mismatch')
                ->assertRedirect(route('admin.dashboard'));
            $this->assertAuthenticatedAs($user);
        }
    }

    // ─── Cenários reproduzidos do log de produção (mobile Android Chrome) ────

    #[Test]
    public function session_token_is_regenerated_for_authenticated_user_on_csrf_mismatch()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Força criação de um token na sessão
        $this->get(route('admin.dashboard'));
        $oldToken = session()->token();

        $this->post('/test-csrf-mismatch');

        $newToken = session()->token();
        $this->assertNotSame($oldToken, $newToken, 'Token deve ser regenerado após 419');
        $this->assertNotEmpty($newToken);
    }

    #[Test]
    public function csrf_mismatch_on_non_login_post_preserves_authentication()
    {
        // O cenário no log foi POST /login, mas o mesmo padrão pode ocorrer
        // em qualquer POST autenticado (criar animal, vacina, etc.) — garante
        // que o handler trata todos igual.
        Route::post('/admin/test-protected-action', function () {
            throw new TokenMismatchException('CSRF token mismatch.');
        })->middleware('web');

        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/admin/test-protected-action')
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    #[Test]
    public function login_view_includes_double_submit_guard_script()
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        // form com id específico que o JS procura
        $response->assertSee('id="login-form"', false);
        // o guard de double-submit
        $response->assertSee('dataset.submitted', false);
    }

    #[Test]
    public function login_view_disables_submit_button_via_inline_handler()
    {
        $response = $this->get(route('login'));

        // o JS deve marcar form como submetido E desabilitar o botão
        $response->assertSee("form.dataset.submitted = '1'", false);
        $response->assertSee('btn.disabled = true', false);
    }

    protected function setUp(): void
    {
        parent::setUp();

        Route::post('/test-csrf-mismatch', function () {
            throw new TokenMismatchException('CSRF token mismatch.');
        })->middleware('web');
    }
}
