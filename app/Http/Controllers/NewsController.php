<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\NewsCategory;
use App\Models\News;
use App\Models\NewsReaction;
use App\Models\User;
use App\Support\ActivityLogger;
use App\Services\ThemeManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class NewsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->allNews(request());
    }

    public function home()
    {
        $query = News::query()
            ->with(['user'])
            ->withCount('comments')
            ->publiclyVisible();

        if (Schema::hasColumn('news', 'published_at')) {
            $query->orderByRaw('COALESCE(published_at, created_at) DESC');
        } else {
            $query->orderByDesc('created_at');
        }

        $news = $query->limit(3)->get();

        return view('news.home', [
            'news' => $news,
            'homeServices' => $this->homeServiceCards(),
        ]);
    }

    /**
     * @return list<array{icon: string, title: string, text: string}>
     */
    protected function homeServiceCards(): array
    {
        if (app(ThemeManager::class)->activeSlug() === 'handwerk') {
            return [
                [
                    'icon' => 'fa-snowflake',
                    'title' => __('Air conditioning & heat pumps'),
                    'text' => __('Split units, VRF systems and heat pumps — planned, installed and serviced.'),
                ],
                [
                    'icon' => 'fa-temperature-low',
                    'title' => __('Commercial refrigeration'),
                    'text' => __('Cold rooms, refrigeration cabinets and industrial cooling to specification.'),
                ],
                [
                    'icon' => 'fa-fan',
                    'title' => __('Ventilation & maintenance'),
                    'text' => __('Ventilation technology, leak checks, maintenance contracts and emergency service.'),
                ],
            ];
        }

        return [
            [
                'icon' => 'fa-wrench',
                'title' => __('Installation & repair'),
                'text' => __('Professional execution for new builds and renovations.'),
            ],
            [
                'icon' => 'fa-house-chimney',
                'title' => __('Residential projects'),
                'text' => __('Solutions tailored to your property.'),
            ],
            [
                'icon' => 'fa-building',
                'title' => __('Commercial clients'),
                'text' => __('Maintenance contracts and project support.'),
            ],
        ];
    }

    public function allNews(Request $request)
    {
        $selectedCategory = trim((string) $request->query('category', ''));

        $query = News::query()
            ->with(['user'])
            ->withCount('comments')
            ->publiclyVisible();

        if ($selectedCategory !== '') {
            $query->where('category', $selectedCategory);
        }

        if (Schema::hasColumn('news', 'published_at')) {
            $query->orderByRaw('COALESCE(published_at, created_at) DESC');
        } else {
            $query->orderByDesc('created_at');
        }

        $news = $query->paginate(12)->withQueryString();

        $categories = NewsCategory::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->pluck('name');

        return view('news.index', compact('news', 'categories', 'selectedCategory'));
    }

    /**
     * Display the specified resource.
     */
    public function show(News $news)
    {
        if (! $news->isPubliclyVisible() && (! auth()->check() || ! auth()->user()->is_admin)) {
            abort(404);
        }

        $isAdminViewer = auth()->check() && auth()->user()->is_admin;
        $news->load([
            'user',
            'rootComments' => function ($query) use ($isAdminViewer): void {
                if (\App\Models\Comment::supportsModeration() && ! $isAdminViewer) {
                    $query->where('moderation_status', 'approved');
                }
                $query->orderBy('created_at');
            },
            'rootComments.user',
            'rootComments.replies' => function ($query) use ($isAdminViewer): void {
                if (\App\Models\Comment::supportsModeration() && ! $isAdminViewer) {
                    $query->where('moderation_status', 'approved');
                }
                $query->orderBy('created_at');
            },
            'rootComments.replies.user',
        ]);

        if (\App\Models\Comment::supportsModeration() && ! $isAdminViewer) {
            $news->setRelation('comments', $news->comments()->where('moderation_status', 'approved')->get());
        } else {
            $news->setRelation('comments', $news->comments()->get());
        }

        $teamMembers = User::query()
            ->when(
                Schema::hasColumn('users', 'role'),
                static fn (Builder $query) => $query->where(function (Builder $inner): void {
                    $inner->whereIn('role', [
                        User::ROLE_OWNER,
                        User::ROLE_ADMIN,
                        User::ROLE_MODERATOR,
                    ])->orWhere('is_admin', true);
                }),
                static fn (Builder $query) => $query->where('is_admin', true)
            )
            ->where('is_banned', false)
            ->orderByDesc('created_at')
            ->limit(9)
            ->get(['id', 'name', 'avatar', 'task']);

        $latestNews = News::query()
            ->publiclyVisible()
            ->where('id', '!=', $news->id)
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->limit(6)
            ->get(['id', 'title', 'image', 'content', 'created_at', 'published_at']);

        $reactionCounts = NewsReaction::countsForNews($news->id);
        $userReaction = auth()->check()
            ? NewsReaction::userReactionType($news->id, (int) auth()->id())
            : null;

        return view('news.show', compact('news', 'teamMembers', 'latestNews', 'reactionCounts', 'userReaction'));
    }

    /**
     * Store a comment for the specified news.
     */
    public function storeComment(Request $request, News $news)
    {
        if (! $news->commentsEnabled()) {
            return redirect()->route('news.show', $news)
                ->with('error', __('Comments are disabled for this article.'));
        }

        if (auth()->user()->is_banned) {
            return redirect()->route('news.show', $news)
                ->with('error', __('Your account cannot post comments.'));
        }

        $validated = $request->validate([
            'content' => 'required|string|max:1000',
            'parent_id' => 'nullable|integer|exists:comments,id',
        ]);

        $parentId = null;
        if (! empty($validated['parent_id'])) {
            $parent = Comment::query()
                ->where('news_id', $news->id)
                ->whereNull('parent_id')
                ->whereKey($validated['parent_id'])
                ->first();

            if (! $parent) {
                return redirect()->route('news.show', $news)
                    ->withErrors(['content' => __('You can only reply to top-level comments.')]);
            }

            $parentId = $parent->id;
        }

        $moderationStatus = 'approved';
        $moderationScore = null;
        $moderationFlags = null;

        if (Comment::supportsModeration()) {
            $serviceClass = \Plugins\CommentModerationAI\Services\CommentModerationService::class;
            if (class_exists($serviceClass)) {
                $decision = app($serviceClass)->evaluate((string) $validated['content']);
                $moderationStatus = in_array(($decision['status'] ?? ''), ['approved', 'pending', 'rejected'], true)
                    ? (string) $decision['status']
                    : 'approved';
                $moderationScore = isset($decision['score']) ? (int) $decision['score'] : null;
                $moderationFlags = is_array($decision['flags'] ?? null) ? $decision['flags'] : null;
            }
        }

        $comment = $news->comments()->create([
            'user_id' => auth()->id(),
            'parent_id' => $parentId,
            'content' => $validated['content'],
            'moderation_status' => $moderationStatus,
            'moderation_score' => $moderationScore,
            'moderation_flags' => $moderationFlags,
        ]);

        ActivityLogger::log(
            'comment.created',
            \Illuminate\Support\Str::limit(strip_tags($comment->content), 80),
            $comment,
            ['news_id' => $news->id, 'news_title' => $news->title]
        );

        if (Comment::supportsModeration() && $moderationStatus === 'pending') {
            return redirect()->route('news.show', $news)
                ->with('info', __('Your comment is awaiting moderation.'));
        }

        if (Comment::supportsModeration() && $moderationStatus === 'rejected') {
            return redirect()->route('news.show', $news)
                ->with('error', __('Your comment was rejected by moderation.'));
        }

        return redirect()->route('news.show', $news)
            ->with('success', __('Comment added successfully!'));
    }

    /**
     * Delete a comment.
     */
    public function destroyComment(News $news, Comment $comment)
    {
        if ((int) $comment->news_id !== (int) $news->id) {
            abort(404);
        }

        if (auth()->id() !== $comment->user_id && ! auth()->user()->is_admin) {
            abort(403);
        }

        ActivityLogger::log(
            'comment.deleted',
            \Illuminate\Support\Str::limit(strip_tags($comment->content), 80),
            $comment,
            ['news_id' => $news->id, 'news_title' => $news->title]
        );

        $comment->delete();

        return redirect()->route('news.show', $news)
            ->with('success', __('Comment deleted successfully!'));
    }
}
