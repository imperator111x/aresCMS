@extends('layouts.app')

@section('title', __('Login'))

@section('content')
    @include('partials.auth-wrap-open', [
        'authTitle' => __('Welcome back!'),
        'authSubtitle' => __('Sign in to your account to continue'),
    ])

        <form method="POST" action="{{ route('login') }}" class="hw-auth-form">
            @csrf

            @error('login')
                <div id="login-error" class="hw-auth-alert hw-auth-alert--error" role="alert">
                    <i class="fas fa-circle-exclamation" aria-hidden="true"></i>
                    <span>{{ $message }}</span>
                </div>
            @enderror

            <div class="hw-auth-field">
                <label for="login">{{ __('Email or Username') }}</label>
                <div class="hw-auth-field__icon-wrap">
                    <i class="fas fa-user" aria-hidden="true"></i>
                    <input id="login" type="text" name="login" value="{{ old('login') }}" required autocomplete="username" autofocus placeholder="{{ __('Enter your email or username') }}" @error('login') class="hw-auth-input--error" aria-invalid="true" aria-describedby="login-error" @enderror>
                </div>
            </div>

            <div class="hw-auth-field">
                <label for="password">{{ __('Password') }}</label>
                <div class="hw-auth-field__icon-wrap">
                    <i class="fas fa-lock" aria-hidden="true"></i>
                    <input id="password" type="password" name="password" required autocomplete="current-password" placeholder="{{ __('Enter your password') }}" @error('password') class="hw-auth-input--error" @enderror>
                </div>
                @error('password')
                    <p class="hw-auth-field__error">{{ $message }}</p>
                @enderror
            </div>

            <div class="hw-auth-row">
                <label class="hw-auth-remember">
                    <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                    <span>{{ __('Remember Me') }}</span>
                </label>
                <a href="{{ route('password.request') }}" class="hw-auth-link">{{ __('Forgot Your Password?') }}</a>
            </div>

            @if($turnstileSiteKey ?? false)
                <div class="hw-auth-field">
                    <div class="cf-turnstile" data-sitekey="{{ $turnstileSiteKey }}"></div>
                    @error('captcha')
                        <p class="hw-auth-field__error">{{ $message }}</p>
                    @enderror
                </div>
            @endif

            <button type="submit" class="hw-auth-btn">
                <i class="fas fa-sign-in-alt" aria-hidden="true"></i>
                {{ __('Login') }}
            </button>
        </form>

        @if(($oauthGoogle ?? false) || ($oauthDiscord ?? false))
            <div class="hw-auth-divider"><span>{{ __('or') }}</span></div>
            @include('partials.oauth-buttons', ['oauthGoogle' => $oauthGoogle ?? false, 'oauthDiscord' => $oauthDiscord ?? false, 'mode' => 'login'])
        @endif

        @if(! \App\Models\Setting::getBoolValue('disable_registration', false))
            <p class="hw-auth-footer-text">
                {{ __("Don't have an account?") }}
                <a href="{{ route('register') }}" class="hw-auth-link hw-auth-link--strong">{{ __('Register') }}</a>
            </p>
        @endif

    @include('partials.auth-wrap-close')

    @if($turnstileSiteKey ?? false)
        <script data-cfasync="false" src="https://challenges.cloudflare.com/turnstile/v0/api.js" async></script>
    @endif
@endsection
