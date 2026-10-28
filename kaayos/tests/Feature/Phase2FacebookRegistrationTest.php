<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WorkerProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Tests\TestCase;
use Mockery;

class Phase2FacebookRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Create a mock Socialite user object for Facebook.
     * Facebook does not provide a verified_email claim, so it is not set.
     */
    protected function mockFacebookUser(array $overrides = []): SocialiteUser
    {
        $attributes = array_merge([
            'id'    => $overrides['oauth_id'] ?? 'facebook_12345',
            'email' => $overrides['email'] ?? 'newuser@facebook.com',
            'name'  => $overrides['name'] ?? 'New User',
            'avatar'=> $overrides['avatar'] ?? null,
            // Facebook does not expose verified_email in the standard Graph API response
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
     * Mock Socialite to return the given Facebook user.
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
     * Set up a valid Facebook registration session payload.
     * email_verified is false because Facebook does not provide a reliable claim.
     */
    protected function sessionWithFacebookRegistration(array $overrides = []): array
    {
        return array_merge([
            'oauth_registration_flow' => true,
            'oauth_registration' => [
                'provider'      => 'facebook',
                'oauth_id'      => 'facebook_test_001',
                'email'         => 'test.new@facebook.com',
                'first_name'    => 'Test',
                'last_name'     => 'New',
                'avatar'        => null,
                'email_verified'=> false,
                'expires_at'    => now()->addMinutes(15)->timestamp,
            ],
            'oauth_intended_role' => 'client',
        ], $overrides);
    }

    // ------------------------------------------------------------------
    // Test: Facebook registration redirect flow
    // ------------------------------------------------------------------

    public function test_facebook_register_redirect_requires_configured_provider(): void
    {
        // Temporarily unset Facebook config to test the not-configured path
        config(['services.facebook.client_id' => null, 'services.facebook.client_secret' => null]);

        $response = $this->get(route('auth.facebook.register.redirect', ['role' => 'client']));

        $response->assertRedirect(route('register'));
        $response->assertSessionHasErrors('oauth');
    }

    // ------------------------------------------------------------------
    // Test: New Facebook Client registration (unverified email)
    // ------------------------------------------------------------------

    public function test_facebook_client_registration_creates_unverified_account(): void
    {
        $socialUser = $this->mockFacebookUser([
            'oauth_id' => 'facebook_test_001',
            'email'    => 'test.new@facebook.com',
            'name'     => 'Test New',
        ]);
        $this->mockSocialite($socialUser);

        // Complete registration directly (session set via withSession)
        $response = $this->withSession($this->sessionWithFacebookRegistration())
             ->post(route('auth.facebook.register.complete'), [
                 'first_name' => 'Test',
                 'last_name'  => 'New',
                 'email'      => 'test.new@facebook.com',
                 'role'       => 'client',
                 'terms'      => 'on',
             ]);

        $response->assertRedirect(route('client.dashboard'));

        $user = User::where('email', 'test.new@facebook.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('client', $user->role);
        $this->assertEquals('facebook', $user->oauth_provider);
        $this->assertEquals('facebook_test_001', $user->oauth_id);
        // Facebook email is NOT auto-verified
        $this->assertNull($user->email_verified_at);
        $this->assertNull($user->workerProfile);
    }

    public function test_facebook_worker_registration_creates_unverified_account_with_profile(): void
    {
        $socialUser = $this->mockFacebookUser([
            'oauth_id' => 'facebook_worker_001',
            'email'    => 'worker.new@facebook.com',
            'name'     => 'Worker New',
        ]);
        $this->mockSocialite($socialUser);

        $this->withSession($this->sessionWithFacebookRegistration([
            'oauth_id'      => 'facebook_worker_001',
            'email'         => 'worker.new@facebook.com',
            'oauth_intended_role' => 'worker',
            'oauth_registration' => [
                'provider'      => 'facebook',
                'oauth_id'      => 'facebook_worker_001',
                'email'         => 'worker.new@facebook.com',
                'first_name'    => 'Worker',
                'last_name'     => 'New',
                'avatar'        => null,
                'email_verified'=> false,
                'expires_at'    => now()->addMinutes(15)->timestamp,
            ],
        ]))->post(route('auth.facebook.register.complete'), [
            'first_name'       => 'Worker',
            'last_name'        => 'New',
            'email'            => 'worker.new@facebook.com',
            'phone'            => '09123456789',
            'role'             => 'worker',
            'service_category' => 'plumbing',
            'barangay'         => 'Dalima',
            'terms'            => 'on',
        ])->assertRedirect(route('worker.dashboard'));

        $user = User::where('email', 'worker.new@facebook.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('worker', $user->role);
        $this->assertNotNull($user->workerProfile);
        // Facebook email is NOT auto-verified
        $this->assertNull($user->email_verified_at);
    }

    // ------------------------------------------------------------------
    // Test: Role validation
    // ------------------------------------------------------------------

    public function test_facebook_registration_requires_valid_role(): void
    {
        $this->withSession($this->sessionWithFacebookRegistration([
            'oauth_id' => 'facebook_norole_001',
            'email'    => 'norole.new@facebook.com',
        ]))->post(route('auth.facebook.register.complete'), [
            'first_name' => 'No',
            'last_name'  => 'Role',
            'email'      => 'norole.new@facebook.com',
            'role'       => 'admin', // admin not allowed via Facebook registration
            'terms'      => 'on',
        ])->assertSessionHasErrors('role');

        $this->assertNull(User::where('email', 'norole.new@facebook.com')->first());
    }

    public function test_facebook_registration_missing_role_is_rejected(): void
    {
        $this->withSession($this->sessionWithFacebookRegistration([
            'oauth_id' => 'facebook_norole_002',
            'email'    => 'norole2.new@facebook.com',
        ]))->post(route('auth.facebook.register.complete'), [
            'first_name' => 'No',
            'last_name'  => 'Role2',
            'email'      => 'norole2.new@facebook.com',
            'terms'      => 'on',
            // role missing
        ])->assertSessionHasErrors('role');

        $this->assertNull(User::where('email', 'norole2.new@facebook.com')->first());
    }

    // ------------------------------------------------------------------
    // Test: Facebook callback allows registration without verified email claim
    // ------------------------------------------------------------------

    public function test_facebook_callback_allows_unverified_email_for_registration(): void
    {
        $socialUser = $this->mockFacebookUser([
            'oauth_id' => 'facebook_unverified_001',
            'email'    => 'unverified.new@facebook.com',
            'name'     => 'Unverified User',
        ]);
        $this->mockSocialite($socialUser);

        // Facebook callback should NOT reject (unlike Google) — Facebook has no verified claim
        $this->withSession(['oauth_registration_flow' => true])
             ->get(route('auth.facebook.register.callback'))
             ->assertRedirect(route('auth.facebook.register.form'));
    }

    public function test_facebook_callback_redirects_to_registration_form(): void
    {
        $socialUser = $this->mockFacebookUser([
            'oauth_id' => 'facebook_form_001',
            'email'    => 'form.new@facebook.com',
            'name'     => 'Form User',
        ]);
        $this->mockSocialite($socialUser);

        $this->withSession(['oauth_registration_flow' => true])
             ->get(route('auth.facebook.register.callback'))
             ->assertRedirect(route('auth.facebook.register.form'));
    }

    // ------------------------------------------------------------------
    // Test: Existing linked Facebook identity → redirect to login
    // ------------------------------------------------------------------

    public function test_facebook_registration_rejects_already_linked_identity(): void
    {
        User::factory()->create([
            'email'              => 'linked.existing@facebook.com',
            'oauth_provider'     => 'facebook',
            'oauth_id'           => 'facebook_linked_001',
            'role'               => 'client',
            'email_verified_at'  => now(),
        ]);

        $socialUser = $this->mockFacebookUser([
            'oauth_id' => 'facebook_linked_001',
            'email'    => 'linked.existing@facebook.com',
            'name'     => 'Linked Existing',
        ]);
        $this->mockSocialite($socialUser);

        $this->withSession(['oauth_registration_flow' => true])
             ->get(route('auth.facebook.register.callback'))
             ->assertRedirect(route('login'))
             ->assertSessionHas('oauth');
    }

    // ------------------------------------------------------------------
    // Test: Existing email without Facebook link → redirect to login
    // ------------------------------------------------------------------

    public function test_facebook_registration_rejects_existing_email_without_link(): void
    {
        User::factory()->create([
            'email'              => 'existing.email@facebook.com',
            'oauth_provider'     => null,
            'oauth_id'           => null,
            'role'               => 'client',
            'email_verified_at'  => now(),
        ]);

        $socialUser = $this->mockFacebookUser([
            'oauth_id' => 'facebook_existing_001',
            'email'    => 'existing.email@facebook.com',
            'name'     => 'Existing Email',
        ]);
        $this->mockSocialite($socialUser);

        $this->withSession(['oauth_registration_flow' => true])
             ->get(route('auth.facebook.register.callback'))
             ->assertRedirect(route('login'))
             ->assertSessionHasErrors('oauth');
    }

    // ------------------------------------------------------------------
    // Test: Duplicate submission protection
    // ------------------------------------------------------------------

    public function test_facebook_registration_prevents_duplicate_submission(): void
    {
        $socialUser = $this->mockFacebookUser([
            'oauth_id' => 'facebook_dup_001',
            'email'    => 'dup.new@facebook.com',
            'name'     => 'Dup User',
        ]);
        $this->mockSocialite($socialUser);

        // First submission creates account
        $this->withSession(array_merge($this->sessionWithFacebookRegistration(), [
            'oauth_registration' => array_merge(
                $this->sessionWithFacebookRegistration()['oauth_registration'],
                ['oauth_id' => 'facebook_dup_001', 'email' => 'dup.new@facebook.com']
            ),
        ]))->post(route('auth.facebook.register.complete'), [
            'first_name' => 'Dup',
            'last_name'  => 'User',
            'email'      => 'dup.new@facebook.com',
            'role'       => 'client',
            'terms'      => 'on',
        ]);

        $this->assertNotNull(User::where('email', 'dup.new@facebook.com')->first());

        // Second submission with same email → rejected at complete (duplicate email)
        $this->withSession($this->sessionWithFacebookRegistration([
            'oauth_id' => 'facebook_dup_002',
            'email'    => 'dup.new@facebook.com',
        ]))->post(route('auth.facebook.register.complete'), [
            'first_name' => 'Dup2',
            'last_name'  => 'User2',
            'email'      => 'dup.new@facebook.com',
            'role'       => 'client',
            'terms'      => 'on',
        ])->assertSessionHasErrors('email');

        // Duplicate provider identity → rejected at callback (already linked)
        $socialUser2 = $this->mockFacebookUser([
            'oauth_id' => 'facebook_dup_001',
            'email'    => 'other.email@facebook.com',
        ]);
        $this->mockSocialite($socialUser2);

        $this->withSession(['oauth_registration_flow' => true])
             ->get(route('auth.facebook.register.callback'))
             ->assertRedirect(route('login'))
             ->assertSessionHas('oauth');
    }

    // ------------------------------------------------------------------
    // Test: Expired session
    // ------------------------------------------------------------------

    public function test_facebook_registration_expired_session_is_rejected(): void
    {
        $this->withSession(array_merge($this->sessionWithFacebookRegistration(), [
            'oauth_registration' => array_merge(
                $this->sessionWithFacebookRegistration()['oauth_registration'],
                ['expires_at' => now()->subMinutes(1)->timestamp]
            ),
        ]))->post(route('auth.facebook.register.complete'), [
            'first_name' => 'Expired',
            'last_name'  => 'User',
            'email'      => 'expired.new@facebook.com',
            'role'       => 'client',
            'terms'      => 'on',
        ])->assertRedirect(route('register'))
         ->assertSessionHasErrors('oauth');
    }

    public function test_facebook_registration_expired_session_on_complete_is_rejected(): void
    {
        $this->withSession(array_merge($this->sessionWithFacebookRegistration(), [
            'oauth_registration_flow' => false,
        ]))->post(route('auth.facebook.register.complete'), [
            'first_name' => 'Expired',
            'last_name'  => 'User',
            'email'      => 'expired2.new@facebook.com',
            'role'       => 'client',
            'terms'      => 'on',
        ])->assertRedirect(route('register'));
    }

    // ------------------------------------------------------------------
    // Test: Validation errors
    // ------------------------------------------------------------------

    public function test_facebook_registration_validation_errors(): void
    {
        $this->withSession($this->sessionWithFacebookRegistration([
            'oauth_id' => 'facebook_val_001',
            'email'    => 'val.new@facebook.com',
        ]))->post(route('auth.facebook.register.complete'), [
            'first_name' => '',
            'last_name'  => '',
            'email'      => 'not-an-email',
            'role'       => 'client',
            'terms'      => '',
        ])->assertSessionHasErrors(['first_name', 'last_name', 'email', 'terms']);

        $this->assertNull(User::where('email', 'val.new@facebook.com')->first());
    }

    // ------------------------------------------------------------------
    // Test: Missing handoff session
    // ------------------------------------------------------------------

    public function test_facebook_registration_missing_handoff_is_rejected(): void
    {
        $this->withSession([])->post(route('auth.facebook.register.complete'), [
            'first_name' => 'Missing',
            'last_name'  => 'User',
            'email'      => 'missing.new@facebook.com',
            'role'       => 'client',
            'terms'      => 'on',
        ])->assertRedirect(route('register'));
    }

    public function test_facebook_registration_expired_handoff_is_rejected(): void
    {
        $this->withSession(array_merge($this->sessionWithFacebookRegistration(), [
            'oauth_registration' => array_merge(
                $this->sessionWithFacebookRegistration()['oauth_registration'],
                ['expires_at' => now()->subMinutes(10)->timestamp]
            ),
        ]))->post(route('auth.facebook.register.complete'), [
            'first_name' => 'Expired',
            'last_name'  => 'Handoff',
            'email'      => 'handoff.new@facebook.com',
            'role'       => 'client',
            'terms'      => 'on',
        ])->assertRedirect(route('register'))
         ->assertSessionHasErrors('oauth');
    }

    // ------------------------------------------------------------------
    // Test: Unregistered Facebook login → Facebook registration flow
    // ------------------------------------------------------------------

    public function test_facebook_login_unregistered_enters_facebook_registration_flow(): void
    {
        $socialUser = $this->mockFacebookUser([
            'oauth_id' => 'facebook_unreg_001',
            'email'    => 'unreg.new@facebook.com',
            'name'     => 'Unreg User',
        ]);
        $this->mockSocialite($socialUser);

        // Facebook login callback for unregistered user → Facebook registration form
        $response = $this->withSession(['oauth_registration_flow' => true])
             ->get(route('auth.facebook.register.callback'));

        $response->assertRedirect(route('auth.facebook.register.form'));
    }

    // ------------------------------------------------------------------
    // Test: Existing linked Facebook login → works
    // ------------------------------------------------------------------

    public function test_facebook_login_linked_identity_works(): void
    {
        $user = User::factory()->create([
            'email'              => 'linked.login@facebook.com',
            'oauth_provider'     => 'facebook',
            'oauth_id'           => 'facebook_linked_login_001',
            'role'               => 'client',
            'email_verified_at'  => now(),
        ]);

        $socialUser = $this->mockFacebookUser([
            'oauth_id' => 'facebook_linked_login_001',
            'email'    => 'linked.login@facebook.com',
            'name'     => 'Linked Login',
        ]);
        $this->mockSocialite($socialUser);

        $response = $this->get(route('social.callback', ['provider' => 'facebook']));

        $response->assertRedirect(route('client.dashboard'));
        $this->assertAuthenticatedAs($user);
    }
}
