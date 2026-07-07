<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\EmailChangeOtpMail;
use App\Mail\EmailChangedNotification;
use App\Models\PasswordOtpToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;

class EmailOtpController extends Controller
{
    public function sendOtp(Request $request): JsonResponse
    {
        $data = $request->validate([
            'new_email'        => ['required', 'email', 'confirmed', Rule::unique('users', 'email')],
            'current_password' => ['required', 'string'],
        ]);

        $user = $request->user();

        if (!Hash::check($data['current_password'], $user->password)) {
            return response()->json(['message' => 'Current password is incorrect.'], 422);
        }

        if ($data['new_email'] === $user->email) {
            return response()->json(['message' => 'New email is the same as your current email.'], 422);
        }

        if ($user->email_updated_at && $user->email_updated_at->diffInDays(now()) < 30) {
            $daysLeft = (int) ceil(30 - $user->email_updated_at->diffInDays(now()));
            return response()->json(['message' => "You can change your email again in {$daysLeft} day(s)."], 429);
        }

        $key = 'email-otp-send:' . $user->id;

        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json(['message' => "Too many attempts. Try again in {$seconds} seconds."], 429);
        }

        RateLimiter::hit($key, 600);

        $user->update(['pending_email' => $data['new_email']]);

        PasswordOtpToken::where('user_id', $user->id)
            ->where('type', 'email')
            ->where('used', false)
            ->delete();

        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        PasswordOtpToken::create([
            'user_id'    => $user->id,
            'type'       => 'email',
            'token'      => Hash::make($otp),
            'expires_at' => now()->addMinutes(10),
        ]);

        Mail::to($data['new_email'])->send(new EmailChangeOtpMail($otp));

        return response()->json([
            'message' => 'A verification code has been sent to ' . $data['new_email'] . '.',
        ]);
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        $request->validate([
            'otp' => 'required|string|size:6',
        ]);

        $user = $request->user();

        $key = 'email-otp-verify:' . $user->id;

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json(['message' => "Too many attempts. Try again in {$seconds} seconds."], 429);
        }

        $record = PasswordOtpToken::where('user_id', $user->id)
            ->where('type', 'email')
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (!$record || !Hash::check($request->otp, $record->token)) {
            RateLimiter::hit($key, 600);
            return response()->json(['message' => 'Invalid or expired OTP.'], 422);
        }

        $record->update(['used' => true]);

        $oldEmail = $user->email;
        $newEmail = $user->pending_email;

        if (!$newEmail) {
            return response()->json(['message' => 'No pending email change found.'], 422);
        }

        $user->update([
            'email'             => $newEmail,
            'pending_email'     => null,
            'email_verified_at' => now(),
            'email_updated_at'  => now(),
        ]);

        Mail::to($oldEmail)->send(new EmailChangedNotification($oldEmail, $newEmail));

        RateLimiter::clear($key);

        return response()->json([
            'message' => 'Email changed successfully.',
        ]);
    }
}
