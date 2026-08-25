<?php

namespace App\Support;

use Illuminate\Support\Collection;

final class PageBlockRenderer
{
    /**
     * @param  iterable<int, object>  $teamMembers
     */
    public static function renderTeamList(iterable $teamMembers): string
    {
        $members = collect($teamMembers)->take(9);
        if ($members->isEmpty()) {
            return '<div class="text-sm text-gray-500">'.e(__('No team members yet')).'</div>';
        }

        $cards = $members->map(static function ($member): string {
            $name = e((string) ($member->name ?? ''));
            $task = e((string) ($member->task ?? ''));
            $avatar = (string) ($member->avatar ?? '');
            $avatarHtml = $avatar !== ''
                ? '<img src="'.e(asset('storage/'.$avatar)).'" alt="'.$name.'" class="w-12 h-12 rounded-full object-cover">'
                : '<span class="w-12 h-12 rounded-full bg-primary-100 text-primary-600 inline-flex items-center justify-center font-semibold">'.e(mb_substr((string) ($member->name ?? 'U'), 0, 1)).'</span>';

            return '
                    <div class="rounded-lg border border-gray-200 dark:border-dark-700 p-4 sm:p-5 bg-white dark:bg-dark-800 w-full min-w-0">
                        <div class="flex items-center gap-3 sm:gap-4">
                            <div class="shrink-0">'.$avatarHtml.'</div>
                            <div class="min-w-0 flex-1">
                                <div class="text-sm sm:text-base font-semibold text-gray-900 dark:text-white break-words">'.$name.'</div>
                                '.($task !== '' ? '<div class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 break-words mt-1">'.$task.'</div>' : '').'
                            </div>
                        </div>
                    </div>
                ';
        })->implode('');

        return '<div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-5 w-full">'.$cards.'</div>';
    }

    /**
     * @param  iterable<int, object>  $latestNews
     */
    public static function renderNewsBoxes(iterable $latestNews, int $limit): string
    {
        $items = collect($latestNews)->take($limit);
        if ($items->isEmpty()) {
            return '<div class="text-sm text-gray-500">'.e(__('No news articles available.')).'</div>';
        }

        $cards = $items->map(static function ($article): string {
            $title = e((string) ($article->title ?? ''));
            $url = e(route('news.show', $article));
            $image = (string) ($article->image ?? '');
            $excerpt = e(\Illuminate\Support\Str::limit(strip_tags((string) ($article->content ?? '')), 120));
            $imageHtml = $image !== ''
                ? '<img src="'.e(asset('storage/'.$image)).'" alt="'.$title.'" class="w-full h-40 object-cover">'
                : '<div class="w-full h-40 shrink-0 bg-gradient-to-br from-primary-500/20 to-purple-500/20 flex items-center justify-center" role="presentation"><i class="fas fa-newspaper text-4xl text-primary-500/30" aria-hidden="true"></i></div>';
            $readMore = e(__('Read More'));

            return '
                    <article class="rounded-lg overflow-hidden border border-gray-200 dark:border-dark-700 bg-white dark:bg-dark-800 flex flex-col">
                        '.$imageHtml.'
                        <div class="p-4 flex flex-col flex-1">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-2 line-clamp-2"><a href="'.$url.'" class="hover:text-primary-600">'.$title.'</a></h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-3 flex-1">'.$excerpt.'</p>
                            <a href="'.$url.'" class="inline-flex items-center gap-1.5 text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 text-xs font-semibold mt-3">
                                '.$readMore.'
                                <i class="fas fa-arrow-right text-[10px]" aria-hidden="true"></i>
                            </a>
                        </div>
                    </article>
                ';
        })->implode('');

        return '<div class="grid gap-4 md:grid-cols-'.($limit >= 3 ? '3' : '2').'">'.$cards.'</div>';
    }

    /**
     * @param  iterable<int, object>|Collection<int, object>  $teamMembers
     * @param  iterable<int, object>|Collection<int, object>  $latestNews
     */
    public static function renderTextWithButtons(?string $value, iterable $teamMembers, iterable $latestNews): string
    {
        $html = (string) $value;
        $html = str_replace('{{team_list}}', self::renderTeamList($teamMembers), $html);
        $html = str_replace('{{news_boxes_3}}', self::renderNewsBoxes($latestNews, 3), $html);
        $html = str_replace('{{news_boxes_6}}', self::renderNewsBoxes($latestNews, 6), $html);
        $viewerName = auth()->check() ? (string) (auth()->user()->name ?? '') : '';
        $viewerName = trim($viewerName) !== '' ? $viewerName : (string) __('Visitor');
        $html = str_replace('{{current_user_name}}', e($viewerName), $html);
        $html = preg_replace_callback(
            '/!\[img\|(sm|md|lg)\]\(((?:https?:\/\/|\/)[^)]+)\)/',
            static function (array $matches): string {
                $size = strtolower((string) ($matches[1] ?? 'md'));
                if (! in_array($size, ['sm', 'md', 'lg'], true)) {
                    $size = 'md';
                }
                $url = e($matches[2] ?? '');
                $sizeClass = $size === 'sm'
                    ? 'w-1/4'
                    : ($size === 'lg' ? 'w-3/4' : 'w-1/2');

                return '<img src="'.$url.'" alt="" class="'.$sizeClass.' h-auto rounded my-2">';
            },
            $html
        ) ?? $html;

        return preg_replace_callback(
            '/\[([^\]]+)\]\(((?:https?:\/\/|\/)[^)]+)\)/',
            static function (array $matches): string {
                $labelRaw = (string) ($matches[1] ?? '');
                $size = 'md';
                $color = 'primary';
                if (str_contains($labelRaw, '|')) {
                    $parts = explode('|', $labelRaw);
                    $labelRaw = (string) ($parts[0] ?? $labelRaw);
                    foreach (array_slice($parts, 1) as $token) {
                        $candidate = strtolower(trim((string) $token));
                        if (in_array($candidate, ['sm', 'md', 'lg'], true)) {
                            $size = $candidate;
                        }
                        if (in_array($candidate, ['primary', 'secondary', 'outline', 'none'], true)) {
                            $color = $candidate;
                        }
                    }
                }

                $label = e($labelRaw);
                $url = e($matches[2] ?? '');
                $sizeClass = $size === 'sm'
                    ? 'px-2 py-0.5 text-[10px]'
                    : ($size === 'lg' ? 'px-4 py-2 text-sm' : 'px-3 py-1.5 text-xs');
                $colorClass = $color === 'secondary'
                    ? 'bg-gray-600 text-white'
                    : ($color === 'outline'
                        ? 'bg-transparent text-primary-600 border border-primary-500'
                        : ($color === 'none' ? 'bg-transparent text-primary-600' : 'bg-primary-600 text-white'));

                return '<a href="'.$url.'" class="inline-flex items-center rounded-lg hover:opacity-90 mx-0.5 my-0.5 '.$sizeClass.' '.$colorClass.'">'.$label.'</a>';
            },
            $html
        ) ?? $html;
    }
}
