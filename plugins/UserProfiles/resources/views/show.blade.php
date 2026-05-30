@extends('layouts.app')

@section('title', $user->name)

@section('content')
@php
    $viewer = auth()->user();
    $status = $friendship?->status;
    $isIncomingPending = $friendship && $status === 'pending' && (int)$friendship->addressee_id === (int)$viewer->id;
    $isOutgoingPending = $friendship && $status === 'pending' && (int)$friendship->requester_id === (int)$viewer->id;
    $isAccepted = $friendship && $status === 'accepted';
@endphp

<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="rounded-2xl border border-gray-200 dark:border-dark-700 bg-white dark:bg-dark-800 overflow-hidden shadow-xl">
        <div class="h-28 bg-gradient-to-r from-primary-500/30 to-purple-600/30 relative">
            <div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%239C92AC\' fill-opacity=\'0.08\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')] opacity-60"></div>
        </div>

        <div class="px-6 pb-8 -mt-12 relative">
            <div class="flex flex-col sm:flex-row sm:items-end gap-6">
                @if($user->avatar)
                    <img src="{{ asset('storage/'.$user->avatar) }}" alt="{{ $user->name }}" class="w-28 h-28 rounded-2xl border-4 border-white dark:border-dark-800 object-cover shadow-lg">
                @else
                    <div class="w-28 h-28 rounded-2xl border-4 border-white dark:border-dark-800 bg-gradient-to-br from-primary-400 to-purple-500 flex items-center justify-center text-4xl font-bold text-white shadow-lg">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                @endif
                <div class="flex-1 pt-2 sm:pb-2">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $user->name }}</h1>
                    @if(filled($user->task))
                        <p class="text-primary-600 dark:text-primary-400 font-medium">{{ $user->task }}</p>
                    @endif
                </div>
            </div>

            @if(filled($user->bio))
                <div class="mt-6 prose dark:prose-invert max-w-none text-gray-700 dark:text-gray-300">
                    <p class="whitespace-pre-wrap">{{ $user->bio }}</p>
                </div>
            @endif

            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ route('profiles.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-gray-300 dark:border-dark-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-dark-700">
                    <i class="fas fa-arrow-left"></i> {{ __('Back to directory') }}
                </a>

                @if($isAccepted && $friendship)
                    <a href="{{ route('profiles.chat', $friendship) }}" class="inline-flex items-center gap-2 px-5 py-2 rounded-xl bg-gradient-to-r from-primary-500 to-purple-500 text-white font-semibold shadow-lg shadow-primary-500/25">
                        <i class="fas fa-comments"></i> {{ __('Open chat') }}
                    </a>
                @elseif($isIncomingPending && $friendship)
                    <form method="POST" action="{{ route('profiles.friendships.accept', $friendship) }}" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 px-5 py-2 rounded-xl bg-primary-500 hover:bg-primary-600 text-white font-semibold">
                            <i class="fas fa-check"></i> {{ __('Accept request') }}
                        </button>
                    </form>
                    <form method="POST" action="{{ route('profiles.friendships.decline', $friendship) }}" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 px-5 py-2 rounded-xl border border-gray-300 dark:border-dark-600 text-gray-700 dark:text-gray-300">
                            {{ __('Decline') }}
                        </button>
                    </form>
                @elseif($isOutgoingPending)
                    <span class="inline-flex items-center gap-2 px-5 py-2 rounded-xl bg-gray-100 dark:bg-dark-700 text-gray-600 dark:text-gray-400">
                        <i class="fas fa-clock"></i> {{ __('Friend request pending') }}
                    </span>
                @else
                    <form method="POST" action="{{ route('profiles.friend-request', $user) }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 px-5 py-2 rounded-xl bg-primary-500 hover:bg-primary-600 text-white font-semibold">
                            <i class="fas fa-user-plus"></i> {{ __('Send friend request') }}
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
