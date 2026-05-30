<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Passwortrichtlinie (Registrierung, Passwort zurücksetzen, ggf. Profil)
    |--------------------------------------------------------------------------
    */

    'min_length' => (int) env('PASSWORD_MIN_LENGTH', 10),

    'require_letters' => filter_var(env('PASSWORD_REQUIRE_LETTERS', true), FILTER_VALIDATE_BOOLEAN),

    'require_mixed_case' => filter_var(env('PASSWORD_REQUIRE_MIXED_CASE', true), FILTER_VALIDATE_BOOLEAN),

    'require_numbers' => filter_var(env('PASSWORD_REQUIRE_NUMBERS', true), FILTER_VALIDATE_BOOLEAN),

    'require_symbols' => filter_var(env('PASSWORD_REQUIRE_SYMBOLS', false), FILTER_VALIDATE_BOOLEAN),

];
