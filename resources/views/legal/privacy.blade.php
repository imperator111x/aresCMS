@extends('layouts.app')

@section('title', __('legal.privacy.page_title'))

@section('content')
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-24">
        <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">{{ __('legal.privacy.heading') }}</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-10">{{ __('legal.privacy.updated', ['date' => now()->translatedFormat('d.m.Y')]) }}</p>

        <div class="prose prose-gray dark:prose-invert max-w-none space-y-8 text-gray-700 dark:text-gray-300">
            <p>{!! nl2br(e(__('legal.privacy.intro', ['url' => config('app.url')]))) !!}</p>

            <section>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ __('legal.privacy.section1_title') }}</h2>
                @if (filled($legal['entity_name'] ?? null))
                    <p>{{ $legal['entity_name'] }}@if (filled($legal['street'] ?? null)), {{ $legal['street'] }}@endif
                        @if (filled($legal['zip'] ?? null) || filled($legal['city'] ?? null))
                            , {{ trim(($legal['zip'] ?? '').' '.($legal['city'] ?? '')) }}
                        @endif
                    </p>
                    @if (filled($legal['email'] ?? null))
                        @php
                            $emailUser = \Illuminate\Support\Str::before((string) $legal['email'], '@');
                            $emailDomain = \Illuminate\Support\Str::after((string) $legal['email'], '@');
                        @endphp
                        <p><a href="#" data-protected-email-user="{{ e($emailUser) }}" data-protected-email-domain="{{ e($emailDomain) }}" data-protected-email-reveal="1" class="text-primary-600 dark:text-primary-400 hover:underline">{{ $emailUser }} [at] {{ $emailDomain }}</a></p>
                    @endif
                @else
                    <p>{{ __('legal.privacy.section1_fallback') }}</p>
                    <p><a href="{{ route('legal.imprint') }}" class="text-primary-600 dark:text-primary-400 hover:underline font-medium">{{ __('legal.imprint.page_title') }}</a></p>
                @endif
            </section>

            <section>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ __('legal.privacy.section2_title') }}</h2>
                <p>{!! nl2br(e(__('legal.privacy.section2_body'))) !!}</p>
            </section>

            <section>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ __('legal.privacy.section3_title') }}</h2>
                <p>{!! nl2br(e(__('legal.privacy.section3_body'))) !!}</p>
            </section>

            <section>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ __('legal.privacy.section4_title') }}</h2>
                @if ($turnstileEnabled)
                    <p>{!! nl2br(e(__('legal.privacy.section4_body'))) !!}</p>
                @else
                    <p>{!! nl2br(e(__('legal.privacy.section4_disabled'))) !!}</p>
                @endif
            </section>

            <section>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ __('legal.privacy.section5_title') }}</h2>
                <p>{!! nl2br(e(__('legal.privacy.section5_body'))) !!}</p>
            </section>

            <section>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ __('legal.privacy.section6_title') }}</h2>
                <p>{!! nl2br(e(__('legal.privacy.section6_body'))) !!}</p>
            </section>

            <section id="cookies">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ __('legal.privacy.section7_title') }}</h2>
                <p>{!! nl2br(e(__('legal.privacy.section7_body'))) !!}</p>
                <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">{!! nl2br(e(__('legal.privacy.section7_consent_banner'))) !!}</p>
            </section>

            <section>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ __('legal.privacy.section8_title') }}</h2>
                <p>{!! nl2br(e(__('legal.privacy.section8_body'))) !!}</p>
            </section>

            <section>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ __('legal.privacy.section9_title') }}</h2>
                <p>{!! nl2br(e(__('legal.privacy.section9_body'))) !!}</p>
            </section>
        </div>
    </div>
@endsection
