@extends('layouts.app')

@section('title', __('Team'))

@section('content')
    <section class="relative overflow-hidden hw-hero-panel">
        <div class="absolute inset-0 hw-hero-panel__glow"></div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-24 text-center">
            <div class="hw-hero-badge mx-auto">
                <i class="fas fa-user-gear" aria-hidden="true"></i>
                <span>{{ __('Refrigeration & HVAC technicians') }}</span>
            </div>
            <h1 class="handwerk-brand hw-hero-panel__title">
                {{ __('Your team for cooling and climate technology') }}
            </h1>
            <p class="hw-hero-panel__lead mx-auto">
                {{ __('Certified specialists for installation, commissioning and service of cooling and air-conditioning systems.') }}
            </p>
        </div>
    </section>

    <section class="py-16 md:py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if($users->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($users as $user)
                        @php
                            $userBannerMode = (string) ($user->team_banner_mode ?: 'color');
                            $userBannerColor = (string) ($user->team_banner_color ?: '#5b9fd4');
                            if (! preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $userBannerColor)) {
                                $userBannerColor = '#5b9fd4';
                            }
                            $userBannerSource = '';
                            if (filled($user->team_banner_media_path)) {
                                $userBannerSource = asset('storage/'.$user->team_banner_media_path);
                            } elseif (filled($user->team_banner_media_url)) {
                                $userBannerSource = (string) $user->team_banner_media_url;
                            }
                            $userBannerUsesMedia = $userBannerMode === 'media' && $userBannerSource !== '';
                        @endphp
                        <div class="group hw-news-card text-center">
                            <div class="relative h-32 overflow-hidden">
                                @if($userBannerUsesMedia)
                                    <img src="{{ $userBannerSource }}" alt="" class="absolute inset-0 w-full h-full object-cover" loading="lazy">
                                @else
                                    <div class="absolute inset-0" style="background-color: {{ $userBannerColor }};"></div>
                                @endif
                                <div class="absolute inset-0 bg-black/15"></div>
                            </div>
                            <div class="relative px-6 -mt-12 pb-2">
                                <div class="w-24 h-24 mx-auto rounded-2xl overflow-hidden border-4 border-white shadow-lg group-hover:scale-105 transition-transform">
                                    @if($user->avatar)
                                        <img src="{{ asset('storage/'.$user->avatar) }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full bg-primary-500 flex items-center justify-center">
                                            <span class="text-3xl font-bold text-white">{{ substr($user->name, 0, 1) }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="px-6 pb-6 pt-2">
                                <h3 class="text-xl font-bold text-gray-900 group-hover:text-primary-600 transition-colors">{{ $user->name }}</h3>
                                <div class="mt-2 mb-3">
                                    @if($user->is_admin)
                                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-700 border border-red-200">
                                            <i class="fas fa-crown"></i> {{ __('Admin') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-primary-500/10 text-primary-700 border border-primary-500/20">
                                            <i class="fas fa-fan"></i> {{ __('HVAC technician') }}
                                        </span>
                                    @endif
                                </div>
                                @if($user->task)
                                    <p class="text-sm text-gray-600 mb-3"><i class="fas fa-briefcase text-primary-600"></i> {{ $user->task }}</p>
                                @endif
                                @if($user->bio)
                                    <p class="text-sm text-gray-600 line-clamp-3 mb-4">{{ Str::limit($user->bio, 100) }}</p>
                                @endif
                                @php
                                    $discordAccount = $user->socialAccounts->firstWhere('provider', 'discord');
                                    $emailUser = \Illuminate\Support\Str::before((string) $user->email, '@');
                                    $emailDomain = \Illuminate\Support\Str::after((string) $user->email, '@');
                                @endphp
                                @if($discordAccount)
                                    <a href="https://discord.com/users/{{ $discordAccount->provider_id }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-100 hover:bg-primary-600 hover:text-white text-sm font-medium transition-colors">
                                        <i class="fab fa-discord"></i> {{ __('Discord') }}
                                    </a>
                                @else
                                    <a href="#" data-protected-email-user="{{ e($emailUser) }}" data-protected-email-domain="{{ e($emailDomain) }}" data-protected-email-reveal="0" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-100 hover:bg-primary-600 hover:text-white text-sm font-medium transition-colors">
                                        <i class="fas fa-envelope"></i> {{ __('Contact') }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="hw-news-empty">
                    <i class="fas fa-users" aria-hidden="true"></i>
                    <h3>{{ __('No team members yet') }}</h3>
                    <p>{{ __('Our team will appear here soon.') }}</p>
                </div>
            @endif
        </div>
    </section>

    <section class="py-16 hw-cta-band">
        <div class="max-w-3xl mx-auto px-4 text-center">
            <h2 class="text-2xl md:text-3xl font-bold mb-3">{{ __('Join our refrigeration & HVAC team') }}</h2>
            <p class="text-white/90 mb-6">{{ __('We are looking for motivated refrigeration fitters and HVAC technicians.') }}</p>
            <a href="{{ $contactUrl }}" class="hw-cta-band__btn inline-flex items-center gap-2 px-8 py-3.5 rounded-xl">
                <i class="fas fa-paper-plane" aria-hidden="true"></i> {{ __('Get in Touch') }}
            </a>
        </div>
    </section>
@endsection
