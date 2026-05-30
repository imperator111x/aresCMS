<?php

namespace Plugins\SponsorAdSlots;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Plugins\SponsorAdSlots\Services\AdSlotService;

class SponsorAdSlotsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        require_once __DIR__.'/Models/AdSlot.php';
        require_once __DIR__.'/Services/AdSlotService.php';

        $this->app->singleton(AdSlotService::class, static function (): AdSlotService {
            return new AdSlotService();
        });
    }

    public function boot(): void
    {
        Blade::directive('adSlot', static function ($expression): string {
            return "<?php echo app(\\Plugins\\SponsorAdSlots\\Services\\AdSlotService::class)->render({$expression}); ?>";
        });
    }
}
