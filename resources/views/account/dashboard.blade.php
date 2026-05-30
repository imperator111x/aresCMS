@extends('layouts.app')

@section('title', __('Account'))

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ __('Account settings') }}</h1>
        <p class="mt-2 text-gray-600 dark:text-gray-400">{{ __('Manage your profile, password, email, and linked sign-in methods.') }}</p>
    </div>

    <div class="space-y-8">
        <!-- Profile picture -->
        <section class="bg-white dark:bg-dark-800 rounded-2xl border border-gray-200 dark:border-dark-700 p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <i class="fas fa-image text-primary-500"></i>
                {{ __('Profile picture') }}
            </h2>
            <div class="flex flex-col sm:flex-row sm:items-center gap-6">
                <div class="shrink-0">
                    @if($user->avatar)
                        <img src="{{ asset('storage/' . $user->avatar) }}" alt="" class="w-24 h-24 rounded-full object-cover border border-gray-200 dark:border-dark-600">
                    @else
                        <div class="w-24 h-24 rounded-full bg-gradient-to-br from-primary-400 to-purple-400 flex items-center justify-center text-2xl font-bold text-white">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    @endif
                </div>
                <div class="flex-1 flex flex-col gap-4">
                    <form action="{{ route('account.avatar.update') }}" method="post" enctype="multipart/form-data" class="space-y-3">
                        @csrf
                        <input type="file" name="avatar" accept="image/*" required
                            class="block w-full text-sm text-gray-600 dark:text-gray-300 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 dark:file:bg-primary-900/30 dark:file:text-primary-300 hover:file:bg-primary-100 dark:hover:file:bg-primary-900/50">
                        @error('avatar')
                            <p class="text-sm text-red-500">{{ $message }}</p>
                        @enderror
                        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-primary-500 hover:bg-primary-600 text-white text-sm font-semibold transition-colors">
                            <i class="fas fa-upload"></i>
                            {{ __('Upload new picture') }}
                        </button>
                    </form>
                    @if(($oauthDiscord ?? false) && $user->socialAccounts->where('provider', 'discord')->isNotEmpty())
                        <form action="{{ route('account.avatar.discord') }}" method="post" class="block shrink-0">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-[#5865F2]/40 bg-[#5865F2]/10 hover:bg-[#5865F2]/20 text-[#5865F2] dark:text-[#7289da] text-sm font-semibold transition-colors">
                                <i class="fab fa-discord"></i>
                                {{ __('Use Discord profile picture') }}
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </section>

        <!-- Email -->
        <section class="bg-white dark:bg-dark-800 rounded-2xl border border-gray-200 dark:border-dark-700 p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <i class="fas fa-envelope text-primary-500"></i>
                {{ __('Email address') }}
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">{{ __('Current email: :email', ['email' => $user->email]) }}</p>
            <form action="{{ route('account.email.update') }}" method="post" class="space-y-4">
                @csrf
                @method('PATCH')
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('New email') }}</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required autocomplete="email"
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-dark-600 bg-gray-50 dark:bg-dark-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 @error('email') border-red-500 @enderror">
                    @error('email')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="email-current-password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Current password') }}</label>
                    <input type="password" name="current_password" id="email-current-password" required autocomplete="current-password"
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-dark-600 bg-gray-50 dark:bg-dark-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 @error('current_password') border-red-500 @enderror">
                    @error('current_password')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-primary-500 hover:bg-primary-600 text-white text-sm font-semibold transition-colors">
                    {{ __('Save email') }}
                </button>
            </form>
        </section>

        <!-- Password -->
        <section class="bg-white dark:bg-dark-800 rounded-2xl border border-gray-200 dark:border-dark-700 p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <i class="fas fa-key text-primary-500"></i>
                {{ __('Password') }}
            </h2>
            <form action="{{ route('account.password.update') }}" method="post" class="space-y-4">
                @csrf
                @method('PATCH')
                <div>
                    <label for="current_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Current password') }}</label>
                    <input type="password" name="current_password" id="current_password" required autocomplete="current-password"
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-dark-600 bg-gray-50 dark:bg-dark-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 @error('current_password') border-red-500 @enderror">
                    @error('current_password')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('New password') }}</label>
                    <input type="password" name="password" id="password" required autocomplete="new-password"
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-dark-600 bg-gray-50 dark:bg-dark-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 @error('password') border-red-500 @enderror">
                    @error('password')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Confirm Password') }}</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" required autocomplete="new-password"
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-dark-600 bg-gray-50 dark:bg-dark-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                </div>
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-primary-500 hover:bg-primary-600 text-white text-sm font-semibold transition-colors">
                    {{ __('Update password') }}
                </button>
            </form>
        </section>

        <!-- Linked accounts -->
        <section class="bg-white dark:bg-dark-800 rounded-2xl border border-gray-200 dark:border-dark-700 p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 flex items-center gap-2">
                <i class="fas fa-link text-primary-500"></i>
                {{ __('Linked accounts') }}
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">{{ __('Connect Google or Discord to sign in with one click. Unlinking requires your current password.') }}</p>

            <div class="space-y-4">
                @foreach(['google' => ['label' => 'Google', 'icon' => 'fab fa-google', 'color' => 'text-red-500'], 'discord' => ['label' => 'Discord', 'icon' => 'fab fa-discord', 'color' => 'text-[#5865F2]']] as $key => $meta)
                    @php $linked = $user->socialAccounts->firstWhere('provider', $key); @endphp
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 p-4 rounded-xl border border-gray-200 dark:border-dark-600 bg-gray-50/50 dark:bg-dark-700/50">
                        <div class="flex items-center gap-3">
                            <i class="{{ $meta['icon'] }} text-xl {{ $meta['color'] }}"></i>
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white">{{ $meta['label'] }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $linked ? __('Connected') : __('Not connected') }}
                                </p>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            @if($linked)
                                <form action="{{ route('account.oauth.unlink', $key) }}" method="post" class="flex flex-col sm:flex-row sm:flex-wrap sm:items-center gap-2 w-full sm:w-auto">
                                    @csrf
                                    @method('DELETE')
                                    <input type="password" name="current_password" placeholder="{{ __('Current password') }}" required autocomplete="current-password"
                                        class="px-3 py-2 rounded-lg text-sm border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-800 text-gray-900 dark:text-white w-full sm:w-48">
                                    <button type="submit" class="px-3 py-2 rounded-lg text-sm font-medium bg-red-500 hover:bg-red-600 text-white transition-colors" data-confirm="{{ e(__('Unlink this account?')) }}" onclick="return confirm(this.dataset.confirm);">
                                        {{ __('Unlink') }}
                                    </button>
                                </form>
                            @else
                                @if(Route::has('oauth.redirect.link') && (($key === 'google' && $oauthGoogle) || ($key === 'discord' && $oauthDiscord)))
                                    <a href="{{ route('oauth.redirect.link', $key) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold bg-primary-500 hover:bg-primary-600 text-white transition-colors">
                                        <i class="fas fa-plus"></i>
                                        {{ __('Link') }}
                                    </a>
                                @else
                                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ __('Not configured on this site') }}</span>
                                @endif
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    </div>
</div>
@endsection
