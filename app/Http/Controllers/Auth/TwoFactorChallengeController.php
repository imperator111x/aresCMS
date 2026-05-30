<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\LoginHistory;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TwoFactorChallengeController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function create(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('two_factor.pending_user_id')) {
            return redirect()->route(
                app()->isDownForMaintenance() ? 'maintenance.admin.login' : 'login'
            );
        }

        return view('auth.two-factor');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'regex:/^\d{6}$/'],
        ]);

        $userId = $request->session()->get('two_factor.pending_user_id');
        if (! $userId) {
            return redirect()->route(
                app()->isDownForMaintenance() ? 'maintenance.admin.login' : 'login'
            );
        }

        /** @var User|null $user */
        $user = User::query()->find($userId);
        if (! $user || ! $user->hasTwoFactorEnabled()) {
            $request->session()->forget(['two_factor.pending_user_id', 'two_factor.remember']);
            return redirect()->route(
                app()->isDownForMaintenance() ? 'maintenance.admin.login' : 'login'
            );
        }

        if (! $user->verifyTwoFactorCode($request->input('code'))) {
            LoginHistory::recordFailure($request, $user->email, 'two_factor_invalid', $user->id);
            throw ValidationException::withMessages([
                'code' => [__('The verification code is invalid.')],
            ]);
        }

        $remember = (bool) $request->session()->pull('two_factor.remember', false);
        $request->session()->forget('two_factor.pending_user_id');

        Auth::login($user, $remember);
        $request->session()->regenerate();
        $request->session()->put('auth.password_confirmed_at', time());

        return redirect()->intended('/');
    }
}
