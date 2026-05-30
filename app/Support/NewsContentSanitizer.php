<?php

namespace App\Support;

/**
 * Minimal sanitization for rich text from Quill (no external API).
 * Allows common formatting tags; strips scripts and event handlers.
 */
class NewsContentSanitizer
{
    public static function sanitize(?string $html): string
    {
        if ($html === null || $html === '') {
            return '';
        }

        // Remove script/style blocks
        $html = preg_replace('#<(script|style)\b[^>]*>.*?</\1>#is', '', $html) ?? '';
        // Strip common inline event handlers (onclick=, onerror=, ...)
        $html = preg_replace('#\s+on\w+\s*=\s*("|\')[^"\']*\1#i', '', $html) ?? '';
        $html = preg_replace('#\s+on\w+\s*=\s*[^\s>]+#i', '', $html) ?? '';

        // Allow only tags produced by our Quill toolbar (no links/images to avoid href XSS)
        $allowed = '<p><br><strong><b><em><i><u><s><h1><h2><h3><h4><ul><ol><li><blockquote>';

        return trim(strip_tags($html, $allowed));
    }
}
