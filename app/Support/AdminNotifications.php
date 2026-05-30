<?php

namespace App\Support;

use App\Models\Comment;
use App\Models\News;
use App\Models\User;
use Carbon\Carbon;

class AdminNotifications
{
    /**
     * Zeitpunkt, ab dem Einträge als „neu“ für Badge & Kennzeichnung gelten.
     * Pro Benutzer in der DB (überlebt Logout).
     */
    public static function sinceForUnread(User $user): Carbon
    {
        if ($user->admin_notif_last_seen_at) {
            return Carbon::parse($user->admin_notif_last_seen_at);
        }

        return Carbon::now()->subDays(7);
    }

    public static function unreadCount(User $user): int
    {
        $since = static::sinceForUnread($user);

        return News::query()->where('created_at', '>', $since)->count()
            + Comment::query()->where('created_at', '>', $since)->count()
            + User::query()->where('created_at', '>', $since)->count();
    }

    public static function markAllRead(User $user): void
    {
        $user->admin_notif_last_seen_at = now();
        $user->save();
    }

    public static function feedFloor(User $user): ?Carbon
    {
        if (! $user->admin_notif_feed_floor_at) {
            return null;
        }

        return Carbon::parse($user->admin_notif_feed_floor_at);
    }

    /**
     * Verlauf leeren: Badge auf 0, Liste zeigt nur noch künftige Aktivität.
     */
    public static function clearHistory(User $user): void
    {
        $now = now();
        $user->admin_notif_last_seen_at = $now;
        $user->admin_notif_feed_floor_at = $now;
        $user->save();
    }

    public static function forgetFeedFloor(User $user): void
    {
        $user->admin_notif_feed_floor_at = null;
        $user->save();
    }
}
