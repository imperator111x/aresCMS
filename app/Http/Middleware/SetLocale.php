<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * @return array<int, string>
     */
    protected function availableLocales(): array
    {
        $files = glob(resource_path('lang/*.json')) ?: [];
        $locales = [];
        foreach ($files as $file) {
            $code = pathinfo($file, PATHINFO_FILENAME);
            if (preg_match('/^[a-z]{2}(-[A-Z]{2})?$/', $code)) {
                $locales[] = $code;
            }
        }

        return array_values(array_unique($locales));
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $supported = $this->availableLocales();
        if ($supported === []) {
            $supported = ['de', 'en'];
        }

        // Check if language is set in session
        if (Session::has('locale')) {
            $locale = (string) Session::get('locale');
            if (in_array($locale, $supported, true)) {
                App::setLocale($locale);
            }
        }
        // Check if language is set in URL parameter
        elseif ($request->has('lang')) {
            $locale = $request->get('lang');
            if (in_array($locale, $supported, true)) {
                App::setLocale($locale);
                Session::put('locale', $locale);
            }
        }
        // Check if language is set in browser
        elseif ($request->hasHeader('Accept-Language')) {
            $browserLocale = substr($request->header('Accept-Language'), 0, 2);
            if (in_array($browserLocale, $supported, true)) {
                App::setLocale($browserLocale);
                Session::put('locale', $browserLocale);
            }
        }

        return $next($request);
    }
}
