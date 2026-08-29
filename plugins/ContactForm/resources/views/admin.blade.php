@extends('layouts.admin')

@section('title', __('Contact form'))

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Contact form') }}</h1>
        <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">{{ __('Public page at /kontakt with honeypot and rate limiting. Fields are managed under Forms.') }}</p>
    </div>

    <form method="POST" action="{{ route('admin.contact-form.update') }}" class="max-w-2xl space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-white dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 p-6 space-y-5">
            <div>
                <label for="contact_form_page_title" class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">{{ __('Page title') }}</label>
                <input type="text" name="contact_form_page_title" id="contact_form_page_title"
                    value="{{ old('contact_form_page_title', $settings['contact_form_page_title']) }}"
                    class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 text-gray-900 dark:text-white px-3 py-2.5 text-sm">
            </div>
            <div>
                <label for="contact_form_page_intro" class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">{{ __('Intro text') }}</label>
                <textarea name="contact_form_page_intro" id="contact_form_page_intro" rows="3"
                    class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 text-gray-900 dark:text-white px-3 py-2.5 text-sm">{{ old('contact_form_page_intro', $settings['contact_form_page_intro']) }}</textarea>
            </div>
            <div>
                <label for="contact_form_slug" class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">{{ __('Form slug') }}</label>
                <input type="text" name="contact_form_slug" id="contact_form_slug"
                    value="{{ old('contact_form_slug', $settings['contact_form_slug']) }}"
                    class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 text-gray-900 dark:text-white px-3 py-2.5 text-sm font-mono">
                <p class="mt-1.5 text-xs text-gray-500">{{ __('Must match an active form under Admin → Forms (default: kontakt).') }}</p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium">
                <i class="fas fa-save"></i> {{ __('Save') }}
            </button>
            <a href="{{ url('/kontakt') }}" target="_blank" class="text-sm text-primary-600 dark:text-primary-400 hover:underline">{{ __('Open /kontakt') }}</a>
            @if(Route::has('admin.forms.index'))
                <a href="{{ route('admin.forms.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">{{ __('Manage forms') }}</a>
            @endif
        </div>
    </form>
@endsection
