<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorSecurityController extends Controller
{
    private const SETUP_SESSION_KEY = 'two_factor_setup';

    public function show(Request $request): View
    {
        $this->forgetStaleSetup($request);

        $setup = $request->session()->get(self::SETUP_SESSION_KEY);
        $setupSecret = is_array($setup) ? ($setup['secret'] ?? null) : null;
        $qrUrl = null;
        if ($setupSecret && $request->user()) {
            $google2fa = new Google2FA;
            $qrUrl = $google2fa->getQRCodeUrl(
                config('app.name', 'News'),
                $request->user()->email,
                $setupSecret
            );
        }

        return view('admin.security.two-factor', [
            'enabled' => $request->user()->hasTwoFactorEnabled(),
            'setupSecret' => $setupSecret,
            'qrUrl' => $qrUrl,
        ]);
    }

    public function begin(Request $request): RedirectResponse
    {
        if (! $request->user()->is_admin) {
            abort(403);
        }

        if ($request->user()->hasTwoFactorEnabled()) {
            return redirect()->route('admin.security.two-factor');
        }

        $google2fa = new Google2FA;
        $secret = $google2fa->generateSecretKey();
        $request->session()->put(self::SETUP_SESSION_KEY, [
            'secret' => $secret,
            'at' => now()->timestamp,
        ]);

        return redirect()->route('admin.security.two-factor')
            ->with('success', __('Scan the QR code or enter the secret, then confirm with a code.'));
    }

    public function confirm(Request $request): RedirectResponse
    {
        if (! $request->user()->is_admin) {
            abort(403);
        }

        $request->validate([
            'code' => ['required', 'string', 'regex:/^\d{6}$/'],
        ]);

        $setup = $request->session()->get(self::SETUP_SESSION_KEY);
        $secret = is_array($setup) ? ($setup['secret'] ?? null) : null;
        if (! $secret) {
            return redirect()->route('admin.security.two-factor')
                ->with('error', __('Please start setup again.'));
        }

        $google2fa = new Google2FA;
        if (! $google2fa->verifyKey($secret, preg_replace('/\s+/', '', $request->code), 4)) {
            return redirect()->route('admin.security.two-factor')
                ->with('error', __('The code is invalid. Try again.'))
                ->withInput();
        }

        $request->user()->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => now(),
        ])->save();

        $request->session()->forget(self::SETUP_SESSION_KEY);

        ActivityLogger::log('two_factor.enabled', __('Two-factor authentication enabled'), $request->user());

        return redirect()->route('admin.security.two-factor')
            ->with('success', __('Two-factor authentication is now active.'));
    }

    public function disable(Request $request): RedirectResponse
    {
        if (! $request->user()->is_admin) {
            abort(403);
        }

        $request->validate([
            'password' => 'required|string',
        ]);

        if (! Hash::check($request->password, $request->user()->password)) {
            return redirect()->route('admin.security.two-factor')
                ->with('error', __('Incorrect password.'));
        }

        $request->user()->forceFill([
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        $request->session()->forget(self::SETUP_SESSION_KEY);

        ActivityLogger::log('two_factor.disabled', __('Two-factor authentication disabled'), $request->user());

        return redirect()->route('admin.security.two-factor')
            ->with('success', __('Two-factor authentication has been turned off.'));
    }

    private function forgetStaleSetup(Request $request): void
    {
        $setup = $request->session()->get(self::SETUP_SESSION_KEY);
        if (! is_array($setup) || empty($setup['at'])) {
            return;
        }
        if (now()->timestamp - (int) $setup['at'] > 900) {
            $request->session()->forget(self::SETUP_SESSION_KEY);
        }
    }
}
