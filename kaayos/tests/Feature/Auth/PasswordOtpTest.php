<?php

namespace Tests\Feature\Auth;

use App\Models\PasswordOtpToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PasswordOtpTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();

        $this->user = User::factory()->create([
            'password' => bcrypt('currentpass'),
        ]);

        $this->actingAs($this->user);
    }

    public function test_send_otp_requires_correct_password(): void
    {
        $this->postJson('/password-otp/send', [
            'current_password' => 'wrongpass',
        ])->assertStatus(422);
    }

    public function test_send_otp_has_rate_limit(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $this->postJson('/password-otp/send', [
                'current_password' => 'currentpass',
            ])->assertOk();
        }

        $this->postJson('/password-otp/send', [
            'current_password' => 'currentpass',
        ])->assertStatus(429);
    }

    public function test_verify_otp_with_valid_code_changes_password(): void
    {
        $otp = '123456';

        PasswordOtpToken::create([
            'user_id' => $this->user->id,
            'token' => Hash::make($otp),
            'expires_at' => now()->addMinutes(10),
        ]);

        $this->postJson('/password-otp/verify', [
            'otp' => $otp,
            'current_password' => 'currentpass',
            'new_password' => 'NewPass123!',
            'new_password_confirmation' => 'NewPass123!',
        ])->assertOk();

        $this->user->refresh();
        $this->assertTrue(Hash::check('NewPass123!', $this->user->password));
    }

    public function test_verify_otp_rejects_invalid_code(): void
    {
        PasswordOtpToken::create([
            'user_id' => $this->user->id,
            'token' => Hash::make('123456'),
            'expires_at' => now()->addMinutes(10),
        ]);

        $this->postJson('/password-otp/verify', [
            'otp' => '000000',
            'current_password' => 'currentpass',
            'new_password' => 'NewPass123!',
            'new_password_confirmation' => 'NewPass123!',
        ])->assertStatus(422);
    }

    public function test_old_password_stops_working_after_change(): void
    {
        $otp = '123456';

        PasswordOtpToken::create([
            'user_id' => $this->user->id,
            'token' => Hash::make($otp),
            'expires_at' => now()->addMinutes(10),
        ]);

        $this->postJson('/password-otp/verify', [
            'otp' => $otp,
            'current_password' => 'currentpass',
            'new_password' => 'NewPass123!',
            'new_password_confirmation' => 'NewPass123!',
        ])->assertOk();

        $this->user->refresh();
        $this->assertTrue(Hash::check('NewPass123!', $this->user->password));
        $this->assertFalse(Hash::check('currentpass', $this->user->password));
    }
}
