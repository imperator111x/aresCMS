@extends('layouts.admin')

@section('title', __('Breaking News Mode'))

@section('content')
    <div class="max-w-3xl space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Breaking News Mode') }}</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Show a prominent breaking-news banner across the website.') }}</p>
        </div>

        <div class="bg-white dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl p-5">
            <form action="{{ route('admin.breaking-news.update') }}" method="POST" class="space-y-4">
                @csrf

                <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                    <input type="checkbox" name="enabled" value="1" @checked((bool) ($config['enabled'] ?? false)) class="rounded border-gray-300">
                    <span>{{ __('Enable breaking news banner') }}</span>
                </label>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Badge text') }}</label>
                        <input type="text" name="badge" maxlength="40" value="{{ old('badge', (string) ($config['badge'] ?? '')) }}" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Display mode') }}</label>
                        <select name="display_mode" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700">
                            <option value="banner" @selected(($config['display_mode'] ?? 'banner') === 'banner')>{{ __('Banner (top bar)') }}</option>
                            <option value="popup" @selected(($config['display_mode'] ?? '') === 'popup')>{{ __('Popup (dismissible)') }}</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Theme') }}</label>
                        <select name="theme" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700">
                            <option value="red" @selected(($config['theme'] ?? 'red') === 'red')>{{ __('Red') }}</option>
                            <option value="amber" @selected(($config['theme'] ?? '') === 'amber')>{{ __('Amber') }}</option>
                            <option value="orange" @selected(($config['theme'] ?? '') === 'orange')>{{ __('Orange') }}</option>
                            <option value="blue" @selected(($config['theme'] ?? '') === 'blue')>{{ __('Blue') }}</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Headline') }}</label>
                    <input type="text" name="title" maxlength="190" value="{{ old('title', (string) ($config['title'] ?? '')) }}" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Popup text (optional)') }}</label>
                    <textarea name="text" rows="3" maxlength="2000" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700">{{ old('text', (string) ($config['text'] ?? '')) }}</textarea>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Displayed only in popup mode as normal body text.') }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Target URL (optional)') }}</label>
                    <input type="url" name="url" value="{{ old('url', (string) ($config['url'] ?? '')) }}" placeholder="https://example.com/news/urgent" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700">
                </div>

                <button class="px-4 py-2 rounded-lg bg-primary-600 hover:bg-primary-700 text-white font-semibold">{{ __('Save') }}</button>
            </form>
        </div>
    </div>
@endsection
