@extends('layouts.app')

@section('title', $news->title)

@section('content')
    <section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 pt-8">
        @adSlot('news_show_top')
    </section>

    <section class="py-10 md:py-14">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <a href="{{ route('news.index') }}" class="hw-news-card__more inline-flex items-center gap-2">
                    <i class="fas fa-arrow-left" aria-hidden="true"></i>
                    {{ __('Back to News') }}
                </a>
                @auth
                    @if(auth()->user()->is_admin)
                        <a href="{{ route('admin.news.edit', $news) }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-xl">
                            <i class="fas fa-edit" aria-hidden="true"></i>
                            {{ __('Edit') }}
                        </a>
                    @endif
                @endauth
            </div>

            <article class="hw-article-panel">
                @if($news->image)
                    <div class="hw-article-panel__media">
                        <img src="{{ asset('storage/'.$news->image) }}" alt="{{ $news->title }}" loading="eager" decoding="async">
                    </div>
                @endif
                <div class="hw-article-panel__body">
                    <div class="hw-news-card__meta mb-4">
                        @if($news->category)
                            <span class="hw-news-card__category">{{ $news->category }}</span>
                        @endif
                        <span><i class="fas fa-user" aria-hidden="true"></i> {{ $news->user?->name ?? '—' }}</span>
                        <span><i class="fas fa-calendar" aria-hidden="true"></i> {{ $news->formatted_date }}</span>
                        @if($news->commentsEnabled())
                            <span><i class="fas fa-comments" aria-hidden="true"></i> {{ $news->comments->count() }} {{ __('Comments') }}</span>
                        @endif
                    </div>
                    <h1 class="hw-article-panel__title">{{ $news->title }}</h1>
                    <div class="hw-article-panel__content prose max-w-none">
                        @include('news.partials.article-body', [
                            'content' => $news->content,
                            'teamMembers' => $teamMembers ?? collect(),
                            'latestNews' => $latestNews ?? collect(),
                        ])
                    </div>
                </div>
            </article>

            <div class="hw-panel-box mt-8">
                @include('news.partials.reactions', [
                    'news' => $news,
                    'reactionCounts' => $reactionCounts ?? [],
                    'userReaction' => $userReaction ?? null,
                ])
            </div>

            @if($news->commentsEnabled())
                <section class="mt-10">
                    <h2 class="hw-section-heading text-left mb-6" style="margin-bottom: 1.5rem;">
                        <i class="fas fa-comments text-primary-600 mr-2"></i>
                        {{ __('Comments') }} ({{ $news->comments->count() }})
                    </h2>

                    @auth
                        @if(auth()->user()->is_banned)
                            <div class="hw-panel-box hw-panel-box--warning mb-6">
                                <p class="text-sm flex items-center gap-2">
                                    <i class="fas fa-ban"></i>
                                    {{ __('Your account cannot post comments.') }}
                                </p>
                            </div>
                        @else
                            <div class="hw-panel-box mb-8">
                                <form action="{{ route('news.comments.store', $news) }}" method="POST" class="space-y-4">
                                    @csrf
                                    <label for="comment-content-root" class="block text-sm font-semibold text-gray-900">{{ __('Your Comment') }}</label>
                                    <textarea name="content" id="comment-content-root" rows="4" class="hw-filter-panel__select w-full min-h-[6rem]" required placeholder="{{ __('Write your comment here...') }}">{{ old('content') }}</textarea>
                                    @error('content')
                                        <p class="text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    <button type="submit" class="inline-flex items-center gap-2 px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-xl">
                                        <i class="fas fa-paper-plane" aria-hidden="true"></i>
                                        {{ __('Post Comment') }}
                                    </button>
                                </form>
                            </div>
                        @endif
                    @else
                        <div class="hw-panel-box mb-8 text-center">
                            <p class="text-gray-600 mb-4">{{ __('Please login to leave a comment') }}.</p>
                            <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-primary-600 text-white font-semibold rounded-xl">
                                <i class="fas fa-sign-in-alt" aria-hidden="true"></i>
                                {{ __('Login') }}
                            </a>
                        </div>
                    @endauth

                    @if($news->rootComments->isEmpty())
                        <div class="hw-news-empty">
                            <i class="fas fa-comments" aria-hidden="true"></i>
                            <p>{{ __('No comments yet') }}. {{ __('Be the first to comment!') }}</p>
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach($news->rootComments as $comment)
                                @include('news.partials.comment-node', ['news' => $news, 'comment' => $comment, 'isReply' => false])
                            @endforeach
                        </div>
                    @endif
                </section>
            @endif
        </div>
    </section>

    <section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 pb-8">
        @adSlot('news_show_bottom')
    </section>
@endsection
