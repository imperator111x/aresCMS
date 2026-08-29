<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class UrlRedirect extends Model
{
    protected $fillable = [
        'from_path',
        'to_url',
        'status_code',
        'is_active',
        'hits',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'status_code' => 'integer',
        'hits' => 'integer',
    ];

    public static function normalizeFromPath(string $path): string
    {
        $path = trim($path);
        if ($path === '') {
            return '/';
        }

        // Absolute URLs → path only
        if (preg_match('#^https?://#i', $path)) {
            $parsed = parse_url($path);
            $path = ($parsed['path'] ?? '/').(isset($parsed['query']) ? '?'.$parsed['query'] : '');
        }

        $path = '/'.ltrim($path, '/');
        if ($path !== '/' && str_ends_with($path, '/')) {
            $path = rtrim($path, '/');
        }

        return Str::limit($path, 500, '');
    }

    public function matchesRequestPath(string $requestPath): bool
    {
        return self::normalizeFromPath($requestPath) === self::normalizeFromPath($this->from_path);
    }
}
