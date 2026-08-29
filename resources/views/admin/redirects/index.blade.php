@extends('layouts.admin')

@section('title', __('Redirects'))

@section('content')
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Redirect Manager') }}</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1">{{ __('Map old URLs to new ones (301/302) for SEO after relaunches or slug changes.') }}</p>
        </div>
    </div>

    <div class="bg-white dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 p-5 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('Add redirect') }}</h2>
        <form method="POST" action="{{ route('admin.redirects.store') }}" class="grid gap-4 md:grid-cols-2">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">{{ __('From path') }}</label>
                <input type="text" name="from_path" value="{{ old('from_path') }}" placeholder="/alte-seite" required
                    class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 text-gray-900 dark:text-white px-3 py-2 text-sm font-mono">
                @error('from_path')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">{{ __('To URL or path') }}</label>
                <input type="text" name="to_url" value="{{ old('to_url') }}" placeholder="/neue-seite or https://…" required
                    class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 text-gray-900 dark:text-white px-3 py-2 text-sm font-mono">
                @error('to_url')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">{{ __('Status code') }}</label>
                <select name="status_code" class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 text-sm px-3 py-2">
                    <option value="301" @selected(old('status_code', '301') == '301')>301 {{ __('Permanent') }}</option>
                    <option value="302" @selected(old('status_code') == '302')>302 {{ __('Temporary') }}</option>
                    <option value="307" @selected(old('status_code') == '307')>307</option>
                    <option value="308" @selected(old('status_code') == '308')>308</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">{{ __('Notes') }}</label>
                <input type="text" name="notes" value="{{ old('notes') }}" class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 text-sm px-3 py-2">
            </div>
            <div class="md:col-span-2 flex flex-wrap items-center gap-4">
                <label class="inline-flex items-center gap-2 text-sm">
                    <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-primary-600" checked>
                    {{ __('Active') }}
                </label>
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium">
                    <i class="fas fa-plus"></i> {{ __('Save') }}
                </button>
            </div>
        </form>
    </div>

    <div class="bg-white dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-dark-700">
                    <tr>
                        <th class="px-4 py-3 text-left">{{ __('From') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('To') }}</th>
                        <th class="hidden sm:table-cell px-4 py-3 text-left">{{ __('Code') }}</th>
                        <th class="hidden sm:table-cell px-4 py-3 text-left">{{ __('Hits') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-dark-700">
                    @forelse($redirects as $redirect)
                        <tr>
                            <td class="px-4 py-3 font-mono text-xs">{{ $redirect->from_path }}</td>
                            <td class="px-4 py-3 font-mono text-xs break-all">{{ $redirect->to_url }}</td>
                            <td class="hidden sm:table-cell px-4 py-3">{{ $redirect->status_code }}</td>
                            <td class="hidden sm:table-cell px-4 py-3">{{ $redirect->hits }}</td>
                            <td class="px-4 py-3">
                                <form method="POST" action="{{ route('admin.redirects.destroy', $redirect) }}" onsubmit="return confirm('{{ __('Delete this redirect?') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline text-xs">{{ __('Delete') }}</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500">{{ __('No redirects yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($redirects->hasPages())
            <div class="p-4 border-t border-gray-200 dark:border-dark-700">{{ $redirects->links() }}</div>
        @endif
    </div>
@endsection
