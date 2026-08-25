<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\NewsReaction;
use App\Support\ReactionType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NewsReactionController extends Controller
{
    public function toggle(Request $request, News $news): RedirectResponse
    {
        if (! NewsReaction::tableExists()) {
            return redirect()->route('news.show', $news)
                ->with('error', __('Reactions are not available yet. Please run database migrations.'));
        }

        if (! $news->isPubliclyVisible() && (! auth()->check() || ! auth()->user()->is_admin)) {
            abort(404);
        }

        $user = auth()->user();
        if ($user->is_banned) {
            return redirect()->route('news.show', $news)
                ->with('error', __('Your account cannot react to articles.'));
        }

        $validated = $request->validate([
            'type' => 'required|string|in:'.implode(',', ReactionType::all()),
        ]);

        $type = (string) $validated['type'];

        $existing = NewsReaction::query()
            ->where('news_id', $news->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            if ($existing->type === $type) {
                $existing->delete();

                return redirect()->route('news.show', $news)
                    ->with('success', __('Reaction removed.'));
            }

            $existing->update(['type' => $type]);

            return redirect()->route('news.show', $news)
                ->with('success', __('Reaction updated.'));
        }

        NewsReaction::query()->create([
            'news_id' => $news->id,
            'user_id' => $user->id,
            'type' => $type,
        ]);

        return redirect()->route('news.show', $news)
            ->with('success', __('Reaction added.'));
    }
}
