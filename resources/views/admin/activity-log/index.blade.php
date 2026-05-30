@extends('layouts.admin')

@section('title', __('Activity log'))

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Activity log') }}</h1>
        <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">{{ __('Recent changes in the admin area and on the site.') }}</p>
        <div class="mt-3 flex flex-wrap gap-2">
            <a href="{{ route('admin.activity-log.export.pdf', request()->query()) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-sm font-medium">
                <i class="fas fa-file-pdf"></i>
                {{ __('Export PDF') }}
            </a>
            <a href="{{ route('admin.activity-log.export.excel', request()->query()) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium">
                <i class="fas fa-file-excel"></i>
                {{ __('Export Excel') }}
            </a>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.activity-log.index') }}" class="mb-6 flex flex-wrap gap-3 items-end bg-white dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 p-4">
        <div class="w-full sm:w-auto">
            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('User') }}</label>
            <select name="user_id" class="w-full sm:w-auto rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 text-sm text-gray-900 dark:text-white px-3 py-2 min-w-[10rem]">
                <option value="">{{ __('All users') }}</option>
                @foreach($users as $u)
                    <option value="{{ $u->id }}" @selected((string)request('user_id') === (string)$u->id)>{{ $u->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="w-full sm:w-auto">
            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('Action') }}</label>
            <input type="text" name="action" value="{{ request('action') }}" placeholder="news.updated" class="w-full sm:w-44 rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 text-sm text-gray-900 dark:text-white px-3 py-2">
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
        <a href="{{ route('admin.activity-log.index') }}" class="w-full sm:w-auto text-center px-4 py-2 rounded-lg border border-gray-300 dark:border-dark-600 text-sm text-gray-700 dark:text-gray-300">{{ __('Reset') }}</a>
    </form>

    <div class="bg-white dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 overflow-hidden">
        <div class="overflow-x-auto md:overflow-x-visible">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 dark:bg-dark-700/50 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3">{{ __('When') }}</th>
                        <th class="hidden sm:table-cell px-4 py-3">{{ __('User') }}</th>
                        <th class="hidden sm:table-cell px-4 py-3">{{ __('Action') }}</th>
                        <th class="px-4 py-3">{{ __('Details') }}</th>
                        <th class="hidden sm:table-cell px-4 py-3">{{ __('IP') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-dark-700">
                    @forelse($logs as $log)
                        <tr class="hover:bg-gray-50 dark:hover:bg-dark-700/30">
                            <td class="px-4 py-3 whitespace-nowrap text-gray-600 dark:text-gray-300">
                                {{ $log->created_at->format('d.m.Y H:i') }}
                                <span class="block text-xs text-gray-400">{{ $log->created_at->diffForHumans() }}</span>
                            </td>
                            <td class="hidden sm:table-cell px-4 py-3 text-gray-900 dark:text-white">
                                {{ $log->user?->name ?? '—' }}
                            </td>
                            <td class="hidden sm:table-cell px-4 py-3">
                                <code class="text-xs bg-gray-100 dark:bg-dark-700 px-2 py-0.5 rounded text-primary-600 dark:text-primary-400">{{ $log->action }}</code>
                            </td>
                            <td class="px-4 py-3 text-gray-700 dark:text-gray-300 max-w-md">
                                <div class="sm:hidden mb-1 space-y-1">
                                    <p class="text-xs text-gray-900 dark:text-white">{{ $log->user?->name ?? '—' }}</p>
                                    <p><code class="text-xs bg-gray-100 dark:bg-dark-700 px-2 py-0.5 rounded text-primary-600 dark:text-primary-400 break-all">{{ $log->action }}</code></p>
                                    <p class="font-mono text-[11px] text-gray-500 break-all">{{ $log->ip_address ?? '—' }}</p>
                                </div>
                                @if($log->description)
                                    <span class="font-medium">{{ \Illuminate\Support\Str::limit($log->description, 120) }}</span>
                                @endif
                                @if($log->properties && count($log->properties))
                                    <details class="mt-1 text-xs text-gray-500">
                                        <summary class="cursor-pointer hover:text-primary-500">{{ __('JSON') }}</summary>
                                        <pre class="mt-1 p-2 bg-gray-50 dark:bg-dark-900 rounded overflow-x-auto max-h-32">{{ json_encode($log->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                    </details>
                                @endif
                            </td>
                            <td class="hidden sm:table-cell px-4 py-3 text-gray-500 dark:text-gray-400 font-mono text-xs whitespace-nowrap">
                                {{ $log->ip_address ?? '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="px-4 py-12 text-center text-gray-500 dark:text-gray-400">{{ __('No activity recorded yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
            <div class="px-4 py-3 border-t border-gray-200 dark:border-dark-700">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
@endsection
