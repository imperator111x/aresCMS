<?php

namespace App\Http\Controllers;

use App\Services\LicenseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LicenseActivationController extends Controller
{
    public function show(Request $request, LicenseService $license): View|RedirectResponse
    {
        if (config('license.enabled', true) && $license->validateHttpRequest($request)) {
            return redirect()->intended(route('home'));
        }

        $envKeySet = trim((string) config('license.key')) !== '';

        return view('license.activate', [
            'envKeySet' => $envKeySet,
            'alert' => session('license_alert'),
        ]);
    }

    public function store(Request $request, LicenseService $license): RedirectResponse
    {
        if (trim((string) config('license.key')) !== '') {
            return redirect()
                ->route('license.show')
                ->with('license_alert', __('License key is set in the environment file and cannot be changed here.'));
        }

        $validated = $request->validate([
            'license_key' => ['required', 'string', 'max:512'],
        ]);

        if ($license->persistValidatedKey($validated['license_key'], $request)) {
            return redirect()
                ->intended(route('home'))
                ->with('status', __('License activated successfully.'));
        }

        return redirect()
            ->route('license.show')
            ->withInput($request->only('license_key'))
            ->with('license_alert', $license->getLastError() ?? __('This license is not valid for this installation.'));
    }
}
