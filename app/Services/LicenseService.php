<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class LicenseService
{
    private ?string $lastError = null;

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    /**
     * Effektiver Schlüssel: zuerst .env (CMS_LICENSE_KEY), sonst lokal gespeicherte Datei (verschlüsselt).
     */
    public function getEffectiveKey(): string
    {
        $fromEnv = trim((string) config('license.key'));
        if ($fromEnv !== '') {
            return $fromEnv;
        }

        return $this->readStoredKey();
    }

    /**
     * Host der aktuellen Anfrage (lowercase), z. B. localhost, 127.0.0.1, www.example.com
     */
    public function currentDomain(Request $request): string
    {
        $host = strtolower(trim($request->getHost()));

        return $host !== '' ? $host : 'localhost';
    }

    public function validateHttpRequest(Request $request): bool
    {
        $this->lastError = null;

        if (! config('license.enabled', true)) {
            return true;
        }

        $key = $this->getEffectiveKey();
        if ($key === '') {
            $this->lastError = __('No license key is configured.');

            return false;
        }

        $domain = $this->currentDomain($request);
        $keys = $this->cacheKeys($key, $domain);

        if (Cache::get($keys['ok']) === true) {
            return true;
        }

        $result = $this->callRemote($key, $domain);

        if ($result === null) {
            if (Cache::get($keys['grace']) === true) {
                Log::warning('CMS license: license server unreachable, using grace period', [
                    'domain' => $domain,
                ]);
                $short = min(300, (int) config('license.cache_ttl', 3600));
                Cache::put($keys['ok'], true, max(60, $short));

                return true;
            }
            if ($this->lastError === null) {
                $this->lastError = __('The license server could not be reached. Check the network or try again later.');
            }

            return false;
        }

        if ($result['valid'] === true) {
            Cache::put($keys['ok'], true, (int) config('license.cache_ttl', 3600));
            Cache::put($keys['grace'], true, (int) config('license.grace_ttl', 604800));

            return true;
        }

        Cache::forget($keys['ok']);
        Cache::forget($keys['grace']);
        $this->lastError = $result['message'] ?? __('This license is not valid for this installation.');

        return false;
    }

    /**
     * Prüft den Schlüssel gegen den Server und speichert ihn bei Erfolg verschlüsselt (wenn nicht nur .env).
     */
    public function persistValidatedKey(string $licenseKey, Request $request): bool
    {
        $this->lastError = null;
        $licenseKey = trim($licenseKey);
        if ($licenseKey === '') {
            $this->lastError = __('No license key is configured.');

            return false;
        }

        if (trim((string) config('license.key')) !== '') {
            $this->lastError = __('License key is set in the environment file and cannot be changed here.');

            return false;
        }

        $domain = $this->currentDomain($request);
        $result = $this->callRemote($licenseKey, $domain);

        if ($result === null) {
            $this->lastError = __('The license server could not be reached. Check the network or try again later.');

            return false;
        }

        if ($result['valid'] !== true) {
            $this->lastError = $result['message'] ?? __('This license is not valid for this installation.');

            return false;
        }

        $this->writeStoredKey($licenseKey);

        $keys = $this->cacheKeys($licenseKey, $domain);
        Cache::put($keys['ok'], true, (int) config('license.cache_ttl', 3600));
        Cache::put($keys['grace'], true, (int) config('license.grace_ttl', 604800));

        return true;
    }

    /**
     * Für Artisan: Request mit Host aus APP_URL simulieren.
     */
    public function validateForCli(): bool
    {
        $appUrl = (string) config('app.url', 'http://localhost');
        $host = parse_url($appUrl, PHP_URL_HOST);
        if (! is_string($host) || $host === '') {
            $host = 'localhost';
        }
        $host = strtolower($host);

        $request = Request::create('/', 'GET', server: [
            'HTTP_HOST' => $host,
            'SERVER_NAME' => $host,
        ]);

        return $this->validateHttpRequest($request);
    }

    public function forgetCacheForCurrentConfig(): void
    {
        $key = $this->getEffectiveKey();
        if ($key === '') {
            return;
        }
        $appUrl = (string) config('app.url', 'http://localhost');
        $host = parse_url($appUrl, PHP_URL_HOST);
        $host = is_string($host) && $host !== '' ? strtolower($host) : 'localhost';
        $keys = $this->cacheKeys($key, $host);
        Cache::forget($keys['ok']);
        Cache::forget($keys['grace']);
    }

    private function storedKeyPath(): string
    {
        return storage_path('app/cms/.license');
    }

    private function readStoredKey(): string
    {
        $path = $this->storedKeyPath();
        if (! is_readable($path)) {
            return '';
        }
        try {
            return trim(Crypt::decryptString((string) file_get_contents($path)));
        } catch (\Throwable) {
            return '';
        }
    }

    private function writeStoredKey(string $plain): void
    {
        $dir = dirname($this->storedKeyPath());
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($this->storedKeyPath(), Crypt::encryptString($plain));
    }

    /**
     * @return array{valid: bool, message: ?string}|null null = Transport-/Parse-Fehler
     */
    private function callRemote(string $licenseKey, string $domain): ?array
    {
        $url = trim((string) config('license.validate_url'));
        if ($url === '') {
            $this->lastError = __('License validation URL is not configured.');

            return ['valid' => false, 'message' => $this->lastError];
        }

        $allowedHosts = config('license.allowed_validate_hosts', []);
        if (is_array($allowedHosts) && $allowedHosts !== []) {
            $parsedHost = parse_url($url, PHP_URL_HOST);
            if (! is_string($parsedHost) || $parsedHost === '') {
                $this->lastError = __('License validation URL must include a hostname.');

                return ['valid' => false, 'message' => $this->lastError];
            }
            $parsedHost = strtolower($parsedHost);
            if (! in_array($parsedHost, $allowedHosts, true)) {
                $this->lastError = __('License validation host is not allowed.');

                return ['valid' => false, 'message' => $this->lastError];
            }
        }

        $client = Http::timeout((int) config('license.timeout', 10))
            ->acceptJson()
            ->asJson();

        if (! config('license.verify_ssl', true)) {
            $client = $client->withOptions(['verify' => false]);
        }

        try {
            $response = $client->post($url, [
                'license_key' => $licenseKey,
                'domain' => $domain,
            ]);
        } catch (\Throwable $e) {
            Log::notice('CMS license request failed', ['message' => $e->getMessage(), 'url' => $url]);
            if (config('app.debug')) {
                $this->lastError = __('License request failed: :msg', ['msg' => $e->getMessage()]);
            }

            return null;
        }

        if (! $response->successful()) {
            $status = $response->status();
            $body = $response->body();
            Log::notice('CMS license HTTP error', [
                'status' => $status,
                'url' => $url,
                'body' => Str::limit($body, 800),
            ]);

            $msg = __('License server responded with HTTP :status. Check CMS_LICENSE_VALIDATE_URL and whether /api/validate reaches your PHP file (see deploy/key.luetcke.eu.htaccess).', [
                'status' => (string) $status,
            ]);
            if (config('app.debug') && $body !== '') {
                $msg .= ' '.Str::limit(strip_tags($body), 400);
            }
            $this->lastError = $msg;

            return ['valid' => false, 'message' => $msg];
        }

        $json = $response->json();
        if (! is_array($json) || ! array_key_exists('valid', $json)) {
            Log::notice('CMS license invalid JSON response', ['url' => $url, 'body' => Str::limit($response->body(), 500)]);
            $this->lastError = __('Invalid response from license server. Expected JSON with a "valid" field (HTTP 200).');

            return ['valid' => false, 'message' => $this->lastError];
        }

        return [
            'valid' => filter_var($json['valid'], FILTER_VALIDATE_BOOLEAN),
            'message' => isset($json['message']) ? (string) $json['message'] : null,
        ];
    }

    /**
     * @return array{ok: string, grace: string}
     */
    private function cacheKeys(string $licenseKey, string $domain): array
    {
        $hash = hash('sha256', $licenseKey.'|'.$domain);

        return [
            'ok' => 'cms.license.ok.'.$hash,
            'grace' => 'cms.license.grace.'.$hash,
        ];
    }
}
