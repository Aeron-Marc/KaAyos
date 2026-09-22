<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\TuyBarangays;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CompleteProfileController extends Controller
{
    public function show(): View
    {
        $user = auth()->user();

        if ($user->role !== 'worker' || $user->workerProfile) {
            return redirect()->route('client.dashboard');
        }

        return view('auth.complete-profile', [
            'barangays'  => TuyBarangays::allBarangays(),
            'user'       => $user,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();

        if ($user->role !== 'worker' || $user->workerProfile) {
            return redirect()->route('client.dashboard');
        }

        $validated = $request->validate([
            'service_category' => ['required', 'string'],
            'barangay'         => ['required', Rule::in(TuyBarangays::allBarangays())],
            'phone'            => ['nullable', 'string', 'max:20', 'regex:/^(?:\+63|0)[0-9]{10}$/'],
        ]);

        $user->update([
            'service_category' => $validated['service_category'],
            'barangay'         => $validated['barangay'],
            'city'             => 'Tuy',
            'city_municipality'=> 'Tuy',
            'phone'            => $validated['phone'] ?? $user->phone,
        ]);

        return redirect()->route('worker.dashboard')
            ->with('success', 'Profile complete! Welcome to KaAyos. Start browsing available jobs.');
    }
}
