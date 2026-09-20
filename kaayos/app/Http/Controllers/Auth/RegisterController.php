<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ServiceCategory;
use App\Models\User;
use App\Models\WorkerProfile;
use App\Support\TuyBarangays;
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
        return view('auth.register', [
            'barangays'  => TuyBarangays::allBarangays(),
            'categories' => ServiceCategory::active()->orderBy('name')->get(),
        ]);
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
            $rules['service_category'] = ['required', 'string', Rule::in(ServiceCategory::pluck('name'))];
            $rules['barangay']         = ['required', Rule::in(TuyBarangays::allBarangays())];
        }

        $validated = $request->validate($rules);

        $role = $request->input('intended') ? 'client' : $validated['role'];

        try {
            $user = User::create([
                'first_name'       => $validated['first_name'],
                'last_name'        => $validated['last_name'],
                'name'             => $validated['first_name'] . ' ' . $validated['last_name'],
                'email'            => $validated['email'],
                'phone'            => $validated['phone'] ?? null,
                'password'         => Hash::make($validated['password']),
                'role'             => $role,
                'service_category' => $validated['service_category'] ?? null,
                'barangay'         => $validated['barangay'] ?? null,
                'city'             => $role === 'worker' ? 'Tuy' : null,
                'city_municipality'=> $role === 'worker' ? 'Tuy' : null,
            ]);

            if ($role === 'worker') {
                [$lat, $lng] = TuyBarangays::pointFor($user->barangay, $user->id);
                $defaultAvailability = [
                    ['day' => 'Monday',    'active' => true,  'start' => '08:00', 'end' => '17:00'],
                    ['day' => 'Tuesday',   'active' => true,  'start' => '08:00', 'end' => '17:00'],
                    ['day' => 'Wednesday', 'active' => true,  'start' => '08:00', 'end' => '17:00'],
                    ['day' => 'Thursday',  'active' => true,  'start' => '08:00', 'end' => '17:00'],
                    ['day' => 'Friday',    'active' => true,  'start' => '08:00', 'end' => '17:00'],
                    ['day' => 'Saturday',  'active' => false, 'start' => null,    'end' => null],
                    ['day' => 'Sunday',    'active' => false, 'start' => null,    'end' => null],
                ];

                WorkerProfile::create([
                    'user_id'                 => $user->id,
                    'availability'            => $defaultAvailability,
                    'service_zone'            => ['barangay' => $user->barangay],
                    'current_latitude'        => $lat,
                    'current_longitude'       => $lng,
                    'location_is_approximate' => true,
                    'government_id_verified'  => false,
                ]);
            }
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