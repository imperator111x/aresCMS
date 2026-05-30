@extends('layouts.admin')

@section('title', __('General Settings'))

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('General Settings') }}</h1>
        <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">{{ __('Site name, URL, description, logo, and registration for your portal.') }}</p>
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

    <div class="space-y-8">
        {{-- Website: Name, URL, Beschreibung --}}
        <form action="{{ route('admin.settings.general.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid gap-6 lg:grid-cols-2">
                <div id="settings-site" class="scroll-mt-24 bg-white dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-globe text-primary-500"></i>
                        {{ __('Website identity') }}
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">{{ __('Name and address visitors see in the browser and in metadata.') }}</p>

                    <div class="mt-6 space-y-5">
                        <div>
                            <label for="site_name" class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">
                                {{ __('Site Name') }}
                                <span class="cursor-help shrink-0" title="{{ __('This will be displayed in the browser tab and throughout the site.') }}">
                                    <i class="fas fa-info-circle text-gray-400 hover:text-primary-500"></i>
                                </span>
                            </label>
                            <input
                                type="text"
                                name="site_name"
                                id="site_name"
                                value="{{ old('site_name', $settings['site_name']) }}"
                                required
                                class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('site_name') border-red-500 ring-1 ring-red-500 @enderror"
                                placeholder="{{ __('News Portal') }}"
                            >
                            @error('site_name')
                                <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="site_url" class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">
                                {{ __('Site URL') }}
                                <span class="cursor-help shrink-0" title="{{ __('This will be used throughout the site for links and references.') }}">
                                    <i class="fas fa-info-circle text-gray-400 hover:text-primary-500"></i>
                                </span>
                            </label>
                            <input
                                type="url"
                                name="site_url"
                                id="site_url"
                                value="{{ old('site_url', $settings['site_url']) }}"
                                required
                                class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 px-3 py-2.5 text-sm font-mono focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('site_url') border-red-500 ring-1 ring-red-500 @enderror"
                                placeholder="https://example.com"
                            >
                            @error('site_url')
                                <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 p-6 flex flex-col">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-align-left text-amber-500"></i>
                        {{ __('Site Description') }}
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">{{ __('A brief description of your website for SEO purposes.') }}</p>

                    <div class="mt-6 flex-1 flex flex-col min-h-[8rem]">
                        <label for="site_description" class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">
                            {{ __('Description text') }}
                            <span class="cursor-help shrink-0" title="{{ __('Shown in meta tags where supported.') }}">
                                <i class="fas fa-info-circle text-gray-400 hover:text-amber-500"></i>
                            </span>
                        </label>
                        <textarea
                            name="site_description"
                            id="site_description"
                            rows="6"
                            class="w-full flex-1 min-h-[10rem] rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 px-3 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-transparent resize-y @error('site_description') border-red-500 ring-1 ring-red-500 @enderror"
                            placeholder="{{ __('Short summary for search engines and social previews…') }}"
                        >{{ old('site_description', $settings['site_description']) }}</textarea>
                        @error('site_description')
                            <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">{{ __('Maximum :max characters.', ['max' => 500]) }}</p>
                    </div>
                </div>
            </div>

            <div id="settings-social" class="scroll-mt-24 bg-white dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="fas fa-share-alt text-sky-500"></i>
                    {{ __('Social networks') }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">{{ __('These links are shown in the website footer.') }}</p>

                <div class="mt-6 grid gap-4 md:grid-cols-2">
                    <div class="rounded-lg border border-gray-200 dark:border-dark-600 p-3">
                        <label for="social_twitter" class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Twitter / X</label>
                        <input type="url" name="social_twitter" id="social_twitter" value="{{ old('social_twitter', $settings['social_twitter'] ?? '') }}"
                            placeholder="https://twitter.com/..."
                            class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 text-gray-900 dark:text-white px-3 py-2.5 text-sm @error('social_twitter') border-red-500 ring-1 ring-red-500 @enderror">
                        <label class="mt-2 inline-flex items-center gap-2 text-xs text-gray-600 dark:text-gray-300">
                            <input type="checkbox" name="social_twitter_enabled" value="1" @checked(old('social_twitter_enabled', $settings['social_twitter_enabled'] ?? false)) class="rounded border-gray-300 dark:border-dark-600 text-primary-600 focus:ring-primary-500">
                            <span>{{ __('Show in footer') }}</span>
                        </label>
                        @error('social_twitter')
                            <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="rounded-lg border border-gray-200 dark:border-dark-600 p-3">
                        <label for="social_facebook" class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Facebook</label>
                        <input type="url" name="social_facebook" id="social_facebook" value="{{ old('social_facebook', $settings['social_facebook'] ?? '') }}"
                            placeholder="https://facebook.com/..."
                            class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 text-gray-900 dark:text-white px-3 py-2.5 text-sm @error('social_facebook') border-red-500 ring-1 ring-red-500 @enderror">
                        <label class="mt-2 inline-flex items-center gap-2 text-xs text-gray-600 dark:text-gray-300">
                            <input type="checkbox" name="social_facebook_enabled" value="1" @checked(old('social_facebook_enabled', $settings['social_facebook_enabled'] ?? false)) class="rounded border-gray-300 dark:border-dark-600 text-primary-600 focus:ring-primary-500">
                            <span>{{ __('Show in footer') }}</span>
                        </label>
                        @error('social_facebook')
                            <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="rounded-lg border border-gray-200 dark:border-dark-600 p-3">
                        <label for="social_instagram" class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Instagram</label>
                        <input type="url" name="social_instagram" id="social_instagram" value="{{ old('social_instagram', $settings['social_instagram'] ?? '') }}"
                            placeholder="https://instagram.com/..."
                            class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 text-gray-900 dark:text-white px-3 py-2.5 text-sm @error('social_instagram') border-red-500 ring-1 ring-red-500 @enderror">
                        <label class="mt-2 inline-flex items-center gap-2 text-xs text-gray-600 dark:text-gray-300">
                            <input type="checkbox" name="social_instagram_enabled" value="1" @checked(old('social_instagram_enabled', $settings['social_instagram_enabled'] ?? false)) class="rounded border-gray-300 dark:border-dark-600 text-primary-600 focus:ring-primary-500">
                            <span>{{ __('Show in footer') }}</span>
                        </label>
                        @error('social_instagram')
                            <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="rounded-lg border border-gray-200 dark:border-dark-600 p-3">
                        <label for="social_youtube" class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">YouTube</label>
                        <input type="url" name="social_youtube" id="social_youtube" value="{{ old('social_youtube', $settings['social_youtube'] ?? '') }}"
                            placeholder="https://youtube.com/@..."
                            class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 text-gray-900 dark:text-white px-3 py-2.5 text-sm @error('social_youtube') border-red-500 ring-1 ring-red-500 @enderror">
                        <label class="mt-2 inline-flex items-center gap-2 text-xs text-gray-600 dark:text-gray-300">
                            <input type="checkbox" name="social_youtube_enabled" value="1" @checked(old('social_youtube_enabled', $settings['social_youtube_enabled'] ?? false)) class="rounded border-gray-300 dark:border-dark-600 text-primary-600 focus:ring-primary-500">
                            <span>{{ __('Show in footer') }}</span>
                        </label>
                        @error('social_youtube')
                            <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium shadow-sm transition-colors">
                    <i class="fas fa-save"></i>
                    {{ __('Save site settings') }}
                </button>
            </div>
        </form>

        {{-- Logo --}}
        <form id="settings-logo" action="{{ route('admin.settings.logo.update') }}" method="POST" enctype="multipart/form-data" class="scroll-mt-24 bg-white dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 p-6 space-y-6">
            @csrf
            @method('PUT')

            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="fas fa-image text-purple-500"></i>
                    {{ __('Logo Settings') }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">{{ __('Header and admin bar branding. Recommended wide format.') }}</p>
            </div>

            <div class="flex flex-wrap items-start gap-4 p-4 rounded-lg bg-gray-50 dark:bg-dark-900/50 border border-gray-200 dark:border-dark-600">
                <div class="w-56 max-w-full h-24 rounded-md bg-white dark:bg-dark-700 border border-gray-200 dark:border-dark-600 p-2 flex items-center justify-center overflow-hidden">
                    @if($settings['site_logo'])
                        <img src="{{ asset('storage/' . $settings['site_logo']) }}" alt="{{ __('Current logo') }}" class="max-h-full w-auto object-contain">
                    @else
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ __('No logo uploaded yet.') }}</span>
                    @endif
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">{{ __('Current logo') }}</p>
                    @if($settings['site_logo'])
                        <p class="text-sm text-gray-600 dark:text-gray-300 mt-1 font-mono break-all">{{ $settings['site_logo'] }}</p>
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('No logo uploaded yet.') }}</p>
                    @endif
                </div>
            </div>

            <div>
                <label for="site_logo" class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-2">{{ __('Upload new logo') }}</label>
                <input
                    type="file"
                    name="site_logo"
                    id="site_logo"
                    accept="image/*"
                    class="block w-full text-sm text-gray-600 dark:text-gray-300 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 dark:file:bg-dark-700 dark:file:text-primary-400 dark:hover:file:bg-dark-600 cursor-pointer @error('site_logo') border border-red-500 rounded-lg @enderror"
                >
                @error('site_logo')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">{{ __('Recommended size: 200x50 pixels. Max file size: 2MB.') }}</p>
            </div>

            <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium shadow-sm transition-colors">
                <i class="fas fa-save"></i>
                {{ __('Save logo') }}
            </button>
        </form>

        {{-- Registrierung --}}
        <form id="settings-registration" action="{{ route('admin.settings.registration.update') }}" method="POST" class="scroll-mt-24 bg-white dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 p-6 space-y-6">
            @csrf
            @method('PUT')

            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="fas fa-user-plus text-green-500"></i>
                    {{ __('Registration Settings') }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">{{ __('Control whether new accounts can register on the public site.') }}</p>
                <p class="text-xs mt-2 {{ $settings['disable_registration'] ? 'text-red-600 dark:text-red-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                    {{ __('Current status: :status', ['status' => $settings['disable_registration'] ? __('Disabled') : __('Enabled')]) }}
                </p>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 p-4 rounded-xl border border-gray-200 dark:border-dark-600 bg-gray-50/80 dark:bg-dark-900/30">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ __('Disable Registration') }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('If enabled, new users will not be able to register.') }}</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer shrink-0">
                    <input type="checkbox" name="disable_registration" value="1" class="sr-only peer" @checked(old('disable_registration', $settings['disable_registration']))>
                    <div class="w-11 h-6 bg-gray-300 rounded-full peer dark:bg-gray-600 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300/40 dark:peer-focus:ring-primary-800/40 peer-checked:bg-primary-600 after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:shadow-sm after:transition-all dark:border-gray-500 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full"></div>
                </label>
            </div>

            <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-medium shadow-sm transition-colors">
                <i class="fas fa-save"></i>
                {{ __('Save registration') }}
            </button>
        </form>
    </div>
@endsection
