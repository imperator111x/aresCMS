<?php

namespace Plugins\CommentModerationAI;

use Illuminate\Support\ServiceProvider;
use Plugins\CommentModerationAI\Services\CommentModerationService;

class CommentModerationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        require_once __DIR__.'/Services/CommentModerationService.php';

        $this->app->singleton(CommentModerationService::class, static function (): CommentModerationService {
            return new CommentModerationService();
        });
    }
}
