<?php

namespace Plugins\UserProfiles;

use Illuminate\Support\ServiceProvider;

class UserProfilesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        require_once __DIR__.'/Models/Friendship.php';
        require_once __DIR__.'/Models/ChatMessage.php';
        require_once __DIR__.'/Models/E2ePublicKey.php';
        require_once __DIR__.'/Http/Controllers/ProfileController.php';
    }

    public function boot(): void
    {
        //
    }
}
