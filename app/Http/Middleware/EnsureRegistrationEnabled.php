<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;

class EnsureRegistrationEnabled
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (Setting::getBoolValue('disable_registration', false)) {
            return redirect()->route('login')->with('error', __('Registration is currently disabled.'));
        }

        return $next($request);
    }
}

