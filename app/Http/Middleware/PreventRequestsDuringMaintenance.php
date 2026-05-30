<?php

namespace App\Http\Middleware;

use Closure;
use ErrorException;
use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance as Middleware;
use Illuminate\Support\Facades\Auth;

class PreventRequestsDuringMaintenance extends Middleware
{
    /**
     * URIs die für alle Besucher während Wartung erreichbar bleiben (z. B. Login für Admins).
     *
     * @var array<int, string>
     */
    protected $except = [
        'wartung/*',
        'login',
        'logout',
        'register',
        'password/*',
        'email/*',
        'two-factor/*',
        'language/*',
    ];

    /**
     * {@inheritdoc}
     *
     * Wartungsantwort wird immer dynamisch aus `errors.maintenance` gerendert (nicht das eingefrorene
     * HTML aus `storage/framework/down`), damit z. B. der Admin-Link stets aktuell ist.
     * Eingeloggte Admins dürfen die komplette Seite nutzen.
     */
    public function handle($request, Closure $next)
    {
        if ($this->inExceptArray($request)) {
            return $next($request);
        }

        if (! $this->app->maintenanceMode()->active()) {
            return $next($request);
        }

        try {
            $data = $this->app->maintenanceMode()->data();
        } catch (ErrorException $exception) {
            if (! $this->app->maintenanceMode()->active()) {
                return $next($request);
            }

            throw $exception;
        }

        $user = Auth::guard('web')->user();
        if ($user !== null && $user->isAdmin()) {
            return $next($request);
        }

        if (isset($data['secret']) && $request->path() === $data['secret']) {
            return $this->bypassResponse($data['secret']);
        }

        if ($this->hasValidBypassCookie($request, $data)) {
            return $next($request);
        }

        if (isset($data['redirect'])) {
            $path = $data['redirect'] === '/'
                ? $data['redirect']
                : trim($data['redirect'], '/');

            if ($request->path() !== $path) {
                return redirect($path);
            }
        }

        $status = $data['status'] ?? 503;
        $retryAfter = $data['retry'] ?? null;

        $response = response()->view('errors.maintenance', [
            'retryAfter' => $retryAfter,
        ], $status);

        foreach ($this->getHeaders($data) as $name => $value) {
            $response->headers->set($name, $value);
        }

        return $response;
    }
}
