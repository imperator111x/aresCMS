@extends('layouts.app')

@section('title', $news->title)

@section('content')
    <section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        @adSlot('news_show_top')
    </section>

    <!-- Article Section -->
    <section class="py-16 md:py-24">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Back Link + Admin Edit -->
            <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-gray-600 dark:text-gray-400 hover:text-primary-500 transition-colors">
                    <i class="fas fa-arrow-left"></i>
                    {{ __('Back to News') }}
                </a>
                @auth
                    @if(auth()->user()->is_admin)
                        <a href="{{ route('admin.news.edit', $news) }}" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-primary-500 hover:bg-primary-600 text-white text-sm font-semibold rounded-xl transition-colors shadow-lg shadow-primary-500/25">
                            <i class="fas fa-edit"></i>
                            {{ __('Edit') }}
                        </a>
                    @endif
                @endauth
            </div>
            
            <!-- Article Card -->
            <article class="bg-white dark:bg-dark-800 rounded-2xl border border-gray-200 dark:border-dark-700 overflow-hidden">
                <!-- Image -->
                @if($news->image)
                    <div class="relative h-64 md:h-96 overflow-hidden">
                        <img src="{{ asset('storage/' . $news->image) }}" alt="{{ $news->title }}" class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
                    </div>
                @endif
                
                <!-- Content -->
                <div class="p-8 md:p-12">
                    <!-- Meta -->
                    <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500 dark:text-gray-400 mb-6">
                        @if($news->category)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-primary-500/10 text-primary-600 dark:text-primary-400">{{ $news->category }}</span>
                        @endif
                        <div class="flex items-center gap-2">
                            <i class="fas fa-user"></i>
                            <span>{{ $news->user?->name ?? '—' }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="fas fa-calendar"></i>
                            <span>{{ $news->formatted_date }}</span>
                        </div>
                        @if($news->commentsEnabled())
                            <div class="flex items-center gap-2">
                                <i class="fas fa-comments"></i>
                                <span>{{ $news->comments->count() }} {{ __('Comments') }}</span>
                            </div>
                        @else
                            <div class="flex items-center gap-2 text-amber-600 dark:text-amber-400">
                                <i class="fas fa-comment-slash"></i>
                                <span>{{ __('Comments disabled') }}</span>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Title -->
                    <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-6">
                        {{ $news->title }}
                    </h1>
                    
                    <!-- Content -->
                    <div class="prose dark:prose-invert max-w-none text-gray-700 dark:text-gray-300 leading-relaxed">
                        @include('news.partials.article-body', [
                            'content' => $news->content,
                            'teamMembers' => $teamMembers ?? collect(),
                            'latestNews' => $latestNews ?? collect(),
                        ])
                    </div>
                </div>
            </article>

            @include('news.partials.reactions', [
                'news' => $news,
                'reactionCounts' => $reactionCounts ?? [],
                'userReaction' => $userReaction ?? null,
            ])
            
            <!-- Comments Section -->
            @if($news->commentsEnabled())
                <section class="mt-12">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
                        <i class="fas fa-comments mr-2"></i>
                        {{ __('Comments') }} ({{ $news->comments->count() }})
                    </h2>
                    
                    <!-- Add Comment Form -->
                    @auth
                        @if(auth()->user()->is_banned)
                            <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-2xl p-6 mb-8">
                                <p class="text-amber-800 dark:text-amber-200 text-sm flex items-center gap-2">
                                    <i class="fas fa-ban"></i>
                                    {{ __('Your account cannot post comments.') }}
                                </p>
                            </div>
                        @else
                            <div class="bg-white dark:bg-dark-800 rounded-2xl border border-gray-200 dark:border-dark-700 p-6 mb-8">
                                <div class="flex items-center gap-3 mb-4">
                                    @if(auth()->user()->avatar)
                                        <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="{{ auth()->user()->name }}" class="w-11 h-11 rounded-full object-cover border-2 border-gray-200 dark:border-dark-600 shrink-0">
                                    @else
                                        <div class="w-11 h-11 rounded-full bg-gradient-to-br from-primary-400 to-purple-400 flex items-center justify-center shrink-0 border-2 border-gray-200 dark:border-dark-600">
                                            <span class="text-sm font-bold text-white">{{ substr(auth()->user()->name, 0, 1) }}</span>
                                        </div>
                                    @endif
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Add a Comment') }}</h3>
                                </div>
                                <form action="{{ route('news.comments.store', $news) }}" method="POST">
                                    @csrf
                                    <div class="mb-4">
                                        <label for="comment-content-root" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Your Comment') }}</label>
                                        <textarea name="content" id="comment-content-root" rows="4" class="w-full px-4 py-3 bg-gray-50 dark:bg-dark-700 border border-gray-300 dark:border-dark-600 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent text-gray-900 dark:text-white placeholder-gray-400 @error('content') border-red-500 @enderror" required placeholder="{{ __('Write your comment here...') }}">{{ old('content') }}</textarea>
                                        @error('content')
                                            <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <button type="submit" class="inline-flex items-center gap-2 px-6 py-3 bg-primary-500 hover:bg-primary-600 text-white font-semibold rounded-xl transition-all duration-300 shadow-lg shadow-primary-500/25 hover:shadow-primary-500/40">
                                        <i class="fas fa-paper-plane"></i>
                                        {{ __('Post Comment') }}
                                    </button>
                                </form>
                            </div>
                        @endif
                    @else
                        <div class="bg-white dark:bg-dark-800 rounded-2xl border border-gray-200 dark:border-dark-700 p-6 mb-8 text-center">
                            <p class="text-gray-600 dark:text-gray-400 mb-4">{{ __('Please login to leave a comment') }}.</p>
                            <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-primary-500 hover:bg-primary-600 text-white font-semibold rounded-xl transition-all duration-300">
                                <i class="fas fa-sign-in-alt"></i>
                                {{ __('Login') }}
                            </a>
                        </div>
                    @endauth
                    
                    <!-- Comments List -->
                    @if($news->rootComments->isEmpty())
                        <div class="bg-white dark:bg-dark-800 rounded-2xl border border-gray-200 dark:border-dark-700 p-6 text-center">
                            <i class="fas fa-comments text-4xl text-gray-400 mb-4"></i>
                            <p class="text-gray-600 dark:text-gray-400">{{ __('No comments yet') }}. {{ __('Be the first to comment!') }}</p>
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach($news->rootComments as $comment)
                                @include('news.partials.comment-node', ['news' => $news, 'comment' => $comment, 'isReply' => false])
                            @endforeach
                        </div>
                    @endif
                </section>
            @else
                <section class="mt-12">
                    <div class="bg-white dark:bg-dark-800 rounded-2xl border border-gray-200 dark:border-dark-700 p-6">
                        <p class="text-sm text-gray-600 dark:text-gray-400 flex items-center gap-2">
                            <i class="fas fa-comment-slash text-amber-500"></i>
                            {{ __('Comments are disabled for this article.') }}
                        </p>
                    </div>
                </section>
            @endif
        </div>
    </section>

    <section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        @adSlot('news_show_bottom')
    </section>
@endsection
