@extends('layouts.admin')

@section('title', __('View User'))

@section('content')
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('View User') }}</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1">{{ __('View user details') }}</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.users.edit', $user) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-500 hover:bg-primary-600 text-white rounded-lg transition-colors">
                <i class="fas fa-edit"></i>
                {{ __('Edit') }}
            </a>
            <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-dark-700 hover:bg-gray-200 dark:hover:bg-dark-600 text-gray-700 dark:text-gray-300 rounded-lg transition-colors">
                <i class="fas fa-arrow-left"></i>
                {{ __('Back to List') }}
            </a>
        </div>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- User Profile Card -->
            <div class="bg-white dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 p-6">
                <div class="flex flex-col md:flex-row items-center md:items-start gap-6">
                    <!-- Avatar -->
                    <div class="flex-shrink-0">
                        @if($user->avatar)
                            <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}" class="w-32 h-32 rounded-full object-cover">
                        @else
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&color=7F9CF5&background=EBF4FF&size=128" alt="{{ $user->name }}" class="w-32 h-32 rounded-full">
                        @endif
                    </div>
                    
                    <!-- User Info -->
                    <div class="flex-1 text-center md:text-left">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $user->name }}</h2>
                        <p class="text-gray-500 dark:text-gray-400 mt-1">{{ $user->email }}</p>
                        
                        @if($user->task)
                            <p class="text-sm text-primary-500 mt-2">
                                <i class="fas fa-briefcase mr-1"></i>
                                {{ $user->task }}
                            </p>
                        @endif
                        
                        <div class="flex flex-wrap gap-2 mt-4 justify-center md:justify-start">
                            @if($user->is_admin)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400">
                                    <i class="fas fa-user-shield mr-1"></i>
                                    {{ __('Admin') }}
                                </span>
                            @endif
                            
                            @if($user->is_banned)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400">
                                    <i class="fas fa-ban mr-1"></i>
                                    {{ __('Banned') }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    {{ __('Active') }}
                                </span>
                            @endif
                            
                            @if($user->email_verified_at)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400">
                                    <i class="fas fa-envelope-circle-check mr-1"></i>
                                    {{ __('Verified') }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400">
                                    <i class="fas fa-envelope mr-1"></i>
                                    {{ __('Unverified') }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
                
                <!-- Bio -->
                @if($user->bio)
                    <div class="mt-6 pt-6 border-t border-gray-200 dark:border-dark-700">
                        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase mb-2">{{ __('Bio') }}</h3>
                        <p class="text-gray-700 dark:text-gray-300">{{ $user->bio }}</p>
                    </div>
                @endif
            </div>
            
            <!-- User News -->
            <div class="bg-white dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <i class="fas fa-newspaper text-primary-500"></i>
                    {{ __('News Articles') }} ({{ $user->news->count() }})
                </h3>
                
                @if($user->news->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 dark:bg-dark-700">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">{{ __('Title') }}</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">{{ __('Status') }}</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">{{ __('Date') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-dark-700">
                                @foreach($user->news as $news)
                                    <tr>
                                        <td class="px-4 py-2">
                                            <a href="{{ route('admin.news.show', $news) }}" class="text-sm text-primary-500 hover:text-primary-600">{{ Str::limit($news->title, 40) }}</a>
                                        </td>
                                        <td class="px-4 py-2">
                                            @if($news->published)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400">
                                                    {{ __('Published') }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400">
                                                    {{ __('Draft') }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2">
                                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ $news->created_at->format('d.m.Y') }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-gray-500 dark:text-gray-400 text-center py-4">{{ __('No news articles yet') }}</p>
                @endif
            </div>
            
            <!-- User Comments -->
            <div class="bg-white dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <i class="fas fa-comments text-primary-500"></i>
                    {{ __('Comments') }} ({{ $user->comments->count() }})
                </h3>
                
                @if($user->comments->count() > 0)
                    <div class="space-y-4">
                        @foreach($user->comments->take(5) as $comment)
                            <div class="p-4 bg-gray-50 dark:bg-dark-700 rounded-lg">
                                <div class="flex items-center justify-between mb-2">
                                    <a href="{{ route('admin.news.show', $comment->news) }}" class="text-sm font-medium text-primary-500 hover:text-primary-600">{{ Str::limit($comment->news->title, 40) }}</a>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ $comment->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="text-sm text-gray-700 dark:text-gray-300">{{ Str::limit($comment->content, 100) }}</p>
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
            <!-- User Details -->
            <div class="bg-white dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('User Details') }}</h3>
                <div class="space-y-4">
                    <div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('ID') }}</span>
                        <p class="text-sm font-medium text-gray-900 dark:text-white mt-1">{{ $user->id }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('Email') }}</span>
                        <p class="text-sm font-medium text-gray-900 dark:text-white mt-1">{{ $user->email }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('Email Verified') }}</span>
                        <p class="text-sm font-medium text-gray-900 dark:text-white mt-1">
                            @if($user->email_verified_at && $user->email_verified_at instanceof \Illuminate\Support\Carbon)
                                {{ $user->email_verified_at->format('d.m.Y H:i') }}
                            @elseif($user->email_verified_at)
                                {{ \Illuminate\Support\Carbon::parse($user->email_verified_at)->format('d.m.Y H:i') }}
                            @else
                                <span class="text-yellow-500">{{ __('Not verified') }}</span>
                            @endif
                        </p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('Joined') }}</span>
                        <p class="text-sm font-medium text-gray-900 dark:text-white mt-1">{{ $user->created_at->format('d.m.Y H:i') }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('Last Updated') }}</span>
                        <p class="text-sm font-medium text-gray-900 dark:text-white mt-1">{{ $user->updated_at->format('d.m.Y H:i') }}</p>
                    </div>
                </div>
            </div>
            
            <!-- Actions -->
            <div class="bg-white dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('Actions') }}</h3>
                <div class="space-y-3">
                    @if($user->id !== auth()->id())
                        <form action="{{ route('admin.users.toggle-admin', $user) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="flex items-center gap-2 w-full px-4 py-2 bg-purple-50 dark:bg-purple-900/20 hover:bg-purple-100 dark:hover:bg-purple-900/30 text-purple-600 dark:text-purple-400 rounded-lg transition-colors">
                                <i class="fas fa-user-shield"></i>
                                {{ $user->is_admin ? __('Remove Admin') : __('Make Admin') }}
                            </button>
                        </form>
                        
                        <form action="{{ route('admin.users.toggle-ban', $user) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="flex items-center gap-2 w-full px-4 py-2 bg-red-50 dark:bg-red-900/20 hover:bg-red-100 dark:hover:bg-red-900/30 text-red-600 dark:text-red-400 rounded-lg transition-colors">
                                <i class="fas fa-{{ $user->is_banned ? 'unlock' : 'ban' }}"></i>
                                {{ $user->is_banned ? __('Unban') : __('Ban') }}
                            </button>
                        </form>
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-2">{{ __('You cannot modify your own account') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
