<?php

use App\Models\Comment;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Plugins\CommentModerationAI\Services\CommentModerationService;

Route::middleware(['web', 'auth', 'admin'])
    ->prefix('admin/comment-moderation')
    ->name('admin.comment-moderation.')
    ->group(function (): void {
        Route::get('/', function (CommentModerationService $service) {
            if (! auth()->user()->hasAdminPermission('news')) {
                abort(403);
            }

            if (! Comment::supportsModeration()) {
                $emptyPaginator = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
                $emptyPaginator->setPath(request()->url());

                return view()->file(__DIR__.'/../resources/views/index.blade.php', [
                    'pendingComments' => $emptyPaginator,
                    'config' => $service->config(),
                ])->with('info', __('Run migrations to enable comment moderation columns.'));
            }

            $pendingComments = Comment::query()
                ->with(['user:id,name', 'news:id,title'])
                ->where('moderation_status', 'pending')
                ->latest()
                ->paginate(20);

            return view()->file(__DIR__.'/../resources/views/index.blade.php', [
                'pendingComments' => $pendingComments,
                'config' => $service->config(),
            ]);
        })->name('index');

        Route::post('/settings', function (Request $request) {
            if (! auth()->user()->hasAdminPermission('news')) {
                abort(403);
            }

            $validated = $request->validate([
                'pending_threshold' => ['required', 'integer', 'min:0', 'max:100'],
                'reject_threshold' => ['required', 'integer', 'min:0', 'max:100'],
                'max_links' => ['required', 'integer', 'min:0', 'max:10'],
                'toxic_words' => ['nullable', 'string', 'max:4000'],
            ]);

            $pending = (int) $validated['pending_threshold'];
            $reject = max((int) $validated['reject_threshold'], $pending);
            $maxLinks = (int) $validated['max_links'];
            $words = preg_split('/[\r\n,;]+/', (string) ($validated['toxic_words'] ?? ''), -1, PREG_SPLIT_NO_EMPTY);
            $normalizedWords = collect($words ?: [])
                ->map(static fn ($word) => mb_strtolower(trim((string) $word)))
                ->filter()
                ->unique()
                ->values()
                ->all();

            if ($normalizedWords === []) {
                $normalizedWords = app(CommentModerationService::class)->config()['toxic_words'];
            }

            Setting::setValue('comment_moderation_ai_config', json_encode([
                'pending_threshold' => $pending,
                'reject_threshold' => $reject,
                'max_links' => $maxLinks,
                'toxic_words' => $normalizedWords,
            ], JSON_UNESCAPED_UNICODE));

            return back()->with('success', __('Comment moderation settings updated.'));
        })->name('settings');

        Route::post('/{comment}/approve', function (Request $request, Comment $comment) {
            if (! auth()->user()->hasAdminPermission('news')) {
                abort(403);
            }
            if (Comment::supportsModeration()) {
                $comment->forceFill([
                    'moderation_status' => 'approved',
                    'moderated_at' => now(),
                    'moderated_by_user_id' => auth()->id(),
                ])->save();
            }

            return back()->with('success', __('Comment approved.'));
        })->name('approve');

        Route::post('/{comment}/reject', function (Request $request, Comment $comment) {
            if (! auth()->user()->hasAdminPermission('news')) {
                abort(403);
            }
            if (Comment::supportsModeration()) {
                $comment->forceFill([
                    'moderation_status' => 'rejected',
                    'moderated_at' => now(),
                    'moderated_by_user_id' => auth()->id(),
                ])->save();
            }

            return back()->with('success', __('Comment rejected.'));
        })->name('reject');
    });
