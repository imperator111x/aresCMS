@php
    $oauthGoogle = $oauthGoogle ?? false;
    $oauthDiscord = $oauthDiscord ?? false;
    $mode = $mode ?? 'login';
    $oauthRoutesReady = Route::has('oauth.redirect.login')
        && Route::has('oauth.redirect.register');
@endphp
@if($oauthRoutesReady && ($oauthGoogle || $oauthDiscord))
    <div class="flex flex-col gap-3">
        @if($oauthGoogle)
            <a href="{{ $mode === 'register' ? route('oauth.redirect.register', 'google') : route('oauth.redirect.login', 'google') }}"
               class="flex items-center justify-center gap-3 w-full py-3 px-4 rounded-xl border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 text-gray-800 dark:text-gray-100 font-medium hover:bg-gray-50 dark:hover:bg-dark-600 transition-colors">
                <i class="fab fa-google text-red-500"></i>
                {{ $mode === 'register' ? __('Continue with Google') : __('Sign in with Google') }}
            </a>
        @endif
        @if($oauthDiscord)
            <a href="{{ $mode === 'register' ? route('oauth.redirect.register', 'discord') : route('oauth.redirect.login', 'discord') }}"
               class="flex items-center justify-center gap-3 w-full py-3 px-4 rounded-xl border border-gray-300 dark:border-dark-600 bg-[#5865F2] hover:bg-[#4752C4] text-white font-medium transition-colors">
                <i class="fab fa-discord"></i>
                {{ $mode === 'register' ? __('Continue with Discord') : __('Sign in with Discord') }}
            </a>
        @endif
    </div>
@endif
