@extends('layouts.admin')

@section('title', __('Login history'))

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Login history') }}</h1>
        <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">{{ __('Successful logins and failed attempts (web).') }}</p>
        <div class="mt-3 flex flex-wrap gap-2">
            <a href="{{ route('admin.login-history.export.pdf', request()->query()) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-sm font-medium">
                <i class="fas fa-file-pdf"></i>
                {{ __('Export PDF') }}
            </a>
            <a href="{{ route('admin.login-history.export.excel', request()->query()) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium">
                <i class="fas fa-file-excel"></i>
                {{ __('Export Excel') }}
            </a>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.login-history.index') }}" class="mb-6 flex flex-wrap gap-3 items-end bg-white dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 p-4">
        <div class="w-full sm:w-auto">
            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('Result') }}</label>
            <select name="success" class="w-full sm:w-auto rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 text-sm text-gray-900 dark:text-white px-3 py-2">
                <option value="">{{ __('All') }}</option>
                <option value="1" @selected(request('success') === '1')>{{ __('Success') }}</option>
                <option value="0" @selected(request('success') === '0')>{{ __('Failed') }}</option>
            </select>
        </div>
        <div class="w-full sm:w-auto">
            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('User') }}</label>
            <select name="user_id" class="w-full sm:w-auto rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 text-sm text-gray-900 dark:text-white px-3 py-2 min-w-[10rem]">
                <option value="">{{ __('All users') }}</option>
                @foreach($users as $u)
                    <option value="{{ $u->id }}" @selected((string)request('user_id') === (string)$u->id)>{{ $u->name }} ({{ $u->email }})</option>
                @endforeach
            </select>
        </div>
        <div class="w-full sm:w-auto">
            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('Email / login') }}</label>
            <input type="text" name="identifier" value="{{ request('identifier') }}" placeholder="{{ __('Search…') }}" class="w-full sm:w-48 rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 text-sm text-gray-900 dark:text-white px-3 py-2">
        </div>
        <div class="w-full sm:w-auto">
            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('From') }}</label>
            <input type="date" name="from" value="{{ request('from') }}" class="w-full sm:w-auto rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 text-sm text-gray-900 dark:text-white px-3 py-2">
        </div>
        <div class="w-full sm:w-auto">
            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('To') }}</label>
            <input type="date" name="to" value="{{ request('to') }}" class="w-full sm:w-auto rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 text-sm text-gray-900 dark:text-white px-3 py-2">
        </div>
        <button type="submit" class="w-full sm:w-auto px-4 py-2 rounded-lg bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium">{{ __('Filter') }}</button>
        <a href="{{ route('admin.login-history.index') }}" class="w-full sm:w-auto text-center px-4 py-2 rounded-lg border border-gray-300 dark:border-dark-600 text-sm text-gray-700 dark:text-gray-300">{{ __('Reset') }}</a>
    </form>

    <div class="bg-white dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 overflow-hidden">
        <div class="overflow-x-auto md:overflow-x-visible">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 dark:bg-dark-700/50 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3">{{ __('When') }}</th>
                        <th class="px-4 py-3">{{ __('Result') }}</th>
                        <th class="hidden sm:table-cell px-4 py-3">{{ __('User') }}</th>
                        <th class="hidden sm:table-cell px-4 py-3">{{ __('Identifier') }}</th>
                        <th class="hidden lg:table-cell px-4 py-3">{{ __('Note') }}</th>
                        <th class="hidden sm:table-cell px-4 py-3">{{ __('IP') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-dark-700">
                    @forelse($histories as $h)
                        <tr class="hover:bg-gray-50 dark:hover:bg-dark-700/30">
                            <td class="px-4 py-3 whitespace-nowrap text-gray-600 dark:text-gray-300">
                                {{ $h->created_at->format('d.m.Y H:i:s') }}
                            </td>
                            <td class="px-4 py-3">
                                @if($h->success)
                                    <span class="text-green-600 dark:text-green-400 font-medium">{{ __('Success') }}</span>
                                @else
                                    <span class="text-red-600 dark:text-red-400 font-medium">{{ __('Failed') }}</span>
                                @endif
                                <div class="sm:hidden mt-1 space-y-1">
                                    <p class="text-xs text-gray-900 dark:text-white">{{ $h->user?->name ?? '—' }}</p>
                                    <p class="font-mono text-[11px] text-gray-600 dark:text-gray-300 break-all">{{ $h->identifier }}</p>
                                    <p class="font-mono text-[11px] text-gray-500 break-all">{{ $h->ip_address ?? '—' }}</p>
                                </div>
                            </td>
                            <td class="hidden sm:table-cell px-4 py-3 text-gray-900 dark:text-white">
                                {{ $h->user?->name ?? '—' }}
                            </td>
                            <td class="hidden sm:table-cell px-4 py-3 font-mono text-xs text-gray-700 dark:text-gray-300 break-all">{{ $h->identifier }}</td>
                            <td class="hidden lg:table-cell px-4 py-3 text-gray-500 dark:text-gray-400 text-xs">{{ $h->failure_reason ?? '—' }}</td>
                            <td class="hidden sm:table-cell px-4 py-3 font-mono text-xs text-gray-500">{{ $h->ip_address ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="px-4 py-12 text-center text-gray-500 dark:text-gray-400">{{ __('No entries found.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($histories->hasPages())
            <div class="px-4 py-3 border-t border-gray-200 dark:border-dark-700">
                {{ $histories->links() }}
            </div>
        @endif
    </div>
@endsection
