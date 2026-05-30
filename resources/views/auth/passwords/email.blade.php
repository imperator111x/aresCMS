@extends('layouts.app')

@section('title', __('Forgot Your Password?'))

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
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white">{{ __('Reset Password') }}</h2>
                <p class="mt-2 text-gray-600 dark:text-gray-400">{{ __('Enter your email to receive a password reset link') }}</p>
            </div>
            
            <!-- Reset Password Card -->
            <div class="bg-white dark:bg-dark-800 rounded-2xl shadow-xl border border-gray-200 dark:border-dark-700 p-8">
                @if(session('status'))
                    <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl flex items-center gap-3">
                        <i class="fas fa-check-circle text-green-500"></i>
                        <span class="text-green-700 dark:text-green-400">{{ session('status') }}</span>
                    </div>
                @endif
                
                <form method="POST" action="{{ route('password.email') }}">
                    @csrf
                    
                    <!-- Email -->
                    <div class="mb-6">
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Email') }}</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-envelope text-gray-400"></i>
                            </div>
                            <input id="email" type="email" class="w-full pl-10 pr-4 py-3 bg-gray-50 dark:bg-dark-700 border border-gray-300 dark:border-dark-600 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent text-gray-900 dark:text-white placeholder-gray-400 @error('email') border-red-500 @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="{{ __('Enter your email') }}">
                        </div>
                        @error('email')
                            <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Submit Button -->
                    <button type="submit" class="w-full py-3 px-4 bg-gradient-to-r from-primary-500 to-purple-500 hover:from-primary-600 hover:to-purple-600 text-white font-semibold rounded-xl transition-all duration-300 shadow-lg shadow-primary-500/25 hover:shadow-primary-500/40 hover:-translate-y-0.5">
                        <i class="fas fa-paper-plane mr-2"></i>
                        {{ __('Send Password Reset Link') }}
                    </button>
                </form>
                
                <!-- Divider -->
                <div class="relative my-8">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-200 dark:border-dark-700"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-4 bg-white dark:bg-dark-800 text-gray-500">{{ __('or') }}</span>
                    </div>
                </div>
                
                <!-- Back to Login -->
                <div class="text-center">
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-2 text-primary-500 hover:text-primary-600 font-semibold">
                        <i class="fas fa-arrow-left"></i>
                        {{ __('Back to Login') }}
                    </a>
                </div>
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
@endsection
