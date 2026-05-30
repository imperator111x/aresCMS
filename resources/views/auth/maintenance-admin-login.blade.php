<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Admin sign-in (maintenance)') }} — {{ config('app.name', 'aresCMS') }}</title>
    @vite(['resources/css/app.css'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="min-h-screen bg-slate-950 text-slate-100 flex flex-col items-center justify-center p-6 antialiased">
    <div class="max-w-md w-full">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-slate-800 border border-slate-600 mb-4">
                <i class="fas fa-user-shield text-2xl text-amber-400"></i>
            </div>
            <h1 class="text-xl font-bold tracking-tight">{{ __('Admin sign-in (maintenance)') }}</h1>
            <p class="text-slate-400 text-sm mt-2">{{ __('Sign in with an administrator account to use the site during maintenance.') }}</p>
        </div>

        <div class="rounded-2xl bg-slate-900/80 border border-slate-700 p-6 shadow-xl">
            @if(!empty($alreadyLoggedInNonAdmin))
                <div class="mb-6 p-4 rounded-lg bg-amber-500/10 border border-amber-500/30 text-amber-100 text-sm">
                    {{ __('You are signed in as a non-administrator. Sign out first, then sign in with an admin account.') }}
                </div>
                <form method="POST" action="{{ route('logout') }}" class="flex justify-center">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-slate-700 hover:bg-slate-600 text-white text-sm font-medium">
                        <i class="fas fa-sign-out-alt"></i>
                        {{ __('Logout') }}
                    </button>
                </form>
            @else
                <form method="POST" action="{{ route('maintenance.admin.login.store') }}">
                    @csrf
                    @error('login')
                        <div id="login-error" class="mb-4 flex gap-3 rounded-xl border border-red-500/40 bg-red-950/50 px-4 py-3 text-sm text-red-100 shadow-sm" role="alert">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-red-900/60 text-red-300">
                                <i class="fas fa-circle-exclamation" aria-hidden="true"></i>
                            </span>
                            <span class="min-w-0 pt-1 leading-snug">{{ $message }}</span>
                        </div>
                    @enderror
                    <div class="mb-4">
                        <label for="login" class="block text-xs font-medium text-slate-400 mb-1.5">{{ __('Email or Username') }}</label>
                        <input id="login" type="text" name="login" value="{{ old('login') }}" required autocomplete="username" autofocus
                            class="w-full px-4 py-3 rounded-xl bg-slate-800 border border-slate-600 text-white placeholder-slate-500 focus:ring-2 focus:ring-amber-500 focus:border-transparent @error('login') border-red-400 ring-1 ring-red-900/60 @enderror"
                            placeholder="{{ __('Enter your email or username') }}"
                            aria-invalid="{{ $errors->has('login') ? 'true' : 'false' }}"
                            @error('login') aria-describedby="login-error" @enderror>
                    </div>
                    <div class="mb-4">
                        <label for="password" class="block text-xs font-medium text-slate-400 mb-1.5">{{ __('Password') }}</label>
                        <input id="password" type="password" name="password" required autocomplete="current-password"
                            class="w-full px-4 py-3 rounded-xl bg-slate-800 border border-slate-600 text-white placeholder-slate-500 focus:ring-2 focus:ring-amber-500 focus:border-transparent @error('password') border-red-500 @enderror"
                            placeholder="{{ __('Enter your password') }}">
                        @error('password')
                            <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <label class="flex items-center gap-2 mb-6 cursor-pointer text-sm text-slate-300">
                        <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }} class="rounded border-slate-600 bg-slate-800 text-amber-500 focus:ring-amber-500">
                        {{ __('Remember Me') }}
                    </label>
                    <button type="submit" class="w-full py-3 px-4 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-semibold transition-colors">
                        <i class="fas fa-sign-in-alt mr-2"></i>
                        {{ __('Login') }}
                    </button>
                </form>
            @endif
        </div>

        <p class="text-center text-xs text-slate-600 mt-6">{{ config('app.name') }}</p>
    </div>
</body>
</html>
