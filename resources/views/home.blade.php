@extends('layouts.app')

@section('title', __('Home'))

@section('content')
    <!-- Hero Section -->
    <section class="relative overflow-hidden">
        <!-- Background gradient -->
        <div class="absolute inset-0 bg-gradient-to-br from-primary-500/10 via-transparent to-purple-500/10"></div>
        
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 md:py-32">
            <div class="text-center">
                <!-- Badge -->
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-primary-500/10 border border-primary-500/20 mb-8">
                    <span class="w-2 h-2 rounded-full bg-primary-500 animate-pulse"></span>
                    <span class="text-sm font-medium text-primary-600 dark:text-primary-400">{{ __('Welcome back!') }}</span>
                </div>
                
                <!-- Main heading -->
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-gray-900 dark:text-white mb-6">
                    {{ __('Welcome') }}, <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary-500 to-purple-500">{{ auth()->user()->name }}</span>!
                </h1>
                
                <!-- Subtitle -->
                <p class="text-xl text-gray-600 dark:text-gray-400 max-w-2xl mx-auto mb-10">
                    {{ __('You are logged in!') }} {{ __('Explore the latest news and stay updated with our platform.') }}
                </p>
                
                <!-- CTA Buttons -->
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-2 px-8 py-4 bg-primary-500 hover:bg-primary-600 text-white font-semibold rounded-xl transition-all duration-300 shadow-lg shadow-primary-500/25 hover:shadow-primary-500/40 hover:-translate-y-1">
                        <i class="fas fa-newspaper"></i>
                        {{ __('View News') }}
                    </a>
                    @if(auth()->user()->is_admin)
                        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-2 px-8 py-4 bg-gray-100 dark:bg-dark-800 hover:bg-gray-200 dark:hover:bg-dark-700 text-gray-700 dark:text-gray-300 font-semibold rounded-xl transition-all duration-300 border border-gray-200 dark:border-dark-700 hover:-translate-y-1">
                            <i class="fas fa-shield-alt"></i>
                            {{ __('Admin Dashboard') }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </section>
    
    <!-- Features Section -->
    <section class="py-16 md:py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">{{ __('What you can do') }}</h2>
                <p class="text-lg text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">{{ __('Explore all the features available to you as a registered user.') }}</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="group p-8 bg-white dark:bg-dark-800 rounded-2xl border border-gray-200 dark:border-dark-700 hover:border-primary-500/50 transition-all duration-300 hover:-translate-y-2 hover:shadow-xl">
                    <div class="w-14 h-14 bg-primary-500/10 rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <i class="fas fa-newspaper text-2xl text-primary-500"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">{{ __('Read News') }}</h3>
                    <p class="text-gray-600 dark:text-gray-400">{{ __('Stay updated with the latest news and articles from our platform.') }}</p>
                </div>
                
                <!-- Feature 2 -->
                <div class="group p-8 bg-white dark:bg-dark-800 rounded-2xl border border-gray-200 dark:border-dark-700 hover:border-green-500/50 transition-all duration-300 hover:-translate-y-2 hover:shadow-xl">
                    <div class="w-14 h-14 bg-green-500/10 rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <i class="fas fa-comments text-2xl text-green-500"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">{{ __('Comment & Discuss') }}</h3>
                    <p class="text-gray-600 dark:text-gray-400">{{ __('Share your thoughts and engage with other readers through comments.') }}</p>
                </div>
                
                <!-- Feature 3 -->
                <div class="group p-8 bg-white dark:bg-dark-800 rounded-2xl border border-gray-200 dark:border-dark-700 hover:border-purple-500/50 transition-all duration-300 hover:-translate-y-2 hover:shadow-xl">
                    <div class="w-14 h-14 bg-purple-500/10 rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <i class="fas fa-users text-2xl text-purple-500"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">{{ __('Meet the Team') }}</h3>
                    <p class="text-gray-600 dark:text-gray-400">{{ __('Get to know the amazing people behind our platform.') }}</p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Admin Badge Section -->
    @if(auth()->user()->is_admin)
        <section class="py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="relative overflow-hidden bg-gradient-to-r from-primary-500 to-purple-500 rounded-3xl p-8 md:p-12">
                    <div class="absolute top-0 right-0 -mt-12 -mr-12 w-48 h-48 bg-white/10 rounded-full blur-3xl"></div>
                    <div class="absolute bottom-0 left-0 -mb-12 -ml-12 w-48 h-48 bg-white/10 rounded-full blur-3xl"></div>
                    
                    <div class="relative flex flex-col md:flex-row items-center justify-between gap-6">
                        <div class="flex items-center gap-4">
                            <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center">
                                <i class="fas fa-crown text-3xl text-white"></i>
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold text-white mb-1">{{ __('Admin Privileges') }}</h3>
                                <p class="text-white/80">{{ __('You have full access to the admin panel.') }}</p>
                            </div>
                        </div>
                        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-white text-primary-600 font-semibold rounded-xl hover:bg-gray-100 transition-colors">
                            <i class="fas fa-arrow-right"></i>
                            {{ __('Go to Admin Dashboard') }}
                        </a>
                    </div>
                </div>
            </div>
        </section>
    @endif
    
    <!-- Quick Links Section -->
    <section class="py-16 md:py-24 bg-gray-50 dark:bg-dark-800/50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">{{ __('Quick Links') }}</h2>
                <p class="text-lg text-gray-600 dark:text-gray-400">{{ __('Navigate to important sections of the platform.') }}</p>
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <a href="{{ route('home') }}" class="group p-6 bg-white dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 hover:border-primary-500 transition-all duration-300 text-center hover:-translate-y-1 hover:shadow-lg">
                    <i class="fas fa-newspaper text-3xl text-primary-500 mb-3 group-hover:scale-110 transition-transform"></i>
                    <p class="font-medium text-gray-900 dark:text-white">{{ __('News') }}</p>
                </a>
                
                <a href="{{ route('team') }}" class="group p-6 bg-white dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 hover:border-green-500 transition-all duration-300 text-center hover:-translate-y-1 hover:shadow-lg">
                    <i class="fas fa-users text-3xl text-green-500 mb-3 group-hover:scale-110 transition-transform"></i>
                    <p class="font-medium text-gray-900 dark:text-white">{{ __('Team') }}</p>
                </a>
                
                @auth
                    <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="group p-6 bg-white dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 hover:border-red-500 transition-all duration-300 text-center hover:-translate-y-1 hover:shadow-lg">
                        <i class="fas fa-sign-out-alt text-3xl text-red-500 mb-3 group-hover:scale-110 transition-transform"></i>
                        <p class="font-medium text-gray-900 dark:text-white">{{ __('Logout') }}</p>
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                        @csrf
                    </form>
                @endauth
            </div>
        </div>
    </section>
@endsection
