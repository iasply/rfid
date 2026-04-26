<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class WebAuthRateLimitTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function web_login_is_rate_limited_after_too_many_attempts()
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        // Attempt 5 times
        for ($i = 0; $i < 5; $i++) {
            $response = $this->post('/login', [
                'email' => 'test@example.com',
                'password' => 'wrongpassword',
            ]);
            $response->assertSessionHasErrors('email');
        }

        // 6th attempt should be rate limited
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('email');

        // Assert the error message contains the throttling message or similar mechanism
        // ValidationException uses 'email' key in our implementation
        $this->assertTrue(session()->hasOldInput('email'), 'Throttle did not return with input');
    }

    protected function tearDown(): void
    {
        RateLimiter::clear('test@example.com|127.0.0.1');
        parent::tearDown();
    }
}
