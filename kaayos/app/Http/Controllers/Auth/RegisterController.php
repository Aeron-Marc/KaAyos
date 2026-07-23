<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $rules = [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name'  => ['required', 'string', 'max:100'],
            'email'      => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone'      => ['nullable', 'string', 'max:20', 'regex:/^(?:\+63|0)[0-9]{10}$/'],
            'role'       => ['required', 'in:client,worker'],
            'password'   => ['required', 'confirmed', Password::min(8)
                                ->letters()
                                ->numbers()],
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
            Log::error('Registration failed for email: ' . $validated['email'] . ' — ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'We could not create your account due to a server error. Please try again or contact support.');
        }

        Log::info('New account created', ['user_id' => $user->id, 'email' => $user->email, 'role' => $user->role]);

        if (config('mail.mailers.smtp.username')) {
            event(new Registered($user));

            $loginUrl = route('login');

            if ($intended = $request->input('intended')) {
                $loginUrl .= '?intended=' . urlencode($intended);
            }

            return redirect($loginUrl)
                ->with('status', 'Account created! We sent a verification email to ' . $user->email . '. Please check your inbox (and spam folder) before logging in.')
                ->with('registered_email', $user->email);
        }

        $user->markEmailAsVerified();
        auth()->login($user);

        if ($intended = $request->input('intended')) {
            session()->put('url.intended', $intended);
        }

        $dashboard = match ($user->role) {
            'worker' => route('worker.dashboard'),
            default  => route('client.dashboard'),
        };

        return redirect()->intended($dashboard)
            ->with('success', 'Registration successful! Welcome to KaAyos.');
    }
}