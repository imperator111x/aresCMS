@extends('layouts.admin')

@section('title', __('News Categories'))

@section('content')
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('News Categories') }}</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1">{{ __('Create and manage categories for news filtering.') }}</p>
        </div>
        <a href="{{ route('admin.news.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-dark-700 hover:bg-gray-200 dark:hover:bg-dark-600 text-gray-700 dark:text-gray-300 rounded-lg transition-colors">
            <i class="fas fa-arrow-left"></i>
            {{ __('Back to List') }}
        </a>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="bg-white dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Create category') }}</h2>
            <form action="{{ route('admin.news-categories.store') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Name') }}</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required maxlength="120" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 text-gray-900 dark:text-white @error('name') border-red-500 @enderror">
                    @error('name')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="sort_order" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Order') }}</label>
                    <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}" min="0" max="9999" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 text-gray-900 dark:text-white @error('sort_order') border-red-500 @enderror">
                    @error('sort_order')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-primary-600 hover:bg-primary-700 text-white text-sm">
                    <i class="fas fa-save"></i>
                    {{ __('Create category') }}
                </button>
            </form>
        </div>

        <div class="lg:col-span-2 bg-white dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-dark-700">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">{{ __('Name') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">{{ __('Order') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-dark-700">
                        @forelse($categories as $category)
                            <tr>
                                <td class="px-4 py-3">
                                    <form action="{{ route('admin.news-categories.update', $category) }}" method="POST" class="flex flex-wrap gap-2 items-center">
                                        @csrf
                                        @method('PUT')
                                        <input type="text" name="name" value="{{ $category->name }}" maxlength="120" required class="px-3 py-2 rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 text-gray-900 dark:text-white">
                                        <input type="number" name="sort_order" value="{{ $category->sort_order }}" min="0" max="9999" class="w-24 px-3 py-2 rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 text-gray-900 dark:text-white">
                                        <button type="submit" class="inline-flex items-center gap-1 px-3 py-2 rounded-lg bg-primary-600 hover:bg-primary-700 text-white text-xs">
                                            <i class="fas fa-save"></i>{{ __('Save') }}
                                        </button>
                                    </form>
                                </td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $category->sort_order }}</td>
                                <td class="px-4 py-3">
                                    <form action="{{ route('admin.news-categories.destroy', $category) }}" method="POST" onsubmit="return confirm(@json(__('Delete this category? Assigned news will be uncategorized.')));">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center gap-1 px-3 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white text-xs">
                                            <i class="fas fa-trash"></i>{{ __('Delete') }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-10 text-center text-gray-500 dark:text-gray-400">{{ __('No categories yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
