<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WorkerProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    protected array $supportedProviders = ['google', 'facebook'];

    public function redirectToProvider(Request $request, string $provider): RedirectResponse
    {
        if (!in_array($provider, $this->supportedProviders)) {
            return redirect()->route('login')->withErrors(['oauth' => 'Unsupported login provider.']);
        }

        if ($request->has('role') && in_array($request->query('role'), ['client', 'worker'])) {
            session(['oauth_intended_role' => $request->query('role')]);
        }

        $clientId = config("services.{$provider}.client_id");
        $clientSecret = config("services.{$provider}.client_secret");

        if (empty($clientId) || empty($clientSecret)) {
            return redirect()->route('login')->withErrors([
                'oauth' => ucfirst($provider) . ' login is not configured yet. Please configure ' . strtoupper($provider) . '_CLIENT_ID in your .env or sign in with email.',
            ]);
        }

        $redirectUrl = config("services.{$provider}.redirect") ?: url("/auth/{$provider}/callback");

        try {
            return Socialite::driver($provider)->stateless()->redirectUrl($redirectUrl)->redirect();
        } catch (\Throwable $e) {
            Log::warning("OAuth {$provider} redirect failed: " . $e->getMessage());
            return redirect()->route('login')->withErrors([
                'oauth' => "Unable to connect to {$provider} login. Please ensure 'composer install' has been run on your machine.",
            ]);
        }
    }

    public function handleProviderCallback(Request $request, string $provider): RedirectResponse
    {
        if (!in_array($provider, $this->supportedProviders)) {
            return redirect()->route('login')->withErrors(['oauth' => 'Unsupported login provider.']);
        }

        $redirectUrl = config("services.{$provider}.redirect") ?: url("/auth/{$provider}/callback");

        try {
            $socialUser = Socialite::driver($provider)->stateless()->redirectUrl($redirectUrl)->user();
        } catch (\Throwable $e) {
            Log::warning("OAuth {$provider} failed: " . get_class($e) . ' - ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('login')->withErrors([
                'oauth' => "Unable to authenticate with {$provider}. Please try again or log in with email.",
            ]);
        }

        $email = $socialUser->getEmail();
        if (empty($email)) {
            $email = "{$provider}_{$socialUser->getId()}@kaayos.app";
        }

        // 1. Check if user already linked with this OAuth provider
        $user = User::where('oauth_provider', $provider)
            ->where('oauth_id', $socialUser->getId())
            ->orWhere(function ($q) use ($provider, $socialUser) {
                $q->where('provider', $provider)->where('provider_id', $socialUser->getId());
            })
            ->first();

        // 2. If not found by OAuth ID, check by email
        if (!$user) {
            $user = User::where('email', $email)->first();

            if ($user) {
                $user->update([
                    'oauth_provider'    => $provider,
                    'oauth_id'          => $socialUser->getId(),
                    'oauth_avatar'      => $socialUser->getAvatar(),
                    'provider'          => $provider,
                    'provider_id'       => $socialUser->getId(),
                    'email_verified_at' => $user->email_verified_at ?? now(),
                ]);
            }
        }

        // 3. If user still does not exist, create a new account
        if (!$user) {
            $fullName = trim($socialUser->getName() ?? 'KaAyos User');
            $parts = explode(' ', $fullName, 2);
            $firstName = $parts[0] ?? 'KaAyos';
            $lastName = $parts[1] ?? 'User';

            $role = session()->pull('oauth_intended_role', session('social_role', 'client'));
            if (!in_array($role, ['client', 'worker'])) {
                $role = 'client';
            }

            $user = User::create([
                'name'              => $fullName,
                'first_name'        => $firstName,
                'last_name'         => $lastName,
                'email'             => $email,
                'password'          => Hash::make(Str::random(32)),
                'role'              => $role,
                'client_type'       => 'homeowner',
                'oauth_provider'    => $provider,
                'oauth_id'          => $socialUser->getId(),
                'oauth_avatar'      => $socialUser->getAvatar(),
                'provider'          => $provider,
                'provider_id'       => $socialUser->getId(),
                'avatar'            => $socialUser->getAvatar(),
                'email_verified_at' => now(),
                'city'              => 'Tuy',
                'province'          => 'Batangas',
            ]);

            if ($role === 'worker') {
                WorkerProfile::create([
                    'user_id'         => $user->id,
                    'hourly_rate'     => 350.00,
                    'service_radius'  => 15,
                    'service_radius_km' => 15,
                    'average_rating'  => 5.00,
                    'skills'          => [],
                    'tools_equipped'  => [],
                ]);
            }
        }

        if ($user->suspended_at) {
            return redirect()->route('login')->withErrors([
                'email' => 'Your account has been suspended. Reason: ' . ($user->suspended_reason ?? 'Administrative action.'),
            ]);
        }

        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        if ($user->isAdmin()) {
            return redirect()->intended(route('admin.dashboard'));
        }

        if ($user->isWorker()) {
            return redirect()->intended(route('worker.dashboard'));
        }

        return redirect()->intended(route('client.dashboard'));
    }
}

