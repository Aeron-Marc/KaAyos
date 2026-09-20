<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase1FoundationTest extends TestCase
{
    public function test_locale_switcher_updates_session_and_user_preference(): void
    {
        $response = $this->get(route('locale.switch', 'fil'));
        $response->assertRedirect();
        $this->assertEquals('fil', session('locale'));

        $user = User::factory()->create([
            'language' => 'English',
        ]);

        $this->actingAs($user)
            ->get(route('locale.switch', 'fil'));

        $this->assertEquals('Filipino', $user->fresh()->language);
    }

    public function test_user_can_have_client_type_and_business_metadata(): void
    {
        $businessUser = User::factory()->create([
            'role' => 'client',
            'client_type' => 'business_commercial',
            'organization_name' => 'Tuy Hardware & Supplies',
            'tin_number' => '123-456-789-000',
        ]);

        $this->assertTrue($businessUser->isBusiness());
        $this->assertEquals('Tuy Hardware & Supplies', $businessUser->organization_name);
        $this->assertEquals('123-456-789-000', $businessUser->tin_number);

        $tenantUser = User::factory()->create([
            'role' => 'client',
            'client_type' => 'tenant_renter',
        ]);

        $this->assertTrue($tenantUser->isTenant());
    }

    public function test_social_auth_redirects_for_supported_providers(): void
    {
        $response = $this->get(route('auth.social.redirect', 'google'));
        // Socialite redirects to accounts.google.com
        $this->assertTrue($response->isRedirection());

        // Unsupported provider redirects back with error
        $badResponse = $this->get(route('auth.social.redirect', 'unsupported_oauth'));
        $badResponse->assertRedirect(route('login'));
        $badResponse->assertSessionHasErrors('oauth');
    }
}

