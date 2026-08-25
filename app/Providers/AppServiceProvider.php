<?php

namespace App\Providers;

use App\Models\News;
use App\Observers\NewsObserver;
use App\Services\PluginManager;
use App\Services\ThemeManager;
use App\View\Composers\AdminLayoutComposer;
use App\View\Composers\CmsVersionComposer;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $helpers = app_path('helpers.php');
        if (is_file($helpers)) {
            require_once $helpers;
        }

        $this->app->singleton(PluginManager::class, static fn () => new PluginManager());
        $this->app->singleton(ThemeManager::class, static fn () => new ThemeManager());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        View::composer('layouts.admin', AdminLayoutComposer::class);
        View::composer(['layouts.admin', 'layouts.app', 'layouts._public-shell'], CmsVersionComposer::class);
        News::observe(NewsObserver::class);
        app(ThemeManager::class)->boot();
        app(PluginManager::class)->bootEnabledPlugins();
    }
}
