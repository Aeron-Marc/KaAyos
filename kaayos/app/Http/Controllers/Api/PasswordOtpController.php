<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\PasswordOtpMail;
use App\Models\PasswordOtpToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

class PasswordOtpController extends Controller
{
    public function send(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|string',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect.',
            ], 422);
        }

        $key = 'otp-send:' . $user->id;

        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'message' => "Too many attempts. Please try again in {$seconds} seconds.",
            ], 429);
        }

        RateLimiter::hit($key, 600);

        // Invalidate all previous unused tokens for this user
        PasswordOtpToken::where('user_id', $user->id)
            ->where('used', false)
            ->delete();

        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        PasswordOtpToken::create([
            'user_id'    => $user->id,
            'token'      => Hash::make($otp),
            'expires_at' => now()->addMinutes(10),
        ]);

        Mail::to($user->email)->send(new PasswordOtpMail($otp));

        return response()->json([
            'message' => 'OTP sent to your email.',
        ]);
    }

    public function verify(Request $request): JsonResponse
    {
        $request->validate([
            'otp'                      => 'required|string|size:6',
            'current_password'         => 'required|string',
            'new_password'             => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect.',
            ], 422);
        }

        $record = PasswordOtpToken::where('user_id', $user->id)
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (!$record || !Hash::check($request->otp, $record->token)) {
            return response()->json([
                'message' => 'Invalid or expired OTP.',
            ], 422);
        }

        $record->update(['used' => true]);

        $user->update(['password' => Hash::make($request->new_password)]);

        return response()->json([
            'message' => 'Password changed successfully.',
        ]);
    }
}