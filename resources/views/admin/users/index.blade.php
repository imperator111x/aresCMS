@extends('layouts.admin')

@section('title', __('User Management'))

@section('content')
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('User Management') }}</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1">{{ __('Manage all users') }}</p>
        </div>
    </div>
    
    <!-- Users Table -->
    <div class="bg-white dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 overflow-hidden">
        <div class="overflow-x-auto md:overflow-x-visible">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-dark-700">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('User') }}</th>
                        <th class="hidden sm:table-cell px-5 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Email') }}</th>
                        <th class="hidden sm:table-cell px-5 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Role') }}</th>
                        <th class="hidden sm:table-cell px-5 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Status') }}</th>
                        <th class="hidden lg:table-cell px-5 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Joined') }}</th>
                        <th class="hidden sm:table-cell px-5 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-dark-700">
                    @forelse($users as $user)
                        <tr class="hover:bg-gray-50 dark:hover:bg-dark-700/50">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    @if($user->avatar)
                                        <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}" class="w-10 h-10 rounded-full object-cover">
                                    @else
                                        <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&color=7F9CF5&background=EBF4FF" alt="{{ $user->name }}" class="w-10 h-10 rounded-full">
                                    @endif
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-start justify-between gap-2 sm:block">
                                            <div class="min-w-0">
                                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $user->name }}</span>
                                                <p class="text-xs text-gray-500 dark:text-gray-400 break-all sm:hidden">{{ $user->email }}</p>
                                            </div>
                                            <div class="sm:hidden shrink-0 relative" x-data="{ open: false }">
                                                <button type="button" @click="open = !open" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-dark-600 hover:bg-gray-100 dark:hover:bg-dark-700">
                                                    <i class="fas fa-ellipsis-h"></i>
                                                    {{ __('Actions') }}
                                                </button>
                                                <div x-show="open" x-cloak @click.away="open = false" class="absolute right-0 mt-2 flex flex-wrap items-center justify-end gap-1.5 w-56 p-2 rounded-lg bg-white dark:bg-dark-800 border border-gray-200 dark:border-dark-700 shadow-lg z-20">
                                                    <a href="{{ route('admin.users.show', $user) }}" class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-dark-600 hover:bg-gray-100 dark:hover:bg-dark-700" title="{{ __('View') }}">
                                                        <i class="fas fa-eye"></i>
                                                        {{ __('View') }}
                                                    </a>
                                                    <a href="{{ route('admin.users.edit', $user) }}" class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs text-primary-600 dark:text-primary-400 border border-primary-200 dark:border-primary-800/60 hover:bg-primary-50 dark:hover:bg-primary-900/20" title="{{ __('Edit') }}">
                                                        <i class="fas fa-edit"></i>
                                                        {{ __('Edit') }}
                                                    </a>
                                                    @if($user->id !== auth()->id() && (auth()->user()?->isOwner() ?? false) && ! $user->isOwner())
                                                        <form action="{{ route('admin.users.toggle-admin', $user) }}" method="POST" class="inline">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit" class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs text-purple-600 dark:text-purple-400 border border-purple-200 dark:border-purple-800/60 hover:bg-purple-50 dark:hover:bg-purple-900/20" title="{{ $user->isAdmin() ? __('Remove Admin') : __('Make Admin') }}">
                                                                <i class="fas fa-user-shield"></i>
                                                                {{ $user->isAdmin() ? __('Remove Admin') : __('Make Admin') }}
                                                            </button>
                                                        </form>
                                                        <form action="{{ route('admin.users.toggle-ban', $user) }}" method="POST" class="inline">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit" class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs text-red-600 dark:text-red-400 border border-red-200 dark:border-red-800/60 hover:bg-red-50 dark:hover:bg-red-900/20" title="{{ $user->is_banned ? __('Unban') : __('Ban') }}">
                                                                <i class="fas fa-{{ $user->is_banned ? 'unlock' : 'ban' }}"></i>
                                                                {{ $user->is_banned ? __('Unban') : __('Ban') }}
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-1 flex flex-wrap items-center gap-1.5 sm:hidden">
                                            @if($user->isAdmin())
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400">
                                                    {{ __($user->adminRoleLabel()) }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                                    {{ __('User') }}
                                                </span>
                                            @endif
                                            @if($user->is_banned)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400">
                                                    {{ __('Banned') }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400">
                                                    {{ __('Active') }}
                                                </span>
                                            @endif
                                        </div>
                                        @if($user->task)
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $user->task }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="hidden sm:table-cell px-5 py-4">
                                <span class="text-sm text-gray-500 dark:text-gray-400 break-all">{{ $user->email }}</span>
                            </td>
                            <td class="hidden sm:table-cell px-5 py-4">
                                @if($user->isAdmin())
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400">
                                        {{ __($user->adminRoleLabel()) }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                        {{ __('User') }}
                                    </span>
                                @endif
                            </td>
                            <td class="hidden sm:table-cell px-5 py-4">
                                @if($user->is_banned)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400">
                                        {{ __('Banned') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400">
                                        {{ __('Active') }}
                                    </span>
                                @endif
                            </td>
                            <td class="hidden lg:table-cell px-5 py-4">
                                <span class="text-sm text-gray-500 dark:text-gray-400">{{ $user->created_at->format('d.m.Y') }}</span>
                            </td>
                            <td class="hidden sm:table-cell px-5 py-4">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('admin.users.show', $user) }}" class="p-1.5 text-gray-500 hover:text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg transition-colors" title="{{ __('View') }}">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.users.edit', $user) }}" class="p-1.5 text-gray-500 hover:text-primary-500 hover:bg-primary-50 dark:hover:bg-primary-900/30 rounded-lg transition-colors" title="{{ __('Edit') }}">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if($user->id !== auth()->id() && (auth()->user()?->isOwner() ?? false) && ! $user->isOwner())
                                        <form action="{{ route('admin.users.toggle-admin', $user) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="p-1.5 text-gray-500 hover:text-purple-500 hover:bg-purple-50 dark:hover:bg-purple-900/30 rounded-lg transition-colors" title="{{ $user->isAdmin() ? __('Remove Admin') : __('Make Admin') }}">
                                                <i class="fas fa-user-shield"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.users.toggle-ban', $user) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="p-1.5 text-gray-500 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors" title="{{ $user->is_banned ? __('Unban') : __('Ban') }}">
                                                <i class="fas fa-{{ $user->is_banned ? 'unlock' : 'ban' }}"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-gray-500 dark:text-gray-400">
                                <i class="fas fa-users text-4xl mb-3 block"></i>
                                {{ __('No users found') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($users->hasPages())
            <div class="px-5 py-4 border-t border-gray-200 dark:border-dark-700">
                {{ $users->links() }}
            </div>
        @endif
    </div>
@endsection
