@extends('layouts.app')

@section('title', __('Home'))

@section('content')
    <header class="border-b border-stone-200 dark:border-stone-700 bg-stone-50 dark:bg-stone-900/80">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-16 text-center">
            <p class="text-xs font-sans uppercase tracking-[0.25em] text-rose-700 dark:text-rose-400 mb-4">{{ __('Latest News') }}</p>
            <h1 class="magazine-brand text-4xl md:text-5xl lg:text-6xl font-bold text-stone-900 dark:text-stone-50 leading-tight">
                {{ \App\Models\Setting::getValue('site_name', config('app.name')) }}
            </h1>
            @if(filled(\App\Models\Setting::getValue('site_description')))
                <p class="mt-4 text-lg text-stone-600 dark:text-stone-400 max-w-2xl mx-auto font-sans">
                    {{ \App\Models\Setting::getValue('site_description') }}
                </p>
            @else
                <p class="mt-4 text-lg text-stone-600 dark:text-stone-400 max-w-2xl mx-auto font-sans">
                    {{ __('Discover the latest stories, insights, and updates from our platform.') }}
                </p>
            @endif
        </div>
    </header>

    <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        @adSlot('home_top')
    </section>

    <section class="py-12 md:py-16">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            @if($news->isEmpty())
                <div class="text-center py-16 border border-dashed border-stone-300 dark:border-stone-600 rounded-lg">
                    <p class="text-stone-600 dark:text-stone-400 font-sans">{{ __('No news articles available yet') }}</p>
                </div>
            @else
                @php $featured = $news->first(); $rest = $news->slice(1); @endphp

                <article class="grid lg:grid-cols-2 gap-8 mb-14 pb-14 border-b border-stone-200 dark:border-stone-700">
                    <div class="order-2 lg:order-1 flex flex-col justify-center">
                        @if($featured->category)
                            <span class="inline-block text-xs font-sans uppercase tracking-widest text-rose-700 dark:text-rose-400 mb-3">{{ $featured->category }}</span>
                        @endif
                        <h2 class="magazine-brand text-3xl md:text-4xl font-bold text-stone-900 dark:text-white leading-snug">
                            <a href="{{ route('news.show', $featured) }}" class="hover:text-rose-700 dark:hover:text-rose-400 transition-colors">{{ $featured->title }}</a>
                        </h2>
                        <p class="mt-4 text-stone-600 dark:text-stone-400 leading-relaxed">{{ $featured->excerpt }}</p>
                        <p class="mt-4 text-sm font-sans text-stone-500">{{ $featured->formatted_date }}</p>
                        <a href="{{ route('news.show', $featured) }}" class="mt-6 inline-flex items-center gap-2 font-sans text-sm font-bold uppercase tracking-wide text-rose-700 dark:text-rose-400 hover:underline">
                            {{ __('Read More') }} <i class="fas fa-arrow-right text-xs"></i>
                        </a>
                    </div>
                    <a href="{{ route('news.show', $featured) }}" class="order-1 lg:order-2 block overflow-hidden rounded-sm shadow-lg aspect-[4/3] bg-stone-200 dark:bg-stone-800">
                        @if($featured->image)
                            <img src="{{ asset('storage/'.$featured->image) }}" alt="{{ $featured->title }}" class="w-full h-full object-cover hover:scale-105 transition-transform duration-700" loading="eager">
                        @else
                            <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-rose-100 to-stone-200 dark:from-rose-950 dark:to-stone-800">
                                <i class="fas fa-newspaper text-6xl text-rose-300/60"></i>
                            </div>
                        @endif
                    </a>
                </article>

                @if($rest->isNotEmpty())
                    <div class="grid md:grid-cols-2 gap-x-10 gap-y-12">
                        @foreach($rest as $article)
                            <article class="group border-t border-stone-200 dark:border-stone-700 pt-6">
                                @if($article->category)
                                    <span class="text-xs font-sans uppercase tracking-widest text-stone-500">{{ $article->category }}</span>
                                @endif
                                <h3 class="magazine-brand mt-2 text-xl font-bold text-stone-900 dark:text-white group-hover:text-rose-700 dark:group-hover:text-rose-400 transition-colors">
                                    <a href="{{ route('news.show', $article) }}">{{ $article->title }}</a>
                                </h3>
                                <p class="mt-2 text-sm text-stone-600 dark:text-stone-400 line-clamp-3">{{ $article->excerpt }}</p>
                                <div class="mt-3 flex items-center justify-between font-sans text-xs text-stone-500">
                                    <span>{{ $article->formatted_date }}</span>
                                    <a href="{{ route('news.show', $article) }}" class="text-rose-700 dark:text-rose-400 font-semibold">{{ __('Read More') }}</a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif

                <div class="mt-14 text-center font-sans">
                    <a href="{{ route('news.index') }}" class="inline-flex items-center gap-2 px-8 py-3 border-2 border-stone-900 dark:border-stone-200 text-stone-900 dark:text-stone-100 font-bold uppercase tracking-wider text-sm hover:bg-stone-900 hover:text-white dark:hover:bg-stone-100 dark:hover:text-stone-900 transition-colors">
                        {{ __('Show all news') }}
                    </a>
                </div>

                <div class="mt-12">
                    @adSlot('home_middle')
                </div>
            @endif
        </div>
    </section>

    <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pb-12">
        @adSlot('home_bottom')
    </section>
@endsection
