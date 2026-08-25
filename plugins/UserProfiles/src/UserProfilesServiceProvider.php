<?php

namespace Plugins\UserProfiles;

use Illuminate\Support\ServiceProvider;

class UserProfilesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        require_once __DIR__.'/bootstrap.php';
    }

    public function boot(): void
    {
        //
    }
}
