@extends('layouts.admin')

@section('title', __('Two-factor authentication'))

@section('content')
    <div class="max-w-xl">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">{{ __('Two-factor authentication') }}</h1>
        <p class="text-gray-500 dark:text-gray-400 text-sm mb-6">{{ __('Admins can secure their account with an authenticator app (TOTP).') }}</p>

        @if(session('success'))
            <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg text-green-700 dark:text-green-400 text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg text-red-700 dark:text-red-400 text-sm">{{ session('error') }}</div>
        @endif

        @if($enabled)
            <div class="p-4 rounded-xl border border-green-200 dark:border-green-800 bg-green-50/50 dark:bg-green-900/10 mb-6">
                <p class="text-green-800 dark:text-green-200 text-sm font-medium mb-4">
                    <i class="fas fa-check-circle mr-2"></i>{{ __('Two-factor authentication is enabled.') }}
                </p>
                <form method="POST" action="{{ route('admin.security.two-factor.disable') }}" class="space-y-4" onsubmit="return confirm(@json(__('Disable two-factor authentication?')));">
                    @csrf
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Current password') }}</label>
                        <input type="password" name="password" id="password" required class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 text-gray-900 dark:text-white px-3 py-2 text-sm">
                    </div>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white text-sm font-medium">
                        {{ __('Disable 2FA') }}
                    </button>
                </form>
            </div>
        @else
            @if($setupSecret)
                <div class="p-4 rounded-xl border border-gray-200 dark:border-dark-700 bg-white dark:bg-dark-800 mb-6 space-y-4">
                    <p class="text-sm text-gray-600 dark:text-gray-300">{{ __('Add this account in Google Authenticator, Authy or a similar app.') }}</p>
                    @if($qrUrl)
                        <div class="flex justify-center p-4 bg-white rounded-lg">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode($qrUrl) }}" alt="QR" class="w-48 h-48" width="200" height="200">
                        </div>
                    @endif
                    <div>
                        <span class="text-xs font-semibold text-gray-500 uppercase">{{ __('Secret key (manual entry)') }}</span>
                        <p class="font-mono text-sm break-all mt-1 p-2 bg-gray-100 dark:bg-dark-700 rounded text-gray-900 dark:text-white select-all">{{ $setupSecret }}</p>
                    </div>
                    <form method="POST" action="{{ route('admin.security.two-factor.confirm') }}" class="space-y-3">
                        @csrf
                        <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('6-digit code') }}</label>
                        <input type="text" name="code" id="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required autocomplete="one-time-code" class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 text-gray-900 dark:text-white px-3 py-2 text-sm font-mono tracking-widest" placeholder="000000">
                        @error('code')
                            <p class="text-red-500 text-sm">{{ $message }}</p>
                        @enderror
                        <button type="submit" class="w-full py-2 rounded-lg bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium">
                            {{ __('Confirm and activate') }}
                        </button>
                    </form>
                </div>
            @else
                <form method="POST" action="{{ route('admin.security.two-factor.begin') }}">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-lg bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium">
                        {{ __('Set up two-factor authentication') }}
                    </button>
                </form>
            @endif
        @endif
    </div>
@endsection
