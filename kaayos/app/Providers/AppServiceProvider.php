<?php

namespace App\Providers;

use App\Models\ClientIdentityVerification;
use App\Observers\ClientIdentityVerificationObserver;
use App\Policies\ClientIdentityVerificationPolicy;
use App\Services\AdminDashboardMetrics;
use App\Support\ClientSampleData;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(Registered::class, SendEmailVerificationNotification::class);

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->input('email').'|'.$request->ip());
        });

        RateLimiter::for('register', function (Request $request) {
            return $this->registrationLimits($request, $this->registrationEmail($request));
        });

        // Google registration completion gets its own bucket so retries in that
        // flow never drain the classic /register allowance (and vice versa).
        RateLimiter::for('google-register', function (Request $request) {
            return $this->registrationLimits($request, $this->registrationEmail($request, fromSession: true));
        });

        RateLimiter::for('email-otp-send', function (Request $request) {
            return Limit::perHour(3)->by($request->user()->id);
        });

        RateLimiter::for('email-otp-verify', function (Request $request) {
            return Limit::perHour(5)->by($request->user()->id);
        });

        RateLimiter::for('chatbot', function (Request $request) {
            return Limit::perMinute(20)->by($request->ip());
        });

        View::composer('layouts.client', function ($view) {
            $view->with('notifications', ClientSampleData::notifications());
        });

        View::composer('layouts.admin', function ($view) {
            $view->with('adminNav', app(AdminDashboardMetrics::class)->navCounts());
        });

        ClientIdentityVerification::observe(ClientIdentityVerificationObserver::class);

        Gate::policy(ClientIdentityVerification::class, ClientIdentityVerificationPolicy::class);
    }

    /**
     * Per-IP ceiling plus a tighter per-email limit for registration attempts.
     *
     * Registration attempts are still counted in middleware (so invalid
     * submissions cannot be spammed), but the IP bucket is large enough that a
     * client sign-up does not block a worker sign-up from the same network.
     *
     * @return array<int, \Illuminate\Cache\RateLimiting\Limit>
     */
    protected function registrationLimits(Request $request, ?string $email): array
    {
        $limits = [
            Limit::perHour($this->registrationIpLimit())
                ->by('register-ip:'.$request->ip())
                ->response($this->registrationThrottleResponse()),
        ];

        if ($email !== null) {
            $limits[] = Limit::perHour($this->registrationEmailLimit())
                ->by('register-email:'.$email)
                ->response($this->registrationThrottleResponse());
        }

        return $limits;
    }

    protected function registrationIpLimit(): int
    {
        return max(1, (int) config('kaayos.registration_ip_hourly_limit', 10));
    }

    protected function registrationEmailLimit(): int
    {
        return max(1, (int) config('kaayos.registration_email_hourly_limit', 3));
    }

    /**
     * Normalized email used for the per-address limit.
     *
     * Missing, blank, or non-string values safely fall back to the per-IP
     * limit only. For Google completion the verified address is kept in the
     * OAuth handoff session, so it is used when the form field is absent.
     */
    protected function registrationEmail(Request $request, bool $fromSession = false): ?string
    {
        $email = $request->input('email');

        if ($fromSession && (! is_string($email) || trim($email) === '')) {
            $email = $request->hasSession()
                ? $request->session()->get('oauth_registration.email')
                : null;
        }

        if (! is_string($email)) {
            return null;
        }

        $email = Str::lower(trim($email));

        return $email === '' ? null : $email;
    }

    /**
     * Friendly "too many attempts" response using the real retry interval
     * supplied by the throttle middleware.
     */
    protected function registrationThrottleResponse(): \Closure
    {
        return function (Request $request, array $headers) {
            $retryAfter = max(1, (int) ($headers['Retry-After'] ?? 60));

            if ($request->expectsJson()) {
                return response()->json([
                    'message'     => 'Too many attempts. Please try again later.',
                    'retry_after' => $retryAfter,
                ], 429, $headers);
            }

            return response()
                ->view('errors.429', ['retryAfter' => $retryAfter], 429)
                ->withHeaders($headers);
        };
    }
}
