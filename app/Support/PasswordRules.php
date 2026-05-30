<?php

namespace App\Support;

use Illuminate\Validation\Rules\Password;

class PasswordRules
{
    /**
     * Standard-Regel gemäß config/password.php
     */
    public static function default(): Password
    {
        $rule = Password::min(max(1, (int) config('password.min_length', 10)));

        if (config('password.require_letters', true)) {
            $rule->letters();
        }

        if (config('password.require_mixed_case', true)) {
            $rule->mixedCase();
        }

        if (config('password.require_numbers', true)) {
            $rule->numbers();
        }

        if (config('password.require_symbols', false)) {
            $rule->symbols();
        }

        return $rule;
    }

    /**
     * Aktuelle Passwortrichtlinie für UI (Registrierung, Hinweise).
     *
     * @return array{min_length: int, require_letters: bool, require_mixed_case: bool, require_numbers: bool, require_symbols: bool}
     */
    public static function policySummary(): array
    {
        return [
            'min_length' => max(1, (int) config('password.min_length', 10)),
            'require_letters' => (bool) config('password.require_letters', true),
            'require_mixed_case' => (bool) config('password.require_mixed_case', true),
            'require_numbers' => (bool) config('password.require_numbers', true),
            'require_symbols' => (bool) config('password.require_symbols', false),
        ];
    }
}
