<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WorkerProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Tests\TestCase;
use Mockery;

class Phase2GoogleRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Create a mock Socialite user object with configurable properties.
     * Builds a real Socialite User instance so verified_email is accessible
     * via __get/offsetGet as the controller expects.
     */
    protected function mockGoogleUser(array $overrides = []): SocialiteUser
    {
        $attributes = array_merge([
            'id'             => $overrides['oauth_id'] ?? 'google_12345',
            'email'          => $overrides['email'] ?? 'newuser@gmail.com',
            'name'           => $overrides['name'] ?? 'New User',
            'avatar'         => $overrides['avatar'] ?? null,
            // GoogleProvider maps email_verified -> verified_email; set directly here
            'verified_email' => $overrides['email_verified'] ?? true,
            'token'          => 'test-token',
            'refreshToken'   => 'test-refresh',
            'expiresIn'      => 3600,
            'approvedScopes' => [],
        ], $overrides);

        $user = new SocialiteUser();
        $user->setRaw($attributes);
        $user->map($attributes);
        $user->setToken($attributes['token']);
        $user->setRefreshToken($attributes['refreshToken']);
        $user->setExpiresIn($attributes['expiresIn']);
        $user->setApprovedScopes($attributes['approvedScopes']);

        return $user;
    }

    /**
     * Mock Socialite to return the given Google user.
     * Also stubs redirect() so the redirect flow doesn't fail.
     */
    protected function mockSocialite(SocialiteUser $socialUser): void
    {
        $driver = Mockery::mock();
        $driver->shouldReceive('redirectUrl')->andReturn($driver);
        $driver->shouldReceive('redirect')->andReturn(new \Illuminate\Http\RedirectResponse('/'));
        $driver->shouldReceive('user')->andReturn($socialUser);
        Socialite::shouldReceive('driver')->andReturn($driver);
    }

    /**
     * Set up a valid Google registration session payload.
     */
    protected function sessionWithGoogleRegistration(array $overrides = []): array
    {
        return array_merge([
            'oauth_registration_flow' => true,
            'oauth_registration' => [
                'provider'      => 'google',
                'oauth_id'      => 'google_test_001',
                'email'         => 'test.new@gmail.com',
                'first_name'    => 'Test',
                'last_name'     => 'New',
                'avatar'        => null,
                'email_verified'=> true,
                'expires_at'    => now()->addMinutes(15)->timestamp,
            ],
            'oauth_intended_role' => 'client',
        ], $overrides);
    }

    // ------------------------------------------------------------------
    // Test: New Google Client registration
    // ------------------------------------------------------------------

    public function test_google_client_registration_creates_account_and_redirects(): void
    {
        $socialUser = $this->mockGoogleUser([
            'oauth_id' => 'google_test_001',
            'email'    => 'test.new@gmail.com',
            'name'     => 'Test New',
        ]);
        $this->mockSocialite($socialUser);

        // Complete registration directly (session set via withSession)
        $response = $this->withSession($this->sessionWithGoogleRegistration())
             ->post(route('auth.google.register.complete'), [
                 'first_name' => 'Test',
                 'last_name'  => 'New',
                 'email'      => 'test.new@gmail.com',
                 'role'       => 'client',
                 'terms'      => 'on',
             ]);

        $response->assertRedirect(route('client.dashboard'));

        $user = User::where('email', 'test.new@gmail.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('client', $user->role);
        $this->assertEquals('google', $user->oauth_provider);
        $this->assertEquals('google_test_001', $user->oauth_id);
        $this->assertNotNull($user->email_verified_at);
        $this->assertNull($user->workerProfile);
    }

    // ------------------------------------------------------------------
    // Test: New Google Worker registration
    // ------------------------------------------------------------------

    public function test_google_worker_registration_creates_worker_profile(): void
    {
        $socialUser = $this->mockGoogleUser([
            'oauth_id' => 'google_worker_001',
            'email'    => 'worker.new@gmail.com',
            'name'     => 'Worker New',
        ]);
        $this->mockSocialite($socialUser);

        $this->withSession($this->sessionWithGoogleRegistration([
            'oauth_id'      => 'google_worker_001',
            'email'         => 'worker.new@gmail.com',
            'oauth_intended_role' => 'worker',
        ]))->post(route('auth.google.register.complete'), [
            'first_name'       => 'Worker',
            'last_name'        => 'New',
            'email'            => 'worker.new@gmail.com',
            'phone'            => '09123456789',
            'role'             => 'worker',
            'service_category' => 'plumbing',
            'barangay'         => 'Dalima',
            'terms'            => 'on',
        ])->assertRedirect(route('worker.dashboard'));

        $user = User::where('email', 'worker.new@gmail.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('worker', $user->role);
        $this->assertNotNull($user->workerProfile);
        $this->assertNotNull($user->email_verified_at);
    }

    // ------------------------------------------------------------------
    // Test: Role selection is required and validated
    // ------------------------------------------------------------------

    public function test_google_registration_requires_valid_role(): void
    {
        $this->withSession($this->sessionWithGoogleRegistration([
            'oauth_id' => 'google_norole_001',
            'email'    => 'norole.new@gmail.com',
        ]))->post(route('auth.google.register.complete'), [
            'first_name' => 'No',
            'last_name'  => 'Role',
            'email'      => 'norole.new@gmail.com',
            'role'       => 'admin', // admin not allowed via Google registration
            'terms'      => 'on',
        ])->assertSessionHasErrors('role');

        $this->assertNull(User::where('email', 'norole.new@gmail.com')->first());
    }

    public function test_google_registration_missing_role_is_rejected(): void
    {
        $this->withSession($this->sessionWithGoogleRegistration([
            'oauth_id' => 'google_norole_002',
            'email'    => 'norole2.new@gmail.com',
        ]))->post(route('auth.google.register.complete'), [
            'first_name' => 'No',
            'last_name'  => 'Role2',
            'email'      => 'norole2.new@gmail.com',
            'terms'      => 'on',
            // role missing
        ])->assertSessionHasErrors('role');

        $this->assertNull(User::where('email', 'norole2.new@gmail.com')->first());
    }

    // ------------------------------------------------------------------
    // Test: Unverified Google email is rejected at callback
    // ------------------------------------------------------------------

    public function test_google_registration_rejects_unverified_email(): void
    {
        $socialUser = $this->mockGoogleUser([
            'oauth_id'      => 'google_unverified_001',
            'email'         => 'unverified.new@gmail.com',
            'name'          => 'Unverified User',
            'email_verified'=> false,
        ]);
        $this->mockSocialite($socialUser);

        // Set registration flow session so callback proceeds past session checks
        $this->withSession(['oauth_registration_flow' => true])
             ->get(route('auth.google.register.callback'))
             ->assertRedirect(route('register'))
             ->assertSessionHasErrors('oauth');
    }

    // ------------------------------------------------------------------
    // Test: Existing linked Google identity → redirect to login
    // ------------------------------------------------------------------

    public function test_google_registration_rejects_already_linked_identity(): void
    {
        User::factory()->create([
            'email'              => 'linked.existing@gmail.com',
            'oauth_provider'     => 'google',
            'oauth_id'           => 'google_linked_001',
            'role'               => 'client',
            'email_verified_at'  => now(),
        ]);

        $socialUser = $this->mockGoogleUser([
            'oauth_id' => 'google_linked_001',
            'email'    => 'linked.existing@gmail.com',
            'name'     => 'Linked Existing',
        ]);
        $this->mockSocialite($socialUser);

        $this->withSession(['oauth_registration_flow' => true])
             ->get(route('auth.google.register.callback'))
             ->assertRedirect(route('login'))
             ->assertSessionHas('oauth');
    }

    // ------------------------------------------------------------------
    // Test: Existing email without Google link → redirect to login
    // ------------------------------------------------------------------

    public function test_google_registration_rejects_existing_email_without_link(): void
    {
        User::factory()->create([
            'email'              => 'existing.email@gmail.com',
            'oauth_provider'     => null,
            'oauth_id'           => null,
            'role'               => 'client',
            'email_verified_at'  => now(),
        ]);

        $socialUser = $this->mockGoogleUser([
            'oauth_id' => 'google_existing_001',
            'email'    => 'existing.email@gmail.com',
            'name'     => 'Existing Email',
        ]);
        $this->mockSocialite($socialUser);

        $this->withSession(['oauth_registration_flow' => true])
             ->get(route('auth.google.register.callback'))
             ->assertRedirect(route('login'))
             ->assertSessionHasErrors('oauth');
    }

    // ------------------------------------------------------------------
    // Test: Duplicate registration submissions prevented
    // ------------------------------------------------------------------

    public function test_google_registration_prevents_duplicate_submission(): void
    {
        $socialUser = $this->mockGoogleUser([
            'oauth_id' => 'google_dup_001',
            'email'    => 'dup.new@gmail.com',
            'name'     => 'Dup New',
        ]);
        $this->mockSocialite($socialUser);

        $session = $this->sessionWithGoogleRegistration([
            'oauth_id' => 'google_dup_001',
            'email'    => 'dup.new@gmail.com',
        ]);

        // First submission succeeds
        $this->withSession($session)
             ->post(route('auth.google.register.complete'), [
                 'first_name' => 'Dup',
                 'last_name'  => 'New',
                 'email'      => 'dup.new@gmail.com',
                 'role'       => 'client',
                 'terms'      => 'on',
             ])
             ->assertRedirect(route('client.dashboard'));

        // Second submission with same Google ID → redirect to login (already linked)
        $socialUser2 = $this->mockGoogleUser([
            'oauth_id' => 'google_dup_001',
            'email'    => 'dup.new@gmail.com',
            'name'     => 'Dup New',
        ]);
        $this->mockSocialite($socialUser2);

        $this->withSession(['oauth_registration_flow' => true])
             ->get(route('auth.google.register.callback'))
             ->assertRedirect(route('login'));
    }

    // ------------------------------------------------------------------
    // Test: Expired registration session
    // ------------------------------------------------------------------

    public function test_google_registration_expired_session_is_rejected(): void
    {
        $this->withSession(array_merge($this->sessionWithGoogleRegistration(), [
            'oauth_registration' => array_merge(
                $this->sessionWithGoogleRegistration()['oauth_registration'],
                ['expires_at' => now()->subMinutes(10)->timestamp]
            ),
        ]))->get(route('auth.google.register.form'))
         ->assertRedirect(route('register'))
         ->assertSessionHasErrors('oauth');
    }

    public function test_google_registration_expired_session_on_complete_is_rejected(): void
    {
        $this->withSession(array_merge($this->sessionWithGoogleRegistration(), [
            'oauth_registration' => array_merge(
                $this->sessionWithGoogleRegistration()['oauth_registration'],
                ['expires_at' => now()->subMinutes(10)->timestamp]
            ),
        ]))->post(route('auth.google.register.complete'), [
            'first_name' => 'Expired',
            'last_name'  => 'New',
            'email'      => 'expired.new@gmail.com',
            'role'       => 'client',
            'terms'      => 'on',
        ])->assertRedirect(route('register'))
         ->assertSessionHasErrors('oauth');
    }

    // ------------------------------------------------------------------
    // Test: Registration validation errors
    // ------------------------------------------------------------------

    public function test_google_registration_validation_errors(): void
    {
        $this->withSession($this->sessionWithGoogleRegistration([
            'oauth_intended_role' => 'worker',
        ]))->post(route('auth.google.register.complete'), [
            'first_name' => '',
            'last_name'  => '',
            'email'      => 'not-an-email',
            'role'       => 'worker',
            'terms'      => 'on',
        ])->assertSessionHasErrors(['first_name', 'last_name', 'email', 'service_category', 'barangay']);
    }

    // ------------------------------------------------------------------
    // Test: Worker profile creation defaults
    // ------------------------------------------------------------------

    public function test_google_worker_registration_creates_worker_profile_with_defaults(): void
    {
        $socialUser = $this->mockGoogleUser([
            'oauth_id' => 'google_wp_001',
            'email'    => 'workerprofile.new@gmail.com',
            'name'     => 'Worker Profile',
        ]);
        $this->mockSocialite($socialUser);

        $session = $this->sessionWithGoogleRegistration([
            'oauth_id'      => 'google_wp_001',
            'email'         => 'workerprofile.new@gmail.com',
            'oauth_intended_role' => 'worker',
        ]);

        $this->withSession($session)
             ->post(route('auth.google.register.complete'), [
                 'first_name'       => 'Worker',
                 'last_name'        => 'Profile',
                 'email'            => 'workerprofile.new@gmail.com',
                 'role'             => 'worker',
                 'service_category' => 'electrical',
                 'barangay'         => 'Dalima',
                 'terms'            => 'on',
             ])
             ->assertRedirect(route('worker.dashboard'));

        $user = User::where('email', 'workerprofile.new@gmail.com')->first();
        $this->assertNotNull($user->workerProfile);
        $this->assertEquals(350.00, $user->workerProfile->hourly_rate);
        $this->assertEquals(15, $user->workerProfile->service_radius_km);
    }

    // ------------------------------------------------------------------
    // Test: Email verification behavior
    // ------------------------------------------------------------------

    public function test_google_registration_marks_email_verified_when_google_verified(): void
    {
        $socialUser = $this->mockGoogleUser([
            'oauth_id'      => 'google_verified_001',
            'email'         => 'verified.new@gmail.com',
            'name'          => 'Verified New',
            'email_verified'=> true,
        ]);
        $this->mockSocialite($socialUser);

        $session = $this->sessionWithGoogleRegistration([
            'oauth_id'      => 'google_verified_001',
            'email'         => 'verified.new@gmail.com',
        ]);

        $this->withSession($session)
             ->post(route('auth.google.register.complete'), [
                 'first_name' => 'Verified',
                 'last_name'  => 'New',
                 'email'      => 'verified.new@gmail.com',
                 'role'       => 'client',
                 'terms'      => 'on',
             ])
             ->assertRedirect(route('client.dashboard'));

        $user = User::where('email', 'verified.new@gmail.com')->first();
        $this->assertTrue($user->hasVerifiedEmail());
    }

    // ------------------------------------------------------------------
    // Test: Existing Google login still refuses unregistered users
    // ------------------------------------------------------------------

    public function test_google_login_still_refuses_unregistered_users(): void
    {
        $socialUser = $this->mockGoogleUser([
            'oauth_id' => 'google_login_unreg_001',
            'email'    => 'unreg.login@gmail.com',
            'name'     => 'Unreg Login',
        ]);
        $this->mockSocialite($socialUser);

        $response = $this->get(route('social.callback', ['provider' => 'google']));
        $response->assertRedirect(route('register'));
        $response->assertSessionHas('oauth');
    }

    public function test_google_login_authenticates_existing_linked_user(): void
    {
        $existingUser = User::factory()->create([
            'email'              => 'linked.login@gmail.com',
            'oauth_provider'     => 'google',
            'oauth_id'           => 'google_linked_login_001',
            'role'               => 'client',
            'email_verified_at'  => now(),
        ]);

        $socialUser = $this->mockGoogleUser([
            'oauth_id' => 'google_linked_login_001',
            'email'    => 'linked.login@gmail.com',
            'name'     => 'Linked Login',
        ]);
        $this->mockSocialite($socialUser);

        $response = $this->get(route('social.callback', ['provider' => 'google']));
        $response->assertRedirect(route('client.dashboard'));
        $this->assertAuthenticatedAs($existingUser);
    }
}
