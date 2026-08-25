@extends('layouts.app')

@section('title', __('News'))

@section('content')
    <section class="relative overflow-hidden hw-hero-panel">
        <div class="absolute inset-0 hw-hero-panel__glow"></div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-20 text-center">
            <div class="hw-hero-badge mx-auto">
                <i class="fas fa-newspaper" aria-hidden="true"></i>
                <span>{{ __('All News') }}</span>
            </div>
            <h1 class="handwerk-brand hw-hero-panel__title">{{ __('Projects & updates') }}</h1>
            <p class="hw-hero-panel__lead mx-auto">{{ __('Installations, maintenance and tips from our technicians.') }}</p>
        </div>
    </section>

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        @adSlot('news_index_top')
    </section>

    <section class="pb-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <form method="GET" action="{{ route('news.index') }}" class="hw-filter-panel">
                <div class="flex-1">
                    <label for="category" class="hw-filter-panel__label">{{ __('Category') }}</label>
                    <select name="category" id="category" class="hw-filter-panel__select">
                        <option value="">{{ __('All categories') }}</option>
                        @foreach($categories as $categoryName)
                            <option value="{{ $categoryName }}" @selected($selectedCategory === $categoryName)>{{ $categoryName }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold">
                        <i class="fas fa-filter" aria-hidden="true"></i>
                        {{ __('Filter') }}
                    </button>
                    <a href="{{ route('news.index') }}" class="hw-btn-secondary text-sm py-2.5">
                        <i class="fas fa-rotate-left" aria-hidden="true"></i>
                        {{ __('Reset') }}
                    </a>
                </div>
            </form>
        </div>
    </section>

    <section class="hw-news-section py-12 md:py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
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
                <div class="mt-10">
                    {{ $news->links() }}
                </div>
            @endif
        </div>
    </section>
@endsection
