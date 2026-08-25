@extends('layouts.app')

@section('title', __('Team'))

@section('content')
    <!-- Hero Section -->
    <section class="relative overflow-hidden">
        <!-- Background gradient -->
        <div class="absolute inset-0 bg-gradient-to-br from-green-500/10 via-transparent to-blue-500/10"></div>
        
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 md:py-32">
            <div class="text-center">
                <!-- Badge -->
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-green-500/10 border border-green-500/20 mb-8">
                    <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                    <span class="text-sm font-medium text-green-600 dark:text-green-400">{{ __('Our Team') }}</span>
                </div>
                
                <!-- Main heading -->
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-gray-900 dark:text-white mb-6">
                    {{ __('Meet the amazing people') }} <span class="text-transparent bg-clip-text bg-gradient-to-r from-green-500 to-blue-500">{{ __('behind our platform') }}</span>
                </h1>
                
                <!-- Subtitle -->
                <p class="text-xl text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
                    {{ __('Get to know the talented individuals who make everything possible.') }}
                </p>
            </div>
        </div>
    </section>
    
    <!-- Team Grid Section -->
    <section class="py-16 md:py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if($users->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($users as $user)
                        @php
                            $userBannerMode = (string) ($user->team_banner_mode ?: 'color');
                            $userBannerColor = (string) ($user->team_banner_color ?: '#7c3aed');
                            if (! preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $userBannerColor)) {
                                $userBannerColor = '#7c3aed';
                            }
                            $userBannerSource = '';
                            if (filled($user->team_banner_media_path)) {
                                $userBannerSource = asset('storage/'.$user->team_banner_media_path);
                            } elseif (filled($user->team_banner_media_url)) {
                                $userBannerSource = (string) $user->team_banner_media_url;
                            }
                            $userBannerUsesMedia = $userBannerMode === 'media' && $userBannerSource !== '';
                        @endphp
                        <div class="group relative bg-white dark:bg-dark-800 rounded-2xl border border-gray-200 dark:border-dark-700 overflow-hidden hover:border-primary-500/50 transition-all duration-500 hover:-translate-y-2 hover:shadow-2xl">
                            <!-- Card Header with Avatar -->
                            <div class="relative h-32 overflow-hidden">
                                @if($userBannerUsesMedia)
                                    <img src="{{ $userBannerSource }}" alt="" class="absolute inset-0 w-full h-full object-cover" loading="lazy" decoding="async">
                                @else
                                    <div class="absolute inset-0" style="background-color: {{ $userBannerColor }};"></div>
                                @endif
                                <div class="absolute inset-0 bg-black/20"></div>
                                <!-- Decorative circles -->
                                <div class="absolute top-0 right-0 -mt-8 -mr-8 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
                                <div class="absolute bottom-0 left-0 -mb-8 -ml-8 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
                            </div>
                            
                            <!-- Avatar -->
                            <div class="relative px-6 -mt-12">
                                <div class="w-24 h-24 mx-auto rounded-2xl overflow-hidden border-4 border-white dark:border-dark-800 shadow-xl group-hover:scale-110 transition-transform duration-500">
                                    @if($user->avatar)
                                        <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                                    @else
                                        <div @class([
                                            'w-full h-full bg-gradient-to-br flex items-center justify-center',
                                            'from-primary-400 to-purple-400' => $user->is_admin,
                                            'from-sky-400 to-blue-400' => ! $user->is_admin,
                                        ])>
                                            <span class="text-3xl font-bold text-white">{{ substr($user->name, 0, 1) }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Card Body -->
                            <div class="p-6 pt-4 text-center">
                                <!-- Name -->
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2 group-hover:text-primary-500 transition-colors">
                                    {{ $user->name }}
                                </h3>
                                
                                <!-- Role Badge -->
                                <div class="mb-4">
                                    @if($user->is_admin)
                                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-red-500/10 text-red-600 dark:text-red-400 border border-red-500/20">
                                            <i class="fas fa-crown"></i>
                                            {{ __('Admin') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-green-500/10 text-green-600 dark:text-green-400 border border-green-500/20">
                                            <i class="fas fa-user"></i>
                                            {{ __('Team Member') }}
                                        </span>
                                    @endif
                                </div>
                                
                                <!-- Task/Position -->
                                @if($user->task)
                                    <div class="flex items-center justify-center gap-2 text-gray-600 dark:text-gray-400 mb-4">
                                        <i class="fas fa-briefcase text-sm"></i>
                                        <span class="text-sm">{{ $user->task }}</span>
                                    </div>
                                @endif
                                
                                <!-- Bio -->
                                @if($user->bio)
                                    <p class="text-gray-600 dark:text-gray-400 text-sm mb-4 line-clamp-3">
                                        {{ Str::limit($user->bio, 100) }}
                                    </p>
                                @endif
                                
                                <!-- Contact Button -->
                                @php
                                    $discordAccount = $user->socialAccounts->firstWhere('provider', 'discord');
                                    $emailUser = \Illuminate\Support\Str::before((string) $user->email, '@');
                                    $emailDomain = \Illuminate\Support\Str::after((string) $user->email, '@');
                                @endphp
                                @if($discordAccount)
                                    <a href="https://discord.com/users/{{ $discordAccount->provider_id }}"
                                        target="_blank" rel="noopener noreferrer"
                                        class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-dark-700 hover:bg-primary-500 hover:text-white dark:hover:bg-primary-500 text-gray-700 dark:text-gray-300 font-medium rounded-xl transition-all duration-300 text-sm">
                                        <i class="fab fa-discord"></i>
                                        {{ __('Discord') }}
                                    </a>
                                @else
                                    <a href="#"
                                        data-protected-email-user="{{ e($emailUser) }}"
                                        data-protected-email-domain="{{ e($emailDomain) }}"
                                        data-protected-email-reveal="0"
                                        class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-dark-700 hover:bg-primary-500 hover:text-white dark:hover:bg-primary-500 text-gray-700 dark:text-gray-300 font-medium rounded-xl transition-all duration-300 text-sm">
                                        <i class="fas fa-envelope"></i>
                                        {{ __('Contact') }}
                                    </a>
                                @endif
                            </div>
                            
                            <!-- Hover Effect Overlay -->
                            <div class="absolute inset-0 bg-gradient-to-t from-primary-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>
                        </div>
                    @endforeach
                </div>
            @else
                <!-- Empty State -->
                <div class="text-center py-16">
                    <div class="w-24 h-24 mx-auto bg-gray-100 dark:bg-dark-800 rounded-full flex items-center justify-center mb-6">
                        <i class="fas fa-users text-4xl text-gray-400"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">{{ __('No team members yet') }}</h3>
                    <p class="text-gray-600 dark:text-gray-400">{{ __('Our team will appear here soon.') }}</p>
                </div>
            @endif
        </div>
    </section>
    
    <!-- Stats Section -->
    <section class="py-16 bg-gray-50 dark:bg-dark-800/50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="text-4xl md:text-5xl font-bold text-primary-500 mb-2">{{ $users->count() }}</div>
                    <div class="text-gray-600 dark:text-gray-400">{{ __('Team Members') }}</div>
                </div>
                <div class="text-center">
                    <div class="text-4xl md:text-5xl font-bold text-blue-500 mb-2">{{ \App\Models\News::count() }}</div>
                    <div class="text-gray-600 dark:text-gray-400">{{ __('Articles') }}</div>
                </div>
                <div class="text-center">
                    <div class="text-4xl md:text-5xl font-bold text-purple-500 mb-2">{{ \App\Models\Comment::count() }}</div>
                    <div class="text-gray-600 dark:text-gray-400">{{ __('Comments') }}</div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- CTA Section -->
    <section class="py-16 md:py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="relative overflow-hidden bg-gradient-to-r from-green-500 to-blue-500 rounded-3xl p-8 md:p-12">
                <div class="absolute top-0 right-0 -mt-12 -mr-12 w-48 h-48 bg-white/10 rounded-full blur-3xl"></div>
                <div class="absolute bottom-0 left-0 -mb-12 -ml-12 w-48 h-48 bg-white/10 rounded-full blur-3xl"></div>
                
                <div class="relative text-center">
                    <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">{{ __('Want to join our team?') }}</h2>
                    <p class="text-white/80 mb-8 max-w-2xl mx-auto">{{ __('We are always looking for talented individuals to join our growing team.') }}</p>
                    @php
                        $teamCtaUser = 'team';
                        $teamCtaDomain = 'example.com';
                    @endphp
                    <a href="#"
                        data-protected-email-user="{{ $teamCtaUser }}"
                        data-protected-email-domain="{{ $teamCtaDomain }}"
                        data-protected-email-reveal="0"
                        class="inline-flex items-center gap-2 px-8 py-4 bg-white text-green-600 font-semibold rounded-xl hover:bg-gray-100 transition-colors">
                        <i class="fas fa-paper-plane"></i>
                        {{ __('Get in Touch') }}
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection
