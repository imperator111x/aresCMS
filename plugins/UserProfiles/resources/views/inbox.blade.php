@extends('layouts.app')

@section('title', __('Messages'))

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ __('Messages') }}</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">{{ __('Your conversations with friends.') }}</p>
        </div>
        <a href="{{ route('profiles.index') }}" class="inline-flex items-center gap-2 text-sm text-primary-600 dark:text-primary-400 hover:underline">
            <i class="fas fa-user-group"></i> {{ __('Member directory') }}
        </a>
    </div>

    @if($conversations->isEmpty())
        <div class="rounded-2xl border border-gray-200 dark:border-dark-700 bg-white dark:bg-dark-800 p-10 text-center">
            <i class="fas fa-comments text-4xl text-gray-400 mb-4"></i>
            <p class="text-gray-600 dark:text-gray-400">{{ __('No conversations yet.') }}</p>
            <p class="text-sm text-gray-500 dark:text-gray-500 mt-2">{{ __('Send a friend request in the member directory to start chatting.') }}</p>
            <a href="{{ route('profiles.index') }}" class="mt-6 inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-primary-500 hover:bg-primary-600 text-white font-medium">
                <i class="fas fa-user-group"></i> {{ __('Browse members') }}
            </a>
        </div>
    @else
        <ul class="space-y-2">
            @foreach($conversations as $row)
                @php
                    $peer = $row['peer'];
                    $last = $row['last_message'];
                    $unread = (int) $row['unread_count'];
                @endphp
                <li>
                    <a href="{{ $row['chat_url'] }}"
                       class="flex items-center gap-4 p-4 rounded-2xl border transition-colors {{ $unread > 0 ? 'border-primary-400/60 bg-primary-50/50 dark:bg-primary-950/20 dark:border-primary-700/50' : 'border-gray-200 dark:border-dark-700 bg-white dark:bg-dark-800 hover:border-primary-500/40' }}">
                        @if($peer->avatar)
                            <img src="{{ asset('storage/'.$peer->avatar) }}" alt="" class="w-14 h-14 rounded-xl object-cover shrink-0" loading="lazy">
                        @else
                            <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-green-400 to-blue-500 flex items-center justify-center text-white text-xl font-bold shrink-0">
                                {{ strtoupper(substr($peer->name, 0, 1)) }}
                            </div>
                        @endif
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-2">
                                <p class="font-semibold text-gray-900 dark:text-white truncate">{{ $peer->name }}</p>
                                @if($last?->created_at)
                                    <time class="text-xs text-gray-500 dark:text-gray-400 shrink-0">{{ $last->created_at->diffForHumans() }}</time>
                                @endif
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400 truncate mt-0.5 {{ $unread > 0 ? 'font-medium text-gray-800 dark:text-gray-200' : '' }}">
                                {{ $row['preview'] }}
                            </p>
                        </div>
                        @if($unread > 0)
                            <span class="shrink-0 min-w-[1.5rem] h-6 px-2 inline-flex items-center justify-center rounded-full bg-primary-500 text-white text-xs font-bold">
                                {{ $unread > 99 ? '99+' : $unread }}
                            </span>
                        @else
                            <i class="fas fa-chevron-right text-gray-400 shrink-0"></i>
                        @endif
                    </a>
                </li>
            @endforeach
        </ul>
    @endif
</div>
@endsection
