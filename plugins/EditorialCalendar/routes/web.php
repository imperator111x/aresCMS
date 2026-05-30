<?php

use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'admin'])->prefix('admin/editorial-calendar')->name('admin.editorial-calendar.')->group(function (): void {
    Route::get('/', function () {
        if (! auth()->user()->hasAdminPermission('news')) {
            abort(403);
        }

        return view()->file(__DIR__.'/../resources/views/index.blade.php');
    })->name('index');

    Route::get('/events', function (Request $request) {
        if (! auth()->user()->hasAdminPermission('news')) {
            abort(403);
        }

        $status = trim((string) $request->query('status', ''));
        $category = trim((string) $request->query('category', ''));

        $query = News::query()->with('user')->orderByDesc('published_at')->orderByDesc('created_at');

        if ($status === 'draft') {
            $query->where('published', false);
        } elseif ($status === 'scheduled') {
            $query->where('published', true)->whereNotNull('published_at')->where('published_at', '>', now());
        } elseif ($status === 'published') {
            $query->where('published', true)->where(function ($q): void {
                $q->whereNull('published_at')->orWhere('published_at', '<=', now());
            });
        }

        if ($category !== '') {
            $query->where('category', $category);
        }

        $newsItems = $query->get(['id', 'title', 'published', 'published_at', 'created_at', 'category', 'user_id']);

        $events = $newsItems->map(function (News $news): array {
            $start = $news->published_at ?? $news->created_at;
            $isScheduled = $news->published && $news->published_at && $news->published_at->isFuture();
            $isPublished = $news->published && (! $news->published_at || $news->published_at->isPast());
            $status = ! $news->published ? 'draft' : ($isScheduled ? 'scheduled' : ($isPublished ? 'published' : 'draft'));
            $color = $status === 'draft' ? '#94a3b8' : ($status === 'scheduled' ? '#f59e0b' : '#10b981');

            return [
                'id' => (string) $news->id,
                'title' => $news->title,
                'start' => optional($start)->toIso8601String(),
                'allDay' => false,
                'backgroundColor' => $color,
                'borderColor' => $color,
                'extendedProps' => [
                    'status' => $status,
                    'category' => $news->category ?: '—',
                    'author' => $news->user?->name ?: '—',
                    'editUrl' => route('admin.news.edit', $news),
                ],
            ];
        })->values();

        $categories = News::query()
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category')
            ->values();

        return response()->json([
            'events' => $events,
            'categories' => $categories,
        ]);
    })->name('events');

    Route::patch('/news/{news}/schedule', function (Request $request, News $news) {
        if (! auth()->user()->hasAdminPermission('news')) {
            abort(403);
        }

        $validated = $request->validate([
            'published_at' => ['required', 'date'],
        ]);

        $news->published_at = $validated['published_at'];
        $news->published = true;
        $news->save();

        return response()->json([
            'ok' => true,
            'message' => 'Termin gespeichert.',
        ]);
    })->name('schedule');
});

