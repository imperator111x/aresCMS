<?php

namespace App\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Symfony\Component\Process\Process;
use ZipArchive;

class CmsUpdateManager
{
    private const UPDATE_URL_CONTEXT_MANIFEST = 'manifest';
    private const UPDATE_URL_CONTEXT_PACKAGE = 'package';

    public function versionFilePath(): string
    {
        return storage_path('app/cms/installed_version');
    }

    public function getInstalledVersion(): string
    {
        $path = $this->versionFilePath();
        if (is_readable($path)) {
            $v = trim((string) file_get_contents($path));

            return $v !== '' ? $v : (string) config('cms.bundle_version', '1.0.0');
        }

        return (string) config('cms.bundle_version', '1.0.0');
    }

    public function setInstalledVersion(string $version): void
    {
        $dir = dirname($this->versionFilePath());
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($this->versionFilePath(), trim($version));
    }

    public function manifestUrl(): ?string
    {
        $url = trim((string) config('cms.update_manifest_url', ''));

        return $url !== '' ? $url : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function fetchManifest(bool $forceRefresh = false): ?array
    {
        $url = $this->manifestUrl();
        if ($url === null) {
            return null;
        }
        $this->assertAllowedUpdateUrl($url, self::UPDATE_URL_CONTEXT_MANIFEST);

        $cacheKey = 'cms.update.manifest.v1.'.md5($url);
        $ttl = max(60, (int) config('cms.manifest_cache_ttl', 600));

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, $ttl, function () use ($url) {
            $req = Http::timeout(25)->acceptJson();
            $token = trim((string) config('cms.update_token', ''));
            if ($token !== '') {
                $req = $req->withToken($token);
            }

            $response = $req->get($url);
            if (! $response->successful()) {
                Log::warning('CMS manifest HTTP error', ['url' => $url, 'status' => $response->status()]);

                return null;
            }

            $data = $response->json();
            if (! is_array($data) || empty($data['version']) || empty($data['package_url'])) {
                Log::warning('CMS manifest invalid JSON', ['url' => $url]);

                return null;
            }

            return $data;
        });
    }

    public function isUpdateAvailable(?array $manifest = null): bool
    {
        $manifest ??= $this->fetchManifest();
        if ($manifest === null) {
            return false;
        }

        $remote = (string) $manifest['version'];
        $installed = $this->getInstalledVersion();

        return version_compare($remote, $installed, '>');
    }

    /**
     * @param  array<string, mixed>  $manifest
     */
    public function applyUpdate(array $manifest): void
    {
        if (! config('cms.update_enabled', true)) {
            throw new RuntimeException(__('CMS updates are disabled on this installation.'));
        }

        $remote = (string) $manifest['version'];
        $installed = $this->getInstalledVersion();
        if (! version_compare($remote, $installed, '>')) {
            throw new RuntimeException(__('No newer version to install.'));
        }

        $minPhp = $manifest['min_php'] ?? '8.1.0';
        if (version_compare(PHP_VERSION, (string) $minPhp, '<')) {
            throw new RuntimeException(__('PHP :current is below required :required.', [
                'current' => PHP_VERSION,
                'required' => (string) $minPhp,
            ]));
        }

        $packageUrl = (string) $manifest['package_url'];
        $this->assertAllowedUpdateUrl($packageUrl, self::UPDATE_URL_CONTEXT_PACKAGE);
        $expectedSha = isset($manifest['sha256']) ? trim((string) $manifest['sha256']) : '';

        $workDir = storage_path('app/cms');
        if (! is_dir($workDir)) {
            mkdir($workDir, 0755, true);
        }

        $zipPath = $workDir.DIRECTORY_SEPARATOR.'update-'.preg_replace('/[^a-zA-Z0-9._-]+/', '_', $remote).'.zip';

        $this->downloadFile($packageUrl, $zipPath);

        if ($expectedSha !== '') {
            $hash = hash_file('sha256', $zipPath);
            if (! hash_equals(strtolower($expectedSha), strtolower($hash))) {
                @unlink($zipPath);
                throw new RuntimeException(__('Checksum of the update package does not match.'));
            }
        }

        $this->extractZipRespectingBlacklist($zipPath, base_path());

        @unlink($zipPath);

        $this->runComposerIfPossible();

        try {
            Artisan::call('migrate', ['--force' => true]);
        } catch (\Throwable $e) {
            Log::error('CMS update migrate failed', ['exception' => $e->getMessage()]);
            throw new RuntimeException(__('Database migration failed: :msg', ['msg' => $e->getMessage()]), 0, $e);
        }

        try {
            Artisan::call('optimize:clear');
        } catch (\Throwable $e) {
            Log::warning('CMS update optimize:clear failed', ['exception' => $e->getMessage()]);
        }

        $this->setInstalledVersion($remote);
    }

    private function downloadFile(string $url, string $destination): void
    {
        $req = Http::timeout(120)->sink($destination);
        $token = trim((string) config('cms.update_token', ''));
        if ($token !== '') {
            $req = $req->withToken($token);
        }

        $response = $req->get($url);
        if (! $response->successful()) {
            @unlink($destination);
            throw new RuntimeException(__('Could not download update package (HTTP :status).', [
                'status' => $response->status(),
            ]));
        }
    }

    private function extractZipRespectingBlacklist(string $zipPath, string $projectRoot): void
    {
        $zip = new ZipArchive;
        if ($zip->open($zipPath) !== true) {
            throw new RuntimeException(__('Update archive could not be opened.'));
        }

        $projectRoot = rtrim(str_replace('\\', '/', $projectRoot), '/');
        $blacklist = $this->normalizedBlacklist();

        $stripPrefix = $this->detectZipRootPrefix($zip);

        try {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $stat = $zip->statIndex($i);
                if ($stat === false) {
                    continue;
                }
                $name = str_replace('\\', '/', $stat['name']);
                $name = ltrim($name, '/');

                // Ordner-Einträge / leer
                if ($name === '' || str_ends_with($name, '/')) {
                    continue;
                }

                $relative = $stripPrefix !== '' && str_starts_with($name, $stripPrefix.'/')
                    ? substr($name, strlen($stripPrefix) + 1)
                    : $name;

                if ($this->isBlacklisted($relative, $blacklist)) {
                    continue;
                }

                if (str_contains($relative, '..')) {
                    continue;
                }

                $targetPath = $projectRoot.'/'.$relative;
                $targetDir = dirname($targetPath);
                if (! is_dir($targetDir)) {
                    mkdir($targetDir, 0755, true);
                }

                // Sicherheit: Ziel muss unter Projektroot bleiben
                $realTarget = realpath($targetDir);
                $realRoot = realpath($projectRoot);
                if ($realTarget === false || $realRoot === false || ! str_starts_with($realTarget, $realRoot)) {
                    continue;
                }

                $contents = $zip->getFromIndex($i);
                if ($contents === false) {
                    continue;
                }

                file_put_contents($targetPath, $contents);
            }
        } finally {
            $zip->close();
        }
    }

    /**
     * @return list<string>
     */
    private function normalizedBlacklist(): array
    {
        $raw = array_merge(
            ['.env', 'config', 'storage/app/public', 'storage/app/backups', 'storage/logs', 'storage/app/cms'],
            (array) config('cms.update_path_blacklist', [])
        );

        $out = [];
        foreach ($raw as $p) {
            $p = strtolower(str_replace('\\', '/', trim((string) $p)));
            $p = ltrim($p, '/');
            if ($p !== '') {
                $out[] = $p;
            }
        }

        return array_values(array_unique($out));
    }

    /**
     * @param  list<string>  $blacklist
     */
    private function isBlacklisted(string $relative, array $blacklist): bool
    {
        $rel = strtolower(str_replace('\\', '/', ltrim($relative, '/')));

        foreach ($blacklist as $rule) {
            if ($rel === $rule) {
                return true;
            }
            if (str_starts_with($rel, $rule.'/')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Wenn alle Pfade mit demselben ersten Ordner beginnen und dieser NICHT wie ein typischer Projekt-Wurzelordner wirkt,
     * wird dieser Präfix entfernt (z. B. „release-1.2/app/...“ → „app/...“).
     */
    private function detectZipRootPrefix(ZipArchive $zip): string
    {
        $knownRoots = ['app', 'bootstrap', 'config', 'database', 'public', 'resources', 'routes', 'tests', 'vendor', 'storage'];

        $firstSegments = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);
            if ($stat === false) {
                continue;
            }
            $name = str_replace('\\', '/', ltrim($stat['name'], '/'));
            if ($name === '' || str_ends_with($name, '/')) {
                continue;
            }
            $seg = explode('/', $name, 2)[0];
            if ($seg === '' || str_contains($seg, '..')) {
                continue;
            }
            $firstSegments[$seg] = true;
        }

        if (count($firstSegments) !== 1) {
            return '';
        }

        $only = array_key_first($firstSegments);
        if (in_array(strtolower($only), $knownRoots, true)) {
            return '';
        }

        // Nur strippen, wenn es wirklich ein Wrapper-Ordner ist (mind. eine Datei „only/…“)
        $hasNested = false;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);
            if ($stat === false) {
                continue;
            }
            $name = str_replace('\\', '/', ltrim($stat['name'], '/'));
            if ($name === '' || str_ends_with($name, '/')) {
                continue;
            }
            if (str_starts_with($name, $only.'/')) {
                $hasNested = true;
                break;
            }
        }

        return $hasNested ? $only : '';
    }

    private function runComposerIfPossible(): void
    {
        $composerPhar = base_path('composer.phar');
        $composerBin = 'composer';
        $phpCli = \App\Support\PhpCliBinary::resolve();

        if (is_file($composerPhar)) {
            $cmd = [$phpCli, $composerPhar, 'install', '--no-dev', '--no-interaction', '--optimize-autoloader'];
        } else {
            $cmd = [$composerBin, 'install', '--no-dev', '--no-interaction', '--optimize-autoloader'];
        }

        $process = new Process($cmd, base_path(), null, null, 600);
        try {
            $process->run();
            if (! $process->isSuccessful()) {
                Log::warning('CMS update composer install skipped or failed', [
                    'error' => $process->getErrorOutput(),
                    'output' => $process->getOutput(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('CMS update composer exception', ['exception' => $e->getMessage()]);
        }
    }

    /**
     * @return list<string>
     */
    private function allowedUpdateHosts(): array
    {
        $hosts = [];

        $manifestUrl = $this->manifestUrl();
        if (is_string($manifestUrl) && $manifestUrl !== '') {
            $manifestHost = strtolower((string) parse_url($manifestUrl, PHP_URL_HOST));
            if ($manifestHost !== '') {
                $hosts[] = $manifestHost;
            }
        }

        foreach ((array) config('cms.update_allowed_hosts', []) as $host) {
            $host = strtolower(trim((string) $host));
            if ($host !== '') {
                $hosts[] = $host;
            }
        }

        return array_values(array_unique($hosts));
    }

    private function assertAllowedUpdateUrl(string $url, string $context): void
    {
        $parts = parse_url($url);
        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = strtolower((string) ($parts['host'] ?? ''));

        if ($host === '' || ! in_array($scheme, ['http', 'https'], true)) {
            throw new RuntimeException(__('Invalid update URL in :context.', ['context' => $context]));
        }

        $requireHttps = (bool) config('cms.update_require_https', true);
        if ($requireHttps && $scheme !== 'https') {
            throw new RuntimeException(__('Blocked non-HTTPS update URL in :context.', ['context' => $context]));
        }

        $allowedHosts = $this->allowedUpdateHosts();
        if ($allowedHosts !== [] && ! $this->hostMatchesAllowlist($host, $allowedHosts)) {
            throw new RuntimeException(__('Blocked update host ":host" in :context.', ['host' => $host, 'context' => $context]));
        }
    }

    /**
     * @param  list<string>  $allowedHosts
     */
    private function hostMatchesAllowlist(string $host, array $allowedHosts): bool
    {
        foreach ($allowedHosts as $allowedHost) {
            if ($host === $allowedHost || str_ends_with($host, '.'.$allowedHost)) {
                return true;
            }
        }

        return false;
    }
}
