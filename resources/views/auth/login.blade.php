@extends('layouts.app')

@section('title', __('Login'))

@section('content')
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full">
            <!-- Logo/Header -->
            <div class="text-center mb-8">
                <a href="{{ url('/') }}" class="inline-flex items-center gap-2 mb-6">
                    <div class="w-12 h-12 bg-gradient-to-br from-primary-500 to-purple-500 rounded-xl flex items-center justify-center shadow-lg shadow-primary-500/25">
                        <i class="fas fa-newspaper text-white text-xl"></i>
                    </div>
                </a>
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white">{{ __('Welcome back!') }}</h2>
                <p class="mt-2 text-gray-600 dark:text-gray-400">{{ __('Sign in to your account to continue') }}</p>
            </div>
            
            <!-- Login Card -->
            <div class="bg-white dark:bg-dark-800 rounded-2xl shadow-xl border border-gray-200 dark:border-dark-700 p-8">
                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    @error('login')
                        <div id="login-error" class="mb-6 flex gap-3 rounded-xl border border-red-200 dark:border-red-800/80 bg-red-50 dark:bg-red-950/35 px-4 py-3 text-sm text-red-800 dark:text-red-100 shadow-sm" role="alert">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-red-100 dark:bg-red-900/50 text-red-600 dark:text-red-300">
                                <i class="fas fa-circle-exclamation" aria-hidden="true"></i>
                            </span>
                            <span class="min-w-0 pt-1 leading-snug">{{ $message }}</span>
                        </div>
                    @enderror
                    
                    <!-- Email or Username -->
                    <div class="mb-6">
                        <label for="login" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Email or Username') }}</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-user text-gray-400"></i>
                            </div>
                            <input id="login" type="text" class="w-full pl-10 pr-4 py-3 bg-gray-50 dark:bg-dark-700 border border-gray-300 dark:border-dark-600 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent text-gray-900 dark:text-white placeholder-gray-400 @error('login') border-red-400 dark:border-red-500 ring-1 ring-red-200 dark:ring-red-900/50 @enderror" name="login" value="{{ old('login') }}" required autocomplete="username" autofocus placeholder="{{ __('Enter your email or username') }}" aria-invalid="{{ $errors->has('login') ? 'true' : 'false' }}" @error('login') aria-describedby="login-error" @enderror>
                        </div>
                    </div>
                    
                    <!-- Password -->
                    <div class="mb-6">
                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Password') }}</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-gray-400"></i>
                            </div>
                            <input id="password" type="password" class="w-full pl-10 pr-4 py-3 bg-gray-50 dark:bg-dark-700 border border-gray-300 dark:border-dark-600 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent text-gray-900 dark:text-white placeholder-gray-400 @error('password') border-red-500 @enderror" name="password" required autocomplete="current-password" placeholder="{{ __('Enter your password') }}">
                        </div>
                        @error('password')
                            <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Remember Me & Forgot Password -->
                    <div class="flex items-center justify-between mb-6">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }} class="w-4 h-4 text-primary-500 focus:ring-primary-500 border-gray-300 rounded">
                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Remember Me') }}</span>
                        </label>
                        <a href="{{ route('password.request') }}" class="text-sm text-primary-500 hover:text-primary-600 font-medium">
                            {{ __('Forgot Your Password?') }}
                        </a>
                    </div>
                    
                    <!-- Turnstile CAPTCHA -->
                    @if($turnstileSiteKey ?? false)
                        <div class="mb-6">
                            <div class="cf-turnstile" data-sitekey="{{ $turnstileSiteKey }}"></div>
                            @error('captcha')
                                <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif
                    
                    <!-- Submit Button -->
                    <button type="submit" class="w-full py-3 px-4 bg-gradient-to-r from-primary-500 to-purple-500 hover:from-primary-600 hover:to-purple-600 text-white font-semibold rounded-xl transition-all duration-300 shadow-lg shadow-primary-500/25 hover:shadow-primary-500/40 hover:-translate-y-0.5">
                        <i class="fas fa-sign-in-alt mr-2"></i>
                        {{ __('Login') }}
                    </button>
                </form>

                @if(($oauthGoogle ?? false) || ($oauthDiscord ?? false))
                    <div class="relative my-8">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-200 dark:border-dark-700"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-4 bg-white dark:bg-dark-800 text-gray-500">{{ __('or') }}</span>
                        </div>
                    </div>
                    @include('partials.oauth-buttons', ['oauthGoogle' => $oauthGoogle ?? false, 'oauthDiscord' => $oauthDiscord ?? false, 'mode' => 'login'])
                @endif

                @if(! \App\Models\Setting::getBoolValue('disable_registration', false))
                    <div class="text-center mt-8">
                        <p class="text-gray-600 dark:text-gray-400">
                            {{ __("Don't have an account?") }}
                            <a href="{{ route('register') }}" class="text-primary-500 hover:text-primary-600 font-semibold ml-1">
                                {{ __('Register') }}
                            </a>
                        </p>
                    </div>
                @endif
            </div>
            
            <!-- Back to Home -->
            <div class="text-center mt-6">
                <a href="{{ url('/') }}" class="inline-flex items-center gap-2 text-gray-600 dark:text-gray-400 hover:text-primary-500 transition-colors">
                    <i class="fas fa-arrow-left"></i>
                    {{ __('Back to Home') }}
                </a>
            </div>
        </div>
    </div>
    
    @if($turnstileSiteKey ?? false)
        <script data-cfasync="false" src="https://challenges.cloudflare.com/turnstile/v0/api.js" async></script>
    @endif
@endsection
