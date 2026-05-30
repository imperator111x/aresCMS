@extends('layouts.admin')

@section('title', __('Admin Dashboard'))

@section('content')
    <!-- Page Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Dashboard') }}</h1>
        <p class="text-gray-500 dark:text-gray-400 mt-1">{{ __('Welcome back! Here\'s what\'s happening with your news portal.') }}</p>
    </div>
    
    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- News Articles -->
        <div class="bg-white dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 p-5 hover:shadow-lg transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('News Articles') }}</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ \App\Models\News::count() }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                    <i class="fas fa-newspaper text-blue-500 text-xl"></i>
                </div>
            </div>
            <a href="{{ route('admin.news.index') }}" class="mt-4 inline-flex items-center text-sm text-blue-500 hover:text-blue-600">
                {{ __('View all') }} <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
        
        <!-- Users -->
        <div class="bg-white dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 p-5 hover:shadow-lg transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Users') }}</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ \App\Models\User::count() }}</p>
                </div>
                <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                    <i class="fas fa-users text-green-500 text-xl"></i>
                </div>
            </div>
            <a href="{{ route('admin.users.index') }}" class="mt-4 inline-flex items-center text-sm text-green-500 hover:text-green-600">
                {{ __('View all') }} <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
        
        <!-- Comments -->
        <div class="bg-white dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 p-5 hover:shadow-lg transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Comments') }}</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ \App\Models\Comment::count() }}</p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg flex items-center justify-center">
                    <i class="fas fa-comments text-yellow-500 text-xl"></i>
                </div>
            </div>
            <span class="mt-4 inline-flex items-center text-sm text-gray-400">
                {{ __('Total comments') }}
            </span>
        </div>
        
        <!-- Banned Users -->
        <div class="bg-white dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 p-5 hover:shadow-lg transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Banned Users') }}</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ \App\Models\User::where('is_banned', true)->count() }}</p>
                </div>
                <div class="w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center">
                    <i class="fas fa-user-slash text-red-500 text-xl"></i>
                </div>
            </div>
            <a href="{{ route('admin.users.index') }}" class="mt-4 inline-flex items-center text-sm text-red-500 hover:text-red-600">
                {{ __('Manage users') }} <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-white dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 p-5">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <i class="fas fa-bolt text-primary-500"></i>
                {{ __('Quick Actions') }}
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <a href="{{ route('admin.news.create') }}" class="flex items-center gap-2 p-3 bg-gray-50 dark:bg-dark-700 rounded-lg hover:bg-gray-100 dark:hover:bg-dark-600 transition-colors min-w-0">
                    <i class="fas fa-plus text-primary-500"></i>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300 truncate">{{ __('Create News') }}</span>
                </a>
                <a href="{{ route('admin.team') }}" class="flex items-center gap-2 p-3 bg-gray-50 dark:bg-dark-700 rounded-lg hover:bg-gray-100 dark:hover:bg-dark-600 transition-colors min-w-0">
                    <i class="fas fa-people-carry text-green-500"></i>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300 truncate">{{ __('View Team') }}</span>
                </a>
                <a href="{{ route('admin.users.index') }}" class="flex items-center gap-2 p-3 bg-gray-50 dark:bg-dark-700 rounded-lg hover:bg-gray-100 dark:hover:bg-dark-600 transition-colors min-w-0">
                    <i class="fas fa-users text-blue-500"></i>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300 truncate">{{ __('Manage Users') }}</span>
                </a>
                <a href="{{ url('/') }}" target="_blank" class="flex items-center gap-2 p-3 bg-gray-50 dark:bg-dark-700 rounded-lg hover:bg-gray-100 dark:hover:bg-dark-600 transition-colors min-w-0">
                    <i class="fas fa-external-link-alt text-purple-500"></i>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300 truncate">{{ __('View Site') }}</span>
                </a>
            </div>
        </div>
        
        <!-- Recent Activity -->
        <div class="bg-white dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 p-5">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <i class="fas fa-clock text-primary-500"></i>
                {{ __('Recent Activity') }}
            </h3>
            <div class="space-y-4">
                @foreach(\App\Models\News::latest()->take(3)->get() as $news)
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-newspaper text-blue-500 text-xs"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $news->title }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $news->user->name }} • {{ $news->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                @endforeach
                
                @if(\App\Models\News::count() === 0)
                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">{{ __('No recent activity') }}</p>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Latest News Table -->
    <div class="bg-white dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 overflow-hidden">
        <div class="p-5 border-b border-gray-200 dark:border-dark-700 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-newspaper text-primary-500"></i>
                {{ __('Latest News') }}
            </h3>
            <a href="{{ route('admin.news.index') }}" class="text-sm text-primary-500 hover:text-primary-600">
                {{ __('View all') }} <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
        <div class="overflow-x-auto md:overflow-x-visible">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-dark-700">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Title') }}</th>
                        <th class="hidden sm:table-cell px-5 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Author') }}</th>
                        <th class="hidden sm:table-cell px-5 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Status') }}</th>
                        <th class="hidden lg:table-cell px-5 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Date') }}</th>
                        <th class="hidden sm:table-cell px-5 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-dark-700">
                    @foreach(\App\Models\News::latest()->take(5)->get() as $news)
                        <tr class="hover:bg-gray-50 dark:hover:bg-dark-700/50">
                            <td class="px-5 py-4">
                                <div class="min-w-0">
                                    <div class="flex items-start justify-between gap-2 sm:block">
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ Str::limit($news->title, 40) }}</span>
                                        <div class="sm:hidden shrink-0 flex items-center gap-2">
                                            <a href="{{ route('admin.news.show', $news) }}" class="p-1.5 text-gray-500 hover:text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg transition-colors">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.news.edit', $news) }}" class="p-1.5 text-gray-500 hover:text-primary-500 hover:bg-primary-50 dark:hover:bg-primary-900/30 rounded-lg transition-colors">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="mt-1 sm:hidden text-xs text-gray-500 dark:text-gray-400">
                                        {{ $news->user->name }}
                                        <span class="mx-1">·</span>
                                        {{ $news->created_at->format('d.m.Y') }}
                                    </div>
                                    <div class="mt-1 sm:hidden">
                                        @if($news->published)
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
                            </td>
                            <td class="hidden sm:table-cell px-5 py-4">
                                <span class="text-sm text-gray-500 dark:text-gray-400">{{ $news->user->name }}</span>
                            </td>
                            <td class="hidden sm:table-cell px-5 py-4">
                                @if($news->published)
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
                                <span class="text-sm text-gray-500 dark:text-gray-400">{{ $news->created_at->format('d.m.Y') }}</span>
                            </td>
                            <td class="hidden sm:table-cell px-5 py-4">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('admin.news.show', $news) }}" class="p-1.5 text-gray-500 hover:text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg transition-colors">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.news.edit', $news) }}" class="p-1.5 text-gray-500 hover:text-primary-500 hover:bg-primary-50 dark:hover:bg-primary-900/30 rounded-lg transition-colors">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    
                    @if(\App\Models\News::count() === 0)
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-gray-500 dark:text-gray-400">
                                {{ __('No news articles yet') }}
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
@endsection
