<?php

namespace App\Support;

class OAuthProviders
{
    public const SUPPORTED = ['google', 'discord'];

    public static function isSupported(string $provider): bool
    {
        return in_array($provider, self::SUPPORTED, true);
    }

    public static function isConfigured(string $provider): bool
    {
        if (! self::isSupported($provider)) {
            return false;
        }

        $config = config("services.{$provider}", []);

        if (! is_array($config)) {
            return false;
        }

        return filled($config['client_id'] ?? null)
            && filled($config['client_secret'] ?? null)
            && filled($config['redirect'] ?? null);
    }
}
