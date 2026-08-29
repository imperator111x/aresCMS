@extends('layouts.admin')

@section('title', __('Cookie consent'))

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Cookie consent') }}</h1>
        <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">{{ __('Configure the GDPR cookie banner shown to visitors. Empty text fields use the default translations.') }}</p>
    </div>

    @include('admin.settings._nav')

    <form action="{{ route('admin.settings.cookie-consent.update') }}" method="POST" class="max-w-3xl space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-white dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 p-6 space-y-5">
            <label class="inline-flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="cookie_consent_enabled" value="1" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                    {{ old('cookie_consent_enabled', $settings['cookie_consent_enabled']) ? 'checked' : '' }}>
                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ __('Show cookie consent banner') }}</span>
            </label>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('When disabled, the banner and footer “Cookie settings” links are hidden. Essential session cookies may still be used.') }}</p>

            <div>
                <label for="cookie_consent_title" class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">{{ __('Banner title') }}</label>
                <input type="text" name="cookie_consent_title" id="cookie_consent_title"
                    value="{{ old('cookie_consent_title', $settings['cookie_consent_title']) }}"
                    placeholder="{{ __('Cookie consent') }}"
                    class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 text-gray-900 dark:text-white px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">
            </div>

            <div>
                <label for="cookie_consent_text" class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">{{ __('Banner text') }}</label>
                <textarea name="cookie_consent_text" id="cookie_consent_text" rows="4"
                    placeholder="{{ __('We use cookies and similar technologies for essential functions, language and session. With your consent, optional services (e.g. CAPTCHA) may load as described in the privacy policy.') }}"
                    class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 text-gray-900 dark:text-white px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">{{ old('cookie_consent_text', $settings['cookie_consent_text']) }}</textarea>
            </div>

            <div>
                <label for="cookie_consent_privacy_label" class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">{{ __('Privacy link label') }}</label>
                <input type="text" name="cookie_consent_privacy_label" id="cookie_consent_privacy_label"
                    value="{{ old('cookie_consent_privacy_label', $settings['cookie_consent_privacy_label']) }}"
                    placeholder="{{ __('Cookie privacy link') }}"
                    class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 text-gray-900 dark:text-white px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">
                <p class="mt-1.5 text-xs text-gray-500">{{ __('Links to the privacy policy (#cookies).') }}</p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium">
                <i class="fas fa-save"></i> {{ __('Save') }}
            </button>
            <a href="{{ route('legal.privacy') }}#cookies" target="_blank" class="text-sm text-primary-600 dark:text-primary-400 hover:underline">
                {{ __('Preview privacy section') }}
            </a>
        </div>
    </form>
@endsection
