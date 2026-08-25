<!DOCTYPE html>
@include('partials.html-source-banner')
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>{{ __('Maintenance') }} — {{ config('app.name', 'aresCMS') }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-slate-950 text-slate-100 flex flex-col items-center justify-center p-6 antialiased">
    <div class="max-w-md w-full text-center space-y-6">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-amber-500/20 border border-amber-500/40">
            <svg class="w-8 h-8 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
        <h1 class="text-2xl font-bold tracking-tight">{{ __('We are performing maintenance') }}</h1>
        <p class="text-slate-400 text-sm leading-relaxed">
            {{ __('The site is temporarily unavailable. Please try again in a few minutes.') }}
        </p>
        @if(isset($retryAfter) && $retryAfter)
            <p class="text-xs text-slate-500">
                {{ __('Retry suggested in :seconds seconds.', ['seconds' => $retryAfter]) }}
            </p>
        @endif
        <div class="pt-6 border-t border-slate-800/80">
            <a href="{{ route('maintenance.admin.login') }}" class="inline-flex items-center justify-center gap-2 w-full sm:w-auto px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-600 text-slate-200 text-sm font-medium transition-colors">
                <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                {{ __('Administrator sign-in') }}
            </a>
            <p class="text-xs text-slate-500 mt-3">{{ __('For site administrators only.') }}</p>
        </div>
        <p class="text-xs text-slate-600 pt-4">{{ config('app.name') }}</p>
    </div>
</body>
</html>
