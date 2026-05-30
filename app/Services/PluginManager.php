<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class PluginManager
{
    /**
     * @var array<int, array<string, mixed>>|null
     */
    protected ?array $plugins = null;

    protected bool $booted = false;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        if ($this->plugins !== null) {
            return $this->plugins;
        }

        $this->plugins = $this->discover();

        return $this->plugins;
    }

    public function bootEnabledPlugins(): void
    {
        if ($this->booted || ! config('plugins.enabled', true)) {
            return;
        }

        foreach ($this->all() as $plugin) {
            if (! ($plugin['enabled'] ?? false)) {
                continue;
            }

            $providerFile = (string) ($plugin['provider_file'] ?? '');
            $providerClass = (string) ($plugin['provider'] ?? '');
            if ($providerFile !== '') {
                require_once $providerFile;
            }

            if ($providerClass !== '' && class_exists($providerClass)) {
                app()->register($providerClass);
            }

            $routesFile = (string) ($plugin['routes_file'] ?? '');
            if ($routesFile !== '' && is_file($routesFile)) {
                require $routesFile;
            }
        }

        $this->booted = true;
    }

    public function setEnabledByDirectory(string $directory, bool $enabled): bool
    {
        $directory = trim($directory);
        if ($directory === '' || ! preg_match('/^[A-Za-z0-9._-]+$/', $directory)) {
            return false;
        }

        $manifestPath = $this->manifestPathForDirectory($directory);
        if ($manifestPath === null || ! is_file($manifestPath)) {
            return false;
        }

        $decoded = json_decode((string) file_get_contents($manifestPath), true);
        if (! is_array($decoded)) {
            return false;
        }

        $decoded['enabled'] = $enabled;
        $encoded = json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if (! is_string($encoded)) {
            return false;
        }

        file_put_contents($manifestPath, $encoded.PHP_EOL);
        $this->plugins = null;

        return true;
    }

    public function pluginRootPath(): string
    {
        return (string) config('plugins.path', base_path('plugins'));
    }

    public function manifestPathForDirectory(string $directory): ?string
    {
        $root = realpath($this->pluginRootPath());
        if ($root === false) {
            return null;
        }

        $pluginDir = realpath($root.DIRECTORY_SEPARATOR.$directory);
        if ($pluginDir === false) {
            return null;
        }

        if (! str_starts_with($pluginDir, $root.DIRECTORY_SEPARATOR) && $pluginDir !== $root) {
            return null;
        }

        return $pluginDir.DIRECTORY_SEPARATOR.(string) config('plugins.manifest', 'plugin.json');
    }

    /**
     * @return array<int, array<string, string|null>>
     */
    public function adminNavigationItems(): array
    {
        $items = [];

        foreach ($this->all() as $plugin) {
            if (! ($plugin['enabled'] ?? false)) {
                continue;
            }

            $navItems = $plugin['admin_nav'] ?? [];
            if (! is_array($navItems)) {
                continue;
            }

            foreach ($navItems as $entry) {
                if (! is_array($entry)) {
                    continue;
                }

                $route = isset($entry['route']) ? trim((string) $entry['route']) : '';
                $label = isset($entry['label']) ? trim((string) $entry['label']) : '';
                if ($route === '' || $label === '') {
                    continue;
                }

                $items[] = [
                    'route' => $route,
                    'label' => $label,
                    'icon' => isset($entry['icon']) ? trim((string) $entry['icon']) : 'fas fa-puzzle-piece',
                    'active_pattern' => isset($entry['active_pattern']) ? trim((string) $entry['active_pattern']) : $route,
                    'permission' => isset($entry['permission']) ? trim((string) $entry['permission']) : null,
                ];
            }
        }

        return $items;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function discover(): array
    {
        $pluginRoot = (string) config('plugins.path', base_path('plugins'));
        $manifestName = (string) config('plugins.manifest', 'plugin.json');
        if (! is_dir($pluginRoot)) {
            return [];
        }

        $items = File::directories($pluginRoot);
        sort($items);
        $plugins = [];

        foreach ($items as $dir) {
            $manifestPath = $dir.DIRECTORY_SEPARATOR.$manifestName;
            $slug = basename($dir);

            if (! is_file($manifestPath)) {
                $plugins[] = [
                    'slug' => $slug,
                    'name' => Str::headline($slug),
                    'enabled' => false,
                    'version' => null,
                    'description' => 'Missing plugin.json',
                    'provider' => null,
                    'provider_file' => null,
                    'routes_file' => null,
                    'errors' => ['Missing plugin manifest.'],
                ];
                continue;
            }

            $decoded = json_decode((string) file_get_contents($manifestPath), true);
            if (! is_array($decoded)) {
                $plugins[] = [
                    'slug' => $slug,
                    'name' => Str::headline($slug),
                    'enabled' => false,
                    'version' => null,
                    'description' => 'Invalid plugin.json',
                    'provider' => null,
                    'provider_file' => null,
                    'routes_file' => null,
                    'errors' => ['plugin.json is not valid JSON.'],
                ];
                continue;
            }

            $providerFile = $this->resolvePluginFile($dir, (string) ($decoded['provider_file'] ?? ''));
            $routesFile = $this->resolvePluginFile($dir, (string) ($decoded['routes_file'] ?? ''));
            $errors = [];

            if (($decoded['provider_file'] ?? '') && $providerFile === null) {
                $errors[] = 'provider_file is invalid or outside plugin directory.';
            }
            if (($decoded['routes_file'] ?? '') && $routesFile === null) {
                $errors[] = 'routes_file is invalid or outside plugin directory.';
            }

            $plugins[] = [
                'slug' => (string) ($decoded['slug'] ?? $slug),
                'directory' => $slug,
                'name' => (string) ($decoded['name'] ?? Str::headline($slug)),
                'enabled' => (bool) ($decoded['enabled'] ?? true),
                'version' => isset($decoded['version']) ? (string) $decoded['version'] : null,
                'description' => isset($decoded['description']) ? (string) $decoded['description'] : '',
                'provider' => isset($decoded['provider']) ? (string) $decoded['provider'] : null,
                'provider_file' => $providerFile,
                'routes_file' => $routesFile,
                'manifest_path' => $manifestPath,
                'admin_nav' => isset($decoded['admin_nav']) && is_array($decoded['admin_nav']) ? $decoded['admin_nav'] : [],
                'errors' => $errors,
            ];
        }

        return $plugins;
    }

    protected function resolvePluginFile(string $pluginDir, string $relativePath): ?string
    {
        if ($relativePath === '') {
            return null;
        }

        $candidate = realpath($pluginDir.DIRECTORY_SEPARATOR.$relativePath);
        $realPluginDir = realpath($pluginDir);
        if ($candidate === false || $realPluginDir === false) {
            return null;
        }

        if (! str_starts_with($candidate, $realPluginDir.DIRECTORY_SEPARATOR) && $candidate !== $realPluginDir) {
            return null;
        }

        return $candidate;
    }
}

