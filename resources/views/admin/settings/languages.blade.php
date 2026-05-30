@extends('layouts.admin')

@section('title', __('Language Settings'))

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Language Settings') }}</h1>
        <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">{{ __('Manage JSON translations and add new language files.') }}</p>
    </div>

    <div class="mb-6 flex flex-wrap gap-2">
        <a href="{{ route('admin.settings.general') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border {{ request()->routeIs('admin.settings.general*') ? 'bg-primary-600 text-white border-primary-600' : 'border-gray-300 dark:border-dark-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-dark-700' }}">
            <i class="fas fa-sliders-h"></i>
            {{ __('General Settings') }}
        </a>
        <a href="{{ route('admin.settings.languages') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border {{ request()->routeIs('admin.settings.languages*') ? 'bg-primary-600 text-white border-primary-600' : 'border-gray-300 dark:border-dark-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-dark-700' }}">
            <i class="fas fa-language"></i>
            {{ __('Language Settings') }}
        </a>
        <a href="{{ route('admin.settings.legal-imprint') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border {{ request()->routeIs('admin.settings.legal-imprint*') ? 'bg-primary-600 text-white border-primary-600' : 'border-gray-300 dark:border-dark-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-dark-700' }}">
            <i class="fas fa-balance-scale"></i>
            {{ __('Legal notice (Imprint)') }}
        </a>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <section class="bg-white dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 p-6 space-y-6">
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Add language') }}</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('Create a new locale JSON file, optionally copied from an existing language.') }}</p>
            </div>

            <form action="{{ route('admin.settings.languages.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="locale_code" class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">{{ __('Locale code') }}</label>
                    <input type="text" id="locale_code" name="locale_code" value="{{ old('locale_code') }}" placeholder="fr or pt-BR"
                        class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 text-gray-900 dark:text-white px-3 py-2.5 text-sm @error('locale_code') border-red-500 @enderror">
                    @error('locale_code')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="copy_from" class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">{{ __('Copy from') }}</label>
                    <select id="copy_from" name="copy_from"
                        class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 text-gray-900 dark:text-white px-3 py-2.5 text-sm">
                        @foreach($locales as $locale)
                            <option value="{{ $locale }}">{{ $locale }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium">
                    <i class="fas fa-plus"></i>
                    {{ __('Create language') }}
                </button>
            </form>
        </section>

        <section class="xl:col-span-2 bg-white dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 p-6">
            <div class="flex flex-col sm:flex-row sm:items-end gap-3 mb-4">
                <div class="sm:min-w-[16rem]">
                    <label for="locale-select" class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">{{ __('Language file') }}</label>
                    <select id="locale-select"
                        onchange="window.location='{{ route('admin.settings.languages') }}?locale='+encodeURIComponent(this.value)"
                        class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 text-gray-900 dark:text-white px-3 py-2.5 text-sm">
                        @foreach($locales as $locale)
                            <option value="{{ $locale }}" @selected($locale === $selectedLocale)>{{ $locale }}</option>
                        @endforeach
                    </select>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Edit JSON key/value pairs. Save writes to resources/lang/:locale.json.', ['locale' => $selectedLocale]) }}</p>
            </div>

            <form action="{{ route('admin.settings.languages.update') }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <input type="hidden" name="locale" value="{{ $selectedLocale }}">
                <div>
                    <textarea name="translations_json" rows="28"
                        class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-900 text-gray-900 dark:text-gray-100 font-mono text-xs leading-5 px-3 py-3 @error('translations_json') border-red-500 @enderror">{{ old('translations_json', $translationsJson) }}</textarea>
                    @error('translations_json')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium">
                    <i class="fas fa-save"></i>
                    {{ __('Save translations') }}
                </button>
            </form>
        </section>
    </div>
@endsection

