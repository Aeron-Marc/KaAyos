<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    protected int $maxAttempts = 5;

    protected int $lockoutMinutes = 15;

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $request->input('email'))->first();

        if ($user && $this->isLocked($user)) {
            $minutes = now()->diffInMinutes($user->locked_until) + 1;

            return response()->json([
                'message' => "Account temporarily locked. Too many failed login attempts. Try again in {$minutes} minute(s).",
            ], 423);
        }

        if (!$user || !Hash::check($request->input('password'), $user->password)) {
            if ($user) {
                $this->incrementAttempts($user);
            }

            return response()->json([
                'message' => 'The provided email or password is incorrect.',
            ], 401);
        }

        if (!$user->isActive()) {
            return response()->json([
                'message' => 'Your account has been suspended.',
            ], 403);
        }

        $user->update([
            'failed_login_attempts' => 0,
            'locked_until'          => null,
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'token'   => $token,
            'user'    => $this->userPayload($user),
        ]);
    }

    public function register(Request $request): JsonResponse
    {
        $rules = [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name'  => ['required', 'string', 'max:100'],
            'email'      => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone'      => ['nullable', 'string', 'max:20', 'regex:/^(?:\+63|0)[0-9]{10}$/'],
            'role'       => ['sometimes', 'required', 'in:client,worker'],
            'password'   => ['required', 'string', Password::min(8)->letters()->numbers()],
        ];

        if ($request->input('role') === 'worker') {
            $rules['service_category'] = ['required', 'string'];
            $rules['city']             = ['required', 'string', 'max:100'];
        }

        $validated = $request->validate($rules);

        $user = User::create([
            'first_name'       => $validated['first_name'],
            'last_name'        => $validated['last_name'],
            'name'             => $validated['first_name'] . ' ' . $validated['last_name'],
            'email'            => $validated['email'],
            'phone'            => $validated['phone'] ?? null,
            'password'         => Hash::make($validated['password']),
            'role'             => $validated['role'] ?? 'client',
            'service_category' => $validated['service_category'] ?? null,
            'city'             => $validated['city'] ?? null,
        ]);

        event(new Registered($user));

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Account created successfully.',
            'token'   => $token,
            'user'    => $this->userPayload($user),
        ], 201);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }

    protected function isLocked(User $user): bool
    {
        return $user->locked_until && now()->lessThan($user->locked_until);
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

    protected function userPayload(User $user): array
    {
        return [
            'id'              => $user->id,
            'first_name'      => $user->first_name,
            'last_name'       => $user->last_name,
            'name'            => $user->name,
            'email'           => $user->email,
            'phone'           => $user->phone,
            'role'            => $user->role,
            'service_category'=> $user->service_category,
            'city'            => $user->city,
            'avatar'          => $user->avatar,
            'email_verified'  => !is_null($user->email_verified_at),
        ];
    }
}
