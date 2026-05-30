@extends('layouts.admin')

@section('title', __('Pages'))

@section('content')
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Pages') }}</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1">{{ __('Create and manage custom pages with drag-and-drop blocks.') }}</p>
        </div>
        <a href="{{ route('admin.pages.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-500 hover:bg-primary-600 text-white rounded-lg transition-colors">
            <i class="fas fa-plus"></i>
            {{ __('Create Page') }}
        </a>
    </div>

    <div class="bg-white dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 overflow-hidden">
        <div class="overflow-x-auto md:overflow-x-visible">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-dark-700">
                    <tr>
                        <th class="px-4 py-3 text-left">{{ __('Title') }}</th>
                        <th class="hidden sm:table-cell px-4 py-3 text-left">{{ __('Slug') }}</th>
                        <th class="hidden sm:table-cell px-4 py-3 text-left">{{ __('Status') }}</th>
                        <th class="hidden md:table-cell px-4 py-3 text-left">{{ __('Navigation') }}</th>
                        <th class="hidden md:table-cell px-4 py-3 text-left">{{ __('Icon') }}</th>
                        <th class="hidden lg:table-cell px-4 py-3 text-left">{{ __('Last Updated') }}</th>
                        <th class="hidden sm:table-cell px-4 py-3 text-left">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-dark-700">
                    @forelse($pages as $page)
                        <tr class="hover:bg-gray-50 dark:hover:bg-dark-700/30">
                            <td class="px-4 py-3 text-gray-900 dark:text-white">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="font-medium truncate">{{ $page->title }}</p>
                                        <p class="sm:hidden font-mono text-[11px] text-gray-500 break-all mt-0.5">/page/{{ $page->slug }}</p>
                                        <div class="sm:hidden mt-1 flex flex-wrap items-center gap-2">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] {{ $page->is_published ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300' }}">
                                                {{ $page->is_published ? __('Published') : __('Draft') }}
                                            </span>
                                            @if($page->show_in_navigation)
                                                <span class="text-[11px] text-gray-500">{{ $page->navigation_label ?: $page->title }} ({{ $page->navigation_order }})</span>
                                            @else
                                                <span class="text-[11px] text-gray-400">{{ __('No') }}</span>
                                            @endif
                                            @if(!empty($page->navigation_icon))
                                                <span class="text-[11px] text-gray-500 inline-flex items-center gap-1">
                                                    <i class="{{ $page->navigation_icon }}"></i>
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="sm:hidden shrink-0 relative" x-data="{ open: false }">
                                        <button type="button" @click="open = !open" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-dark-600 hover:bg-gray-100 dark:hover:bg-dark-700">
                                            <i class="fas fa-ellipsis-h"></i>
                                            {{ __('Actions') }}
                                        </button>
                                        <div x-show="open" x-cloak @click.away="open = false" class="absolute right-0 mt-2 w-44 p-2 rounded-lg bg-white dark:bg-dark-800 border border-gray-200 dark:border-dark-700 shadow-lg z-20 space-y-1.5">
                                            <a href="{{ route('page.show', $page->slug) }}" target="_blank" class="block px-2 py-1 rounded border border-gray-300 dark:border-dark-600 text-xs">{{ __('View') }}</a>
                                            <a href="{{ route('admin.pages.edit', $page) }}" class="block px-2 py-1 rounded border border-primary-300 text-primary-600 dark:text-primary-400 text-xs">{{ __('Edit') }}</a>
                                            <form action="{{ route('admin.pages.destroy', $page) }}" method="POST" onsubmit="return confirm('{{ __('Delete this page?') }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="w-full text-left px-2 py-1 rounded border border-red-300 text-red-600 dark:text-red-400 text-xs">{{ __('Delete') }}</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="hidden sm:table-cell px-4 py-3 font-mono text-xs text-gray-600 dark:text-gray-300">/page/{{ $page->slug }}</td>
                            <td class="hidden sm:table-cell px-4 py-3">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs {{ $page->is_published ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300' }}">
                                    {{ $page->is_published ? __('Published') : __('Draft') }}
                                </span>
                            </td>
                            <td class="hidden md:table-cell px-4 py-3 text-gray-600 dark:text-gray-300">
                                @if($page->show_in_navigation)
                                    <span class="text-xs">{{ $page->navigation_label ?: $page->title }} ({{ $page->navigation_order }})</span>
                                @else
                                    <span class="text-xs text-gray-400">{{ __('No') }}</span>
                                @endif
                            </td>
                            <td class="hidden md:table-cell px-4 py-3 text-gray-600 dark:text-gray-300">
                                @if(!empty($page->navigation_icon))
                                    <i class="{{ $page->navigation_icon }}"></i>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="hidden lg:table-cell px-4 py-3 text-gray-500 dark:text-gray-400">{{ $page->updated_at?->format('d.m.Y H:i') }}</td>
                            <td class="hidden sm:table-cell px-4 py-3">
                                <div class="flex flex-wrap items-center gap-2">
                                    <a href="{{ route('page.show', $page->slug) }}" target="_blank" class="px-2 py-1 rounded border border-gray-300 dark:border-dark-600 text-xs">{{ __('View') }}</a>
                                    <a href="{{ route('admin.pages.edit', $page) }}" class="px-2 py-1 rounded border border-primary-300 text-primary-600 dark:text-primary-400 text-xs">{{ __('Edit') }}</a>
                                    <form action="{{ route('admin.pages.destroy', $page) }}" method="POST" onsubmit="return confirm('{{ __('Delete this page?') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2 py-1 rounded border border-red-300 text-red-600 dark:text-red-400 text-xs">{{ __('Delete') }}</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="px-4 py-10 text-center text-gray-500 dark:text-gray-400">{{ __('No pages created yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($pages->hasPages())
            <div class="px-4 py-3 border-t border-gray-200 dark:border-dark-700">
                {{ $pages->links() }}
            </div>
        @endif
    </div>
@endsection

