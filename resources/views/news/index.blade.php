@extends('layouts.app')

@section('title', __('News'))

@section('content')
    <!-- Hero Section -->
    <section class="relative overflow-hidden">
        <!-- Background gradient -->
        <div class="absolute inset-0 bg-gradient-to-br from-primary-500/10 via-transparent to-purple-500/10"></div>
        
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 md:py-32">
            <div class="text-center">
                <!-- Badge -->
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-primary-500/10 border border-primary-500/20 mb-8">
                    <span class="w-2 h-2 rounded-full bg-primary-500 animate-pulse"></span>
                    <span class="text-sm font-medium text-primary-600 dark:text-primary-400">{{ __('All News') }}</span>
                </div>
                
                <!-- Main heading -->
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-6">
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary-500 to-purple-500">{{ __('Browse all news') }}</span>
                </h1>
                
                <!-- Subtitle -->
                <p class="text-xl text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
                    {{ __('Filter by category and discover all published articles.') }}
                </p>
            </div>
        </div>
    </section>

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @adSlot('news_index_top')
    </section>

    <section class="pb-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <form method="GET" action="{{ route('news.index') }}" class="bg-white dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl p-4 flex flex-col sm:flex-row gap-3 sm:items-end">
                <div class="flex-1">
                    <label for="category" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Category') }}</label>
                    <select name="category" id="category" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-dark-700 border border-gray-300 dark:border-dark-600 rounded-lg text-gray-900 dark:text-white">
                        <option value="">{{ __('All categories') }}</option>
                        @foreach($categories as $categoryName)
                            <option value="{{ $categoryName }}" @selected($selectedCategory === $categoryName)>{{ $categoryName }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium">
                        <i class="fas fa-filter"></i>
                        {{ __('Filter') }}
                    </button>
                    <a href="{{ route('news.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg border border-gray-300 dark:border-dark-600 text-gray-700 dark:text-gray-300 text-sm font-medium hover:bg-gray-50 dark:hover:bg-dark-700">
                        <i class="fas fa-rotate-left"></i>
                        {{ __('Reset') }}
                    </a>
                </div>
            </form>
        </div>
    </section>
    
    <!-- News Grid Section -->
    <section class="py-16 md:py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if($news->isEmpty())
                <!-- Empty State -->
                <div class="text-center py-16">
                    <div class="w-24 h-24 mx-auto bg-gray-100 dark:bg-dark-800 rounded-full flex items-center justify-center mb-6">
                        <i class="fas fa-newspaper text-4xl text-gray-400"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">{{ __('No news articles available yet') }}</h3>
                    <p class="text-gray-600 dark:text-gray-400">{{ __('Check back later for the latest updates.') }}</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($news as $article)
                        <article class="group bg-white dark:bg-dark-800 rounded-2xl border border-gray-200 dark:border-dark-700 overflow-hidden hover:border-primary-500/50 transition-all duration-500 hover:-translate-y-2 hover:shadow-2xl">
                            <!-- Image -->
                            @if($article->image)
                                <div class="relative h-48 overflow-hidden">
                                    <img src="{{ asset('storage/' . $article->image) }}" alt="{{ $article->title }}" loading="lazy" decoding="async" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
                                </div>
                            @else
                                <div class="relative h-48 bg-gradient-to-br from-primary-500/20 to-purple-500/20 flex items-center justify-center">
                                    <i class="fas fa-newspaper text-6xl text-primary-500/30"></i>
                                </div>
                            @endif
                            
                            <!-- Content -->
                            <div class="p-6">
                                <!-- Meta -->
                                <div class="flex items-center gap-4 text-sm text-gray-500 dark:text-gray-400 mb-4 flex-wrap">
                                    @if($article->category)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-primary-500/10 text-primary-600 dark:text-primary-400">{{ $article->category }}</span>
                                    @endif
                                    <div class="flex items-center gap-1">
                                        <i class="fas fa-user"></i>
                                        <span>{{ $article->user?->name ?? '—' }}</span>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <i class="fas fa-calendar"></i>
                                        <span>{{ $article->formatted_date }}</span>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <i class="fas fa-comments"></i>
                                        <span>{{ (int) ($article->comments_count ?? 0) }}</span>
                                    </div>
                                </div>
                                
                                <!-- Title -->
                                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-3 group-hover:text-primary-500 transition-colors">
                                    <a href="{{ route('news.show', $article) }}">{{ $article->title }}</a>
                                </h2>
                                
                                <!-- Excerpt -->
                                <p class="text-gray-600 dark:text-gray-400 mb-4 line-clamp-3">
                                    {{ $article->excerpt }}
                                </p>
                                
                                <!-- Read More Link -->
                                <a href="{{ route('news.show', $article) }}" class="inline-flex items-center gap-2 text-primary-500 hover:text-primary-600 font-semibold transition-colors">
                                    {{ __('Read More') }}
                                    <i class="fas fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>
                
                <!-- Pagination -->
                <div class="mt-12">
                    {{ $news->links() }}
                </div>
            @endif
        </div>
    </section>
    
    <!-- CTA Section -->
    @guest
        @php $registrationDisabled = \App\Models\Setting::getBoolValue('disable_registration', false); @endphp
        <section class="py-16 md:py-24 bg-gray-50 dark:bg-dark-800/50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="relative overflow-hidden bg-gradient-to-r from-primary-500 to-purple-500 rounded-3xl p-8 md:p-12">
                    <div class="absolute top-0 right-0 -mt-12 -mr-12 w-48 h-48 bg-white/10 rounded-full blur-3xl"></div>
                    <div class="absolute bottom-0 left-0 -mb-12 -ml-12 w-48 h-48 bg-white/10 rounded-full blur-3xl"></div>
                    
                    <div class="relative text-center">
                        <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">{{ __('Want to stay updated?') }}</h2>
                        <p class="text-white/80 mb-8 max-w-2xl mx-auto">{{ __('Join our community and never miss the latest news and updates.') }}</p>
                        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                            @unless($registrationDisabled)
                                <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-8 py-4 bg-white text-primary-600 font-semibold rounded-xl hover:bg-gray-100 transition-colors">
                                    <i class="fas fa-user-plus"></i>
                                    {{ __('Register Now') }}
                                </a>
                            @endunless
                            <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-8 py-4 bg-white/20 text-white font-semibold rounded-xl hover:bg-white/30 transition-colors">
                                <i class="fas fa-sign-in-alt"></i>
                                {{ __('Login') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endguest

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @adSlot('news_index_bottom')
    </section>
@endsection
