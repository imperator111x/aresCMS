<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class LaravelReleaseService
{
    public function installedVersion(): string
    {
        return app()->version();
    }

    public function latestStableVersion(): ?string
    {
        if (! config('cms.laravel_version_check_enabled', true)) {
            return null;
        }

        return Cache::remember(
            'cms.laravel.latest_stable',
            (int) config('cms.laravel_version_check_ttl', 86400),
            fn (): ?string => $this->fetchLatestStableVersion()
        );
    }

    public function isUpdateAvailable(): ?bool
    {
        $latest = $this->latestStableVersion();
        if ($latest === null) {
            return null;
        }

        return version_compare($latest, $this->installedVersion(), '>');
    }

    private function fetchLatestStableVersion(): ?string
    {
        try {
            $response = Http::timeout(8)
                ->acceptJson()
                ->get('https://packagist.org/packages/laravel/framework.json');

            if (! $response->successful()) {
                return null;
            }

            $versions = array_keys((array) $response->json('package.versions', []));
            $stable = array_values(array_filter($versions, static function (string $version): bool {
                if (Str::contains($version, ['dev', 'alpha', 'beta', 'RC'])) {
                    return false;
                }

                return preg_match('/^\d+\.\d+\.\d+$/', $version) === 1;
            }));

            if ($stable === []) {
                return null;
            }

            usort($stable, 'version_compare');

            return (string) end($stable);
        } catch (\Throwable) {
            return null;
        }
    }
}
