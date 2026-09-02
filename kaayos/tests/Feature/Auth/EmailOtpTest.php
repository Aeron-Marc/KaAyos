<?php

namespace Tests\Feature\Auth;

use App\Models\PasswordOtpToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmailOtpTest extends TestCase
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
        $this->postJson('/email-otp/send', [
            'new_email' => 'new@example.com',
            'new_email_confirmation' => 'new@example.com',
            'current_password' => 'wrongpass',
        ])->assertStatus(422);
    }

    public function test_send_otp_rejects_same_email(): void
    {
        $this->postJson('/email-otp/send', [
            'new_email' => $this->user->email,
            'new_email_confirmation' => $this->user->email,
            'current_password' => 'currentpass',
        ])->assertStatus(422);
    }

    public function test_send_otp_has_rate_limit(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $this->postJson('/email-otp/send', [
                'new_email' => "new{$i}@example.com",
                'new_email_confirmation' => "new{$i}@example.com",
                'current_password' => 'currentpass',
            ])->assertOk();
        }

        $this->postJson('/email-otp/send', [
            'new_email' => 'another@example.com',
            'new_email_confirmation' => 'another@example.com',
            'current_password' => 'currentpass',
        ])->assertStatus(429);
    }

    public function test_verify_otp_with_valid_code(): void
    {
        $otp = '123456';

        PasswordOtpToken::create([
            'user_id' => $this->user->id,
            'type' => 'email',
            'token' => Hash::make($otp),
            'expires_at' => now()->addMinutes(10),
        ]);

        $this->user->update(['pending_email' => 'new@example.com']);

        $this->postJson('/email-otp/verify', ['otp' => $otp])
            ->assertOk();

        $this->user->refresh();
        $this->assertEquals('new@example.com', $this->user->email);
        $this->assertNull($this->user->pending_email);
    }

    public function test_verify_otp_rejects_invalid_code(): void
    {
        PasswordOtpToken::create([
            'user_id' => $this->user->id,
            'type' => 'email',
            'token' => Hash::make('123456'),
            'expires_at' => now()->addMinutes(10),
        ]);

        $this->postJson('/email-otp/verify', ['otp' => '000000'])
            ->assertStatus(422);
    }

    public function test_verify_otp_has_rate_limit(): void
    {
        PasswordOtpToken::create([
            'user_id' => $this->user->id,
            'type' => 'email',
            'token' => Hash::make('123456'),
            'expires_at' => now()->addMinutes(10),
        ]);

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/email-otp/verify', ['otp' => '000000']);
        }

        $this->postJson('/email-otp/verify', ['otp' => '000000'])
            ->assertStatus(429);
    }
}
