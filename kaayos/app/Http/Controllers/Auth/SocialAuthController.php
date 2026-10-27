<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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
            // Stateful OAuth flow restores session-based state validation.
            return Socialite::driver($provider)->redirectUrl($redirectUrl)->redirect();
        } catch (\Throwable $e) {
            Log::warning("OAuth {$provider} redirect failed: " . $e->getMessage());

            return redirect()->route('login')->withErrors([
                'oauth' => "Unable to connect to {$provider} login. Please try again later.",
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
            // Stateful flow validates the OAuth state parameter automatically.
            $socialUser = Socialite::driver($provider)->redirectUrl($redirectUrl)->user();
        } catch (\Throwable $e) {
            Log::error("OAuth {$provider} failed: " . get_class($e) . ' - ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('login')->withErrors([
                'oauth' => "Unable to authenticate with {$provider}. Please try again or log in with email.",
            ]);
        }

        // Diagnostic inspection of Google's email_verified claim (set as verified_email by Socialite).
        // We do not auto-verify accounts; this is logged for observability only.
        $emailVerifiedClaim = $socialUser->verified_email ?? null;

        Log::debug("OAuth {$provider} callback received", [
            'email_verified_claim' => $emailVerifiedClaim,
            'provider_id' => $socialUser->getId(),
            'has_email' => !empty($socialUser->getEmail()),
        ]);

        // Only authenticate accounts explicitly linked to this Google identity.
        // Do NOT fall back to matching by email (unsafe automatic linking).
        $user = User::where('oauth_provider', $provider)
            ->where('oauth_id', $socialUser->getId())
            ->first();

        if (!$user) {
            // Unregistered Google user or an existing email account without a Google link.
            // Do not create a user record, do not link, do not log in, do not mark verified.
            session()->forget('oauth_intended_role');

            return redirect()->route('register')->with([
                'oauth' => 'This Google account is not linked to a KaAyos account. Please register first, then link your Google account.',
            ]);
        }

        if ($user->suspended_at) {
            return redirect()->route('login')->withErrors([
                'email' => 'Your account has been suspended. Reason: ' . ($user->suspended_reason ?? 'Administrative action.'),
            ]);
        }

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        session()->forget('oauth_intended_role');

        if ($user->isAdmin()) {
            return redirect()->intended(route('admin.dashboard'));
        }

        if ($user->isWorker()) {
            return redirect()->intended(route('worker.dashboard'));
        }

        return redirect()->intended(route('client.dashboard'));
    }
}
