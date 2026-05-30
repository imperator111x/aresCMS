<?php

namespace App\Http\Middleware;

use App\Services\LicenseService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureValidLicense
{
    public function __construct(
        protected LicenseService $license
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! config('license.enabled', true)) {
            return $next($request);
        }

        // Öffentliche Uploads (/storage/…) nicht auf die Lizenzseite umleiten (sonst kaputte Bild-URLs).
        if ($request->is('storage') || $request->is('storage/*')) {
            return $next($request);
        }

        if ($this->license->validateHttpRequest($request)) {
            return $next($request);
        }

        $message = $this->license->getLastError() ?? __('This installation is not licensed.');

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'error' => 'license_invalid',
            ], 403);
        }

        if (! $request->session()->has('url.intended')) {
            $request->session()->put('url.intended', $request->fullUrl());
        }

        return redirect()
            ->route('license.show')
            ->with('license_alert', $message)
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }
}
