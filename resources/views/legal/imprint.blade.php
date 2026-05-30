@extends('layouts.app')

@section('title', __('legal.imprint.page_title'))

@section('content')
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-24">
        <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-8">{{ __('legal.imprint.heading') }}</h1>

        <div class="prose prose-gray dark:prose-invert max-w-none space-y-10 text-gray-700 dark:text-gray-300">
            <section>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">{{ __('legal.imprint.section_provider') }}</h2>
                @if (filled($legal['entity_name'] ?? null))
                    <p class="font-medium text-gray-900 dark:text-white">{{ $legal['entity_name'] }}</p>
                @endif
                @if (filled($legal['street'] ?? null) || filled($legal['zip'] ?? null) || filled($legal['city'] ?? null))
                    <p>
                        @if (filled($legal['street'] ?? null)){{ $legal['street'] }}<br>@endif
                        @if (filled($legal['zip'] ?? null) || filled($legal['city'] ?? null))
                            {{ trim(($legal['zip'] ?? '').' '.($legal['city'] ?? '')) }}<br>
                        @endif
                        @if (filled($legal['country'] ?? null))
                            {{ $legal['country'] }}
                        @endif
                    </p>
                @endif
                @if (filled($legal['representative'] ?? null))
                    <p><span class="text-gray-500 dark:text-gray-400">{{ __('legal.imprint.label_representative') }}:</span> {{ $legal['representative'] }}</p>
                @endif
            </section>

            <section>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">{{ __('legal.imprint.label_contact') }}</h2>
                <ul class="list-none space-y-1 pl-0">
                    @if (filled($legal['email'] ?? null))
                        @php
                            $emailUser = \Illuminate\Support\Str::before((string) $legal['email'], '@');
                            $emailDomain = \Illuminate\Support\Str::after((string) $legal['email'], '@');
                        @endphp
                        <li><i class="fas fa-envelope w-5 text-gray-400"></i> <a href="#" data-protected-email-user="{{ e($emailUser) }}" data-protected-email-domain="{{ e($emailDomain) }}" data-protected-email-reveal="1" class="text-primary-600 dark:text-primary-400 hover:underline">{{ $emailUser }} [at] {{ $emailDomain }}</a></li>
                    @endif
                    @if (filled($legal['phone'] ?? null))
                        <li><i class="fas fa-phone w-5 text-gray-400"></i> {{ $legal['phone'] }}</li>
                    @endif
                </ul>
            </section>

            @if (filled($legal['vat_id'] ?? null))
                <section>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">{{ __('legal.imprint.label_vat') }}</h2>
                    <p>{{ $legal['vat_id'] }}</p>
                </section>
            @endif

            @if (filled($legal['register_info'] ?? null))
                <section>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">{{ __('legal.imprint.label_register') }}</h2>
                    <p class="whitespace-pre-line">{{ $legal['register_info'] }}</p>
                </section>
            @endif

            @if (filled($legal['content_responsibility'] ?? null))
                <section>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">{{ __('legal.imprint.label_content_liability') }}</h2>
                    <p class="whitespace-pre-line">{{ $legal['content_responsibility'] }}</p>
                </section>
            @endif

            <section>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">{{ __('legal.imprint.label_dispute') }}</h2>
                <p class="text-sm leading-relaxed">{{ __('legal.imprint.dispute_eu_text') }}</p>
            </section>
        </div>
    </div>
@endsection
