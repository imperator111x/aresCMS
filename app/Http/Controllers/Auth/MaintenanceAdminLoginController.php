<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\LoginHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Separates Admin-Login während des Wartungsmodus (öffentliche Login-Seite ist oft unerreichbar bzw. mit CAPTCHA).
 */
class MaintenanceAdminLoginController extends Controller
{
    public function create(Request $request)
    {
        if (! app()->isDownForMaintenance()) {
            return redirect()->route('login');
        }

        if (Auth::check()) {
            if (Auth::user()->isAdmin()) {
                return redirect()->route('admin.dashboard');
            }

            return view('auth.maintenance-admin-login', [
                'alreadyLoggedInNonAdmin' => true,
            ]);
        }

        return view('auth.maintenance-admin-login', [
            'alreadyLoggedInNonAdmin' => false,
        ]);
    }

    public function store(Request $request)
    {
        if (! app()->isDownForMaintenance()) {
            return redirect()->route('login');
        }

        if (Auth::check() && Auth::user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        if (Auth::check() && ! Auth::user()->isAdmin()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $login = $request->input('login');
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'name';

        $user = User::query()->where($field, $login)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            LoginHistory::recordFailure($request, (string) $login, 'invalid_credentials');

            throw ValidationException::withMessages([
                'login' => [trans('auth.failed')],
            ]);
        }

        if (! $user->is_admin) {
            LoginHistory::recordFailure($request, (string) $login, 'not_admin', $user->id);

            throw ValidationException::withMessages([
                'login' => [__('Only administrators can sign in on this page.')],
            ]);
        }

        if ($user->is_banned) {
            LoginHistory::recordFailure($request, $user->email, 'banned', $user->id);

            throw ValidationException::withMessages([
                'login' => [__('Your account has been suspended.')],
            ]);
        }

        if ($user->hasTwoFactorEnabled()) {
            $request->session()->put('url.intended', route('admin.dashboard'));
            $request->session()->put('two_factor.pending_user_id', $user->id);
            $request->session()->put('two_factor.remember', $request->boolean('remember'));

            return redirect()->route('two-factor.challenge');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        if ($request->hasSession()) {
            $request->session()->put('auth.password_confirmed_at', time());
        }

        return redirect()->intended(route('admin.dashboard'));
    }
}
