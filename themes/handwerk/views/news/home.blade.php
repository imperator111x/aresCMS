@extends('layouts.app')

@section('title', __('Home'))

@section('content')
    <section class="relative overflow-hidden hw-hero-panel">
        <div class="absolute inset-0 hw-hero-panel__glow"></div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 md:py-28">
            <div class="max-w-3xl">
                <div class="hw-hero-badge">
                    <i class="fas fa-snowflake" aria-hidden="true"></i>
                    <span>{{ __('Cooling · Climate · Ventilation') }}</span>
                </div>
                <h1 class="handwerk-brand hw-hero-panel__title">
                    {{ \App\Models\Setting::getValue('site_name', config('app.name')) }}
                </h1>
                <p class="hw-hero-panel__lead">
                    {{ \App\Models\Setting::getValue('site_description') ?: __('Refrigeration and HVAC specialists — planning, installation, maintenance and emergency service for commercial and residential clients.') }}
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ $contactUrl }}" class="inline-flex items-center gap-2 px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-xl shadow-lg shadow-primary-500/25 transition-all">
                        <i class="fas fa-headset" aria-hidden="true"></i> {{ __('Request a consultation') }}
                    </a>
                    <a href="{{ route('team') }}" class="hw-btn-secondary">
                        <i class="fas fa-users" aria-hidden="true"></i> {{ __('Our Team') }}
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="hw-trust-strip" aria-label="{{ __('Our strengths') }}">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <ul class="hw-trust-strip__grid list-none m-0 p-0">
                <li class="hw-trust-strip__item">
                    <i class="fas fa-certificate" aria-hidden="true"></i>
                    <span><strong>{{ __('Master craft business') }}</strong>{{ __('Certified refrigeration & HVAC') }}</span>
                </li>
                <li class="hw-trust-strip__item">
                    <i class="fas fa-bolt" aria-hidden="true"></i>
                    <span><strong>{{ __('Energy efficient') }}</strong>{{ __('Modern systems, lower consumption') }}</span>
                </li>
                <li class="hw-trust-strip__item">
                    <i class="fas fa-clock" aria-hidden="true"></i>
                    <span><strong>{{ __('Fast response') }}</strong>{{ __('Maintenance and emergency service') }}</span>
                </li>
                <li class="hw-trust-strip__item">
                    <i class="fas fa-map-marker-alt" aria-hidden="true"></i>
                    <span><strong>{{ __('Regional') }}</strong>{{ __('On site in your area') }}</span>
                </li>
            </ul>
        </div>
    </section>

    <section class="py-14 hw-services-section">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <p class="hw-section-eyebrow">{{ __('Refrigeration & HVAC') }}</p>
            <h2 class="hw-section-heading">{{ __('Our services') }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($homeServices ?? [] as $service)
                    <div class="hw-service-card">
                        <div class="hw-service-card__icon">
                            <i class="fas {{ $service['icon'] }}" aria-hidden="true"></i>
                        </div>
                        <h3 class="hw-service-card__title">{{ $service['title'] }}</h3>
                        <p class="hw-service-card__text">{{ $service['text'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        @adSlot('home_top')
    </section>

    <section class="hw-news-section py-16 md:py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <header class="hw-news-section__head">
                <div>
                    <p class="hw-news-section__eyebrow">{{ __('References') }}</p>
                    <h2 class="hw-news-section__title">{{ __('Projects & updates') }}</h2>
                    <p class="hw-news-section__lead">{{ __('Installations, maintenance and tips from our technicians.') }}</p>
                </div>
                <a href="{{ route('news.index') }}" class="hw-news-section__link">
                    {{ __('Show all news') }}
                    <i class="fas fa-arrow-right" aria-hidden="true"></i>
                </a>
            </header>

            @if($news->isEmpty())
                <div class="hw-news-empty">
                    <i class="fas fa-newspaper" aria-hidden="true"></i>
                    <h3>{{ __('No news articles available yet') }}</h3>
                    <p>{{ __('Check back later for the latest updates.') }}</p>
                </div>
            @else
                <div class="hw-news-grid">
                    @foreach($news as $article)
                        @include('partials.news-card', ['article' => $article])
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <section class="py-16 hw-cta-band">
        <div class="max-w-3xl mx-auto px-4 text-center">
            <h2 class="text-2xl md:text-3xl font-bold mb-3">{{ __('Need cooling or climate control?') }}</h2>
            <p class="text-white/90 mb-6">{{ __('We advise on air conditioning, refrigeration and ventilation — free initial consultation.') }}</p>
            <a href="{{ $contactUrl }}" class="hw-cta-band__btn inline-flex items-center gap-2 px-8 py-3.5 rounded-xl">
                <i class="fas fa-snowflake" aria-hidden="true"></i> {{ __('Request a consultation') }}
            </a>
        </div>
    </section>

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @adSlot('home_bottom')
    </section>
@endsection
