@extends('layouts.admin')

@section('title', __('Forms'))

@section('content')
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Forms') }}</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1">{{ __('Create reusable forms and embed them in pages.') }}</p>
        </div>
        <a href="{{ route('admin.forms.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-500 hover:bg-primary-600 text-white rounded-lg transition-colors">
            <i class="fas fa-plus"></i>
            {{ __('Create Form') }}
        </a>
    </div>

    <div class="bg-white dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 overflow-visible">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-dark-700">
                    <tr>
                        <th class="px-4 py-3 text-left">{{ __('Name') }}</th>
                        <th class="hidden sm:table-cell px-4 py-3 text-left">{{ __('Slug') }}</th>
                        <th class="hidden sm:table-cell px-4 py-3 text-left">{{ __('Status') }}</th>
                        <th class="hidden sm:table-cell px-4 py-3 text-left">{{ __('Submissions') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-dark-700">
                    @forelse($forms as $form)
                        <tr>
                            <td class="px-4 py-3 font-medium">
                                <div>{{ $form->name }}</div>
                                <div class="sm:hidden mt-1 space-y-1 text-xs text-gray-500 dark:text-gray-400">
                                    <div class="font-mono">{{ $form->slug }}</div>
                                    <div>{{ __('Submissions') }}: {{ $form->submissions_count }}</div>
                                    <div>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] {{ $form->is_active ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300' }}">
                                            {{ $form->is_active ? __('Active') : __('Inactive') }}
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td class="hidden sm:table-cell px-4 py-3 font-mono text-xs">{{ $form->slug }}</td>
                            <td class="hidden sm:table-cell px-4 py-3">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs {{ $form->is_active ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300' }}">
                                    {{ $form->is_active ? __('Active') : __('Inactive') }}
                                </span>
                            </td>
                            <td class="hidden sm:table-cell px-4 py-3">{{ $form->submissions_count }}</td>
                            <td class="px-4 py-3">
                                <div class="hidden sm:flex items-center gap-2">
                                    <a href="{{ route('admin.forms.submissions', $form) }}" class="px-2 py-1 rounded border border-gray-300 dark:border-dark-600 text-xs">{{ __('Submissions') }}</a>
                                    <a href="{{ route('admin.forms.edit', $form) }}" class="px-2 py-1 rounded border border-primary-300 text-primary-600 dark:text-primary-400 text-xs">{{ __('Edit') }}</a>
                                    <form method="POST" action="{{ route('admin.forms.destroy', $form) }}" onsubmit="return confirm('{{ __('Delete this form?') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2 py-1 rounded border border-red-300 text-red-600 dark:text-red-400 text-xs">{{ __('Delete') }}</button>
                                    </form>
                                </div>
                                <div x-data="{ open: false }" class="sm:hidden">
                                    <button type="button" @click="open = !open" class="inline-flex items-center gap-2 px-2 py-1 rounded border border-gray-300 dark:border-dark-600 text-xs">
                                        <i class="fas fa-ellipsis-h"></i>
                                        {{ __('Actions') }}
                                    </button>
                                    <div x-show="open" x-cloak class="mt-2 w-full max-w-[12rem] p-2 rounded-lg bg-white dark:bg-dark-800 border border-gray-200 dark:border-dark-700 shadow-lg space-y-1.5">
                                        <a href="{{ route('admin.forms.submissions', $form) }}" class="block px-2 py-1 rounded border border-gray-300 dark:border-dark-600 text-xs">{{ __('Submissions') }}</a>
                                        <a href="{{ route('admin.forms.edit', $form) }}" class="block px-2 py-1 rounded border border-primary-300 text-primary-600 dark:text-primary-400 text-xs">{{ __('Edit') }}</a>
                                        <form method="POST" action="{{ route('admin.forms.destroy', $form) }}" onsubmit="return confirm('{{ __('Delete this form?') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-full text-left px-2 py-1 rounded border border-red-300 text-red-600 dark:text-red-400 text-xs">{{ __('Delete') }}</button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-gray-500 dark:text-gray-400">{{ __('No forms created yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($forms->hasPages())
            <div class="px-4 py-3 border-t border-gray-200 dark:border-dark-700">
                {{ $forms->links() }}
            </div>
        @endif
    </div>
@endsection

