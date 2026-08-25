<?php

namespace App\Services;

use App\Models\Setting;
use App\Support\LegalUrl;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use InvalidArgumentException;

class ThemeManager
{
    protected ?string $activeSlug = null;

    /** @var array<string, array<string, mixed>>|null */
    protected ?array $discovered = null;

    public function boot(): void
    {
        $slug = $this->activeSlug();
        $viewsPath = $this->path($slug).DIRECTORY_SEPARATOR.'views';

        if (is_dir($viewsPath)) {
            View::prependLocation($viewsPath);
        }

        View::share('cmsTheme', $slug);
        View::share('cmsThemeMeta', $this->meta($slug));
        View::share('contactUrl', $this->contactUrl());
    }

    public function contactUrl(): string
    {
        return \Illuminate\Support\Facades\Cache::remember('layout.contact_url_v2', now()->addMinutes(5), static function (): string {
            $fallback = LegalUrl::imprint();

            if (! \Illuminate\Support\Facades\Schema::hasTable('pages')) {
                return $fallback;
            }

            $contactPage = \App\Models\Page::query()
                ->where('is_published', true)
                ->whereIn('slug', ['kontakt', 'contact', 'anfrage'])
                ->first();

            return $contactPage
                ? route('page.show', $contactPage->slug)
                : $fallback;
        });
    }

    public function activeSlug(): string
    {
        if ($this->activeSlug !== null) {
            return $this->activeSlug;
        }

        $configured = (string) config('themes.default', 'default');
        $stored = Setting::getValue((string) config('themes.setting_key', 'active_theme'));

        $slug = is_string($stored) && $stored !== '' ? $stored : $configured;
        if (! $this->exists($slug)) {
            $slug = 'default';
        }

        return $this->activeSlug = $slug;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function all(): array
    {
        if ($this->discovered !== null) {
            return $this->discovered;
        }

        $root = $this->rootPath();
        if (! is_dir($root)) {
            return $this->discovered = [];
        }

        $themes = [];
        foreach (File::directories($root) as $dir) {
            $slug = basename($dir);
            if (! preg_match('/^[a-z0-9][a-z0-9_-]*$/', $slug)) {
                continue;
            }

            $meta = $this->readManifest($dir);
            $themes[$slug] = array_merge([
                'slug' => $slug,
                'name' => ucfirst($slug),
                'description' => '',
                'version' => '1.0.0',
                'preview' => null,
            ], $meta);
        }

        ksort($themes);

        if (! isset($themes['default'])) {
            $themes = ['default' => [
                'slug' => 'default',
                'name' => 'Standard',
                'description' => __('Built-in layout from resources/views'),
                'version' => '1.0.0',
                'preview' => null,
            ]] + $themes;
        }

        return $this->discovered = $themes;
    }

    /**
     * @return array<string, mixed>
     */
    public function meta(string $slug): array
    {
        return $this->all()[$slug] ?? $this->all()['default'];
    }

    public function exists(string $slug): bool
    {
        if ($slug === 'default') {
            return is_file(resource_path('views/layouts/app.blade.php'))
                || is_dir($this->path('default').DIRECTORY_SEPARATOR.'views');
        }

        return is_dir($this->path($slug));
    }

    public function setActive(string $slug): void
    {
        if (! $this->exists($slug)) {
            throw new InvalidArgumentException(__('Unknown theme: :theme', ['theme' => $slug]));
        }

        Setting::setValue((string) config('themes.setting_key', 'active_theme'), $slug);
        $this->activeSlug = $slug;
        $this->discovered = null;
    }

    public function assetUrl(string $slug, string $relativePath): ?string
    {
        $full = $this->path($slug).DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.ltrim($relativePath, '/');
        if (! is_file($full)) {
            return null;
        }

        $public = public_path('themes'.DIRECTORY_SEPARATOR.$slug.DIRECTORY_SEPARATOR.ltrim($relativePath, '/'));
        if (! is_file($public)) {
            return null;
        }

        return asset('themes/'.$slug.'/'.ltrim($relativePath, '/'));
    }

    public function rootPath(): string
    {
        return base_path('themes');
    }

    public function path(string $slug): string
    {
        return $this->rootPath().DIRECTORY_SEPARATOR.$slug;
    }

    /**
     * @return array<string, mixed>
     */
    protected function readManifest(string $directory): array
    {
        $file = $directory.DIRECTORY_SEPARATOR.'theme.json';
        if (! is_file($file)) {
            return [];
        }

        $decoded = json_decode((string) file_get_contents($file), true);

        return is_array($decoded) ? $decoded : [];
    }
}
