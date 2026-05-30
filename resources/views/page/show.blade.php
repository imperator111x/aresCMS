@extends('layouts.app')

@section('title', $page->title)

@section('content')
    @php
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
        $renderTextWithButtons = static function (?string $value) use ($renderTeamListToken, $renderNewsBoxesToken, $teamMembers, $latestNews): string {
            $html = (string) $value;
            $html = str_replace('{{team_list}}', $renderTeamListToken($teamMembers ?? collect()), $html);
            $html = str_replace('{{news_boxes_3}}', $renderNewsBoxesToken($latestNews ?? collect(), 3), $html);
            $html = str_replace('{{news_boxes_6}}', $renderNewsBoxesToken($latestNews ?? collect(), 6), $html);
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
            );
        };
    @endphp
    @php
        $heroEnabled = (bool) ($page->show_hero ?? false);
        $heroBadge = (string) ($page->hero_badge ?? '');
        $heroHeading = (string) ($page->hero_heading ?? '');
        $heroSubheading = (string) ($page->hero_subheading ?? '');
        $heroTheme = (string) ($page->hero_theme ?? 'blue');
        $heroBackgroundImage = (string) ($page->hero_background_image ?? '');
        $heroOverlayStrength = (string) ($page->hero_overlay_strength ?? 'medium');
        $heroHeight = (string) ($page->hero_height ?? 'md');
        $heroPrimaryButtonText = (string) ($page->hero_primary_button_text ?? '');
        $heroPrimaryButtonUrl = (string) ($page->hero_primary_button_url ?? '');
        $heroSecondaryButtonText = (string) ($page->hero_secondary_button_text ?? '');
        $heroSecondaryButtonUrl = (string) ($page->hero_secondary_button_url ?? '');
        $heroThemes = [
            'blue' => [
                'gradient' => 'from-primary-500/10 via-transparent to-blue-500/10',
                'badge' => 'bg-primary-500/10 border-primary-500/20',
                'dot' => 'bg-primary-500',
                'text' => 'text-primary-600 dark:text-primary-400',
            ],
            'green' => [
                'gradient' => 'from-emerald-500/10 via-transparent to-green-500/10',
                'badge' => 'bg-emerald-500/10 border-emerald-500/20',
                'dot' => 'bg-emerald-500',
                'text' => 'text-emerald-600 dark:text-emerald-400',
            ],
            'purple' => [
                'gradient' => 'from-purple-500/10 via-transparent to-fuchsia-500/10',
                'badge' => 'bg-purple-500/10 border-purple-500/20',
                'dot' => 'bg-purple-500',
                'text' => 'text-purple-600 dark:text-purple-400',
            ],
            'orange' => [
                'gradient' => 'from-orange-500/10 via-transparent to-amber-500/10',
                'badge' => 'bg-orange-500/10 border-orange-500/20',
                'dot' => 'bg-orange-500',
                'text' => 'text-orange-600 dark:text-orange-400',
            ],
        ];
        $heroThemeClasses = $heroThemes[$heroTheme] ?? $heroThemes['blue'];
        $heroOverlayClass = $heroOverlayStrength === 'light'
            ? 'bg-black/20'
            : ($heroOverlayStrength === 'strong' ? 'bg-black/60' : 'bg-black/40');
        $heroHeightClass = $heroHeight === 'sm'
            ? 'py-14 md:py-16'
            : ($heroHeight === 'lg'
                ? 'py-28 md:py-36'
                : ($heroHeight === 'full' ? 'min-h-[80vh] flex flex-col justify-center' : 'py-20 md:py-24'));
        $heroTitleClass = $heroBackgroundImage !== '' ? 'text-white' : 'text-gray-900 dark:text-white';
        $heroSubheadingClass = $heroBackgroundImage !== '' ? 'text-white/90' : 'text-gray-600 dark:text-gray-400';
    @endphp

    @if($heroEnabled)
        <section class="relative overflow-hidden" @if($heroBackgroundImage !== '') style="background-image:url('{{ e($heroBackgroundImage) }}');background-size:cover;background-position:center;" @endif>
            <div class="absolute inset-0 bg-gradient-to-br {{ $heroThemeClasses['gradient'] }}"></div>
            @if($heroBackgroundImage !== '')
                <div class="absolute inset-0 {{ $heroOverlayClass }}"></div>
            @endif
            <div class="relative max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 {{ $heroHeightClass }}">
                <div class="text-center">
                    @if($heroBadge !== '')
                        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full border mb-8 {{ $heroThemeClasses['badge'] }}">
                            <span class="w-2 h-2 rounded-full animate-pulse {{ $heroThemeClasses['dot'] }}"></span>
                            <span class="text-sm font-medium {{ $heroThemeClasses['text'] }}">{{ $heroBadge }}</span>
                        </div>
                    @endif
                    <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-6 {{ $heroTitleClass }}">
                        {{ $heroHeading !== '' ? $heroHeading : $page->title }}
                    </h1>
                    @if($heroSubheading !== '')
                        <p class="text-xl max-w-2xl mx-auto {{ $heroSubheadingClass }}">
                            {{ $heroSubheading }}
                        </p>
                    @endif
                    @if($heroPrimaryButtonText !== '' || $heroSecondaryButtonText !== '')
                        <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                            @if($heroPrimaryButtonText !== '')
                                <a href="{{ $heroPrimaryButtonUrl !== '' ? $heroPrimaryButtonUrl : '#' }}" class="inline-flex items-center rounded-lg px-5 py-2.5 text-sm bg-primary-600 hover:bg-primary-700 text-white">
                                    {{ $heroPrimaryButtonText }}
                                </a>
                            @endif
                            @if($heroSecondaryButtonText !== '')
                                <a href="{{ $heroSecondaryButtonUrl !== '' ? $heroSecondaryButtonUrl : '#' }}" class="inline-flex items-center rounded-lg px-5 py-2.5 text-sm border border-white/70 text-white bg-white/10 hover:bg-white/20">
                                    {{ $heroSecondaryButtonText }}
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </section>
    @endif

    <div class="max-w-5xl mx-auto px-4 py-10">
        @unless($heroEnabled)
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-6">{{ $page->title }}</h1>
        @endunless

        <div class="flex flex-wrap gap-6">
        @forelse(($page->blocks ?? []) as $block)
            @php($type = (string) ($block['type'] ?? 'text'))
            @php($layout = (string) ($block['layout'] ?? 'full'))
            @php($alignment = (string) ($block['alignment'] ?? 'left'))
            @php($background = (string) ($block['background'] ?? 'none'))
            @php($padding = (string) ($block['padding'] ?? 'md'))
            @php($blockWidth = (string) ($block['block_width'] ?? 'full'))
            @php($imageSize = (string) ($block['image_size'] ?? 'full'))
            <section class="rounded-xl border border-gray-200 dark:border-dark-700
                {{ $background === 'gray' ? 'bg-gray-100 dark:bg-dark-700' : ($background === 'primary' ? 'bg-primary-50 dark:bg-primary-900/20' : 'bg-white dark:bg-dark-800') }}
                {{ $padding === 'sm' ? 'p-3' : ($padding === 'lg' ? 'p-8' : 'p-5') }}
                {{ $alignment === 'center' ? 'text-center' : 'text-left' }}
                {{ $blockWidth === 'half' ? 'w-full md:w-[calc(50%-0.75rem)]' : 'w-full' }}">
                @if(!empty($block['title']))
                    <h2 class="text-xl font-semibold mb-3">{{ $block['title'] }}</h2>
                @endif

                @if($type === 'image' && !empty($block['image_url']))
                    <img src="{{ $block['image_url'] }}" alt="{{ $block['title'] ?? 'image' }}" class="h-auto rounded-lg {{ $imageSize === 'sm' ? 'w-1/4' : ($imageSize === 'md' ? 'w-1/2' : ($imageSize === 'lg' ? 'w-3/4' : 'w-full')) }} {{ $alignment === 'center' ? 'mx-auto' : '' }}">
                @elseif($type === 'button')
                    @if(!empty($block['button_url']) && !empty($block['button_text']))
                        @php($buttonSize = (string) ($block['button_size'] ?? 'md'))
                        @php($buttonColor = (string) ($block['button_color'] ?? 'primary'))
                        <a href="{{ $block['button_url'] }}" class="inline-flex items-center rounded-lg {{ $buttonSize === 'sm' ? 'px-2 py-1 text-xs' : ($buttonSize === 'lg' ? 'px-5 py-2.5 text-base' : 'px-4 py-2 text-sm') }} {{ $buttonColor === 'secondary' ? 'bg-gray-600 hover:bg-gray-700 text-white' : ($buttonColor === 'outline' ? 'bg-transparent border border-primary-500 text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/20' : ($buttonColor === 'none' ? 'bg-transparent text-primary-600 hover:underline' : 'bg-primary-600 hover:bg-primary-700 text-white')) }}">
                            {{ $block['button_text'] }}
                        </a>
                    @endif
                @elseif($type === 'form')
                    @php($formId = (int) ($block['form_id'] ?? 0))
                    @php($form = $formsById->get($formId))
                    @if($form)
                        <form method="POST" action="{{ route('forms.submit', $form->slug) }}" class="space-y-3">
                            @csrf
                            <input type="text" name="website" value="" class="hidden" tabindex="-1" autocomplete="off">
                            @foreach((array) ($form->fields ?? []) as $field)
                                @php($fieldName = (string) ($field['name'] ?? ''))
                                @if($fieldName !== '')
                                    <div>
                                        <label class="block text-sm font-medium mb-1">{{ (string) ($field['label'] ?? $fieldName) }}</label>
                                        @if(($field['type'] ?? 'text') === 'textarea')
                                            <textarea name="fields[{{ $fieldName }}]" rows="4" class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 px-3 py-2">{{ old('fields.'.$fieldName) }}</textarea>
                                        @else
                                            <input type="{{ ($field['type'] ?? 'text') === 'email' ? 'email' : 'text' }}" name="fields[{{ $fieldName }}]" value="{{ old('fields.'.$fieldName) }}" class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 px-3 py-2">
                                        @endif
                                    </div>
                                @endif
                            @endforeach
                            @if($turnstileSiteKey ?? false)
                                <div class="cf-turnstile" data-sitekey="{{ $turnstileSiteKey }}"></div>
                            @endif
                            <label class="inline-flex items-start gap-2 text-sm">
                                <input type="checkbox" name="accept_terms" value="1" class="mt-0.5">
                                <span>
                                    {{ __('I accept the') }}
                                    <a href="{{ route('legal.terms') }}" target="_blank" class="text-primary-600 dark:text-primary-400 hover:underline">
                                        {{ __('legal.terms.page_title') }}
                                    </a>
                                </span>
                            </label>
                            <button type="submit" class="inline-flex items-center rounded-lg px-4 py-2 text-sm bg-primary-600 hover:bg-primary-700 text-white">
                                {{ (string) ($block['form_submit_label'] ?? __('Send message')) }}
                            </button>
                        </form>
                    @else
                        <div class="text-sm text-gray-500">{{ __('Selected form is not available.') }}</div>
                    @endif
                @elseif($layout === 'two_columns')
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="prose dark:prose-invert max-w-none">{!! $renderTextWithButtons($block['content_left'] ?? '') !!}</div>
                        <div class="prose dark:prose-invert max-w-none">{!! $renderTextWithButtons($block['content_right'] ?? '') !!}</div>
                    </div>
                @else
                    <div class="prose dark:prose-invert max-w-none">{!! $renderTextWithButtons($block['content'] ?? '') !!}</div>
                @endif
            </section>
        @empty
            <div class="text-gray-500">{{ __('This page has no content blocks yet.') }}</div>
        @endforelse
        </div>
    </div>
    @if($turnstileSiteKey ?? false)
        <script data-cfasync="false" src="https://challenges.cloudflare.com/turnstile/v0/api.js" async></script>
    @endif
@endsection

