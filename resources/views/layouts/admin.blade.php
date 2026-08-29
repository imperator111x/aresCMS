<!DOCTYPE html>
@include('partials.html-source-banner')
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" :class="{ 'dark': darkMode }">
<head>
    @php
        $siteName = \App\Models\Setting::getValue('site_name', config('app.name', 'aresCMS'));
        $siteLogo = \App\Models\Setting::getValue('site_logo');
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
            background: #1e293b;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #475569;
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #64748b;
        }
        
        /* Transitions */
        .sidebar-transition {
            transition: width 0.3s ease, transform 0.3s ease;
        }
        
        .content-transition {
            transition: margin-left 0.3s ease;
        }

        .admin-sidebar-nav {
            scrollbar-width: thin;
            scrollbar-color: #64748b transparent;
            position: relative;
        }
        .admin-sidebar-nav::-webkit-scrollbar {
            width: 7px;
        }
        .admin-sidebar-nav::-webkit-scrollbar-track {
            background: transparent;
        }
        .admin-sidebar-nav::-webkit-scrollbar-thumb {
            background: rgba(100, 116, 139, .6);
            border-radius: 9999px;
        }
        .admin-sidebar-nav::-webkit-scrollbar-thumb:hover {
            background: rgba(100, 116, 139, .85);
        }

        .admin-sidebar-fade {
            position: absolute;
            left: 0;
            right: 0;
            height: 18px;
            pointer-events: none;
            z-index: 3;
        }
        .admin-sidebar-fade-top {
            top: 0;
            background: linear-gradient(to bottom, rgba(255,255,255,0.95), rgba(255,255,255,0));
        }
        .admin-sidebar-fade-bottom {
            bottom: 0;
            background: linear-gradient(to top, rgba(255,255,255,0.95), rgba(255,255,255,0));
        }
        .dark .admin-sidebar-fade-top {
            background: linear-gradient(to bottom, rgba(30,41,59,0.95), rgba(30,41,59,0));
        }
        .dark .admin-sidebar-fade-bottom {
            background: linear-gradient(to top, rgba(30,41,59,0.95), rgba(30,41,59,0));
        }

        /* Global page transition loading */
        #globalPageLoader {
            position: fixed;
            inset: 0;
            z-index: 9999;
            pointer-events: none;
            opacity: 0;
            transition: opacity .2s ease;
            background: rgba(2, 6, 23, .22);
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
</head>
<body class="bg-gray-100 dark:bg-dark-900 text-gray-900 dark:text-gray-100 min-h-screen">
    <div id="globalPageLoader" aria-hidden="true">
        <div class="loader-progress"></div>
        <div class="loader-spinner"></div>
    </div>
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside id="sidebar" class="sidebar-transition fixed inset-y-0 left-0 z-50 w-64 bg-white dark:bg-dark-800 border-r border-gray-200 dark:border-dark-700 transform -translate-x-full lg:translate-x-0 lg:static lg:inset-0 h-screen overflow-hidden flex flex-col min-h-0">
            <div class="flex items-center justify-between h-16 px-4 border-b border-gray-200 dark:border-dark-700">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2">
                    @if($siteLogo)
                        <img src="{{ asset('storage/' . $siteLogo) }}" alt="Logo" class="h-8" loading="eager" decoding="async" fetchpriority="high">
                    @else
                        <div class="w-8 h-8 bg-primary-500 rounded-lg flex items-center justify-center">
                            <i class="fas fa-newspaper text-white text-sm"></i>
                        </div>
                    @endif
                    <span class="text-lg font-bold text-gray-900 dark:text-white">{{ $siteName }}</span>
                </a>
                <button id="closeSidebar" class="lg:hidden text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <div class="relative" style="height: calc(100vh - 4rem);">
            <nav class="admin-sidebar-nav p-4 space-y-1 overflow-y-auto" style="height: calc(100vh - 4rem);">
                @php
                    $adminUser = auth()->user();
                @endphp
                <div class="sticky top-0 z-[2] -mx-1 px-1 py-1 text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-2 bg-white/90 dark:bg-dark-800/90 backdrop-blur-sm">{{ __('Main') }}</div>
                @if(optional($adminUser)->hasAdminPermission('dashboard'))
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('admin.dashboard') ? 'bg-primary-500 text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-700' }}">
                        <i class="fas fa-tachometer-alt w-5"></i>
                        <span>{{ __('Dashboard') }}</span>
                    </a>
                @endif
                
                <div class="sticky top-0 z-[2] -mx-1 px-1 py-1 text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-2 mt-4 bg-white/90 dark:bg-dark-800/90 backdrop-blur-sm">{{ __('Management') }}</div>
                @if(optional($adminUser)->hasAdminPermission('news'))
                    <a href="{{ route('admin.news.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('admin.news.*') ? 'bg-primary-500 text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-700' }}">
                        <i class="fas fa-newspaper w-5"></i>
                        <span>{{ __('News') }}</span>
                    </a>
                    @php
                        $pluginAdminNavItems = app(\App\Services\PluginManager::class)->adminNavigationItems();
                    @endphp
                    @foreach($pluginAdminNavItems as $pluginNavItem)
                        @php
                            $routeName = (string) ($pluginNavItem['route'] ?? '');
                            $permission = (string) ($pluginNavItem['permission'] ?? '');
                            $activePattern = (string) ($pluginNavItem['active_pattern'] ?? $routeName);
                        @endphp
                        @continue($routeName === '')
                        @continue(!Route::has($routeName))
                        @continue($permission !== '' && !optional($adminUser)->hasAdminPermission($permission))
                        <a href="{{ route($routeName) }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs($activePattern) ? 'bg-primary-500 text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-700' }}">
                            <i class="{{ $pluginNavItem['icon'] ?: 'fas fa-puzzle-piece' }} w-5"></i>
                            <span>{{ __($pluginNavItem['label']) }}</span>
                        </a>
                    @endforeach
                @endif
                @if(optional($adminUser)->hasAdminPermission('pages'))
                    <a href="{{ route('admin.pages.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('admin.pages.*') ? 'bg-primary-500 text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-700' }}">
                        <i class="fas fa-th-large w-5"></i>
                        <span>{{ __('Pages') }}</span>
                    </a>
                @endif
                @if(optional($adminUser)->hasAdminPermission('forms'))
                    <a href="{{ route('admin.forms.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('admin.forms.*') ? 'bg-primary-500 text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-700' }}">
                        <i class="fab fa-wpforms w-5"></i>
                        <span>{{ __('Forms') }}</span>
                    </a>
                @endif
                @if(optional($adminUser)->hasAdminPermission('settings'))
                    <a href="{{ route('admin.redirects.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('admin.redirects.*') ? 'bg-primary-500 text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-700' }}">
                        <i class="fas fa-random w-5"></i>
                        <span>{{ __('Redirects') }}</span>
                    </a>
                @endif
                @if(optional($adminUser)->hasAdminPermission('users'))
                    <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('admin.users.*') ? 'bg-primary-500 text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-700' }}">
                        <i class="fas fa-users w-5"></i>
                        <span>{{ __('Users') }}</span>
                    </a>
                @endif
                @if(optional($adminUser)->hasAdminPermission('team'))
                    <a href="{{ route('admin.team') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('admin.team') ? 'bg-primary-500 text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-700' }}">
                        <i class="fas fa-people-carry w-5"></i>
                        <span>{{ __('Team') }}</span>
                    </a>
                @endif
                @php
                    $settingsActive = request()->routeIs('admin.settings.general*')
                        || request()->routeIs('admin.settings.themes*')
                        || request()->routeIs('admin.settings.languages*')
                        || request()->routeIs('admin.settings.legal-imprint*')
                        || request()->routeIs('admin.settings.cookie-consent*');
                @endphp
                @if(optional($adminUser)->hasAdminPermission('settings'))
                    <a href="{{ route('admin.plugins.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('admin.plugins.*') ? 'bg-primary-500 text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-700' }}">
                        <i class="fas fa-plug w-5"></i>
                        <span>{{ __('Plugins') }}</span>
                    </a>
                @endif
                @if(optional($adminUser)->hasAdminPermission('settings'))
                <details class="group" {{ $settingsActive ? 'open' : '' }}>
                    <summary class="list-none flex items-center justify-between gap-3 px-3 py-2 rounded-lg cursor-pointer {{ $settingsActive ? 'bg-primary-500 text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-700' }}">
                        <span class="flex items-center gap-3">
                            <i class="fas fa-sliders-h w-5"></i>
                            <span>{{ __('General Settings') }}</span>
                        </span>
                        <i class="fas fa-chevron-down text-xs transition-transform group-open:rotate-180"></i>
                    </summary>
                    <div class="mt-1 ml-7 space-y-1">
                        <a href="{{ route('admin.settings.general') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.settings.general*') ? 'bg-primary-500 text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-700' }}">
                            <i class="fas fa-globe w-4"></i>
                            <span>{{ __('General Settings') }}</span>
                        </a>
                        <a href="{{ route('admin.settings.themes') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.settings.themes*') ? 'bg-primary-500 text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-700' }}">
                            <i class="fas fa-palette w-4"></i>
                            <span>{{ __('Themes') }}</span>
                        </a>
                        <a href="{{ route('admin.settings.languages') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.settings.languages*') ? 'bg-primary-500 text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-700' }}">
                            <i class="fas fa-language w-4"></i>
                            <span>{{ __('Language Settings') }}</span>
                        </a>
                        <a href="{{ route('admin.settings.legal-imprint') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.settings.legal-imprint*') ? 'bg-primary-500 text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-700' }}">
                            <i class="fas fa-balance-scale w-4"></i>
                            <span>{{ __('Legal notice (Imprint)') }}</span>
                        </a>
                        <a href="{{ route('admin.settings.cookie-consent') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.settings.cookie-consent*') ? 'bg-primary-500 text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-700' }}">
                            <i class="fas fa-cookie-bite w-4"></i>
                            <span>{{ __('Cookie consent') }}</span>
                        </a>
                    </div>
                </details>
                @endif
                @php
                    $operationsActive = request()->routeIs('admin.activity-log.*')
                        || request()->routeIs('admin.login-history.*')
                        || request()->routeIs('admin.system-update.*')
                        || request()->routeIs('admin.operations.*');
                @endphp
                @if(optional($adminUser)->hasAdminPermission('operations') || optional($adminUser)->hasAdminPermission('activity_log') || optional($adminUser)->hasAdminPermission('login_history') || optional($adminUser)->hasAdminPermission('system_update') || optional($adminUser)->hasAdminPermission('server_info') || optional($adminUser)->hasAdminPermission('health_check'))
                <details class="group" {{ $operationsActive ? 'open' : '' }}>
                    <summary class="list-none flex items-center justify-between gap-3 px-3 py-2 rounded-lg cursor-pointer {{ $operationsActive ? 'bg-primary-500 text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-700' }}">
                        <span class="flex items-center gap-3">
                            <i class="fas fa-server w-5"></i>
                            <span>{{ __('Operations') }}</span>
                        </span>
                        <i class="fas fa-chevron-down text-xs transition-transform group-open:rotate-180"></i>
                    </summary>
                    <div class="mt-1 ml-7 space-y-1">
                        @if(optional($adminUser)->hasAdminPermission('operations'))
                            <a href="{{ route('admin.operations.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.operations.index') ? 'bg-primary-500 text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-700' }}">
                                <i class="fas fa-cogs w-4"></i>
                                <span>{{ __('Operations') }}</span>
                            </a>
                            <a href="{{ route('admin.operations.schedule') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.operations.schedule*') ? 'bg-primary-500 text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-700' }}">
                                <i class="fas fa-clock w-4"></i>
                                <span>{{ __('Scheduled jobs') }}</span>
                            </a>
                            @if(optional($adminUser)->isOwner())
                                <a href="{{ route('admin.operations.cli') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.operations.cli*') ? 'bg-primary-500 text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-700' }}">
                                    <i class="fas fa-terminal w-4"></i>
                                    <span>{{ __('CLI Console') }}</span>
                                </a>
                            @endif
                        @endif
                        @if(optional($adminUser)->hasAdminPermission('activity_log'))
                            <a href="{{ route('admin.activity-log.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.activity-log.*') ? 'bg-primary-500 text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-700' }}">
                                <i class="fas fa-history w-4"></i>
                                <span>{{ __('Activity log') }}</span>
                            </a>
                        @endif
                        @if(optional($adminUser)->hasAdminPermission('login_history'))
                            <a href="{{ route('admin.login-history.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.login-history.*') ? 'bg-primary-500 text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-700' }}">
                                <i class="fas fa-sign-in-alt w-4"></i>
                                <span>{{ __('Login history') }}</span>
                            </a>
                        @endif
                        @if(optional($adminUser)->hasAdminPermission('system_update'))
                            <a href="{{ route('admin.system-update.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.system-update.*') ? 'bg-primary-500 text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-700' }}">
                                <i class="fas fa-arrow-circle-up w-4"></i>
                                <span>{{ __('System updates') }}</span>
                            </a>
                        @endif
                        @if(optional($adminUser)->hasAdminPermission('server_info'))
                            <a href="{{ route('admin.operations.server-info') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.operations.server-info') ? 'bg-primary-500 text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-700' }}">
                                <i class="fas fa-microchip w-4"></i>
                                <span>{{ __('Server information') }}</span>
                            </a>
                        @endif
                        @if(optional($adminUser)->hasAdminPermission('health_check'))
                            <a href="{{ route('admin.operations.health-check') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.operations.health-check') ? 'bg-primary-500 text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-700' }}">
                                <i class="fas fa-heart-pulse w-4"></i>
                                <span>{{ __('Health check') }}</span>
                            </a>
                        @endif
                    </div>
                </details>
                @endif
                @if(optional($adminUser)->hasAdminPermission('security'))
                    <a href="{{ route('admin.security.two-factor') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('admin.security.*') ? 'bg-primary-500 text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-700' }}">
                        <i class="fas fa-shield-alt w-5"></i>
                        <span>{{ __('Two-factor authentication') }}</span>
                    </a>
                @endif
                
                <div class="sticky top-0 z-[2] -mx-1 px-1 py-1 text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-2 mt-4 bg-white/90 dark:bg-dark-800/90 backdrop-blur-sm">{{ __('Quick Actions') }}</div>
                @if(optional($adminUser)->hasAdminPermission('news'))
                    <a href="{{ route('admin.news.create') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-700">
                        <i class="fas fa-plus-circle w-5"></i>
                        <span>{{ __('Create News') }}</span>
                    </a>
                @endif
                <a href="{{ url('/') }}" target="_blank" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-700">
                    <i class="fas fa-external-link-alt w-5"></i>
                    <span>{{ __('View Site') }}</span>
                </a>
            </nav>
            <div class="admin-sidebar-fade admin-sidebar-fade-top"></div>
            <div class="admin-sidebar-fade admin-sidebar-fade-bottom"></div>
            </div>
        </aside>
        
        <!-- Main Content -->
        <div class="flex-1 flex flex-col lg:ml-0">
            <!-- Navbar -->
            <header class="sticky top-0 z-40 bg-white dark:bg-dark-800 border-b border-gray-200 dark:border-dark-700">
                <div class="flex items-center justify-between h-16 px-4">
                    <div class="flex items-center gap-4">
                        <button id="openSidebar" class="lg:hidden text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                            <i class="fas fa-bars text-xl"></i>
                        </button>
                        @include('admin.partials.nav-search', ['variant' => 'desktop'])
                    </div>
                    
                    <div class="flex items-center gap-4">
                        <!-- Dark Mode Toggle -->
                        <button @click="darkMode = !darkMode; localStorage.setItem('darkMode', darkMode)" class="p-2 rounded-lg text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-dark-700">
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
                            <button @click="open = !open" class="p-2 rounded-lg text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-dark-700">
                                <i class="fas fa-globe"></i>
                            </button>
                            
                            <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 mt-2 w-40 bg-white dark:bg-dark-800 rounded-lg shadow-lg border border-gray-200 dark:border-dark-700 py-1 z-50">
                                @foreach($availableLocales as $locale)
                                    <a href="{{ route('language.switch', $locale) }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-700 {{ app()->getLocale() == $locale ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-600 dark:text-primary-400' : '' }}">
                                        <span>{{ $localeFlag($locale) }}</span>
                                        <span>{{ strtoupper($locale) }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                        
                        <!-- Notifications -->
                        @include('admin.partials.nav-notifications')
                        
                        <!-- User Dropdown -->
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" class="flex items-center gap-2 p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-dark-700">
                                @if(auth()->user()->avatar)
                                    <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="{{ auth()->user()->name }}" class="w-8 h-8 rounded-full object-cover" loading="lazy" decoding="async">
                                @else
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&color=7F9CF5&background=EBF4FF" alt="{{ auth()->user()->name }}" class="w-8 h-8 rounded-full" loading="lazy" decoding="async">
                                @endif
                                <div class="hidden md:block text-left">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ auth()->user()->name }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ __(auth()->user()->adminRoleLabel()) }}</div>
                                </div>
                                <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                            </button>
                            
                            <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 mt-2 w-48 bg-white dark:bg-dark-800 rounded-lg shadow-lg border border-gray-200 dark:border-dark-700 py-1 z-50">
                                <a href="{{ url('/') }}" target="_blank" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-700">
                                    <i class="fas fa-external-link-alt w-4"></i>
                                    {{ __('View Site') }}
                                </a>
                                <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-700">
                                    <i class="fas fa-sign-out-alt w-4"></i>
                                    {{ __('Logout') }}
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                                    @csrf
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Suche auf schmalen Bildschirmen (unter der Navbar-Zeile) -->
                <div class="md:hidden px-4 pb-3 border-t border-gray-200 dark:border-dark-700 pt-0">
                    <div class="mt-3">
                        @include('admin.partials.nav-search', ['variant' => 'mobile'])
                    </div>
                </div>
            </header>
            
            <!-- Content -->
            <main class="flex-1 p-4 md:p-6">
                @if(session('success'))
                    <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg flex items-center gap-3">
                        <i class="fas fa-check-circle text-green-500"></i>
                        <span class="text-green-700 dark:text-green-400">{{ session('success') }}</span>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg flex items-center gap-3">
                        <i class="fas fa-exclamation-circle text-red-500"></i>
                        <span class="text-red-700 dark:text-red-400">{{ session('error') }}</span>
                    </div>
                @endif

                @if(session('info'))
                    <div class="mb-4 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg flex items-center gap-3">
                        <i class="fas fa-info-circle text-blue-500"></i>
                        <span class="text-blue-700 dark:text-blue-400">{{ session('info') }}</span>
                    </div>
                @endif
                
                @if($errors->any())
                    <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                        <div class="flex items-center gap-3 mb-2">
                            <i class="fas fa-exclamation-circle text-red-500"></i>
                            <span class="text-red-700 dark:text-red-400 font-medium">{{ __('Please fix the following errors:') }}</span>
                        </div>
                        <ul class="list-disc list-inside text-red-600 dark:text-red-400 text-sm">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                @yield('content')
            </main>
            
            <!-- Footer -->
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
            <footer class="bg-white dark:bg-dark-800 border-t border-gray-200 dark:border-dark-700 py-4 px-6">
                <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center md:text-left">
                        &copy; {{ date('Y') }} {{ $siteName }}. {{ __('All rights reserved.') }}
                        <span class="mx-2 text-gray-400">·</span>
                        <a href="{{ route('legal.imprint') }}" target="_blank" rel="noopener" class="hover:text-primary-500">{{ __('legal.imprint.page_title') }}</a>
                        <span class="mx-2 text-gray-400">·</span>
                        <a href="{{ route('legal.privacy') }}" target="_blank" rel="noopener" class="hover:text-primary-500">{{ __('legal.privacy.page_title') }}</a>
                    </p>
                    <div x-data="{ openCmsInfo: false, copied: false, infoText: @js($cmsInfoExport), copyInfo() { try { navigator.clipboard.writeText(this.infoText); this.copied = true; setTimeout(() => this.copied = false, 1600); } catch (_e) {} } }" class="flex items-center gap-2">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
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
                                        <a href="mailto:leon@luetcke.eu" class="mt-2 inline-flex items-center gap-2 text-sm text-primary-600 dark:text-primary-400 hover:underline">
                                            <i class="fas fa-envelope"></i>
                                            <span>leon@luetcke.eu</span>
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
            </footer>
        </div>
    </div>
    
    <!-- Overlay for mobile sidebar -->
    <div id="sidebarOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden lg:hidden"></div>
    
    <!-- Scripts -->
    <script>
        const sidebar = document.getElementById('sidebar');
        const openSidebar = document.getElementById('openSidebar');
        const closeSidebar = document.getElementById('closeSidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const globalPageLoader = document.getElementById('globalPageLoader');
        
        function showSidebar() {
            sidebar.classList.remove('-translate-x-full');
            sidebarOverlay.classList.remove('hidden');
        }
        
        function hideSidebar() {
            sidebar.classList.add('-translate-x-full');
            sidebarOverlay.classList.add('hidden');
        }
        
        if (openSidebar) {
            openSidebar.addEventListener('click', showSidebar);
        }
        
        if (closeSidebar) {
            closeSidebar.addEventListener('click', hideSidebar);
        }
        
        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', hideSidebar);
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
    </script>
    
    @stack('scripts')
</body>
</html>
