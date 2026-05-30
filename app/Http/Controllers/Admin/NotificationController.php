<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\News;
use App\Models\User;
use App\Support\AdminNotifications;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class NotificationController extends Controller
{
    private const LIST_LIMIT = 10;

    /**
     * Aktuelle Einträge für das Benachrichtigungs-Dropdown (JSON).
     */
    public function feed(): JsonResponse
    {
        $user = auth()->user();
        $since = AdminNotifications::sinceForUnread($user);
        $floor = AdminNotifications::feedFloor($user);

        $postsQuery = News::query()
            ->with('user')
            ->when($floor, fn ($q) => $q->where('created_at', '>', $floor))
            ->orderByDesc('created_at')
            ->limit(self::LIST_LIMIT);

        $posts = $postsQuery
            ->get()
            ->map(fn (News $n) => [
                'id' => $n->id,
                'title' => $n->title,
                'published' => (bool) $n->published,
                'is_new' => $n->created_at->gt($since),
                'created_at_human' => $n->created_at->diffForHumans(),
                'url' => route('admin.news.edit', $n),
            ]);

        $comments = Comment::query()
            ->with(['user', 'news'])
            ->when($floor, fn ($q) => $q->where('created_at', '>', $floor))
            ->orderByDesc('created_at')
            ->limit(self::LIST_LIMIT)
            ->get()
            ->map(fn (Comment $c) => [
                'id' => $c->id,
                'excerpt' => Str::limit(strip_tags($c->content), 90),
                'user_name' => $c->user?->name ?? '?',
                'news_title' => $c->news?->title ?? '?',
                'is_new' => $c->created_at->gt($since),
                'created_at_human' => $c->created_at->diffForHumans(),
                'url' => $c->news
                    ? route('admin.news.show', $c->news)
                    : route('admin.dashboard'),
            ]);

        $users = User::query()
            ->when($floor, fn ($q) => $q->where('created_at', '>', $floor))
            ->orderByDesc('created_at')
            ->limit(self::LIST_LIMIT)
            ->get()
            ->map(fn (User $u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'is_new' => $u->created_at->gt($since),
                'created_at_human' => $u->created_at->diffForHumans(),
                'url' => route('admin.users.edit', $u),
            ]);

        return response()->json([
            'posts' => $posts,
            'comments' => $comments,
            'users' => $users,
            'unread_count' => AdminNotifications::unreadCount($user),
            'cleared_since' => $floor !== null,
        ]);
    }

    /**
     * Nach dem Öffnen des Panels: alles bis jetzt als gelesen markieren.
     */
    public function markRead(): JsonResponse
    {
        AdminNotifications::markAllRead(auth()->user());

        return response()->json(['ok' => true]);
    }

    /**
     * Angezeigten Verlauf leeren; Badge wird zurückgesetzt (pro Benutzer in der DB).
     */
    public function clearHistory(): JsonResponse
    {
        AdminNotifications::clearHistory(auth()->user());

        return response()->json([
            'ok' => true,
            'unread_count' => 0,
            'posts' => [],
            'comments' => [],
            'users' => [],
            'cleared_since' => true,
        ]);
    }
}
