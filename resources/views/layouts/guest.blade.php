<!DOCTYPE html>
@include('partials.html-source-banner')
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'aresCMS') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <!-- Styles -->
    <style>
        :root {
            --md-primary: #bb86fc;
            --md-primary-variant: #3700b3;
            --md-secondary: #03dac6;
            --md-background: #121212;
            --md-surface: #1e1e1e;
            --md-error: #cf6679;
            --md-on-primary: #000000;
            --md-on-secondary: #000000;
            --md-on-background: #ffffff;
            --md-on-surface: #ffffff;
            --md-on-error: #000000;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Figtree', sans-serif;
            background-color: var(--md-background);
            color: var(--md-on-background);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Navigation */
        .navbar {
            background-color: var(--md-surface);
            padding: 1rem 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }

        .navbar .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--md-primary);
            text-decoration: none;
        }

        .navbar-nav {
            display: flex;
            list-style: none;
            gap: 1.5rem;
            align-items: center;
        }

        .navbar-nav a {
            color: var(--md-on-surface);
            text-decoration: none;
            transition: color 0.3s;
        }

        .navbar-nav a:hover {
            color: var(--md-primary);
        }

        /* Language Switcher */
        .language-switcher {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        .language-switcher a {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.875rem;
            transition: all 0.3s;
        }

        .language-switcher a.active {
            background-color: var(--md-primary);
            color: var(--md-on-primary);
        }

        .language-switcher a:not(.active):hover {
            background-color: rgba(187, 134, 252, 0.2);
        }

        /* Cards */
        .card {
            background-color: var(--md-surface);
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }

        .card-title {
            font-size: 1.25rem;
            margin-bottom: 1rem;
            color: var(--md-primary);
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
        }

        .btn-primary {
            background-color: var(--md-primary);
            color: var(--md-on-primary);
        }

        .btn-primary:hover {
            background-color: var(--md-primary-variant);
        }

        .btn-secondary {
            background-color: var(--md-secondary);
            color: var(--md-on-secondary);
        }

        .btn-secondary:hover {
            opacity: 0.9;
        }

        .btn-danger {
            background-color: var(--md-error);
            color: var(--md-on-error);
        }

        .btn-danger:hover {
            opacity: 0.9;
        }

        /* Forms */
        .form-group {
            margin-bottom: 1rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--md-on-surface);
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #333;
            border-radius: 4px;
            background-color: var(--md-surface);
            color: var(--md-on-surface);
            font-size: 1rem;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--md-primary);
        }

        textarea.form-control {
            min-height: 150px;
            resize: vertical;
        }

        /* Alerts */
        .alert {
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
        }

        .alert-success {
            background-color: rgba(3, 218, 198, 0.2);
            border: 1px solid var(--md-secondary);
            color: var(--md-secondary);
        }

        .alert-error {
            background-color: rgba(207, 102, 121, 0.2);
            border: 1px solid var(--md-error);
            color: var(--md-error);
        }

        /* Utility Classes */
        .text-center {
            text-align: center;
        }

        .mt-1 { margin-top: 0.5rem; }
        .mt-2 { margin-top: 1rem; }
        .mt-3 { margin-top: 1.5rem; }
        .mt-4 { margin-top: 2rem; }

        .mb-1 { margin-bottom: 0.5rem; }
        .mb-2 { margin-bottom: 1rem; }
        .mb-3 { margin-bottom: 1.5rem; }
        .mb-4 { margin-bottom: 2rem; }

        .d-flex {
            display: flex;
        }

        .justify-between {
            justify-content: space-between;
        }

        .align-center {
            align-items: center;
        }

        .gap-1 { gap: 0.5rem; }
        .gap-2 { gap: 1rem; }
        .gap-3 { gap: 1.5rem; }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="{{ url('/') }}" class="navbar-brand">
                <span class="material-icons">article</span>
                {{ config('app.name', 'aresCMS') }}
            </a>
            <ul class="navbar-nav">
                <li><a href="{{ url('/') }}">{{ __('Home') }}</a></li>
                @php $registrationDisabled = \App\Models\Setting::getBoolValue('disable_registration', false); @endphp
                @guest
                    <li><a href="{{ route('login') }}">{{ __('Login') }}</a></li>
                    @unless($registrationDisabled)
                        <li><a href="{{ route('register') }}">{{ __('Register') }}</a></li>
                    @endunless
                @else
                    @if(auth()->user()->is_admin)
                        <li><a href="{{ route('admin.dashboard') }}">{{ __('Admin') }}</a></li>
                    @endif
                    <li>
                        <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                            @csrf
                            <button type="submit" style="background: none; border: none; color: inherit; cursor: pointer; font: inherit;">
                                {{ __('Logout') }}
                            </button>
                        </form>
                    </li>
                @endguest
                <li>
                    @php
                        $availableLocales = collect(glob(resource_path('lang/*.json')) ?: [])
                            ->map(fn ($path) => pathinfo($path, PATHINFO_FILENAME))
                            ->filter(fn ($locale) => preg_match('/^[a-z]{2}(-[A-Z]{2})?$/', $locale))
                            ->values();
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
                    <div class="language-switcher">
                        <span class="material-icons" style="font-size: 1rem;">language</span>
                        @foreach($availableLocales as $locale)
                            <a href="{{ route('language.switch', $locale) }}" class="{{ app()->getLocale() == $locale ? 'active' : '' }}">{{ $localeFlag($locale) }} {{ strtoupper($locale) }}</a>
                        @endforeach
                    </div>
                </li>
            </ul>
        </div>
    </nav>

    <main class="container" style="padding-top: 2rem; padding-bottom: 2rem; flex: 1;">
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-error">
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </main>

    <footer style="background-color: var(--md-surface); padding: 2rem 0; margin-top: 2rem;">
        <div class="container text-center">
            <p>&copy; {{ date('Y') }} {{ config('app.name', 'aresCMS') }}. {{ __('All rights reserved.') }}</p>
            <p class="mt-3" style="font-size: 0.875rem;">
                <a href="{{ route('legal.imprint') }}" style="color: var(--md-primary); text-decoration: none;">{{ __('legal.imprint.page_title') }}</a>
                <span style="margin: 0 0.5rem; opacity: 0.5;">·</span>
                <a href="{{ route('legal.privacy') }}" style="color: var(--md-primary); text-decoration: none;">{{ __('legal.privacy.page_title') }}</a>
                @if(\App\Models\Setting::getBoolValue('cookie_consent_enabled', true))
                    <span style="margin: 0 0.5rem; opacity: 0.5;">·</span>
                    <a href="#" class="js-open-cookie-settings" style="color: var(--md-primary); text-decoration: none;">{{ __('Cookie settings') }}</a>
                @endif
            </p>
        </div>
    </footer>
    @include('partials.cookie-consent')
</body>
</html>
