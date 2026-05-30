<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class SearchController extends Controller
{
    private const SUGGEST_LIMIT = 8;

    /**
     * Suche in Nachrichten (Titel/Inhalt) und Benutzern (Name/E-Mail).
     */
    public function index(Request $request): View
    {
        $query = trim((string) $request->get('q', ''));
        $minLength = 2;

        $newsResults = collect();
        $userResults = collect();

        $like = $this->likePattern($query);
        if ($like !== null) {
            $hasCategory = Schema::hasColumn('news', 'category');
            $newsResults = News::query()
                ->with('user')
                ->where(function ($q) use ($like, $hasCategory) {
                    $q->where('title', 'like', $like)
                        ->orWhere('content', 'like', $like);
                    if ($hasCategory) {
                        $q->orWhere('category', 'like', $like);
                    }
                })
                ->orderByDesc('updated_at')
                ->limit(25)
                ->get();

            $userResults = User::query()
                ->where(function ($q) use ($like) {
                    $q->where('name', 'like', $like)
                        ->orWhere('email', 'like', $like);
                })
                ->orderBy('name')
                ->limit(25)
                ->get();
        }

        return view('admin.search', [
            'query' => $query,
            'minLength' => $minLength,
            'newsResults' => $newsResults,
            'userResults' => $userResults,
        ]);
    }

    /**
     * JSON-Vorschläge für die Admin-Suchleiste (beim Tippen).
     */
    public function suggestions(Request $request): JsonResponse
    {
        $query = trim((string) $request->get('q', ''));
        $like = $this->likePattern($query);

        if ($like === null) {
            return response()->json(['news' => [], 'users' => []]);
        }

        $hasCategory = Schema::hasColumn('news', 'category');
        $news = News::query()
            ->select(['id', 'title', 'published', 'updated_at'])
            ->where(function ($q) use ($like, $hasCategory) {
                $q->where('title', 'like', $like)
                    ->orWhere('content', 'like', $like);
                if ($hasCategory) {
                    $q->orWhere('category', 'like', $like);
                }
            })
            ->orderByDesc('updated_at')
            ->limit(self::SUGGEST_LIMIT)
            ->get()
            ->map(fn (News $article) => [
                'id' => $article->id,
                'title' => $article->title,
                'published' => (bool) $article->published,
                'url' => route('admin.news.edit', $article),
            ]);

        $users = User::query()
            ->select(['id', 'name', 'email'])
            ->where(function ($q) use ($like) {
                $q->where('name', 'like', $like)
                    ->orWhere('email', 'like', $like);
            })
            ->orderBy('name')
            ->limit(self::SUGGEST_LIMIT)
            ->get()
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'url' => route('admin.users.edit', $user),
            ]);

        return response()->json([
            'news' => $news,
            'users' => $users,
        ]);
    }

    /**
     * LIKE-Muster ab mindestens 2 Zeichen, sonst null.
     */
    private function likePattern(string $query): ?string
    {
        $query = trim($query);
        if (mb_strlen($query) < 2) {
            return null;
        }

        return '%'.addcslashes($query, '%_\\').'%';
    }
}
