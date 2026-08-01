<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\VerificationOtpMail;
use App\Models\PasswordOtpToken;
use App\Models\User;
use App\Models\WorkerProfile;
use App\Services\UnverifiedAccountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    protected int $maxAttempts = 5;

    protected int $lockoutMinutes = 15;

    public function register(Request $request, UnverifiedAccountService $unverified): JsonResponse
    {
        $unverified->purgeExpired();

        $rules = [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name'  => ['required', 'string', 'max:100'],
            'email'      => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone'      => ['nullable', 'string', 'max:20', 'regex:/^(?:\+63|0)[0-9]{10}$/'],
            'role'       => ['required', 'in:client,worker'],
            'password'   => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            'terms'      => ['accepted'],
        ];

        if ($request->input('role') === 'worker') {
            $rules['service_category'] = ['required', 'string'];
            $rules['city']             = ['required', 'string', 'max:100'];
        }

        $validated = $request->validate($rules);

        try {
            $user = User::create([
                'first_name'       => $validated['first_name'],
                'last_name'        => $validated['last_name'],
                'name'             => $validated['first_name'] . ' ' . $validated['last_name'],
                'email'            => $validated['email'],
                'phone'            => $validated['phone'] ?? null,
                'password'         => Hash::make($validated['password']),
                'role'             => $validated['role'],
                'service_category' => $validated['service_category'] ?? null,
                'city'             => $validated['city'] ?? null,
            ]);
        } catch (\Throwable $e) {
            Log::error('API registration failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'We could not create your account due to a server error. Please try again.',
            ], 500);
        }

        if ($user->isWorker()) {
            WorkerProfile::create(['user_id' => $user->id]);
        }

        $otp = $this->issueEmailVerificationOtp($user);

        $debugOtp = null;

        try {
            Mail::to($user->email)->send(new VerificationOtpMail($otp, $user->first_name));
        } catch (\Throwable $e) {
            Log::warning('Verification OTP mail could not be sent: ' . $e->getMessage());
            $debugOtp = $otp;
        }

        return response()->json([
            'success'       => true,
            'message'       => 'Account created. A verification code has been sent to your email.',
            'email_verified' => false,
            'debug_otp'     => $debugOtp,
            'user'          => $this->userPayload($user),
        ], 201);
    }

    public function sendVerificationOtp(Request $request, UnverifiedAccountService $unverified): JsonResponse
    {
        $unverified->purgeExpired();

        $request->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || $user->email_verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'No unverified account found for this email.',
            ], 422);
        }

        $key = 'verification-otp-send:' . $user->id;

        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'success' => false,
                'message' => "Too many requests. Try again in {$seconds} seconds.",
            ], 429);
        }

        RateLimiter::hit($key, 600);

        $otp = $this->issueEmailVerificationOtp($user);

        $debugOtp = null;

        try {
            Mail::to($user->email)->send(new VerificationOtpMail($otp, $user->first_name));
        } catch (\Throwable $e) {
            Log::warning('Verification OTP mail could not be sent: ' . $e->getMessage());
            $debugOtp = $otp;
        }

        return response()->json([
            'success'   => true,
            'message'   => 'A verification code has been sent to your email.',
            'debug_otp' => $debugOtp,
        ]);
    }

    public function verifyEmailOtp(Request $request, UnverifiedAccountService $unverified): JsonResponse
    {
        $unverified->purgeExpired();

        $request->validate([
            'email' => ['required', 'string', 'email'],
            'otp'   => ['required', 'string', 'size:6'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired OTP.',
            ], 422);
        }

        $key = 'verification-otp-verify:' . $user->id;

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'success' => false,
                'message' => "Too many attempts. Try again in {$seconds} seconds.",
            ], 429);
        }

        $record = PasswordOtpToken::where('user_id', $user->id)
            ->where('type', 'verification')
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (!$record || !Hash::check($request->otp, $record->token)) {
            RateLimiter::hit($key, 600);
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired OTP.',
            ], 422);
        }

        $record->update(['used' => true]);

        $user->markEmailAsVerified();

        RateLimiter::clear($key);

        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Email verified successfully.',
            'token'   => $token,
            'user'    => $this->userPayload($user),
        ]);
    }

    public function login(Request $request, UnverifiedAccountService $unverified): JsonResponse
    {
        $unverified->purgeExpired();

        $credentials = $request->validate([
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if ($user && $user->locked_until && now()->lessThan($user->locked_until)) {
            $minutes = now()->diffInMinutes($user->locked_until) + 1;
            return response()->json([
                'success' => false,
                'message' => "Account temporarily locked. Too many failed login attempts. Try again in {$minutes} minute(s).",
            ], 423);
        }

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            if ($user) {
                $this->incrementAttempts($user);
            }
            return response()->json([
                'success' => false,
                'message' => 'The provided email or password is incorrect.',
            ], 422);
        }

        if (!$user->email_verified_at) {
            return response()->json([
                'success'       => false,
                'message'       => 'Please verify your email first. A verification code was sent to your email.',
                'email_verified' => false,
            ], 403);
        }

        if (!$user->isActive()) {
            return response()->json([
                'success' => false,
                'message' => $user->suspended_reason ?? 'This account has been suspended. Contact support for assistance.',
            ], 403);
        }

        $user->update([
            'failed_login_attempts' => 0,
            'locked_until'         => null,
        ]);

        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'success' => true,
            'token'   => $token,
            'user'    => $this->userPayload($user),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['success' => true]);
    }

    public function user(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'user'    => $this->userPayload($request->user()),
        ]);
    }

    protected function userPayload(User $user): array
    {
        return [
            'id'                 => $user->id,
            'first_name'         => $user->first_name,
            'last_name'          => $user->last_name,
            'name'               => $user->name,
            'email'              => $user->email,
            'phone'              => $user->phone,
            'role'               => $user->role,
            'service_category'   => $user->service_category,
            'city'               => $user->city,
            'language'           => $user->language,
            'email_notifications'=> $user->email_notifications,
            'avatar_url'         => $user->avatar ? Storage::url($user->avatar) : null,
            'email_verified'     => $user->email_verified_at !== null,
            'suspended'          => !$user->isActive(),
        ];
    }

    protected function incrementAttempts(User $user): void
    {
        $attempts = $user->failed_login_attempts + 1;
        $updates  = ['failed_login_attempts' => $attempts];

        if ($attempts >= $this->maxAttempts) {
            $updates['locked_until'] = now()->addMinutes($this->lockoutMinutes);
        }

        $user->update($updates);
    }

    protected function issueEmailVerificationOtp(User $user): string
    {
        PasswordOtpToken::where('user_id', $user->id)
            ->where('type', 'verification')
            ->where('used', false)
            ->delete();

        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        PasswordOtpToken::create([
            'user_id'    => $user->id,
            'type'       => 'verification',
            'token'      => Hash::make($otp),
            'expires_at' => now()->addMinutes(10),
        ]);

        return $otp;
    }
}
