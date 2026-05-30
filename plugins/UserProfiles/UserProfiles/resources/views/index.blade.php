@extends('layouts.app')

@section('title', __('Members'))

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ __('Member directory') }}</h1>
        <p class="mt-2 text-gray-600 dark:text-gray-400">{{ __('Find members and send friend requests. When a request is accepted, you can chat.') }}</p>
    </div>

    @if($incoming->isNotEmpty())
        <div class="mb-10 rounded-2xl border border-amber-200 dark:border-amber-800/60 bg-amber-50/80 dark:bg-amber-950/30 p-6">
            <h2 class="text-lg font-semibold text-amber-900 dark:text-amber-100 mb-4 flex items-center gap-2">
                <i class="fas fa-user-plus"></i> {{ __('Incoming friend requests') }}
            </h2>
            <ul class="space-y-3">
                @foreach($incoming as $req)
                    <li class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 py-2 border-b border-amber-200/60 dark:border-amber-800/40 last:border-0">
                        <div class="flex items-center gap-3">
                            @if($req->requester->avatar)
                                <img src="{{ asset('storage/'.$req->requester->avatar) }}" alt="" class="w-10 h-10 rounded-xl object-cover" loading="lazy">
                            @else
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary-400 to-purple-500 flex items-center justify-center text-white font-bold">
                                    {{ strtoupper(substr($req->requester->name, 0, 1)) }}
                                </div>
                            @endif
                            <div>
                                <a href="{{ route('profiles.show', $req->requester) }}" class="font-medium text-gray-900 dark:text-white hover:text-primary-500">{{ $req->requester->name }}</a>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $req->created_at?->diffForHumans() }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <form method="POST" action="{{ route('profiles.friendships.accept', $req) }}">
                                @csrf
                                <button type="submit" class="px-4 py-2 rounded-xl bg-primary-500 hover:bg-primary-600 text-white text-sm font-medium">
                                    {{ __('Accept') }}
                                </button>
                            </form>
                            <form method="POST" action="{{ route('profiles.friendships.decline', $req) }}">
                                @csrf
                                <button type="submit" class="px-4 py-2 rounded-xl border border-gray-300 dark:border-dark-600 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-700">
                                    {{ __('Decline') }}
                                </button>
                            </form>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="GET" action="{{ route('profiles.index') }}" class="mb-8">
        <div class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input type="search" name="q" value="{{ $search }}" placeholder="{{ __('Search by name…') }}"
                    class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-800 text-gray-900 dark:text-white">
            </div>
            <button type="submit" class="px-6 py-3 rounded-xl bg-gray-100 dark:bg-dark-700 text-gray-800 dark:text-white font-medium">
                {{ __('Search') }}
            </button>
        </div>
    </form>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        @forelse($users as $member)
            <a href="{{ route('profiles.show', $member) }}" class="group flex items-center gap-4 p-4 rounded-2xl border border-gray-200 dark:border-dark-700 bg-white dark:bg-dark-800 hover:border-primary-500/50 transition-colors">
                @if($member->avatar)
                    <img src="{{ asset('storage/'.$member->avatar) }}" alt="" class="w-14 h-14 rounded-xl object-cover shrink-0" loading="lazy">
                @else
                    <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-green-400 to-blue-500 flex items-center justify-center text-white text-xl font-bold shrink-0">
                        {{ strtoupper(substr($member->name, 0, 1)) }}
                    </div>
                @endif
                <div class="min-w-0">
                    <p class="font-semibold text-gray-900 dark:text-white group-hover:text-primary-500 truncate">{{ $member->name }}</p>
                    @if(filled($member->task))
                        <p class="text-sm text-gray-500 dark:text-gray-400 truncate">{{ $member->task }}</p>
                    @endif
                </div>
                <i class="fas fa-chevron-right text-gray-400 ml-auto opacity-0 group-hover:opacity-100 transition-opacity"></i>
            </a>
        @empty
            <p class="col-span-full text-center text-gray-500 dark:text-gray-400 py-12">{{ __('No members found.') }}</p>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $users->links() }}
    </div>
</div>
@endsection
