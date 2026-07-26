<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    protected int $maxAttempts = 5;

    protected int $lockoutMinutes = 15;

    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = \App\Models\User::where('email', $request->input('email'))->first();

        if ($user && $this->isLocked($user)) {
            $minutes = now()->diffInMinutes($user->locked_until) + 1;
            return back()
                ->with('error', "Account temporarily locked. Too many failed login attempts. Try again in {$minutes} minute(s).")
                ->onlyInput('email');
        }

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            if ($user) {
                $this->incrementAttempts($user);
            }
            return back()
                ->withErrors(['email' => 'The provided email or password is incorrect.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        $request->user()->update([
            'failed_login_attempts' => 0,
            'locked_until'         => null,
        ]);

        if ($intended = $request->input('intended')) {
            $role = Auth::user()->role;

            if ($role === 'worker' || $role === 'admin') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()
                    ->with('error', 'You need a client account to book services. Please log in with a client account.')
                    ->onlyInput('email');
            }

            return redirect($intended);
        }

        return match (Auth::user()->role) {
            'admin'  => redirect()->intended(route('admin.dashboard')),
            'worker' => redirect()->intended(route('worker.dashboard')),
            default  => redirect()->intended(route('client.dashboard')),
        };
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    protected function isLocked($user): bool
    {
        return $user->locked_until && now()->lessThan($user->locked_until);
    }

    protected function incrementAttempts($user): void
    {
        $attempts = $user->failed_login_attempts + 1;
        $updates = ['failed_login_attempts' => $attempts];

        if ($attempts >= $this->maxAttempts) {
            $updates['locked_until'] = now()->addMinutes($this->lockoutMinutes);
        }

        $user->update($updates);
    }
}
