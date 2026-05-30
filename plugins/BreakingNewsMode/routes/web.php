<?php

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Plugins\BreakingNewsMode\Services\BreakingNewsService;

Route::middleware(['web', 'auth', 'admin'])
    ->prefix('admin/breaking-news')
    ->name('admin.breaking-news.')
    ->group(function (): void {
        Route::get('/', function (BreakingNewsService $service) {
            if (! auth()->user()->hasAdminPermission('news')) {
                abort(403);
            }

            return view()->file(__DIR__.'/../resources/views/index.blade.php', [
                'config' => $service->config(),
            ]);
        })->name('index');

        Route::post('/', function (Request $request, BreakingNewsService $service) {
            if (! auth()->user()->hasAdminPermission('news')) {
                abort(403);
            }

            $validated = $request->validate([
                'enabled' => ['nullable', 'boolean'],
                'badge' => ['nullable', 'string', 'max:40'],
                'title' => ['nullable', 'string', 'max:190'],
                'text' => ['nullable', 'string', 'max:2000'],
                'url' => ['nullable', 'url', 'max:2048'],
                'theme' => ['required', 'in:red,amber,orange,blue'],
                'display_mode' => ['required', 'in:banner,popup'],
            ]);

            Setting::setValue('breaking_news_mode_config', json_encode([
                'enabled' => $request->has('enabled'),
                'badge' => trim((string) ($validated['badge'] ?? '')),
                'title' => trim((string) ($validated['title'] ?? '')),
                'text' => trim((string) ($validated['text'] ?? '')),
                'url' => trim((string) ($validated['url'] ?? '')),
                'theme' => (string) ($validated['theme'] ?? 'red'),
                'display_mode' => (string) ($validated['display_mode'] ?? 'banner'),
            ], JSON_UNESCAPED_UNICODE));

            return back()->with('success', __('Breaking News Mode updated.'));
        })->name('update');
    });
