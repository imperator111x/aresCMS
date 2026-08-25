@extends('layouts.admin')

@section('title', __('Themes'))

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Themes') }}</h1>
        <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">{{ __('Choose the public appearance of your site. Admin area stays unchanged.') }}</p>
    </div>

    <div class="mb-6 flex flex-wrap gap-2">
        <a href="{{ route('admin.settings.general') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 dark:border-dark-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-dark-700">
            <i class="fas fa-sliders-h"></i> {{ __('General Settings') }}
        </a>
        <a href="{{ route('admin.settings.themes') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border bg-primary-600 text-white border-primary-600">
            <i class="fas fa-palette"></i> {{ __('Themes') }}
        </a>
        <a href="{{ route('admin.settings.languages') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 dark:border-dark-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-dark-700">
            <i class="fas fa-language"></i> {{ __('Language Settings') }}
        </a>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200 flex items-center gap-3">
            <i class="fas fa-check-circle"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(count($themes) < 2)
        <div class="mb-6 p-4 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 text-amber-900 dark:text-amber-100 text-sm">
            <p class="font-semibold flex items-center gap-2"><i class="fas fa-exclamation-triangle"></i> {{ __('Only one theme is installed') }}</p>
            <p class="mt-2">{{ __('Upload additional theme folders (e.g. themes/handwerk/ or themes/magazine/) to your server via FTP.') }}</p>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 p-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200 text-sm">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="grid gap-6 md:grid-cols-2">
        @foreach($themes as $slug => $theme)
            @php $isActive = $activeTheme === $slug; @endphp
            <div class="rounded-2xl border-2 {{ $isActive ? 'border-primary-500 ring-2 ring-primary-500/30' : 'border-gray-200 dark:border-dark-700' }} bg-white dark:bg-dark-800 overflow-hidden flex flex-col">
                <div class="h-28 flex items-center justify-center @if($slug === 'handwerk') bg-gradient-to-br from-orange-100 via-stone-100 to-slate-200 @elseif($slug === 'magazine') bg-gradient-to-br from-rose-100 via-stone-100 to-amber-50 dark:from-stone-900 dark:via-rose-950 dark:to-stone-900 @else bg-gradient-to-br from-primary-100 via-white to-purple-100 dark:from-dark-900 dark:via-primary-950 dark:to-purple-950 @endif">
                    @if($slug === 'handwerk')
                        <div class="text-center px-4">
                            <span class="w-12 h-12 mx-auto rounded-lg bg-orange-600 text-white inline-flex items-center justify-center shadow-md mb-2"><i class="fas fa-hammer text-xl"></i></span>
                            <span class="block text-lg font-bold text-slate-900">Handwerk</span>
                            <span class="block text-xs uppercase tracking-widest text-orange-800 mt-1">Craftsman</span>
                        </div>
                    @elseif($slug === 'magazine')
                        <div class="text-center px-4">
                            <span class="block text-2xl font-bold text-stone-900 dark:text-white" style="font-family: Georgia, serif">Magazin</span>
                            <span class="block text-xs uppercase tracking-widest text-rose-700 dark:text-rose-400 mt-1">Editorial</span>
                        </div>
                    @else
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary-500 to-purple-500 flex items-center justify-center shadow-lg">
                                <i class="fas fa-newspaper text-white text-xl"></i>
                            </div>
                            <span class="text-lg font-bold text-gray-900 dark:text-white">Standard</span>
                        </div>
                    @endif
                </div>
                <div class="p-5 flex flex-col flex-1">
                    <div class="flex items-start justify-between gap-2">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $theme['name'] ?? $slug }}</h2>
                        @if($isActive)
                            <span class="shrink-0 inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-primary-100 dark:bg-primary-900/40 text-primary-700 dark:text-primary-300 text-xs font-bold">
                                <i class="fas fa-check"></i> {{ __('Active') }}
                            </span>
                        @endif
                    </div>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400 flex-1">{{ $theme['description'] ?? '' }}</p>
                    <p class="mt-3 text-xs text-gray-500 font-mono">themes/{{ $slug }}</p>

                    <div class="mt-5 pt-4 border-t border-gray-100 dark:border-dark-700">
                        @if($isActive)
                            <p class="text-sm text-gray-500 dark:text-gray-400 flex items-center gap-2">
                                <i class="fas fa-circle-check text-primary-500"></i>
                                {{ __('This theme is currently active.') }}
                            </p>
                        @else
                            <form action="{{ route('admin.settings.themes.update') }}" method="POST" class="m-0">
                                @csrf
                                @method('PUT')
                                <button
                                    type="submit"
                                    name="theme"
                                    value="{{ $slug }}"
                                    class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-primary-600 hover:bg-primary-700 active:bg-primary-800 text-white font-semibold shadow-md cursor-pointer"
                                >
                                    <i class="fas fa-check"></i>
                                    {{ __('Activate this theme') }}
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-8">
        <a href="{{ url('/') }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 text-sm text-primary-600 dark:text-primary-400 hover:underline">
            <i class="fas fa-external-link-alt"></i> {{ __('Preview website') }}
        </a>
    </div>
@endsection
