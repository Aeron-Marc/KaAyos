<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialController extends Controller
{
    protected array $providers = ['google', 'facebook'];

    public function redirect(Request $request, string $provider): RedirectResponse
    {
        abort_unless(in_array($provider, $this->providers), 404);

        $role = $request->query('role');
        if ($role && in_array($role, ['client', 'worker'])) {
            session(['social_role' => $role]);
        }

        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider): RedirectResponse
    {
        abort_unless(in_array($provider, $this->providers), 404);

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            return redirect()->route('login')
                ->with('error', 'Unable to authenticate with ' . ucfirst($provider) . '. Please try again.');
        }

        $providerId = $socialUser->getId();
        $email = $socialUser->getEmail();
        $name = $socialUser->getName() ?? $socialUser->getNickname() ?? '';
        $avatar = $socialUser->getAvatar();

        if (!$email) {
            return redirect()->route('login')
                ->with('error', 'No email found from ' . ucfirst($provider) . ' account. Please use a different login method.');
        }

        $role = session('social_role', 'client');

        $user = User::where('provider', $provider)
            ->where('provider_id', $providerId)
            ->first();

        if (!$user) {
            $user = User::where('email', $email)->first();

            if ($user) {
                $user->update([
                    'provider'     => $provider,
                    'provider_id'  => $providerId,
                ]);
            } else {
                $firstName = $this->extractFirstName($name);
                $lastName = $this->extractLastName($name);

                $user = User::create([
                    'first_name'       => $firstName,
                    'last_name'        => $lastName,
                    'name'             => $name,
                    'email'            => $email,
                    'password'         => Hash::make(Str::random(32)),
                    'role'             => $role,
                    'provider'         => $provider,
                    'provider_id'      => $providerId,
                    'avatar'           => $avatar,
                    'email_verified_at'=> now(),
                ]);
            }
        }

        Auth::login($user, true);

        session()->forget('social_role');

        if ($user->role === 'worker' && !$user->workerProfile) {
            return redirect()->route('worker.complete-profile')
                ->with('success', 'Welcome! Please complete your worker profile to get started.');
        }

        return match ($user->role) {
            'admin'  => redirect()->intended(route('admin.dashboard')),
            'worker' => redirect()->intended(route('worker.dashboard')),
            default  => redirect()->intended(route('client.dashboard')),
        };
    }

    protected function extractFirstName(string $name): string
    {
        $parts = explode(' ', trim($name));
        return $parts[0] ?? 'User';
    }

    protected function extractLastName(string $name): string
    {
        $parts = explode(' ', trim($name));
        return count($parts) > 1 ? end($parts) : '';
    }
}
