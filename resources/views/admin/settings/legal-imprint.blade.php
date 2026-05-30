@extends('layouts.admin')

@section('title', __('Legal notice (Imprint)'))

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Legal notice (Imprint)') }}</h1>
        <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">{{ __('These fields appear on the public imprint page and in the site footer (e-mail). If a field is empty here, the value from the server .env file (LEGAL_*) is used as a fallback.') }}</p>
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

    <form action="{{ route('admin.settings.legal-imprint.update') }}" method="POST" class="max-w-3xl space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-white dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 p-6 space-y-5">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-building text-primary-500"></i>
                {{ __('Provider / company') }}
            </h2>

            <div>
                <label for="legal_entity_name" class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">{{ __('Company or name (§ 5 TMG)') }}</label>
                <input type="text" name="legal_entity_name" id="legal_entity_name" value="{{ old('legal_entity_name', $legal['entity_name']) }}"
                    class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 text-gray-900 dark:text-white px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary-500 @error('legal_entity_name') border-red-500 @enderror">
                @error('legal_entity_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="legal_representative" class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">{{ __('Authorised representative (optional)') }}</label>
                <input type="text" name="legal_representative" id="legal_representative" value="{{ old('legal_representative', $legal['representative']) }}"
                    class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 text-gray-900 dark:text-white px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="legal_address_street" class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">{{ __('Street & number') }}</label>
                    <input type="text" name="legal_address_street" id="legal_address_street" value="{{ old('legal_address_street', $legal['street']) }}"
                        class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 text-gray-900 dark:text-white px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                    <label for="legal_address_zip" class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">{{ __('ZIP code') }}</label>
                    <input type="text" name="legal_address_zip" id="legal_address_zip" value="{{ old('legal_address_zip', $legal['zip']) }}"
                        class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 text-gray-900 dark:text-white px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                    <label for="legal_address_city" class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">{{ __('City') }}</label>
                    <input type="text" name="legal_address_city" id="legal_address_city" value="{{ old('legal_address_city', $legal['city']) }}"
                        class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 text-gray-900 dark:text-white px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">
                </div>
                <div class="sm:col-span-2">
                    <label for="legal_country" class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">{{ __('Country') }}</label>
                    <input type="text" name="legal_country" id="legal_country" value="{{ old('legal_country', $legal['country']) }}"
                        class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 text-gray-900 dark:text-white px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="legal_email" class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">{{ __('Email') }}</label>
                    <input type="email" name="legal_email" id="legal_email" value="{{ old('legal_email', $legal['email']) }}"
                        class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 text-gray-900 dark:text-white px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary-500 @error('legal_email') border-red-500 @enderror">
                    @error('legal_email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="legal_phone" class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">{{ __('Phone (optional)') }}</label>
                    <input type="text" name="legal_phone" id="legal_phone" value="{{ old('legal_phone', $legal['phone']) }}"
                        class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 text-gray-900 dark:text-white px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">
                </div>
            </div>

            <div>
                <label for="legal_vat_id" class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">{{ __('VAT ID (optional)') }}</label>
                <input type="text" name="legal_vat_id" id="legal_vat_id" value="{{ old('legal_vat_id', $legal['vat_id']) }}"
                    class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 text-gray-900 dark:text-white px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">
            </div>

            <div>
                <label for="legal_register_info" class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">{{ __('Commercial register / professional body (optional)') }}</label>
                <textarea name="legal_register_info" id="legal_register_info" rows="3"
                    class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 text-gray-900 dark:text-white px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">{{ old('legal_register_info', $legal['register_info']) }}</textarea>
            </div>

            <div>
                <label for="legal_content_responsibility" class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">{{ __('Editorial responsibility (optional, e.g. § 18 Abs. 2 MStV)') }}</label>
                <input type="text" name="legal_content_responsibility" id="legal_content_responsibility" value="{{ old('legal_content_responsibility', $legal['content_responsibility']) }}"
                    class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 text-gray-900 dark:text-white px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">
            </div>
        </div>

        <div class="flex flex-wrap gap-3">
            <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-primary-500 hover:bg-primary-600 text-white text-sm font-semibold transition-colors">
                <i class="fas fa-save"></i>
                {{ __('Save') }}
            </button>
            <a href="{{ route('legal.imprint') }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg border border-gray-300 dark:border-dark-600 text-gray-700 dark:text-gray-300 text-sm font-medium hover:bg-gray-50 dark:hover:bg-dark-700">
                <i class="fas fa-external-link-alt"></i>
                {{ __('View public imprint') }}
            </a>
        </div>
    </form>
@endsection
