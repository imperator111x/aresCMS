@extends('layouts.app')

@section('title', __('Register'))

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
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white">{{ __('Create an account') }}</h2>
                <p class="mt-2 text-gray-600 dark:text-gray-400">{{ __('Join us today and start exploring') }}</p>
            </div>
            
            <!-- Register Card -->
            <div class="bg-white dark:bg-dark-800 rounded-2xl shadow-xl border border-gray-200 dark:border-dark-700 p-8">
                <form method="POST" action="{{ route('register') }}">
                    @csrf

                    @if ($errors->any())
                        <div class="mb-6 rounded-xl border border-red-200 dark:border-red-800/80 bg-red-50 dark:bg-red-950/35 px-4 py-3" role="alert">
                            <ul class="list-disc list-inside text-sm text-red-800 dark:text-red-200 space-y-1">
                                @foreach ($errors->all() as $message)
                                    <li>{{ $message }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    <!-- Name -->
                    <div class="mb-6">
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Name') }}</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-user text-gray-400"></i>
                            </div>
                            <input id="name" type="text" class="w-full pl-10 pr-4 py-3 bg-gray-50 dark:bg-dark-700 border border-gray-300 dark:border-dark-600 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent text-gray-900 dark:text-white placeholder-gray-400 @error('name') border-red-500 @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus placeholder="{{ __('Enter your name') }}">
                        </div>
                    </div>
                    
                    <!-- Email -->
                    <div class="mb-6">
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Email') }}</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-envelope text-gray-400"></i>
                            </div>
                            <input id="email" type="email" class="w-full pl-10 pr-4 py-3 bg-gray-50 dark:bg-dark-700 border border-gray-300 dark:border-dark-600 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent text-gray-900 dark:text-white placeholder-gray-400 @error('email') border-red-500 @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="{{ __('Enter your email') }}">
                        </div>
                    </div>
                    
                    @php
                        $passwordPolicy = $passwordPolicy ?? \App\Support\PasswordRules::policySummary();
                        $passwordStrengthLabelsForJs = [
                            'simple' => __('password.strength.simple'),
                            'medium' => __('password.strength.medium'),
                            'strong' => __('password.strength.strong'),
                        ];
                    @endphp
                    <!-- Password -->
                    <div class="mb-6" id="password-field-group">
                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Password') }}</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-gray-400"></i>
                            </div>
                            <input id="password" type="password" class="w-full pl-10 pr-4 py-3 bg-gray-50 dark:bg-dark-700 border border-gray-300 dark:border-dark-600 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent text-gray-900 dark:text-white placeholder-gray-400 @error('password') border-red-500 @enderror" name="password" required autocomplete="new-password" placeholder="{{ __('Enter your password') }}" minlength="{{ $passwordPolicy['min_length'] }}">
                        </div>
                        <div class="mt-3 rounded-xl border border-gray-200 dark:border-dark-600 bg-gray-50/80 dark:bg-dark-700/50 p-4 space-y-3" id="password-hints" role="status" aria-live="polite"
                            data-min="{{ $passwordPolicy['min_length'] }}"
                            data-letters="{{ $passwordPolicy['require_letters'] ? '1' : '0' }}"
                            data-mixed="{{ $passwordPolicy['require_mixed_case'] ? '1' : '0' }}"
                            data-numbers="{{ $passwordPolicy['require_numbers'] ? '1' : '0' }}"
                            data-symbols="{{ $passwordPolicy['require_symbols'] ? '1' : '0' }}">
                            <p class="text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wide">{{ __('Password requirements') }}</p>
                            <ul class="space-y-1.5 text-sm text-gray-600 dark:text-gray-400" id="password-req-list">
                                <li class="flex items-center gap-2" data-req="min">
                                    <span class="pw-req-icon w-4 h-4 rounded-full border border-gray-300 dark:border-dark-500 shrink-0" aria-hidden="true"></span>
                                    <span>{{ __('At least :count characters', ['count' => $passwordPolicy['min_length']]) }}</span>
                                </li>
                                @if($passwordPolicy['require_letters'])
                                    <li class="flex items-center gap-2" data-req="letters">
                                        <span class="pw-req-icon w-4 h-4 rounded-full border border-gray-300 dark:border-dark-500 shrink-0" aria-hidden="true"></span>
                                        <span>{{ __('At least one letter') }}</span>
                                    </li>
                                @endif
                                @if($passwordPolicy['require_mixed_case'])
                                    <li class="flex items-center gap-2" data-req="mixed">
                                        <span class="pw-req-icon w-4 h-4 rounded-full border border-gray-300 dark:border-dark-500 shrink-0" aria-hidden="true"></span>
                                        <span>{{ __('Uppercase and lowercase letters') }}</span>
                                    </li>
                                @endif
                                @if($passwordPolicy['require_numbers'])
                                    <li class="flex items-center gap-2" data-req="numbers">
                                        <span class="pw-req-icon w-4 h-4 rounded-full border border-gray-300 dark:border-dark-500 shrink-0" aria-hidden="true"></span>
                                        <span>{{ __('At least one number') }}</span>
                                    </li>
                                @endif
                                @if($passwordPolicy['require_symbols'])
                                    <li class="flex items-center gap-2" data-req="symbols">
                                        <span class="pw-req-icon w-4 h-4 rounded-full border border-gray-300 dark:border-dark-500 shrink-0" aria-hidden="true"></span>
                                        <span>{{ __('At least one special character (e.g. ! ? # @@)') }}</span>
                                    </li>
                                @endif
                            </ul>
                            <div class="pt-1">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ __('Password strength') }}</span>
                                    <span class="text-xs font-medium transition-colors text-gray-400 dark:text-gray-500" id="password-strength-label">—</span>
                                </div>
                                <div class="h-2 rounded-full bg-gray-200 dark:bg-dark-600 overflow-hidden flex gap-0.5" id="password-strength-bar">
                                    <div class="pw-bar flex-1 rounded-sm bg-gray-300 dark:bg-dark-500 transition-colors duration-200" data-bar="0"></div>
                                    <div class="pw-bar flex-1 rounded-sm bg-gray-300 dark:bg-dark-500 transition-colors duration-200" data-bar="1"></div>
                                    <div class="pw-bar flex-1 rounded-sm bg-gray-300 dark:bg-dark-500 transition-colors duration-200" data-bar="2"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Confirm Password -->
                    <div class="mb-6">
                        <label for="password-confirm" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Confirm Password') }}</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-gray-400"></i>
                            </div>
                            <input id="password-confirm" type="password" class="w-full pl-10 pr-4 py-3 bg-gray-50 dark:bg-dark-700 border border-gray-300 dark:border-dark-600 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent text-gray-900 dark:text-white placeholder-gray-400 @error('password') border-red-500 @enderror" name="password_confirmation" required autocomplete="new-password" placeholder="{{ __('Confirm your password') }}">
                        </div>
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400 hidden" id="password-match-hint"><i class="fas fa-check text-green-500 mr-1"></i>{{ __('Passwords match') }}</p>
                    </div>
                    
                    <!-- Turnstile CAPTCHA -->
                    @if($turnstileSiteKey ?? false)
                        <div class="mb-6">
                            <div class="cf-turnstile" data-sitekey="{{ $turnstileSiteKey }}"></div>
                        </div>
                    @endif
                    
                    <!-- Submit Button -->
                    <button type="submit" class="w-full py-3 px-4 bg-gradient-to-r from-primary-500 to-purple-500 hover:from-primary-600 hover:to-purple-600 text-white font-semibold rounded-xl transition-all duration-300 shadow-lg shadow-primary-500/25 hover:shadow-primary-500/40 hover:-translate-y-0.5">
                        <i class="fas fa-user-plus mr-2"></i>
                        {{ __('Register') }}
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
                    @include('partials.oauth-buttons', ['oauthGoogle' => $oauthGoogle ?? false, 'oauthDiscord' => $oauthDiscord ?? false, 'mode' => 'register'])
                @endif
                
                <!-- Divider -->
                <div class="relative my-8">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-200 dark:border-dark-700"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-4 bg-white dark:bg-dark-800 text-gray-500">{{ __('or') }}</span>
                    </div>
                </div>
                
                <!-- Login Link -->
                <div class="text-center">
                    <p class="text-gray-600 dark:text-gray-400">
                        {{ __('Already have an account?') }}
                        <a href="{{ route('login') }}" class="text-primary-500 hover:text-primary-600 font-semibold ml-1">
                            {{ __('Login') }}
                        </a>
                    </p>
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
    
    @if($turnstileSiteKey ?? false)
        <script data-cfasync="false" src="https://challenges.cloudflare.com/turnstile/v0/api.js" async></script>
    @endif
    <script>
    (function () {
        var hints = document.getElementById('password-hints');
        var input = document.getElementById('password');
        var confirmInput = document.getElementById('password-confirm');
        var strengthLabel = document.getElementById('password-strength-label');
        var bars = document.querySelectorAll('#password-strength-bar .pw-bar');
        var matchHint = document.getElementById('password-match-hint');
        if (!hints || !input) return;

        var minLen = parseInt(hints.getAttribute('data-min') || '10', 10);
        var needLetters = hints.getAttribute('data-letters') === '1';
        var needMixed = hints.getAttribute('data-mixed') === '1';
        var needNumbers = hints.getAttribute('data-numbers') === '1';
        var needSymbols = hints.getAttribute('data-symbols') === '1';

        var labels = @json($passwordStrengthLabelsForJs);

        function checkPwd(v) {
            var ok = { min: v.length >= minLen };
            if (needLetters) ok.letters = /\p{L}/u.test(v);
            if (needMixed) ok.mixed = /(\p{Ll}+.*\p{Lu})|(\p{Lu}+.*\p{Ll})/u.test(v);
            if (needNumbers) ok.numbers = /\p{N}/u.test(v);
            if (needSymbols) ok.symbols = /\p{Z}|\p{S}|\p{P}/u.test(v);
            return ok;
        }

        function allRequired(ok) {
            if (!ok.min) return false;
            if (needLetters && !ok.letters) return false;
            if (needMixed && !ok.mixed) return false;
            if (needNumbers && !ok.numbers) return false;
            if (needSymbols && !ok.symbols) return false;
            return true;
        }

        function setRow(key, met) {
            var row = hints.querySelector('[data-req="' + key + '"]');
            if (!row) return;
            var icon = row.querySelector('.pw-req-icon');
            row.classList.toggle('text-green-600', met);
            row.classList.toggle('dark:text-green-400', met);
            if (icon) {
                icon.classList.toggle('bg-green-500', met);
                icon.classList.toggle('border-green-500', met);
                icon.classList.toggle('border-gray-300', !met);
                icon.classList.toggle('dark:border-dark-500', !met);
            }
        }

        function update() {
            var v = input.value || '';
            var ok = checkPwd(v);
            setRow('min', ok.min);
            if (needLetters) setRow('letters', !!ok.letters);
            if (needMixed) setRow('mixed', !!ok.mixed);
            if (needNumbers) setRow('numbers', !!ok.numbers);
            if (needSymbols) setRow('symbols', !!ok.symbols);

            var tier = null;
            var lit = 0;
            if (v.length === 0) {
                tier = null;
                lit = 0;
            } else if (!allRequired(ok) || v.length < minLen + 2) {
                tier = 'simple';
                lit = 1;
            } else if (v.length < minLen + 6) {
                tier = 'medium';
                lit = 2;
            } else {
                tier = 'strong';
                lit = 3;
            }

            var labelBase = 'text-xs font-medium transition-colors ';
            if (strengthLabel) {
                if (v.length === 0) {
                    strengthLabel.textContent = '—';
                    strengthLabel.className = labelBase + 'text-gray-400 dark:text-gray-500';
                } else if (tier === 'simple') {
                    strengthLabel.textContent = labels.simple;
                    strengthLabel.className = labelBase + 'text-red-400 dark:text-red-300';
                } else if (tier === 'medium') {
                    strengthLabel.textContent = labels.medium;
                    strengthLabel.className = labelBase + 'text-orange-500 dark:text-orange-400';
                } else {
                    strengthLabel.textContent = labels.strong;
                    strengthLabel.className = labelBase + 'text-green-600 dark:text-green-400';
                }
            }

            var offBar = 'bg-gray-200 dark:bg-dark-600';
            var fillSimple = 'bg-red-300 dark:bg-red-500/90';
            var fillMedium = 'bg-orange-400 dark:bg-orange-500';
            var fillStrong = 'bg-green-500 dark:bg-green-600';
            var fill = tier === 'simple' ? fillSimple : (tier === 'medium' ? fillMedium : fillStrong);
            bars.forEach(function (b, i) {
                var on = i < lit;
                b.className = 'pw-bar flex-1 rounded-sm transition-colors duration-200 ' + (on ? fill : offBar);
            });

            if (confirmInput && matchHint) {
                var c = confirmInput.value;
                var match = c.length > 0 && v === c;
                matchHint.classList.toggle('hidden', !match);
            }
        }

        input.addEventListener('input', update);
        if (confirmInput) confirmInput.addEventListener('input', update);
        update();
    })();
    </script>
@endsection
