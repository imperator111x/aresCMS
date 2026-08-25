<?php

namespace App\Support;

use Illuminate\Support\Facades\Route;

final class LegalUrl
{
    public static function imprint(): string
    {
        return self::resolve('legal.imprint', '/impressum');
    }

    public static function privacy(): string
    {
        return self::resolve('legal.privacy', '/datenschutz');
    }

    public static function terms(): string
    {
        return self::resolve('legal.terms', '/agb');
    }

    private static function resolve(string $routeName, string $path): string
    {
        if (Route::has($routeName)) {
            return route($routeName);
        }

        return url($path);
    }
}
