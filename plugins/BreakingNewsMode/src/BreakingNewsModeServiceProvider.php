<?php

namespace Plugins\BreakingNewsMode;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Plugins\BreakingNewsMode\Services\BreakingNewsService;

class BreakingNewsModeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        require_once __DIR__.'/Services/BreakingNewsService.php';

        $this->app->singleton(BreakingNewsService::class, static function (): BreakingNewsService {
            return new BreakingNewsService();
        });
    }

    public function boot(): void
    {
        Blade::directive('breakingNewsBanner', static function (): string {
            return "<?php echo app(\\Plugins\\BreakingNewsMode\\Services\\BreakingNewsService::class)->renderBanner(); ?>";
        });
    }
}
