@extends('layouts.admin')

@section('title', __('Search'))

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Search results') }}</h1>
        @if($query !== '')
            <p class="text-gray-500 dark:text-gray-400 mt-1">
                {{ __('Search for:') }} <span class="font-medium text-gray-700 dark:text-gray-300">“{{ $query }}”</span>
            </p>
        @endif
    </div>

    @if(mb_strlen($query) < $minLength)
        <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-6 text-amber-800 dark:text-amber-200 text-sm">
            {{ __('Enter at least :count characters to search.', ['count' => $minLength]) }}
        </div>
    @else
        <div class="space-y-10">
            <!-- News -->
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <i class="fas fa-newspaper text-primary-500"></i>
                    {{ __('News') }}
                    <span class="text-sm font-normal text-gray-500">({{ $newsResults->count() }})</span>
                </h2>
                @if($newsResults->isEmpty())
                    <p class="text-gray-500 dark:text-gray-400 text-sm py-4">{{ __('No news articles found.') }}</p>
                @else
                    <div class="bg-white dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 divide-y divide-gray-200 dark:divide-dark-700 overflow-hidden">
                        @foreach($newsResults as $article)
                            <a href="{{ route('admin.news.edit', $article) }}" class="flex items-center justify-between gap-4 px-4 py-3 hover:bg-gray-50 dark:hover:bg-dark-700/50 transition-colors">
                                <div class="min-w-0">
                                    <p class="font-medium text-gray-900 dark:text-white truncate">{{ $article->title }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $article->published ? __('Published') : __('Draft') }}
                                        · {{ $article->updated_at->diffForHumans() }}
                                    </p>
                                </div>
                                <i class="fas fa-chevron-right text-gray-400 text-sm shrink-0"></i>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Users -->
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <i class="fas fa-users text-primary-500"></i>
                    {{ __('Users') }}
                    <span class="text-sm font-normal text-gray-500">({{ $userResults->count() }})</span>
                </h2>
                @if($userResults->isEmpty())
                    <p class="text-gray-500 dark:text-gray-400 text-sm py-4">{{ __('No users found.') }}</p>
                @else
                    <div class="bg-white dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 divide-y divide-gray-200 dark:divide-dark-700 overflow-hidden">
                        @foreach($userResults as $u)
                            <a href="{{ route('admin.users.edit', $u) }}" class="flex items-center justify-between gap-4 px-4 py-3 hover:bg-gray-50 dark:hover:bg-dark-700/50 transition-colors">
                                <div class="flex items-center gap-3 min-w-0">
                                    @if($u->avatar)
                                        <img src="{{ asset('storage/' . $u->avatar) }}" alt="" class="w-9 h-9 rounded-full object-cover shrink-0">
                                    @else
                                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-primary-400 to-purple-400 flex items-center justify-center shrink-0 text-white text-xs font-bold">
                                            {{ strtoupper(substr($u->name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <p class="font-medium text-gray-900 dark:text-white truncate">{{ $u->name }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $u->email }}</p>
                                    </div>
                                </div>
                                <i class="fas fa-chevron-right text-gray-400 text-sm shrink-0"></i>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @endif
@endsection
