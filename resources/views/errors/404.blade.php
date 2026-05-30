<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>{{ __('Page not found') }} — {{ config('app.name', 'aresCMS') }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-slate-950 text-slate-100 flex flex-col items-center justify-center p-6 antialiased">
    <div class="max-w-lg w-full text-center space-y-8">
        <div class="relative">
            <p class="text-[clamp(5rem,18vw,8rem)] font-black leading-none tracking-tighter text-slate-800 select-none" aria-hidden="true">404</p>
            <div class="absolute inset-0 flex items-center justify-center">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-primary-500/15 border border-primary-400/30 shadow-lg shadow-primary-500/10">
                    <svg class="w-10 h-10 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
        <div class="space-y-3 relative z-10">
            <h1 class="text-2xl font-bold tracking-tight text-white">{{ __('Page not found') }}</h1>
            <p class="text-slate-400 text-sm leading-relaxed max-w-sm mx-auto">
                {{ __('The page you are looking for does not exist or has been moved.') }}
            </p>
        </div>
        <div>
            <a href="{{ url('/') }}" class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-primary-600 hover:bg-primary-500 text-white text-sm font-semibold shadow-lg shadow-primary-600/25 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-400 focus-visible:ring-offset-2 focus-visible:ring-offset-slate-950">
                <svg class="w-4 h-4 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                {{ __('Back to Home') }}
            </a>
        </div>
        <p class="text-xs text-slate-600 pt-4">{{ config('app.name') }}</p>
    </div>
</body>
</html>
