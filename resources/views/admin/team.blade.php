@extends('layouts.admin')

@section('title', __('Team List'))

@section('content')
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Team Members') }}</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1">{{ __('View all team members') }}</p>
        </div>
    </div>
    
    <!-- Team Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($users as $user)
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
            <div class="bg-white dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 overflow-hidden hover:shadow-lg transition-shadow">
                <!-- Card Header -->
                <div class="relative p-6 overflow-hidden">
                    @if($userBannerUsesMedia)
                        <img src="{{ $userBannerSource }}" alt="" class="absolute inset-0 w-full h-full object-cover">
                    @else
                        <div class="absolute inset-0" style="background-color: {{ $userBannerColor }};"></div>
                    @endif
                    <div class="absolute inset-0 bg-black/35"></div>
                    <div class="flex items-center gap-4">
                        @if($user->avatar)
                            <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}" class="relative z-10 w-16 h-16 rounded-full object-cover border-2 border-white/30">
                        @else
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&color=fff&background=random" alt="{{ $user->name }}" class="relative z-10 w-16 h-16 rounded-full border-2 border-white/30">
                        @endif
                        <div class="relative z-10 flex-1">
                            <h3 class="text-lg font-bold text-white">{{ $user->name }}</h3>
                            <div class="mt-1">
                                @if($user->is_admin)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-white/20 text-white">
                                        <i class="fas fa-user-shield mr-1"></i>
                                        {{ __('Admin') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-white/20 text-white">
                                        <i class="fas fa-user mr-1"></i>
                                        {{ __('User') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Card Body -->
                <div class="p-6 space-y-4">
                    <!-- Email -->
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-gray-100 dark:bg-dark-700 rounded-lg flex items-center justify-center">
                            <i class="fas fa-envelope text-gray-500 dark:text-gray-400 text-sm"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Email') }}</p>
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $user->email }}</p>
                        </div>
                    </div>
                    
                    <!-- Task -->
                    @if($user->task)
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-gray-100 dark:bg-dark-700 rounded-lg flex items-center justify-center">
                                <i class="fas fa-briefcase text-gray-500 dark:text-gray-400 text-sm"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Task') }}</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $user->task }}</p>
                            </div>
                        </div>
                    @endif
                    
                    <!-- Bio -->
                    @if($user->bio)
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">{{ __('Bio') }}</p>
                            <p class="text-sm text-gray-700 dark:text-gray-300">{{ Str::limit($user->bio, 100) }}</p>
                        </div>
                    @endif
                    
                    <!-- Member Since -->
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-gray-100 dark:bg-dark-700 rounded-lg flex items-center justify-center">
                            <i class="fas fa-calendar text-gray-500 dark:text-gray-400 text-sm"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Member since') }}</p>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $user->created_at->format('d.m.Y') }}</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('admin.team.banner.update', $user) }}" enctype="multipart/form-data" class="space-y-3 border-t border-gray-200 dark:border-dark-700 pt-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('Banner mode') }}</label>
                            <select name="team_banner_mode" class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 text-gray-900 dark:text-white px-3 py-2 text-sm">
                                <option value="color" @selected($userBannerMode === 'color')>{{ __('Color') }}</option>
                                <option value="media" @selected($userBannerMode === 'media')>{{ __('Image / GIF') }}</option>
                            </select>
                        </div>

                        <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                            <input type="checkbox" name="team_visible" value="1" @checked($user->team_visible ?? true) class="rounded border-gray-300 dark:border-dark-600 text-primary-600 focus:ring-primary-500">
                            <span>{{ __('Show this team member on public team page') }}</span>
                        </label>

                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('Team order (lower = higher)') }}</label>
                            <input type="number" min="0" max="9999" step="1" name="team_sort_order" value="{{ $user->team_sort_order }}" placeholder="0" class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 text-gray-900 dark:text-white px-3 py-2 text-sm">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('Banner color') }}</label>
                            <input type="color" name="team_banner_color" value="{{ $userBannerColor }}" class="h-10 w-20 rounded border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('Banner image/GIF URL (optional)') }}</label>
                            <input type="url" name="team_banner_media_url" value="{{ $user->team_banner_media_url }}" placeholder="https://example.com/banner.gif" class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 text-gray-900 dark:text-white px-3 py-2 text-sm">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('Upload banner image/GIF (optional)') }}</label>
                            <input type="file" name="team_banner_media" accept="image/*" class="block w-full text-xs text-gray-600 dark:text-gray-300 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-gray-100 dark:file:bg-dark-700 file:text-gray-700 dark:file:text-gray-200">
                        </div>

                        @if(filled($user->team_banner_media_path))
                            <label class="inline-flex items-center gap-2 text-xs text-gray-600 dark:text-gray-300">
                                <input type="checkbox" name="team_banner_media_remove" value="1" class="rounded border-gray-300 dark:border-dark-600 text-primary-600 focus:ring-primary-500">
                                <span>{{ __('Remove uploaded banner') }}</span>
                            </label>
                        @endif

                        <button type="submit" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium">
                            <i class="fas fa-save"></i>
                            {{ __('Save banner') }}
                        </button>
                    </form>
                </div>
                
                <!-- Card Footer -->
                <div class="px-6 py-4 bg-gray-50 dark:bg-dark-700/50 border-t border-gray-200 dark:border-dark-700">
                    @php
                        $discordAccount = $user->socialAccounts->firstWhere('provider', 'discord');
                        $contactHref = $discordAccount ? 'https://discord.com/users/'.$discordAccount->provider_id : 'mailto:'.$user->email;
                    @endphp
                    <a href="{{ $contactHref }}"
                        @if($discordAccount) target="_blank" rel="noopener noreferrer" @endif
                        class="flex items-center justify-center gap-2 w-full px-4 py-2 bg-primary-500 hover:bg-primary-600 text-white rounded-lg transition-colors">
                        <i class="{{ $discordAccount ? 'fab fa-discord' : 'fas fa-envelope' }}"></i>
                        {{ $discordAccount ? __('Discord') : __('Contact') }}
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full">
                <div class="bg-white dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 p-12 text-center">
                    <i class="fas fa-users text-4xl text-gray-400 mb-4"></i>
                    <p class="text-gray-500 dark:text-gray-400">{{ __('No team members found') }}</p>
                </div>
            </div>
        @endforelse
    </div>
@endsection
