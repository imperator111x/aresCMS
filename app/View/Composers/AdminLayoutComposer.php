<?php

namespace App\View\Composers;

use App\Support\AdminNotifications;
use Illuminate\View\View;

class AdminLayoutComposer
{
    public function compose(View $view): void
    {
        $user = auth()->user();
        $count = $user ? AdminNotifications::unreadCount($user) : 0;
        $view->with('adminNotificationCount', $count);
    }
}
