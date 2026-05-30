{{-- Rich HTML (Quill) or legacy plain text --}}
@php
    $c = $content ?? '';
    $renderTeamListToken = static function ($teamMembers): string {
        $members = collect($teamMembers ?? [])->take(9);
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
    };
    $renderNewsBoxesToken = static function ($latestNews, int $limit): string {
        $items = collect($latestNews ?? [])->take($limit);
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
    };
    $viewerName = auth()->check() ? (string) (auth()->user()->name ?? '') : '';
    $viewerName = trim($viewerName) !== '' ? $viewerName : (string) __('Visitor');
    $c = str_replace('{{team_list}}', $renderTeamListToken($teamMembers ?? collect()), $c);
    $c = str_replace('{{news_boxes_3}}', $renderNewsBoxesToken($latestNews ?? collect(), 3), $c);
    $c = str_replace('{{news_boxes_6}}', $renderNewsBoxesToken($latestNews ?? collect(), 6), $c);
    $c = str_replace('{{current_user_name}}', e($viewerName), $c);
@endphp
@if(preg_match('/<\s*(p|h[1-6]|ul|ol|li|blockquote|strong|em|b|i|u|s)\b/i', $c))
    {!! $c !!}
@else
    {!! nl2br(e($c)) !!}
@endif
