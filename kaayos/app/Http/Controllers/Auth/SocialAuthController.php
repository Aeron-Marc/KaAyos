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
            'email_verified_claim' => $this->getVerifiedEmailClaim($socialUser),
            'provider_id' => $socialUser->getId(),
            'has_email' => !empty($socialUser->getEmail()),
            'raw_email_verified' => $socialUser->getRaw()['email_verified'] ?? null,
        ]);

        // Only authenticate accounts explicitly linked to this Google identity.
        $user = User::where('oauth_provider', $provider)
            ->where('oauth_id', $socialUser->getId())
            ->first();

        if (!$user) {
            // Unregistered user: redirect into the secure registration flow
            // with the validated identity prefilled. Do NOT auto-create,
            // do NOT auto-link by email, do NOT log in.
            $email = $socialUser->getEmail();
            $emailVerifiedClaim = $this->getVerifiedEmailClaim($socialUser, $provider);

            // Google requires a verified-email claim; Facebook does not provide one reliably,
            // so Facebook users may still enter the registration flow (email stays unverified).
            if (empty($email) || ($provider === 'google' && !$emailVerifiedClaim)) {
                session()->forget('oauth_intended_role');

                return redirect()->route('register')->withErrors([
                    'oauth' => 'Google account is not verified. Please sign up with email instead.',
                ]);
            }

            if (empty($email)) {
                session()->forget('oauth_intended_role');

                return redirect()->route('register')->withErrors([
                    'oauth' => ucfirst($provider) . ' account does not have an email address. Please sign up with email instead.',
                ]);
            }

            // Do NOT auto-link an existing email account without explicit linking.
            $existingEmailUser = User::where('email', $email)->first();
            if ($existingEmailUser) {
                session()->forget('oauth_intended_role');

                return redirect()->route('login')->withErrors([
                    'oauth' => 'An account with this email already exists. Please sign in instead.',
                ]);
            }

            // Store validated identity for the registration form (reuses Phase 2 handoff).
            session([
                'oauth_registration' => [
                    'provider'      => $provider,
                    'oauth_id'      => $socialUser->getId(),
                    'email'         => $email,
                    'name'          => $socialUser->getName() ?? null,
                    'first_name'    => explode(' ', trim($socialUser->getName() ?? ''), 2)[0] ?? null,
                    'last_name'     => explode(' ', trim($socialUser->getName() ?? ''), 2)[1] ?? null,
                    'avatar'        => $socialUser->getAvatar() ?? null,
                    'email_verified'=> $emailVerifiedClaim,
                    'expires_at'    => now()->addMinutes(15)->timestamp,
                ],
                'oauth_registration_flow' => true,
                'oauth_intended_role' => session('oauth_intended_role', null),
            ]);

            return redirect()->route('auth.'.$provider.'.register.form');
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

    public function redirectToGoogleRegister(Request $request): RedirectResponse
    {
        return $this->redirectToRegister($request, 'google');
    }

    public function redirectToFacebookRegister(Request $request): RedirectResponse
    {
        return $this->redirectToRegister($request, 'facebook');
    }

    private function redirectToRegister(Request $request, string $provider): RedirectResponse
    {
        $clientId = config("services.{$provider}.client_id");
        $clientSecret = config("services.{$provider}.client_secret");

        if (empty($clientId) || empty($clientSecret)) {
            return redirect()->route('register')->withErrors([
                'oauth' => ucfirst($provider) . ' registration is not configured yet. Please sign up with email.',
            ]);
        }

        // Mark this as a registration flow so the callback knows not to auto-login.
        session(['oauth_registration_flow' => true]);

        if ($request->has('role') && in_array($request->query('role'), ['client', 'worker'])) {
            session(['oauth_intended_role' => $request->query('role')]);
        }

        $redirectUrl = config("services.{$provider}.redirect") ?: url("/auth/{$provider}/register/callback");

        try {
            return Socialite::driver($provider)->redirectUrl($redirectUrl)->redirect();
        } catch (\Throwable $e) {
            Log::warning("OAuth {$provider} registration redirect failed: " . $e->getMessage());

            session()->forget('oauth_registration_flow');
            session()->forget('oauth_intended_role');

            return redirect()->route('register')->withErrors([
                'oauth' => "Unable to connect to " . ucfirst($provider) . " registration. Please try again later.",
            ]);
        }
    }

    public function handleGoogleRegisterCallback(Request $request): RedirectResponse
    {
        return $this->handleRegisterCallback($request, 'google');
    }

    public function handleFacebookRegisterCallback(Request $request): RedirectResponse
    {
        return $this->handleRegisterCallback($request, 'facebook');
    }

    private function handleRegisterCallback(Request $request, string $provider): RedirectResponse
    {
        $redirectUrl = config("services.{$provider}.redirect") ?: url("/auth/{$provider}/register/callback");

        try {
            $socialUser = Socialite::driver($provider)->redirectUrl($redirectUrl)->user();
        } catch (\Throwable $e) {
            Log::error("OAuth {$provider} registration callback failed: " . get_class($e) . ' - ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            session()->forget('oauth_registration_flow');
            session()->forget('oauth_intended_role');

            return redirect()->route('register')->withErrors([
                'oauth' => "Unable to authenticate with " . ucfirst($provider) . ". Please try again later or sign up with email.",
            ]);
        }

        // Require Google to have verified the email before trusting it as proof of ownership.
        // Facebook does not provide a reliable verified-email claim, so Facebook emails are
        // treated as unverified but still allowed to register; they must verify via email flow.
        $emailVerifiedClaim = $this->getVerifiedEmailClaim($socialUser, $provider);
        if ($provider === 'google' && !$emailVerifiedClaim) {
            Log::warning("OAuth {$provider} registration rejected: unverified email", [
                'provider_id' => $socialUser->getId(),
                'email_verified_claim' => $emailVerifiedClaim,
                'raw_email_verified' => $socialUser->getRaw()['email_verified'] ?? null,
            ]);

            session()->forget('oauth_registration_flow');
            session()->forget('oauth_intended_role');

            return redirect()->route('register')->withErrors([
                'oauth' => 'Google email is not verified. Please sign up with email instead.',
            ]);
        }

        if ($provider === 'facebook' && !$emailVerifiedClaim) {
            Log::info("OAuth facebook registration: email not verified by provider, account will require email verification", [
                'provider_id' => $socialUser->getId(),
                'raw_email_verified' => $socialUser->getRaw()['email_verified'] ?? null,
            ]);
        }

        $email = $socialUser->getEmail();
        if (empty($email)) {
            session()->forget('oauth_registration_flow');
            session()->forget('oauth_intended_role');

            return redirect()->route('register')->withErrors([
                'oauth' => ucfirst($provider) . ' account does not have an email address. Please sign up with email instead.',
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
                'oauth' => 'This ' . ucfirst($provider) . ' account is already linked to a KaAyos account. Please sign in with ' . ucfirst($provider) . ' instead.',
            ]);
        }

        // Do NOT auto-link an existing email account without explicit linking.
        $existingEmailUser = User::where('email', $email)->first();
        if ($existingEmailUser) {
            session()->forget('oauth_registration_flow');
            session()->forget('oauth_intended_role');

            return redirect()->route('login')->withErrors([
                'oauth' => 'An account with this email already exists. Please sign in and link your ' . ucfirst($provider) . ' account through account settings instead.',
            ]);
        }

        // Store validated provider identity in session for the registration form.
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
                'email_verified'=> $emailVerifiedClaim,
                'expires_at'    => now()->addMinutes(15)->timestamp,
            ],
            'oauth_intended_role' => session('oauth_intended_role', null),
        ]);

        return redirect()->route('auth.'.$provider.'.register.form');
    }

    /**
     * Show the Google pre-filled registration form.
     * Requires a valid, unexpired oauth_registration session payload.
     */
    public function showGoogleRegistrationForm(Request $request)
    {
        return $this->showRegistrationForm($request, 'google');
    }

    public function showFacebookRegistrationForm(Request $request)
    {
        return $this->showRegistrationForm($request, 'facebook');
    }

    private function showRegistrationForm(Request $request, string $provider)
    {
        $registration = session('oauth_registration');

        if (!$registration || empty($registration['expires_at']) || now()->timestamp > $registration['expires_at']) {
            session()->forget('oauth_registration');
            session()->forget('oauth_registration_flow');
            session()->forget('oauth_intended_role');

            return redirect()->route('register')->withErrors([
                'oauth' => ucfirst($provider) . ' registration session expired. Please try again.',
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
            'oauth_registration' => $registration,
            'oauth_provider' => $provider,
            'oauth_role' => session('oauth_intended_role'),
            'google_registration' => $provider === 'google' ? $registration : null,
            'google_role' => session('oauth_intended_role'),
        ]);
    }

    /**
     * Complete Google registration after user fills out the form.
     * All data validated server-side; role cannot be set to admin.
     */
    public function completeGoogleRegistration(Request $request): RedirectResponse
    {
        return $this->completeRegistration($request, 'google');
    }

    public function completeFacebookRegistration(Request $request): RedirectResponse
    {
        return $this->completeRegistration($request, 'facebook');
    }

    /**
     * Complete OAuth registration after user fills out the form.
     * All data validated server-side; role cannot be set to admin.
     * Email is only marked verified when the provider reliably confirms it.
     */
    private function completeRegistration(Request $request, string $provider): RedirectResponse
    {
        $registration = session('oauth_registration');

        if (!$registration || empty($registration['expires_at']) || now()->timestamp > $registration['expires_at']) {
            session()->forget('oauth_registration');
            session()->forget('oauth_registration_flow');
            session()->forget('oauth_intended_role');

            return redirect()->route('register')->withErrors([
                'oauth' => ucfirst($provider) . ' registration session expired. Please try again.',
            ]);
        }

        if (!session('oauth_registration_flow')) {
            session()->forget('oauth_registration');
            session()->forget('oauth_registration_flow');
            session()->forget('oauth_intended_role');

            return redirect()->route('register');
        }

        // Role must be explicitly selected and validated; admin is never allowed via OAuth registration.
        $role = $request->input('role');
        if (!in_array($role, ['client', 'worker'])) {
            Log::warning('OAuth registration: invalid role attempted', [
                'attempted_role' => $role,
                'email' => $registration['email'],
                'provider' => $provider,
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

        // Never trust browser-submitted email: must match the server-validated identity.
        if ($validated['email'] !== $registration['email']) {
            Log::warning('OAuth registration: browser email tampering attempt', [
                'submitted_email' => $validated['email'],
                'provider_email' => $registration['email'],
                'provider' => $provider,
            ]);

            return back()->withInput()
                ->withErrors(['email' => 'Email does not match the verified ' . ucfirst($provider) . ' identity. Please use the prefilled email.']);
        }

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

        $linkedDuplicate = User::where('oauth_provider', $provider)
            ->where('oauth_id', $registration['oauth_id'])
            ->first();

        if ($linkedDuplicate) {
            session()->forget('oauth_registration');
            session()->forget('oauth_registration_flow');
            session()->forget('oauth_intended_role');

            return redirect()->route('login')->with([
                'oauth' => 'This ' . ucfirst($provider) . ' account is already linked to a KaAyos account. Please sign in with ' . ucfirst($provider) . ' instead.',
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
                'oauth_provider'   => $provider,
                'oauth_id'         => $registration['oauth_id'],
                'oauth_avatar'     => $registration['avatar'] ?? null,
                'provider'         => $provider,
                'provider_id'      => $registration['oauth_id'],
                'avatar'           => $registration['avatar'] ?? null,
                'email_verified_at'=> $registration['email_verified'] ? now() : null,
            ]);
        } catch (\Throwable $e) {
            Log::error('OAuth registration failed for email: ' . $validated['email'] . ' — ' . $e->getMessage());

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

        Log::info('OAuth account registered', ['user_id' => $user->id, 'email' => $user->email, 'role' => $user->role, 'provider' => $provider]);

        // Mark email verified only when the provider has verified it.
        // Facebook does not provide a reliable verified-email claim, so Facebook email remains unverified.
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
                ->with('success', 'Welcome to KaAyos! Your ' . ucfirst($provider) . ' account is registered.');
        }

        return redirect(route('client.dashboard'))
            ->with('success', 'Welcome to KaAyos! Your ' . ucfirst($provider) . ' account is registered.');
    }

    /**
     * Read the verified-email claim from the Socialite user object.
     *
     * Laravel Socialite's __get() only checks mapped attributes ($this->attributes),
     * but the Google provider stores verified_email in the raw array ($this->user)
     * without mapping it. offsetGet() reads from the raw array, so we use that.
     * We also check 'email_verified' directly as a fallback since GoogleProvider
     * sets both keys in the raw array during mapUserToObject().
     *
     * Facebook does not reliably expose a verified-email claim, so Facebook always
     * returns false. Never treats a missing claim as verified.
     */
    private function getVerifiedEmailClaim($socialUser, string $provider = 'google'): bool
    {
        if ($provider === 'facebook') {
            return false;
        }

        $raw = $socialUser->getRaw();

        // Check raw array first (safe, no exception on missing key)
        if (isset($raw['verified_email'])) {
            return (bool) $raw['verified_email'];
        }

        if (isset($raw['email_verified'])) {
            return (bool) $raw['email_verified'];
        }

        // Fallback to offsetGet for providers that map verified_email into attributes
        try {
            $claim = $socialUser->offsetGet('verified_email') ?? null;
        } catch (\Throwable $e) {
            $claim = null;
        }

        return (bool) $claim;
    }
}
