@extends('layouts.app')

@section('title', __('Home'))

@section('content')
    <section class="relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-primary-500/10 via-transparent to-purple-500/10"></div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 md:py-32">
            <div class="text-center">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-primary-500/10 border border-primary-500/20 mb-8">
                    <span class="w-2 h-2 rounded-full bg-primary-500 animate-pulse"></span>
                    <span class="text-sm font-medium text-primary-600 dark:text-primary-400">{{ __('Latest News') }}</span>
                </div>
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-gray-900 dark:text-white mb-6">
                    {{ __('Stay Updated with') }} <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary-500 to-purple-500">{{ __('Our News') }}</span>
                </h1>
                <p class="text-xl text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
                    {{ __('Discover the latest stories, insights, and updates from our platform.') }}
                </p>
            </div>
        </div>
    </section>

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @adSlot('home_top')
    </section>

    <section class="py-16 md:py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if($news->isEmpty())
                <div class="text-center py-16">
                    <div class="w-24 h-24 mx-auto bg-gray-100 dark:bg-dark-800 rounded-full flex items-center justify-center mb-6">
                        <i class="fas fa-newspaper text-4xl text-gray-400"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">{{ __('No news articles available yet') }}</h3>
                    <p class="text-gray-600 dark:text-gray-400">{{ __('Check back later for the latest updates.') }}</p>
                </div>
            @else
                <div class="flex flex-col gap-16 md:gap-24 lg:gap-28">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 md:gap-10 pb-6 md:pb-10">
                    @foreach($news as $article)
                        <article class="group bg-white dark:bg-dark-800 rounded-2xl border border-gray-200 dark:border-dark-700 overflow-hidden hover:border-primary-500/50 transition-all duration-500 hover:-translate-y-2 hover:shadow-2xl flex flex-col">
                            @if($article->image)
                                <div class="relative h-48 overflow-hidden shrink-0">
                                    <img src="{{ asset('storage/' . $article->image) }}" alt="{{ $article->title }}" loading="lazy" decoding="async" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
                                </div>
                            @else
                                <div class="relative h-48 shrink-0 bg-gradient-to-br from-primary-500/20 to-purple-500/20 flex items-center justify-center">
                                    <i class="fas fa-newspaper text-6xl text-primary-500/30"></i>
                                </div>
                            @endif
                            <div class="p-6 flex flex-col flex-1">
                                <div class="flex items-center gap-4 text-sm text-gray-500 dark:text-gray-400 mb-4 flex-wrap">
                                    @if($article->category)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-primary-500/10 text-primary-600 dark:text-primary-400">{{ $article->category }}</span>
                                    @endif
                                    <div class="flex items-center gap-1"><i class="fas fa-calendar"></i><span>{{ $article->formatted_date }}</span></div>
                                </div>
                                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-3 group-hover:text-primary-500 transition-colors">
                                    <a href="{{ route('news.show', $article) }}">{{ $article->title }}</a>
                                </h2>
                                <p class="text-gray-600 dark:text-gray-400 mb-4 line-clamp-3 flex-1">{{ $article->excerpt }}</p>
                                <a href="{{ route('news.show', $article) }}" class="inline-flex items-center gap-2 text-primary-500 hover:text-primary-600 font-semibold transition-colors mt-auto pt-2">
                                    {{ __('Read More') }}
                                    <i class="fas fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>
                <div class="w-full flex justify-center px-2 pt-2 md:pt-4">
                    <a href="{{ route('news.index') }}" class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold shadow-md shadow-primary-500/20 hover:shadow-lg hover:shadow-primary-500/25 transition-shadow">
                        <i class="fas fa-newspaper shrink-0 text-base" aria-hidden="true"></i>
                        {{ __('Show all news') }}
                    </a>
                </div>
                <div>
                    @adSlot('home_middle')
                </div>
                </div>
            @endif
        </div>
    </section>

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @adSlot('home_bottom')
    </section>
@endsection
