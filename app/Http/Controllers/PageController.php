<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\News;
use App\Models\Page;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class PageController extends Controller
{
    public function show(string $slug): View
    {
        $page = Page::query()
            ->where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();
        $formIds = collect((array) $page->blocks)
            ->map(static fn ($block) => is_array($block) ? (int) ($block['form_id'] ?? 0) : 0)
            ->filter(static fn (int $id) => $id > 0)
            ->unique()
            ->values()
            ->all();
        $formsById = Form::query()
            ->whereIn('id', $formIds)
            ->where('is_active', true)
            ->get()
            ->keyBy('id');
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
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->limit(6)
            ->get(['id', 'title', 'image', 'content', 'created_at', 'published_at']);

        return view('page.show', [
            'page' => $page,
            'formsById' => $formsById,
            'teamMembers' => $teamMembers,
            'latestNews' => $latestNews,
            'turnstileSiteKey' => config('services.cloudflare.turnstile.site_key'),
        ]);
    }
}

