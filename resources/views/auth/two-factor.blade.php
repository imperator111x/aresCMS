@extends('layouts.app')

@section('title', __('Two-factor authentication'))

@section('content')
    <div class="min-h-screen flex items-center justify-center py-12 px-4">
        <div class="max-w-md w-full">
            <div class="text-center mb-8">
                <div class="w-14 h-14 mx-auto mb-4 rounded-xl bg-primary-500 flex items-center justify-center">
                    <i class="fas fa-shield-alt text-white text-2xl"></i>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Two-factor authentication') }}</h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400 text-sm">{{ __('Enter the 6-digit code from your authenticator app.') }}</p>
            </div>

            <div class="bg-white dark:bg-dark-800 rounded-2xl shadow-xl border border-gray-200 dark:border-dark-700 p-8">
                <form method="POST" action="{{ route('two-factor.verify') }}">
                    @csrf
                    <div class="mb-6">
                        <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Authentication code') }}</label>
                        <input
                            id="code"
                            type="text"
                            name="code"
                            inputmode="numeric"
                            pattern="[0-9]{6}"
                            maxlength="6"
                            required
                            autofocus
                            autocomplete="one-time-code"
                            class="w-full text-center text-2xl font-mono tracking-[0.5em] py-3 rounded-xl border border-gray-300 dark:border-dark-600 bg-gray-50 dark:bg-dark-700 text-gray-900 dark:text-white @error('code') border-red-500 @enderror"
                            placeholder="000000"
                        >
                        @error('code')
                            <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit" class="w-full py-3 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-semibold">
                        {{ __('Continue') }}
                    </button>
                </form>
                <p class="mt-6 text-center text-sm text-gray-500">
                    <a href="{{ route('login') }}" class="text-primary-500 hover:underline">{{ __('Back to login') }}</a>
                </p>
            </div>
        </div>
    </div>
@endsection
