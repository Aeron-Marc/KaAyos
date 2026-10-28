<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationRateLimitTest extends TestCase
{
    use RefreshDatabase;

    protected function clientData(string $email, array $overrides = []): array
    {
        return array_merge([
            'first_name'            => 'Juan',
            'last_name'             => 'Dela Cruz',
            'email'                 => $email,
            'role'                  => 'client',
            'password'              => 'Secret123!',
            'password_confirmation' => 'Secret123!',
            'terms'                 => true,
        ], $overrides);
    }

    protected function workerData(string $email, array $overrides = []): array
    {
        return array_merge($this->clientData($email, [
            'first_name'       => 'Maria',
            'last_name'        => 'Santos',
            'role'             => 'worker',
            'service_category' => 'plumbing',
            'barangay'         => 'Dalima',
        ]), $overrides);
    }

    /**
     * Valid OAuth handoff session for the Google registration completion route.
     */
    protected function googleSession(string $email, string $oauthId = 'google_test_001'): array
    {
        return [
            'oauth_registration_flow' => true,
            'oauth_registration'      => [
                'provider'       => 'google',
                'oauth_id'       => $oauthId,
                'email'          => $email,
                'first_name'     => 'Google',
                'last_name'      => 'Worker',
                'avatar'         => null,
                'email_verified' => true,
                'expires_at'     => now()->addMinutes(15)->timestamp,
            ],
            'oauth_intended_role' => 'worker',
        ];
    }

    protected function googlePayload(string $email): array
    {
        return [
            'first_name'       => 'Google',
            'last_name'        => 'Worker',
            'email'            => $email,
            'role'             => 'worker',
            'service_category' => 'plumbing',
            'barangay'         => 'Dalima',
            'terms'            => 'on',
        ];
    }

    public function test_client_and_worker_registration_from_same_ip_do_not_share_a_three_attempt_bucket(): void
    {
        // 1st attempt: a client account is created.
        $this->post('/register', $this->clientData('client@example.com'))
            ->assertRedirect();
        $this->assertDatabaseHas('users', ['email' => 'client@example.com', 'role' => 'client']);

        // Attempts 2 and 3: worker submissions rejected by validation still
        // consume the allowance (abuse protection must stay in middleware).
        $worker = $this->workerData('worker@example.com');
        $invalid = $worker;
        unset($invalid['barangay']);

        $this->post('/register', $invalid)->assertSessionHasErrors('barangay');
        $this->post('/register', $invalid)->assertSessionHasErrors('barangay');

        // 4th attempt: must not be blocked by a tiny three-attempt IP bucket.
        $this->post('/register', $worker)->assertRedirect();
        $this->assertDatabaseHas('users', ['email' => 'worker@example.com', 'role' => 'worker']);
    }

    public function test_per_email_limit_applies_independently(): void
    {
        $payload = $this->clientData('repeat@example.com', [
            'password_confirmation' => 'Different123!',
        ]);

        for ($i = 0; $i < 3; $i++) {
            $this->post('/register', $payload)
                ->assertStatus(302)
                ->assertSessionHasErrors('password');
        }

        $blocked = $this->post('/register', $payload);
        $blocked->assertStatus(429);
        $blocked->assertHeader('Retry-After');
        // The per-email limit (3/hour) is the one that trips.
        $blocked->assertHeader('X-RateLimit-Limit', '3');

        // A different email from the same IP is unaffected.
        $this->post('/register', $this->clientData('other@example.com'))
            ->assertStatus(302)
            ->assertSessionHasNoErrors();
        $this->assertDatabaseHas('users', ['email' => 'other@example.com']);
    }

    public function test_missing_email_is_handled_safely_and_ip_ceiling_is_configurable(): void
    {
        config(['kaayos.registration_ip_hourly_limit' => 3]);

        $payload = $this->clientData('placeholder@example.com');
        unset($payload['email']);

        for ($i = 0; $i < 3; $i++) {
            $this->post('/register', $payload)
                ->assertStatus(302)
                ->assertSessionHasErrors('email');
        }

        $blocked = $this->post('/register', $payload);
        $blocked->assertStatus(429);
        $blocked->assertHeader('X-RateLimit-Limit', '3');
    }

    public function test_google_registration_completion_uses_its_own_limiter(): void
    {
        // Exhaust the classic /register bucket for a single address.
        $drain = $this->clientData('drain@example.com', [
            'password_confirmation' => 'Different123!',
        ]);

        for ($i = 0; $i < 3; $i++) {
            $this->post('/register', $drain)->assertStatus(302);
        }

        $this->post('/register', $drain)->assertStatus(429);

        // Google completion has a separate bucket, so it still succeeds.
        $this->withSession($this->googleSession('google.worker@example.com', 'google_rl_worker_1'))
            ->post(route('auth.google.register.complete'), $this->googlePayload('google.worker@example.com'))
            ->assertRedirect(route('worker.dashboard'));

        $this->assertDatabaseHas('users', [
            'email' => 'google.worker@example.com',
            'role'  => 'worker',
        ]);
    }

    public function test_google_registration_completion_is_rate_limited_per_email(): void
    {
        $session = $this->googleSession('google.limit@example.com', 'google_rl_limit_1');
        $payload = $this->googlePayload('google.limit@example.com');
        $payload['terms'] = null; // invalid request, but still counted

        for ($i = 0; $i < 3; $i++) {
            $this->withSession($session)
                ->post(route('auth.google.register.complete'), $payload)
                ->assertStatus(302)
                ->assertSessionHasErrors('terms');
        }

        $blocked = $this->withSession($session)
            ->post(route('auth.google.register.complete'), $payload);

        $blocked->assertStatus(429);
        $blocked->assertHeader('X-RateLimit-Limit', '3');
    }

    public function test_email_limit_normalizes_case_and_whitespace(): void
    {
        $payload = $this->clientData('normalized@example.com', [
            'password_confirmation' => 'Different123!',
        ]);

        for ($i = 0; $i < 3; $i++) {
            $this->post('/register', $payload)->assertStatus(302);
        }

        // Same address with different casing/whitespace must hit the same bucket.
        $this->post('/register', $this->clientData('  NORMALIZED@Example.com  ', [
            'password_confirmation' => 'Different123!',
        ]))->assertStatus(429);
    }

    public function test_google_completion_uses_the_oauth_session_email_when_the_field_is_missing(): void
    {
        $session = $this->googleSession('google.session@example.com', 'google_rl_session_1');
        $payload = $this->googlePayload('google.session@example.com');
        unset($payload['email']);

        for ($i = 0; $i < 3; $i++) {
            $this->withSession($session)
                ->post(route('auth.google.register.complete'), $payload)
                ->assertStatus(302)
                ->assertSessionHasErrors('email');
        }

        // Bucketed by the verified address from the OAuth handoff session,
        // not by the absent form field.
        $this->withSession($session)
            ->post(route('auth.google.register.complete'), $payload)
            ->assertStatus(429);
    }

    public function test_rate_limited_response_is_friendly_and_hides_internals(): void
    {
        $payload = $this->clientData('friendly@example.com', [
            'password_confirmation' => 'Different123!',
        ]);

        for ($i = 0; $i < 3; $i++) {
            $this->post('/register', $payload)->assertStatus(302);
        }

        $blocked = $this->post('/register', $payload);

        $blocked->assertStatus(429);
        $blocked->assertHeader('Retry-After');
        $blocked->assertSee('Please slow down for a moment');
        $blocked->assertSee('You can try again in');
        $blocked->assertDontSee('Stack trace');
        $blocked->assertDontSee('vendor/laravel');
        $blocked->assertDontSee('AppServiceProvider.php');
    }

    public function test_json_clients_receive_a_json_rate_limit_response(): void
    {
        $payload = $this->clientData('json@example.com', [
            'password_confirmation' => 'Different123!',
        ]);

        for ($i = 0; $i < 3; $i++) {
            $this->postJson('/register', $payload)->assertStatus(422);
        }

        $blocked = $this->postJson('/register', $payload);

        $blocked->assertStatus(429);
        $blocked->assertJsonStructure(['message', 'retry_after']);
        $blocked->assertJsonPath('message', 'Too many attempts. Please try again later.');
    }

    public function test_registration_validation_and_role_restrictions_remain(): void
    {
        // Admin role is not registrable.
        $this->post('/register', $this->clientData('admin@example.com', ['role' => 'admin']))
            ->assertSessionHasErrors('role');
        $this->assertDatabaseMissing('users', ['email' => 'admin@example.com']);

        // Duplicate email is still rejected.
        $this->post('/register', $this->clientData('dupe@example.com'))->assertRedirect();
        $this->post('/register', $this->clientData('dupe@example.com'))
            ->assertSessionHasErrors('email');
        $this->assertSame(1, User::where('email', 'dupe@example.com')->count());

        // Google completion still refuses an admin role.
        $this->withSession($this->googleSession('google.admin@example.com', 'google_rl_admin_1'))
            ->post(route('auth.google.register.complete'), array_merge(
                $this->googlePayload('google.admin@example.com'),
                ['role' => 'admin'],
            ))
            ->assertSessionHasErrors('role');

        $this->assertDatabaseMissing('users', ['email' => 'google.admin@example.com']);
    }
}
