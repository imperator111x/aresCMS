<?php

/**
 * Dokumentation der benannten Rate-Limiter (siehe RouteServiceProvider).
 * Werte sind Orientierung — die eigentlichen Limits stehen in App\Providers\RouteServiceProvider.
 */
return [
    'login' => '8/min pro Login+IP, 30/min pro IP',
    'register' => '3/min, 10/h pro IP',
    'password-reset' => '3/min pro E-Mail, 8/h pro IP',
    'comments' => '6/min, 60/h pro User',
    'reactions' => '30/min, 200/h pro User',
    'forms' => '5/min, 30/h pro IP',
    'account' => '20/min pro User',
    'oauth' => '20/min pro IP',
    'two-factor' => '12/min pro Session',
    'license-activate' => '8/min pro IP',
];
