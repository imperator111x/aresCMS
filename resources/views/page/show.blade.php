@extends('layouts.app')

@section('title', $page->title)

@section('content')
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
        $heroThemeClasses = \App\Support\PageHeroThemes::resolve($heroTheme);
        $heroOverlayClass = $heroOverlayStrength === 'light'
            ? 'bg-black/20'
            : ($heroOverlayStrength === 'strong' ? 'bg-black/60' : 'bg-black/40');
        $heroHeightClass = $heroHeight === 'sm'
            ? 'py-14 md:py-16'
            : ($heroHeight === 'lg'
                ? 'py-28 md:py-36'
                : ($heroHeight === 'full' ? 'min-h-[80vh] flex flex-col justify-center' : 'py-20 md:py-24'));
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
                    <h1 @class([
                        'text-4xl md:text-5xl lg:text-6xl font-bold mb-6',
                        'text-white' => $heroBackgroundImage !== '',
                        'text-gray-900 dark:text-white' => $heroBackgroundImage === '',
                    ])>
                        {{ $heroHeading !== '' ? $heroHeading : $page->title }}
                    </h1>
                    @if($heroSubheading !== '')
                        <p @class([
                            'text-xl max-w-2xl mx-auto',
                            'text-white/90' => $heroBackgroundImage !== '',
                            'text-gray-600 dark:text-gray-400' => $heroBackgroundImage === '',
                        ])>
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
                        <div class="prose dark:prose-invert max-w-none">{!! \App\Support\PageBlockRenderer::renderTextWithButtons($block['content_left'] ?? '', $teamMembers, $latestNews) !!}</div>
                        <div class="prose dark:prose-invert max-w-none">{!! \App\Support\PageBlockRenderer::renderTextWithButtons($block['content_right'] ?? '', $teamMembers, $latestNews) !!}</div>
                    </div>
                @else
                    <div class="prose dark:prose-invert max-w-none">{!! \App\Support\PageBlockRenderer::renderTextWithButtons($block['content'] ?? '', $teamMembers, $latestNews) !!}</div>
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

