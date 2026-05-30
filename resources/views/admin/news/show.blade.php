@extends('layouts.admin')

@section('title', __('View News'))

@section('content')
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('View News') }}</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1">{{ __('View news article details') }}</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.news.edit', $news) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-500 hover:bg-primary-600 text-white rounded-lg transition-colors">
                <i class="fas fa-edit"></i>
                {{ __('Edit') }}
            </a>
            <a href="{{ route('admin.news.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-dark-700 hover:bg-gray-200 dark:hover:bg-dark-600 text-gray-700 dark:text-gray-300 rounded-lg transition-colors">
                <i class="fas fa-arrow-left"></i>
                {{ __('Back to List') }}
            </a>
        </div>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Article Content -->
            <div class="bg-white dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 overflow-hidden">
                @if($news->image)
                    <img src="{{ asset('storage/' . $news->image) }}" alt="{{ $news->title }}" class="w-full h-64 object-cover">
                @endif
                <div class="p-6">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">{{ $news->title }}</h2>
                    <div class="prose dark:prose-invert max-w-none text-gray-700 dark:text-gray-300">
                        @include('news.partials.article-body', ['content' => $news->content])
                    </div>
                </div>
            </div>
            
            <!-- Comments -->
            <div class="bg-white dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <i class="fas fa-comments text-primary-500"></i>
                    {{ __('Comments') }} ({{ $news->comments->count() }})
                </h3>
                
                @if($news->comments->count() > 0)
                    <div class="space-y-4">
                        @foreach($news->rootComments as $comment)
                            <div class="p-4 bg-gray-50 dark:bg-dark-700 rounded-lg">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-2">
                                        @if($comment->user->avatar)
                                            <img src="{{ asset('storage/' . $comment->user->avatar) }}" alt="{{ $comment->user->name }}" class="w-8 h-8 rounded-full object-cover">
                                        @else
                                            <img src="https://ui-avatars.com/api/?name={{ urlencode($comment->user->name) }}&color=7F9CF5&background=EBF4FF" alt="{{ $comment->user->name }}" class="w-8 h-8 rounded-full">
                                        @endif
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $comment->user->name }}</span>
                                    </div>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ $comment->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $comment->content }}</p>
                                @foreach($comment->replies as $reply)
                                    <div class="mt-3 ml-4 pl-3 border-l-2 border-primary-500/30">
                                        <div class="flex items-center justify-between mb-1">
                                            <div class="flex items-center gap-2">
                                                @if($reply->user->avatar)
                                                    <img src="{{ asset('storage/' . $reply->user->avatar) }}" alt="{{ $reply->user->name }}" class="w-7 h-7 rounded-full object-cover">
                                                @else
                                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($reply->user->name) }}&color=7F9CF5&background=EBF4FF&size=64" alt="{{ $reply->user->name }}" class="w-7 h-7 rounded-full">
                                                @endif
                                                <span class="text-xs font-medium text-gray-900 dark:text-white">{{ $reply->user->name }}</span>
                                            </div>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $reply->created_at->diffForHumans() }}</span>
                                        </div>
                                        <p class="text-xs text-gray-600 dark:text-gray-400 whitespace-pre-wrap">{{ $reply->content }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 dark:text-gray-400 text-center py-4">{{ __('No comments yet') }}</p>
                @endif
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Article Info -->
            <div class="bg-white dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('Article Info') }}</h3>
                <div class="space-y-4">
                    <div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('Author') }}</span>
                        <div class="flex items-center gap-2 mt-1">
                            @if($news->user->avatar)
                                <img src="{{ asset('storage/' . $news->user->avatar) }}" alt="{{ $news->user->name }}" class="w-8 h-8 rounded-full object-cover">
                            @else
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($news->user->name) }}&color=7F9CF5&background=EBF4FF" alt="{{ $news->user->name }}" class="w-8 h-8 rounded-full">
                            @endif
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $news->user->name }}</span>
                        </div>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('Status') }}</span>
                        <div class="mt-1">
                            @if($news->published)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400">
                                    {{ __('Published') }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400">
                                    {{ __('Draft') }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('Created') }}</span>
                        <p class="text-sm font-medium text-gray-900 dark:text-white mt-1">{{ $news->created_at->format('d.m.Y H:i') }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('Updated') }}</span>
                        <p class="text-sm font-medium text-gray-900 dark:text-white mt-1">{{ $news->updated_at->format('d.m.Y H:i') }}</p>
                    </div>
                </div>
            </div>
            
            <!-- Actions -->
            <div class="bg-white dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('Actions') }}</h3>
                <div class="space-y-3">
                    <a href="{{ route('news.show', $news) }}" target="_blank" class="flex items-center gap-2 w-full px-4 py-2 bg-gray-50 dark:bg-dark-700 hover:bg-gray-100 dark:hover:bg-dark-600 text-gray-700 dark:text-gray-300 rounded-lg transition-colors">
                        <i class="fas fa-external-link-alt"></i>
                        {{ __('View on Site') }}
                    </a>
                    <form action="{{ route('admin.news.destroy', $news) }}" method="POST" onsubmit="return confirm('{{ __('Are you sure you want to delete this news article?') }}')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="flex items-center gap-2 w-full px-4 py-2 bg-red-50 dark:bg-red-900/20 hover:bg-red-100 dark:hover:bg-red-900/30 text-red-600 dark:text-red-400 rounded-lg transition-colors">
                            <i class="fas fa-trash"></i>
                            {{ __('Delete') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
