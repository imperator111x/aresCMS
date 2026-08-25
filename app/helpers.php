<?php

use App\Support\LegalUrl;

if (! function_exists('legal_route')) {
    /**
     * URL for a legal page (imprint, privacy, terms) with path fallback if routes are missing.
     */
    function legal_route(string $page = 'imprint'): string
    {
        return match ($page) {
            'privacy', 'datenschutz' => LegalUrl::privacy(),
            'terms', 'agb' => LegalUrl::terms(),
            default => LegalUrl::imprint(),
        };
    }
}
