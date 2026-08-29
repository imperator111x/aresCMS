<!DOCTYPE html>
@include('partials.html-source-banner')
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" :class="{ 'dark': darkMode }">
<head>
    @php
        $themeSlug = $themeSlug ?? ($cmsTheme ?? 'default');
        $siteName = \App\Models\Setting::getValue('site_name', config('app.name', 'aresCMS'));
        $siteLogo = \App\Models\Setting::getValue('site_logo');
        $siteDescription = trim((string) \App\Models\Setting::getValue('site_description', ''));
        $footerTagline = $siteDescription !== ''
            ? $siteDescription
            : ($themeSlug === 'handwerk'
                ? __('Your partner for refrigeration, air conditioning and ventilation.')
                : __('Your trusted source for the latest news and updates.'));
        $handwerkAuthPage = $themeSlug === 'handwerk' && request()->routeIs(
            'login',
            'register',
            'password.request',
            'password.reset',
            'verification.notice',
            'two-factor.challenge'
        );
    @endphp
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>
        {{ $siteName }}
        @hasSection('title')
            - @yield('title')
        @endif
    </title>
    
    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <style>
        [x-cloak] { display: none !important; }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        
        .dark ::-webkit-scrollbar-track {
            background: #1e293b;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        
        .dark ::-webkit-scrollbar-thumb {
            background: #475569;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        
        .dark ::-webkit-scrollbar-thumb:hover {
            background: #64748b;
        }
        
        /* Smooth transitions */
        * {
            transition: background-color 0.3s ease, border-color 0.3s ease, color 0.3s ease;
        }
        
        /* Line clamp */
        .line-clamp-3 {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        /* Global page transition loading */
        #globalPageLoader {
            position: fixed;
            inset: 0;
            z-index: 9999;
            pointer-events: none;
            opacity: 0;
            transition: opacity .2s ease;
            background: rgba(15, 23, 42, .2);
            backdrop-filter: blur(2px);
        }
        #globalPageLoader.active {
            opacity: 1;
        }
        #globalPageLoader .loader-progress {
            position: absolute;
            top: 0;
            left: 0;
            width: 0;
            height: 3px;
            background: linear-gradient(90deg, #3b82f6, #8b5cf6);
            transition: width .4s ease;
        }
        #globalPageLoader.active .loader-progress {
            width: 88%;
        }
        #globalPageLoader .loader-spinner {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 44px;
            height: 44px;
            margin-top: -22px;
            margin-left: -22px;
            border-radius: 9999px;
            border: 3px solid rgba(255, 255, 255, .35);
            border-top-color: #fff;
            animation: spinLoader .8s linear infinite;
        }
        @keyframes spinLoader {
            to { transform: rotate(360deg); }
        }
    </style>
    
    @stack('styles')
    @if($themeSlug === 'magazine')
        <link href="https://fonts.bunny.net/css?family=libre-baskerville:400,700|merriweather:400,700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="{{ asset('themes/magazine/theme.css') }}">
    @endif
    @if($themeSlug === 'handwerk')
        <link href="https://fonts.bunny.net/css?family=dm-sans:400,500,600,700|inter:400,500,600,700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="{{ asset('themes/handwerk/theme.css') }}?v=3.9">
        <script>
            document.documentElement.classList.remove('dark');
            try { localStorage.setItem('darkMode', 'false'); } catch (e) {}
        </script>
    @endif
</head>
<body @class([
    'min-h-screen',
    'theme-' . $themeSlug,
    'hw-light-page' => $themeSlug === 'handwerk',
    'bg-gray-50 dark:bg-dark-900 text-gray-900 dark:text-gray-100' => $themeSlug !== 'handwerk',
])>
    <div id="globalPageLoader" aria-hidden="true">
        <div class="loader-progress"></div>
        <div class="loader-spinner"></div>
    </div>
    @php
        $breakingNewsBanner = '';
        if (class_exists(\Plugins\BreakingNewsMode\Services\BreakingNewsService::class)) {
            try {
                $breakingNewsBanner = (string) app(\Plugins\BreakingNewsMode\Services\BreakingNewsService::class)->renderBanner();
            } catch (\Throwable) {
                $breakingNewsBanner = '';
            }
        }
    @endphp
    <!-- Navigation -->
    <nav class="sticky top-0 z-50 bg-white/80 dark:bg-dark-800/80 backdrop-blur-lg border-b border-gray-200 dark:border-dark-700 {{ in_array($themeSlug, ['magazine', 'handwerk'], true) ? 'site-nav' : '' }}">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Logo -->
                <a href="{{ url('/') }}" @class([
                    'flex items-center gap-2',
                    'site-nav__brand' => $themeSlug === 'handwerk',
                ])>
                    @if($siteLogo)
                        <img src="{{ asset('storage/' . $siteLogo) }}" alt="Logo" class="h-10" loading="eager" decoding="async" fetchpriority="high">
                    @else
                        <div @class([
                            'w-10 h-10 bg-gradient-to-br from-primary-500 to-purple-500 rounded-xl flex items-center justify-center shadow-lg shadow-primary-500/25',
                            'site-nav__logo-mark' => $themeSlug === 'handwerk',
                        ])>
                            <i class="fas {{ $themeSlug === 'handwerk' ? 'fa-snowflake' : 'fa-newspaper' }} text-white" @if($themeSlug === 'handwerk') aria-hidden="true" @endif></i>
                        </div>
                    @endif
                    <span class="text-xl font-bold text-gray-900 dark:text-white">{{ $siteName }}</span>
                </a>
                
                <!-- Desktop Navigation -->
                @php
                    $navigationPages = collect();
                    $registrationDisabled = \App\Models\Setting::getBoolValue('disable_registration', false);
                    $currentRouteName = (string) request()->route()?->getName();
                    $currentPageSlug = (string) request()->route('slug', '');
                    $desktopNavClasses = static fn (bool $active): string => $active
                        ? 'px-4 py-2 rounded-lg bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 font-semibold transition-colors'
                        : 'px-4 py-2 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-700 font-medium transition-colors';
                    $mobileNavClasses = static fn (bool $active): string => $active
                        ? 'block px-4 py-2 rounded-lg bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 font-semibold'
                        : 'block px-4 py-2 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-700 font-medium';
                    if (\Illuminate\Support\Facades\Schema::hasTable('pages')) {
                        $navigationPages = \Illuminate\Support\Facades\Cache::remember('layout.app.navigation_pages', now()->addMinutes(5), static function () {
                            return \App\Models\Page::query()
                                ->where('is_published', true)
                                ->where('show_in_navigation', true)
                                ->orderBy('navigation_order')
                                ->orderBy('title')
                                ->get(['slug', 'title', 'navigation_label', 'navigation_icon']);
                        });
                    }
                @endphp
                <div class="hidden md:flex items-center gap-1">
                    <a href="{{ url('/') }}" class="{{ $desktopNavClasses($currentRouteName === 'home') }}">
                        <i class="fas fa-home mr-2"></i>{{ __('Home') }}
                    </a>
                    <a href="{{ route('team') }}" class="{{ $desktopNavClasses($currentRouteName === 'team') }}">
                        <i class="fas fa-users mr-2"></i>{{ __('Team') }}
                    </a>
                    @auth
                        @if(\Illuminate\Support\Facades\Route::has('profiles.inbox'))
                            <a href="{{ route('profiles.inbox') }}" class="{{ $desktopNavClasses(in_array($currentRouteName, ['profiles.inbox', 'profiles.chat'], true)) }} relative">
                                <i class="fas fa-comments mr-2"></i>{{ __('Messages') }}
                                <span data-chat-unread-badge class="hidden absolute -top-1 -right-1 min-w-[1.1rem] h-[1.1rem] px-1 rounded-full bg-red-500 text-white text-[10px] font-bold items-center justify-center"></span>
                            </a>
                        @endif
                        @if(\Illuminate\Support\Facades\Route::has('profiles.index'))
                            <a href="{{ route('profiles.index') }}" class="{{ $desktopNavClasses($currentRouteName === 'profiles.index' || $currentRouteName === 'profiles.show') }}">
                                <i class="fas fa-user-group mr-2"></i>{{ __('Members') }}
                            </a>
                        @endif
                    @endauth
                    @foreach($navigationPages as $navigationPage)
                        <a href="{{ route('page.show', $navigationPage->slug) }}" class="{{ $desktopNavClasses($currentRouteName === 'page.show' && $currentPageSlug === $navigationPage->slug) }}">
                            @if(!empty($navigationPage->navigation_icon))
                                <i class="{{ $navigationPage->navigation_icon }} mr-2"></i>
                            @endif
                            {{ $navigationPage->navigation_label ?: $navigationPage->title }}
                        </a>
                    @endforeach
                    
                    @guest
                        <a href="{{ route('login') }}" class="px-4 py-2 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-700 font-medium transition-colors">
                            <i class="fas fa-sign-in-alt mr-2"></i>{{ __('Login') }}
                        </a>
                        @unless($registrationDisabled)
                            <a href="{{ route('register') }}" class="ml-2 px-6 py-2 bg-primary-500 hover:bg-primary-600 text-white font-semibold rounded-xl transition-all shadow-lg shadow-primary-500/25">
                                {{ __('Register') }}
                            </a>
                        @endunless
                    @else
                        <!-- User Dropdown (inkl. Admin für Administratoren) -->
                        <div x-data="{ open: false }" class="relative ml-2">
                            <button @click="open = !open" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-dark-700 transition-colors">
                                @if(auth()->user()->avatar)
                                    <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="{{ auth()->user()->name }}" class="w-8 h-8 rounded-full object-cover" loading="lazy" decoding="async">
                                @else
                                    <div class="w-8 h-8 bg-gradient-to-br from-primary-400 to-purple-400 rounded-full flex items-center justify-center">
                                        <span class="text-sm font-bold text-white">{{ substr(auth()->user()->name, 0, 1) }}</span>
                                    </div>
                                @endif
                                <span class="hidden lg:block text-sm font-medium text-gray-700 dark:text-gray-300">{{ auth()->user()->name }}</span>
                                <i class="fas fa-chevron-down text-xs text-gray-400"></i>
                            </button>
                            
                            <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 mt-2 w-56 bg-white dark:bg-dark-800 rounded-xl shadow-xl border border-gray-200 dark:border-dark-700 py-2 z-50">
                                <div class="px-4 py-3 border-b border-gray-200 dark:border-dark-700">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ auth()->user()->name }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ auth()->user()->email }}</p>
                                </div>
                                @if(auth()->user()->is_admin)
                                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20">
                                        <i class="fas fa-shield-alt w-4 text-center"></i>
                                        {{ __('Admin') }}
                                    </a>
                                @endif
                                <a href="{{ route('account.dashboard') }}" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-700">
                                    <i class="fas fa-user-cog w-4 text-center"></i>
                                    {{ __('Account') }}
                                </a>
                                <a href="{{ url('/') }}" target="_blank" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-700">
                                    <i class="fas fa-external-link-alt w-4"></i>
                                    {{ __('View Site') }}
                                </a>
                                <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="flex items-center gap-3 px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20">
                                    <i class="fas fa-sign-out-alt w-4"></i>
                                    {{ __('Logout') }}
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                                    @csrf
                                </form>
                            </div>
                        </div>
                    @endguest
                    
                    <!-- Dark Mode Toggle -->
                    <button @click="darkMode = !darkMode; localStorage.setItem('darkMode', darkMode)" class="ml-2 p-2 rounded-lg text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-dark-700 transition-colors">
                        <i x-show="!darkMode" class="fas fa-moon"></i>
                        <i x-show="darkMode" class="fas fa-sun"></i>
                    </button>
                    
                    @php
                        $availableLocales = \Illuminate\Support\Facades\Cache::remember('layout.available_locales', now()->addMinutes(30), static function () {
                            return collect(glob(resource_path('lang/*.json')) ?: [])
                                ->map(fn ($path) => pathinfo($path, PATHINFO_FILENAME))
                                ->filter(fn ($locale) => preg_match('/^[a-z]{2}(-[A-Z]{2})?$/', $locale))
                                ->values();
                        });
                        $localeFlag = fn (string $locale) => match (strtolower(strtok($locale, '-'))) {
                            'de' => '🇩🇪',
                            'en' => '🇬🇧',
                            'fr' => '🇫🇷',
                            'es' => '🇪🇸',
                            'it' => '🇮🇹',
                            'pt' => '🇵🇹',
                            'nl' => '🇳🇱',
                            'pl' => '🇵🇱',
                            'tr' => '🇹🇷',
                            default => '🌐',
                        };
                    @endphp
                    <!-- Language Switcher -->
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="p-2 rounded-lg text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-dark-700 transition-colors">
                            <i class="fas fa-globe"></i>
                        </button>
                        
                        <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 mt-2 w-40 bg-white dark:bg-dark-800 rounded-xl shadow-xl border border-gray-200 dark:border-dark-700 py-1 z-50">
                            @foreach($availableLocales as $locale)
                                <a href="{{ route('language.switch', $locale) }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-700 {{ app()->getLocale() == $locale ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-600 dark:text-primary-400' : '' }}">
                                    <span>{{ $localeFlag($locale) }}</span>
                                    <span>{{ strtoupper($locale) }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
                
                <!-- Mobile Menu Button -->
                <button id="mobileMenuBtn" class="md:hidden p-2 rounded-lg text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-dark-700">
                    <i class="fas fa-bars text-xl"></i>
                </button>
            </div>
        </div>
        
        <!-- Mobile Menu -->
        <div id="mobileMenu" class="hidden md:hidden border-t border-gray-200 dark:border-dark-700">
            <div class="px-4 py-4 space-y-2">
                <a href="{{ url('/') }}" class="{{ $mobileNavClasses($currentRouteName === 'home') }}">
                    <i class="fas fa-home mr-2"></i>{{ __('Home') }}
                </a>
                <a href="{{ route('team') }}" class="{{ $mobileNavClasses($currentRouteName === 'team') }}">
                    <i class="fas fa-users mr-2"></i>{{ __('Team') }}
                </a>
                @auth
                    @if(\Illuminate\Support\Facades\Route::has('profiles.inbox'))
                        <a href="{{ route('profiles.inbox') }}" class="{{ $mobileNavClasses(in_array($currentRouteName, ['profiles.inbox', 'profiles.chat'], true)) }}">
                            <i class="fas fa-comments mr-2"></i>{{ __('Messages') }}
                        </a>
                    @endif
                    @if(\Illuminate\Support\Facades\Route::has('profiles.index'))
                        <a href="{{ route('profiles.index') }}" class="{{ $mobileNavClasses($currentRouteName === 'profiles.index' || $currentRouteName === 'profiles.show') }}">
                            <i class="fas fa-user-group mr-2"></i>{{ __('Members') }}
                        </a>
                    @endif
                @endauth
                @foreach($navigationPages as $navigationPage)
                    <a href="{{ route('page.show', $navigationPage->slug) }}" class="{{ $mobileNavClasses($currentRouteName === 'page.show' && $currentPageSlug === $navigationPage->slug) }}">
                        @if(!empty($navigationPage->navigation_icon))
                            <i class="{{ $navigationPage->navigation_icon }} mr-2"></i>
                        @endif
                        {{ $navigationPage->navigation_label ?: $navigationPage->title }}
                    </a>
                @endforeach
                @guest
                    <a href="{{ route('login') }}" class="block px-4 py-2 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-700 font-medium">
                        <i class="fas fa-sign-in-alt mr-2"></i>{{ __('Login') }}
                    </a>
                    @unless($registrationDisabled)
                        <a href="{{ route('register') }}" class="block px-4 py-2 bg-primary-500 hover:bg-primary-600 text-white font-semibold rounded-xl text-center">
                            {{ __('Register') }}
                        </a>
                    @endunless
                @else
                    @if(auth()->user()->is_admin)
                        <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 rounded-lg text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 font-medium">
                            <i class="fas fa-shield-alt mr-2"></i>{{ __('Admin') }}
                        </a>
                    @endif
                    <a href="{{ route('account.dashboard') }}" class="block px-4 py-2 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-700 font-medium">
                        <i class="fas fa-user-cog mr-2"></i>{{ __('Account') }}
                    </a>
                    <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form-mobile').submit();" class="block px-4 py-2 rounded-lg text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 font-medium">
                        <i class="fas fa-sign-out-alt mr-2"></i>{{ __('Logout') }}
                    </a>
                    <form id="logout-form-mobile" action="{{ route('logout') }}" method="POST" class="hidden">
                        @csrf
                    </form>
                @endguest

                <div class="border-t border-gray-200 dark:border-dark-700 mt-3 pt-3 space-y-2">
                    <button type="button" @click="darkMode = !darkMode; localStorage.setItem('darkMode', darkMode)" class="flex w-full items-center gap-3 px-4 py-2.5 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-700 font-medium text-left transition-colors">
                        <span class="w-8 flex justify-center text-gray-500 dark:text-gray-400">
                            <i x-show="!darkMode" class="fas fa-moon" x-cloak></i>
                            <i x-show="darkMode" class="fas fa-sun" x-cloak></i>
                        </span>
                        <span class="flex flex-col leading-tight">
                            <span x-show="!darkMode" x-cloak>{{ __('Dark mode') }}</span>
                            <span x-show="darkMode" x-cloak>{{ __('Light mode') }}</span>
                        </span>
                    </button>
                    <div>
                        <p class="px-4 pb-1 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Language') }}</p>
                        <div class="flex flex-wrap gap-2 px-4">
                            @foreach($availableLocales as $locale)
                                <a href="{{ route('language.switch', $locale) }}" class="flex-1 min-w-[4rem] text-center py-2.5 rounded-xl text-sm font-semibold border transition-colors {{ app()->getLocale() === $locale ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300' : 'border-gray-200 dark:border-dark-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-dark-700' }}">
                                    {{ $localeFlag($locale) }} {{ strtoupper($locale) }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>
    {!! $breakingNewsBanner !!}
    
    <!-- Main Content -->
    <main @class([
        'min-h-screen',
        'hw-auth-main' => $handwerkAuthPage ?? false,
    ])>
        @if(session('success'))
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8">
                <div class="p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl flex items-center gap-3">
                    <i class="fas fa-check-circle text-green-500"></i>
                    <span class="text-green-700 dark:text-green-400">{{ session('success') }}</span>
                </div>
            </div>
        @endif
        
        @if(session('error'))
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8">
                <div class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl flex items-center gap-3">
                    <i class="fas fa-exclamation-circle text-red-500"></i>
                    <span class="text-red-700 dark:text-red-400">{{ session('error') }}</span>
                </div>
            </div>
        @endif
        
        @yield('content')
    </main>
    
    <!-- Footer -->
    <footer class="{{ $themeSlug === 'handwerk' ? '' : 'bg-white dark:bg-dark-800 border-t border-gray-200 dark:border-dark-700' }} py-12 {{ in_array($themeSlug, ['magazine', 'handwerk'], true) ? 'site-footer' : '' }}">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Brand -->
                <div class="col-span-1 md:col-span-2">
                    <a href="{{ url('/') }}" class="flex items-center gap-2 mb-4">
                        @if($siteLogo)
                            <img src="{{ asset('storage/' . $siteLogo) }}" alt="Logo" class="h-10" loading="lazy" decoding="async">
                        @else
                            <div class="w-10 h-10 bg-gradient-to-br from-primary-500 to-purple-500 rounded-xl flex items-center justify-center">
                                <i class="fas {{ $themeSlug === 'handwerk' ? 'fa-snowflake' : 'fa-newspaper' }} text-white"></i>
                            </div>
                        @endif
                        <span class="text-xl font-bold text-gray-900 dark:text-white">{{ $siteName }}</span>
                    </a>
                    <p class="text-gray-600 dark:text-gray-400 mb-4">{{ $footerTagline }}</p>
                    @php
                        $socialLinks = [
                            ['url' => \App\Models\Setting::getValue('social_twitter', ''), 'enabled' => (bool) \App\Models\Setting::getValue('social_twitter_enabled', false), 'icon' => 'fab fa-twitter', 'label' => 'Twitter'],
                            ['url' => \App\Models\Setting::getValue('social_facebook', ''), 'enabled' => (bool) \App\Models\Setting::getValue('social_facebook_enabled', false), 'icon' => 'fab fa-facebook', 'label' => 'Facebook'],
                            ['url' => \App\Models\Setting::getValue('social_instagram', ''), 'enabled' => (bool) \App\Models\Setting::getValue('social_instagram_enabled', false), 'icon' => 'fab fa-instagram', 'label' => 'Instagram'],
                            ['url' => \App\Models\Setting::getValue('social_youtube', ''), 'enabled' => (bool) \App\Models\Setting::getValue('social_youtube_enabled', false), 'icon' => 'fab fa-youtube', 'label' => 'YouTube'],
                        ];
                        $hasSocialLinks = collect($socialLinks)->contains(fn ($item) => !empty($item['enabled']) && filled($item['url'] ?? null));
                    @endphp
                    @if($hasSocialLinks)
                        <div class="flex flex-wrap gap-4">
                            @foreach($socialLinks as $social)
                                @if(!empty($social['enabled']) && filled($social['url']))
                                    <a href="{{ $social['url'] }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $social['label'] }}"
                                        class="w-10 h-10 bg-gray-100 dark:bg-dark-700 rounded-lg flex items-center justify-center text-gray-600 dark:text-gray-400 hover:bg-primary-500 hover:text-white transition-colors">
                                        <i class="{{ $social['icon'] }}"></i>
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
                
                <!-- Quick Links -->
                <div>
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wider mb-4">{{ __('Quick Links') }}</h4>
                    <ul class="space-y-2">
                        <li><a href="{{ url('/') }}" class="text-gray-600 dark:text-gray-400 hover:text-primary-500 transition-colors">{{ __('Home') }}</a></li>
                        <li><a href="{{ route('team') }}" class="text-gray-600 dark:text-gray-400 hover:text-primary-500 transition-colors">{{ __('Team') }}</a></li>
                        <li><a href="{{ \App\Support\LegalUrl::imprint() }}" class="text-gray-600 dark:text-gray-400 hover:text-primary-500 transition-colors">{{ __('legal.imprint.page_title') }}</a></li>
                        <li><a href="{{ \App\Support\LegalUrl::privacy() }}" class="text-gray-600 dark:text-gray-400 hover:text-primary-500 transition-colors">{{ __('legal.privacy.page_title') }}</a></li>
                        <li><a href="{{ \App\Support\LegalUrl::terms() }}" class="text-gray-600 dark:text-gray-400 hover:text-primary-500 transition-colors">{{ __('legal.terms.page_title') }}</a></li>
                        @if(\App\Models\Setting::getBoolValue('cookie_consent_enabled', true))
                            <li><a href="#" class="js-open-cookie-settings text-gray-600 dark:text-gray-400 hover:text-primary-500 transition-colors">{{ __('Cookie settings') }}</a></li>
                        @endif
                        @guest
                            <li><a href="{{ route('login') }}" class="text-gray-600 dark:text-gray-400 hover:text-primary-500 transition-colors">{{ __('Login') }}</a></li>
                            @unless($registrationDisabled)
                                <li><a href="{{ route('register') }}" class="text-gray-600 dark:text-gray-400 hover:text-primary-500 transition-colors">{{ __('Register') }}</a></li>
                            @endunless
                        @endguest
                    </ul>
                </div>
                
                <!-- Contact (gleiche Quelle wie Impressum: LegalProfile) -->
                @php
                    $legalFooter = \App\Support\LegalProfile::resolved();
                    $hasAddrCore = filled($legalFooter['street'] ?? null) || filled($legalFooter['zip'] ?? null) || filled($legalFooter['city'] ?? null);
                    $locationParts = [];
                    if (filled($legalFooter['street'] ?? null)) {
                        $locationParts[] = $legalFooter['street'];
                    }
                    $zipCity = trim(($legalFooter['zip'] ?? '') . ' ' . ($legalFooter['city'] ?? ''));
                    if ($zipCity !== '') {
                        $locationParts[] = $zipCity;
                    }
                    if ($hasAddrCore && filled($legalFooter['country'] ?? null)) {
                        $locationParts[] = $legalFooter['country'];
                    }
                    $locationLine = implode(', ', $locationParts);
                    $hasContactRow = filled($legalFooter['email'] ?? null) || filled($legalFooter['phone'] ?? null) || $locationLine !== '';
                @endphp
                <div>
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wider mb-4">{{ __('Contact') }}</h4>
                    <ul class="space-y-2">
                        @if (filled($legalFooter['email']))
                            @php
                                $footerEmailUser = \Illuminate\Support\Str::before((string) $legalFooter['email'], '@');
                                $footerEmailDomain = \Illuminate\Support\Str::after((string) $legalFooter['email'], '@');
                            @endphp
                            <li class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                                <i class="fas fa-envelope"></i>
                                <a href="#"
                                   data-protected-email-user="{{ e($footerEmailUser) }}"
                                   data-protected-email-domain="{{ e($footerEmailDomain) }}"
                                   data-protected-email-reveal="1"
                                   class="hover:text-primary-500 transition-colors">{{ $footerEmailUser }} [at] {{ $footerEmailDomain }}</a>
                            </li>
                        @endif
                        @if (filled($legalFooter['phone']))
                            <li class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                                <i class="fas fa-phone"></i>
                                <span>{{ $legalFooter['phone'] }}</span>
                            </li>
                        @endif
                        @if ($locationLine !== '')
                            <li class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                                <i class="fas fa-map-marker-alt"></i>
                                <span>{{ $locationLine }}</span>
                            </li>
                        @endif
                        @if (! $hasContactRow)
                            <li class="flex items-start gap-2 text-gray-600 dark:text-gray-400 text-sm">
                                <i class="fas fa-balance-scale mt-0.5"></i>
                                <a href="{{ \App\Support\LegalUrl::imprint() }}" class="hover:text-primary-500 transition-colors">{{ __('Contact details in imprint') }}</a>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
            
            @php
                $cmsVersionPath = app(\App\Services\CmsUpdateManager::class)->versionFilePath();
                $cmsBuildRelease = is_file($cmsVersionPath) ? date('Y-m-d', (int) @filemtime($cmsVersionPath)) : 'n/a';
                $mysqlVersion = 'n/a';
                try {
                    $row = \Illuminate\Support\Facades\DB::selectOne('select version() as version');
                    $rawVersion = (string) ($row->version ?? 'n/a');
                    if (preg_match('/\d+(?:\.\d+){1,3}/', $rawVersion, $matches) === 1) {
                        $mysqlVersion = (string) ($matches[0] ?? 'n/a');
                    } elseif ($rawVersion !== '') {
                        $mysqlVersion = $rawVersion;
                    }
                } catch (\Throwable) {
                    $mysqlVersion = 'n/a';
                }

                $licenseStatus = __('Unknown');
                try {
                    if (! config('license.enabled', true)) {
                        $licenseStatus = __('Disabled');
                    } else {
                        $licenseOk = app(\App\Services\LicenseService::class)->validateHttpRequest(request());
                        $licenseStatus = $licenseOk ? __('Active') : __('Invalid');
                    }
                } catch (\Throwable) {
                    $licenseStatus = __('Unknown');
                }

                $cmsInfoExport = implode("\n", [
                    'aresCMS',
                    __('CMS version').': '.$cmsBundleVersion,
                    __('Build release').': '.$cmsBuildRelease,
                    __('Laravel version').': '.app()->version(),
                    __('PHP version').': '.PHP_VERSION,
                    __('MySQL version').': '.$mysqlVersion,
                    __('License status').': '.$licenseStatus,
                    __('Environment').': '.app()->environment(),
                    __('Developer').': Z3USM0S',
                    'E-Mail: leon@luetcke.eu',
                ]);
            @endphp
            <div class="mt-12 pt-8 border-t border-gray-200 dark:border-dark-700 flex flex-col md:flex-row items-center justify-between gap-4">
                <p class="text-gray-600 dark:text-gray-400 text-sm text-center md:text-left">
                    &copy; {{ date('Y') }} {{ $siteName }}. {{ __('All rights reserved.') }}
                    <span class="mx-2 text-gray-400">·</span>
                    <a href="{{ \App\Support\LegalUrl::imprint() }}" class="hover:text-primary-500 transition-colors">{{ __('legal.imprint.page_title') }}</a>
                    <span class="mx-2 text-gray-400">·</span>
                    <a href="{{ \App\Support\LegalUrl::privacy() }}" class="hover:text-primary-500 transition-colors">{{ __('legal.privacy.page_title') }}</a>
                    <span class="mx-2 text-gray-400">·</span>
                    <a href="{{ \App\Support\LegalUrl::terms() }}" class="hover:text-primary-500 transition-colors">{{ __('legal.terms.page_title') }}</a>
                    @if(\App\Models\Setting::getBoolValue('cookie_consent_enabled', true))
                        <span class="mx-2 text-gray-400">·</span>
                        <a href="#" class="js-open-cookie-settings hover:text-primary-500 transition-colors">{{ __('Cookie settings') }}</a>
                    @endif
                </p>
                <div x-data="{ openCmsInfo: false, copied: false, infoText: @js($cmsInfoExport), copyInfo() { try { navigator.clipboard.writeText(this.infoText); this.copied = true; setTimeout(() => this.copied = false, 1600); } catch (_e) {} } }" class="flex items-center gap-2">
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        {{ __('Version') }} {{ $cmsBundleVersion }}
                    </p>
                    <button type="button" @click="openCmsInfo = true" class="inline-flex items-center justify-center w-7 h-7 rounded-full border border-gray-300 dark:border-dark-600 text-gray-600 dark:text-gray-300 hover:text-primary-600 hover:border-primary-400 transition-colors" title="{{ __('CMS info') }}" aria-label="{{ __('CMS info') }}">
                        <i class="fas fa-info text-xs"></i>
                    </button>

                    <div x-show="openCmsInfo" x-cloak class="fixed inset-0 z-[110]">
                        <div class="absolute inset-0 bg-black/50" @click="openCmsInfo = false"></div>
                        <div class="relative z-[111] min-h-full flex items-center justify-center p-4">
                            <div class="w-full max-w-lg rounded-xl border border-gray-200 dark:border-dark-700 bg-white dark:bg-dark-800 p-5 shadow-2xl">
                                <div class="flex items-center justify-between gap-3 mb-4">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">aresCMS</h3>
                                    <button type="button" @click="openCmsInfo = false" class="w-8 h-8 rounded-lg border border-gray-300 dark:border-dark-600 text-gray-500 hover:text-gray-700 dark:hover:text-gray-200">
                                        <i class="fas fa-times text-sm"></i>
                                    </button>
                                </div>

                                <div class="space-y-2 text-sm text-gray-700 dark:text-gray-300">
                                    <div class="flex justify-between gap-4"><span>{{ __('CMS version') }}</span><span class="font-medium">{{ $cmsBundleVersion }}</span></div>
                                    <div class="flex justify-between gap-4"><span>{{ __('Build release') }}</span><span class="font-medium">{{ $cmsBuildRelease }}</span></div>
                                    <div class="flex justify-between gap-4"><span>{{ __('Laravel version') }}</span><span class="font-medium">{{ app()->version() }}</span></div>
                                    <div class="flex justify-between gap-4"><span>{{ __('PHP version') }}</span><span class="font-medium">{{ PHP_VERSION }}</span></div>
                                    <div class="flex justify-between gap-4"><span>{{ __('MySQL version') }}</span><span class="font-medium">{{ $mysqlVersion }}</span></div>
                                    <div class="flex justify-between gap-4"><span>{{ __('License status') }}</span><span class="font-medium">{{ $licenseStatus }}</span></div>
                                    <div class="flex justify-between gap-4"><span>{{ __('Environment') }}</span><span class="font-medium">{{ app()->environment() }}</span></div>
                                </div>

                                <div class="mt-5 pt-4 border-t border-gray-200 dark:border-dark-700">
                                    <p class="text-xs uppercase tracking-wide text-gray-500 mb-2">{{ __('Developer') }}</p>
                                    <p class="text-sm text-gray-800 dark:text-gray-200">
                                        <span class="font-semibold">Z3USM0S</span>
                                    </p>
                                    <a href="#"
                                       data-protected-email-user="leon"
                                       data-protected-email-domain="luetcke.eu"
                                       data-protected-email-reveal="1"
                                       class="mt-2 inline-flex items-center gap-2 text-sm text-primary-600 dark:text-primary-400 hover:underline">
                                        <i class="fas fa-envelope"></i>
                                        <span>leon [at] luetcke.eu</span>
                                    </a>
                                    <button type="button" @click="copyInfo()" class="mt-3 inline-flex items-center gap-2 px-3 py-1.5 rounded-lg border border-gray-300 dark:border-dark-600 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-700">
                                        <i class="fas fa-copy"></i>
                                        <span x-show="!copied">{{ __('Copy info') }}</span>
                                        <span x-show="copied" x-cloak>{{ __('Copied') }}</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Scripts -->
    <script>
        // Mobile menu toggle
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const mobileMenu = document.getElementById('mobileMenu');
        const globalPageLoader = document.getElementById('globalPageLoader');
        
        if (mobileMenuBtn && mobileMenu) {
            mobileMenuBtn.addEventListener('click', function() {
                mobileMenu.classList.toggle('hidden');
            });
        }

        const showGlobalLoader = () => {
            if (!globalPageLoader) return;
            globalPageLoader.classList.add('active');
        };
        const hideGlobalLoader = () => {
            if (!globalPageLoader) return;
            globalPageLoader.classList.remove('active');
        };

        window.addEventListener('beforeunload', showGlobalLoader);
        window.addEventListener('pageshow', hideGlobalLoader);
        document.addEventListener('DOMContentLoaded', hideGlobalLoader);
        document.addEventListener('click', function (event) {
            const anchor = event.target.closest('a[href]');
            if (!anchor) return;
            if (event.defaultPrevented || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
            const href = anchor.getAttribute('href') || '';
            if (!href || href.startsWith('#') || href.startsWith('javascript:')) return;
            if (anchor.target === '_blank' || anchor.hasAttribute('download')) return;
            const url = new URL(href, window.location.origin);
            if (url.origin !== window.location.origin) return;
            showGlobalLoader();
        });

        // Basic bot protection: compose mailto links only in browser runtime.
        document.querySelectorAll('[data-protected-email-user][data-protected-email-domain]').forEach(function (el) {
            const user = (el.getAttribute('data-protected-email-user') || '').trim();
            const domain = (el.getAttribute('data-protected-email-domain') || '').trim();
            if (!user || !domain) return;
            const email = user + '@' + domain;
            el.setAttribute('href', 'mailto:' + email);
            if (el.getAttribute('data-protected-email-reveal') === '1') {
                const textContainer = el.querySelector('span');
                if (textContainer) {
                    textContainer.textContent = email;
                } else {
                    el.textContent = email;
                }
            }
        });
    </script>
    
    @stack('scripts')
    @include('partials.chat-notifications')
    @include('partials.cookie-consent')
</body>
</html>
