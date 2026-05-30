@extends('layouts.admin')

@section('title', __('News Management'))

@section('content')
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('News Management') }}</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1">{{ __('Manage all news articles') }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.news-categories.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-violet-500 hover:bg-violet-600 text-white rounded-lg transition-colors">
                <i class="fas fa-tags"></i>
                {{ __('Categories') }}
            </a>
            <a href="{{ route('admin.news.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-500 hover:bg-primary-600 text-white rounded-lg transition-colors">
                <i class="fas fa-plus"></i>
                {{ __('Create News') }}
            </a>
        </div>
    </div>
    
    <!-- News Table -->
    <div class="bg-white dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 overflow-hidden">
        <div class="overflow-x-auto md:overflow-x-visible">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-dark-700">
                    <tr>
                        <th class="hidden sm:table-cell px-5 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('ID') }}</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Title') }}</th>
                        <th class="hidden sm:table-cell px-5 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Category') }}</th>
                        <th class="hidden sm:table-cell px-5 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Author') }}</th>
                        <th class="hidden sm:table-cell px-5 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Status') }}</th>
                        <th class="hidden lg:table-cell px-5 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Date') }}</th>
                        <th class="hidden sm:table-cell px-5 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-dark-700">
                    @forelse($news as $article)
                        <tr class="hover:bg-gray-50 dark:hover:bg-dark-700/50">
                            <td class="hidden sm:table-cell px-5 py-4">
                                <span class="text-sm text-gray-500 dark:text-gray-400">{{ $article->id }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    @if($article->image)
                                        <img src="{{ asset('storage/' . $article->image) }}" alt="{{ $article->title }}" class="w-10 h-10 rounded-lg object-cover">
                                    @else
                                        <div class="w-10 h-10 bg-gray-200 dark:bg-dark-600 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-image text-gray-400"></i>
                                        </div>
                                    @endif
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-start justify-between gap-2 sm:block">
                                            <div class="min-w-0">
                                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ Str::limit($article->title, 40) }}</span>
                                                <p class="text-xs text-gray-500 dark:text-gray-400 sm:hidden">
                                                    {{ $article->category ? Str::limit($article->category, 24) : '—' }} · {{ $article->user->name }}
                                                </p>
                                            </div>
                                            <div class="sm:hidden shrink-0 relative" x-data="{ open: false }">
                                                <button type="button" @click="open = !open" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-dark-600 hover:bg-gray-100 dark:hover:bg-dark-700">
                                                    <i class="fas fa-ellipsis-h"></i>
                                                    {{ __('Actions') }}
                                                </button>
                                                <div x-show="open" x-cloak @click.away="open = false" class="absolute right-0 mt-2 flex flex-wrap items-center justify-end gap-1.5 w-52 p-2 rounded-lg bg-white dark:bg-dark-800 border border-gray-200 dark:border-dark-700 shadow-lg z-20">
                                                    <a href="{{ route('admin.news.show', $article) }}" class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-dark-600 hover:bg-gray-100 dark:hover:bg-dark-700" title="{{ __('View') }}">
                                                        <i class="fas fa-eye"></i>
                                                        {{ __('View') }}
                                                    </a>
                                                    <a href="{{ route('admin.news.edit', $article) }}" class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs text-primary-600 dark:text-primary-400 border border-primary-200 dark:border-primary-800/60 hover:bg-primary-50 dark:hover:bg-primary-900/20" title="{{ __('Edit') }}">
                                                        <i class="fas fa-edit"></i>
                                                        {{ __('Edit') }}
                                                    </a>
                                                    <form action="{{ route('admin.news.destroy', $article) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('Are you sure you want to delete this news article?') }}')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs text-red-600 dark:text-red-400 border border-red-200 dark:border-red-800/60 hover:bg-red-50 dark:hover:bg-red-900/20" title="{{ __('Delete') }}">
                                                            <i class="fas fa-trash"></i>
                                                            {{ __('Delete') }}
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-1 sm:hidden">
                                            @if($article->isScheduled())
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400">
                                                    {{ __('Scheduled') }}
                                                </span>
                                            @elseif($article->published)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400">
                                                    {{ __('Published') }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400">
                                                    {{ __('Draft') }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="hidden sm:table-cell px-5 py-4">
                                <span class="text-sm text-gray-500 dark:text-gray-400">{{ $article->category ? Str::limit($article->category, 24) : '—' }}</span>
                            </td>
                            <td class="hidden sm:table-cell px-5 py-4">
                                <span class="text-sm text-gray-500 dark:text-gray-400">{{ $article->user->name }}</span>
                            </td>
                            <td class="hidden sm:table-cell px-5 py-4">
                                @if($article->isScheduled())
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400">
                                        {{ __('Scheduled') }}
                                    </span>
                                @elseif($article->published)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400">
                                        {{ __('Published') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400">
                                        {{ __('Draft') }}
                                    </span>
                                @endif
                            </td>
                            <td class="hidden lg:table-cell px-5 py-4">
                                <span class="text-sm text-gray-500 dark:text-gray-400">{{ $article->created_at->format('d.m.Y') }}</span>
                            </td>
                            <td class="hidden sm:table-cell px-5 py-4">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('admin.news.show', $article) }}" class="p-1.5 text-gray-500 hover:text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg transition-colors" title="{{ __('View') }}">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.news.edit', $article) }}" class="p-1.5 text-gray-500 hover:text-primary-500 hover:bg-primary-50 dark:hover:bg-primary-900/30 rounded-lg transition-colors" title="{{ __('Edit') }}">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.news.destroy', $article) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('Are you sure you want to delete this news article?') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-gray-500 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors" title="{{ __('Delete') }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-8 text-center text-gray-500 dark:text-gray-400">
                                <i class="fas fa-newspaper text-4xl mb-3 block"></i>
                                {{ __('No news articles yet') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($news->hasPages())
            <div class="px-5 py-4 border-t border-gray-200 dark:border-dark-700">
                {{ $news->links() }}
            </div>
        @endif
    </div>
@endsection
