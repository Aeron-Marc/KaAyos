<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WorkerProfile;
use App\Support\TuyBarangays;
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

    // Phase 1: Login-only OAuth (existing linked accounts only)

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
            $socialUser = Socialite::driver($provider)->redirectUrl($redirectUrl)->user();
        } catch (\Throwable $e) {
            Log::error("OAuth {$provider} failed: " . get_class($e) . ' - ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('login')->withErrors([
                'oauth' => "Unable to authenticate with {$provider}. Please try again or log in with email.",
            ]);
        }

        Log::debug("OAuth {$provider} callback received", [
            'email_verified_claim' => $socialUser->verified_email ?? null,
            'provider_id' => $socialUser->getId(),
            'has_email' => !empty($socialUser->getEmail()),
        ]);

        // Only authenticate accounts explicitly linked to this Google identity.
        $user = User::where('oauth_provider', $provider)
            ->where('oauth_id', $socialUser->getId())
            ->first();

        if (!$user) {
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

    // Phase 2: Secure Google registration flow

    /**
     * Redirect to Google OAuth for registration.
     * Separate from login flow - only creates new accounts, never logs in existing ones.
     */
    public function redirectToGoogleRegister(Request $request): RedirectResponse
    {
        $provider = 'google';

        $clientId = config("services.{$provider}.client_id");
        $clientSecret = config("services.{$provider}.client_secret");

        if (empty($clientId) || empty($clientSecret)) {
            return redirect()->route('register')->withErrors([
                'oauth' => 'Google registration is not configured yet. Please sign up with email.',
            ]);
        }

        // Mark this as a registration flow so the callback knows not to auto-login.
        session(['oauth_registration_flow' => true]);

        if ($request->has('role') && in_array($request->query('role'), ['client', 'worker'])) {
            session(['oauth_intended_role' => $request->query('role')]);
        }

        $redirectUrl = config("services.{$provider}.redirect") ?: url("/auth/google/register/callback");

        try {
            return Socialite::driver($provider)->redirectUrl($redirectUrl)->redirect();
        } catch (\Throwable $e) {
            Log::warning("OAuth {$provider} registration redirect failed: " . $e->getMessage());

            session()->forget('oauth_registration_flow');
            session()->forget('oauth_intended_role');

            return redirect()->route('register')->withErrors([
                'oauth' => "Unable to connect to Google registration. Please try again later.",
            ]);
        }
    }

    /**
     * Handle Google OAuth callback for registration.
     * Validates the provider response, checks for duplicates, stores validated data in session.
     */
    public function handleGoogleRegisterCallback(Request $request): RedirectResponse
    {
        $provider = 'google';
        $redirectUrl = config("services.{$provider}.redirect") ?: url("/auth/google/register/callback");

        try {
            $socialUser = Socialite::driver($provider)->redirectUrl($redirectUrl)->user();
        } catch (\Throwable $e) {
            Log::error("OAuth {$provider} registration callback failed: " . get_class($e) . ' - ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            session()->forget('oauth_registration_flow');
            session()->forget('oauth_intended_role');

            return redirect()->route('register')->withErrors([
                'oauth' => "Unable to authenticate with Google. Please try again or sign up with email.",
            ]);
        }

        // Require Google to have verified the email before trusting it as proof of ownership.
        $emailVerifiedClaim = $socialUser->verified_email ?? null;
        if (!$emailVerifiedClaim) {
            Log::warning("OAuth {$provider} registration rejected: unverified email", [
                'provider_id' => $socialUser->getId(),
                'email_verified_claim' => $emailVerifiedClaim,
            ]);

            session()->forget('oauth_registration_flow');
            session()->forget('oauth_intended_role');

            return redirect()->route('register')->withErrors([
                'oauth' => 'Google email is not verified. Please sign up with email instead.',
            ]);
        }

        $email = $socialUser->getEmail();
        if (empty($email)) {
            session()->forget('oauth_registration_flow');
            session()->forget('oauth_intended_role');

            return redirect()->route('register')->withErrors([
                'oauth' => 'Google account does not have an email address. Please sign up with email instead.',
            ]);
        }

        // Do NOT create duplicate accounts for already-linked identities.
        $linkedUser = User::where('oauth_provider', $provider)
            ->where('oauth_id', $socialUser->getId())
            ->first();

        if ($linkedUser) {
            session()->forget('oauth_registration_flow');
            session()->forget('oauth_intended_role');

            return redirect()->route('login')->with([
                'oauth' => 'This Google account is already linked to a KaAyos account. Please sign in with Google instead.',
            ]);
        }

        // Do NOT auto-link an existing email account without explicit linking.
        $existingEmailUser = User::where('email', $email)->first();
        if ($existingEmailUser) {
            session()->forget('oauth_registration_flow');
            session()->forget('oauth_intended_role');

            return redirect()->route('login')->withErrors([
                'oauth' => 'An account with this email already exists. Please sign in and link your Google account through account settings instead.',
            ]);
        }

        // Store validated Google identity in session for the registration form.
        // Never store access tokens. Never expose provider data in URLs.
        session([
            'oauth_registration' => [
                'provider'      => $provider,
                'oauth_id'      => $socialUser->getId(),
                'email'         => $email,
                'name'          => $socialUser->getName() ?? null,
                'first_name'    => explode(' ', trim($socialUser->getName() ?? ''), 2)[0] ?? null,
                'last_name'     => explode(' ', trim($socialUser->getName() ?? ''), 2)[1] ?? null,
                'avatar'        => $socialUser->getAvatar() ?? null,
                'email_verified'=> true,
                'expires_at'    => now()->addMinutes(15)->timestamp,
            ],
            'oauth_intended_role' => session('oauth_intended_role', null),
        ]);

        return redirect()->route('auth.google.register.form');
    }

    /**
     * Show the Google pre-filled registration form.
     * Requires a valid, unexpired oauth_registration session payload.
     */
    public function showGoogleRegistrationForm(Request $request)
    {
        $registration = session('oauth_registration');

        if (!$registration || empty($registration['expires_at']) || now()->timestamp > $registration['expires_at']) {
            session()->forget('oauth_registration');
            session()->forget('oauth_registration_flow');
            session()->forget('oauth_intended_role');

            return redirect()->route('register')->withErrors([
                'oauth' => 'Google registration session expired. Please try again.',
            ]);
        }

        if (!session('oauth_registration_flow')) {
            session()->forget('oauth_registration');
            session()->forget('oauth_registration_flow');
            session()->forget('oauth_intended_role');

            return redirect()->route('register');
        }

        return view('auth.register', [
            'barangays' => TuyBarangays::allBarangays(),
            'google_registration' => $registration,
            'google_role' => session('oauth_intended_role'),
        ]);
    }

    /**
     * Complete Google registration after user fills out the form.
     * All data validated server-side; role cannot be set to admin.
     */
    public function completeGoogleRegistration(Request $request): RedirectResponse
    {
        $registration = session('oauth_registration');

        if (!$registration || empty($registration['expires_at']) || now()->timestamp > $registration['expires_at']) {
            session()->forget('oauth_registration');
            session()->forget('oauth_registration_flow');
            session()->forget('oauth_intended_role');

            return redirect()->route('register')->withErrors([
                'oauth' => 'Google registration session expired. Please try again.',
            ]);
        }

        if (!session('oauth_registration_flow')) {
            session()->forget('oauth_registration');
            session()->forget('oauth_registration_flow');
            session()->forget('oauth_intended_role');

            return redirect()->route('register');
        }

        // Role must be explicitly selected and validated; admin is never allowed via Google registration.
        $role = $request->input('role');
        if (!in_array($role, ['client', 'worker'])) {
            Log::warning('Google registration: invalid role attempted', [
                'attempted_role' => $role,
                'email' => $registration['email'],
            ]);

            return back()->withErrors(['role' => 'Please select a valid account type.']);
        }

        $rules = [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name'  => ['required', 'string', 'max:100'],
            'email'      => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone'      => ['nullable', 'string', 'max:20', 'regex:/^(?:\+63|0)[0-9]{10}$/'],
            'role'       => ['required', 'in:client,worker'],
            'terms'      => ['accepted'],
            'client_type' => ['nullable', 'string', 'in:homeowner,tenant_renter,business_commercial,property_manager,other'],
            'organization_name' => ['nullable', 'string', 'max:255'],
            'tin_number' => ['nullable', 'string', 'max:50'],
        ];

        if ($role === 'worker') {
            $rules['service_category'] = ['required', 'string'];
            $rules['barangay']         = ['required', 'string'];
        }

        $validated = $request->validate($rules);

        // Double-check no duplicate account was created between OAuth callback and now.
        $duplicate = User::where('email', $validated['email'])->first();
        if ($duplicate) {
            session()->forget('oauth_registration');
            session()->forget('oauth_registration_flow');
            session()->forget('oauth_intended_role');

            return redirect()->route('login')->withErrors([
                'oauth' => 'An account with this email already exists. Please sign in instead.',
            ]);
        }

        $linkedDuplicate = User::where('oauth_provider', 'google')
            ->where('oauth_id', $registration['oauth_id'])
            ->first();

        if ($linkedDuplicate) {
            session()->forget('oauth_registration');
            session()->forget('oauth_registration_flow');
            session()->forget('oauth_intended_role');

            return redirect()->route('login')->with([
                'oauth' => 'This Google account is already linked to a KaAyos account. Please sign in with Google instead.',
            ]);
        }

        try {
            $user = User::create([
                'first_name'       => $validated['first_name'],
                'last_name'        => $validated['last_name'],
                'name'             => $validated['first_name'] . ' ' . $validated['last_name'],
                'email'            => $validated['email'],
                'phone'            => $validated['phone'] ?? null,
                'password'         => Hash::make(Str::random(32)), // No password login; random hash stored
                'role'             => $validated['role'],
                'client_type'      => $validated['client_type'] ?? 'homeowner',
                'organization_name'=> $validated['organization_name'] ?? null,
                'tin_number'       => $validated['tin_number'] ?? null,
                'service_category' => $validated['service_category'] ?? null,
                'barangay'         => $validated['barangay'] ?? null,
                'city'             => $validated['role'] === 'worker' ? 'Tuy' : null,
                'city_municipality'=> $validated['role'] === 'worker' ? 'Tuy' : null,
                'oauth_provider'   => 'google',
                'oauth_id'         => $registration['oauth_id'],
                'oauth_avatar'     => $registration['avatar'] ?? null,
                'provider'         => 'google',
                'provider_id'      => $registration['oauth_id'],
                'avatar'           => $registration['avatar'] ?? null,
                'email_verified_at'=> $registration['email_verified'] ? now() : null,
            ]);
        } catch (\Throwable $e) {
            Log::error('Google registration failed for email: ' . $validated['email'] . ' — ' . $e->getMessage());

            session()->forget('oauth_registration');
            session()->forget('oauth_registration_flow');
            session()->forget('oauth_intended_role');

            return back()->withInput()
                ->with('error', 'We could not create your account due to a server error. Please try again or contact support.');
        }

        if ($validated['role'] === 'worker') {
            WorkerProfile::create([
                'user_id'           => $user->id,
                'hourly_rate'       => 350.00,
                'service_radius'    => 15,
                'service_radius_km' => 15,
                'average_rating'    => 5.00,
                'skills'            => [],
                'tools_equipped'    => [],
            ]);
        }

        Log::info('Google account registered', ['user_id' => $user->id, 'email' => $user->email, 'role' => $user->role]);

        // Mark email verified only when Google has verified it.
        if ($registration['email_verified'] && !$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        session()->forget('oauth_registration');
        session()->forget('oauth_registration_flow');
        session()->forget('oauth_intended_role');

        if ($user->isWorker()) {
            return redirect(route('worker.dashboard'))
                ->with('success', 'Welcome to KaAyos! Your Google account is registered.');
        }

        return redirect(route('client.dashboard'))
            ->with('success', 'Welcome to KaAyos! Your Google account is registered.');
    }
}
