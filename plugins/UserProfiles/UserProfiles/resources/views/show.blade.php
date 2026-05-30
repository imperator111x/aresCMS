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

            @php
                $actionBtn = 'inline-flex shrink-0 items-center justify-center gap-2 min-h-[2.75rem] px-4 sm:px-5 rounded-xl text-sm font-semibold transition-colors leading-none';
                $iconFix = 'fas w-[1.125rem] text-center shrink-0';
            @endphp
            <div class="mt-8 rounded-xl border border-gray-200 dark:border-dark-600 bg-gray-50 dark:bg-dark-900/40 p-4 sm:p-5 space-y-4">
                <a href="{{ route('profiles.index') }}" class="{{ $actionBtn }} w-full sm:w-auto justify-center border border-gray-300 dark:border-dark-600 text-gray-800 dark:text-gray-200 hover:bg-white dark:hover:bg-dark-800">
                    <i class="{{ $iconFix }} fa-arrow-left" aria-hidden="true"></i><span>{{ __('Back to directory') }}</span>
                </a>

                <div class="flex flex-col sm:flex-row sm:flex-wrap gap-3 sm:items-center">
                @if($isAccepted && $friendship)
                    <a href="{{ route('profiles.chat', $friendship) }}" class="{{ $actionBtn }} w-full sm:w-auto justify-center bg-gradient-to-r from-primary-500 to-purple-500 text-white shadow-lg shadow-primary-500/20 hover:from-primary-600 hover:to-purple-600">
                        <i class="{{ $iconFix }} fa-comments" aria-hidden="true"></i><span>{{ __('Open chat') }}</span>
                    </a>
                @elseif($isIncomingPending && $friendship)
                    <form method="POST" action="{{ route('profiles.friendships.accept', $friendship) }}" class="inline-flex m-0 w-full sm:w-auto">
                        @csrf
                        <button type="submit" class="{{ $actionBtn }} w-full justify-center bg-primary-500 hover:bg-primary-600 text-white">
                            <i class="{{ $iconFix }} fa-check" aria-hidden="true"></i><span>{{ __('Accept request') }}</span>
                        </button>
                    </form>
                    <form method="POST" action="{{ route('profiles.friendships.decline', $friendship) }}" class="inline-flex m-0 w-full sm:w-auto">
                        @csrf
                        <button type="submit" class="{{ $actionBtn }} w-full justify-center border border-gray-300 dark:border-dark-600 text-gray-800 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-dark-800">
                            <span>{{ __('Decline') }}</span>
                        </button>
                    </form>
                @elseif($isOutgoingPending)
                    <span class="{{ $actionBtn }} w-full sm:w-auto justify-center cursor-default border border-gray-300/80 dark:border-dark-600 bg-gray-200 dark:bg-slate-700 text-gray-900 dark:text-gray-100">
                        <i class="{{ $iconFix }} fa-clock" aria-hidden="true"></i><span>{{ __('Friend request pending') }}</span>
                    </span>
                @else
                    <form method="POST" action="{{ route('profiles.friend-request', $user) }}" class="inline-flex m-0 w-full sm:w-auto">
                        @csrf
                        <button type="submit" class="{{ $actionBtn }} w-full justify-center bg-primary-500 hover:bg-primary-600 text-white shadow-md shadow-primary-500/20">
                            <i class="{{ $iconFix }} fa-user-plus" aria-hidden="true"></i><span>{{ __('Send friend request') }}</span>
                        </button>
                    </form>
                @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
