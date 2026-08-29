<?php

namespace Plugins\ContactForm;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Plugins\ContactForm\Services\ContactFormService;

class ContactFormServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        require_once __DIR__.'/Services/ContactFormService.php';

        $this->app->singleton(ContactFormService::class, static function (): ContactFormService {
            return new ContactFormService();
        });
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'contact-form');

        Blade::directive('contactForm', static function ($expression): string {
            $expr = trim((string) $expression);
            if ($expr === '') {
                return '<?php echo app(\\Plugins\\ContactForm\\Services\\ContactFormService::class)->renderHtml(); ?>';
            }

            return "<?php echo app(\\Plugins\\ContactForm\\Services\\ContactFormService::class)->renderHtml({$expr}); ?>";
        });
    }
}
