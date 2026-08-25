@extends('layouts.admin')

@section('title', __('System updates'))

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('System updates') }}</h1>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="bg-white dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-code-branch text-primary-500"></i>
                {{ __('Installed version') }}
            </h2>
            <p class="mt-4 text-3xl font-bold text-gray-900 dark:text-white font-mono tracking-tight">{{ $installed }}</p>
        </div>

        <div class="bg-white dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-cloud-download-alt text-amber-500"></i>
                {{ __('Update source') }}
            </h2>
            @if(!$configured)
                <p class="text-sm text-amber-700 dark:text-amber-300 mt-3">{{ __('Not configured. Set CMS_UPDATE_MANIFEST_URL in .env to a HTTPS JSON manifest.') }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">{{ __('See docs/UPDATES.md for manifest format and packaging.') }}</p>
            @else
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 break-all font-mono">{{ $manifestUrl }}</p>
                <a href="{{ route('admin.system-update.index', ['refresh' => 1]) }}" class="inline-flex items-center gap-2 mt-4 text-sm text-primary-600 dark:text-primary-400 hover:underline">
                    <i class="fas fa-sync-alt"></i>
                    {{ __('Check again now') }}
                </a>
            @endif
        </div>
    </div>

    @if($configured && $manifest)
        <div class="mt-8 bg-white dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-box-open text-purple-500"></i>
                {{ __('Available release') }}
            </h2>
            <div class="mt-4 flex flex-wrap items-center gap-3">
                <span class="text-2xl font-mono font-bold text-gray-900 dark:text-white">{{ $manifest['version'] }}</span>
                @if($updateAvailable)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-200 text-xs font-semibold">
                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                        {{ __('Update available') }}
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-200 text-xs font-semibold">
                        <i class="fas fa-check"></i>
                        {{ __('Up to date') }}
                    </span>
                @endif
            </div>
            @if(!empty($manifest['min_php']))
                <p class="text-sm text-gray-600 dark:text-gray-300 mt-3">{{ __('Requires PHP :v or newer.', ['v' => $manifest['min_php']]) }}</p>
            @endif
            @if(!empty($manifest['notes']))
                <div class="mt-4 p-4 rounded-lg bg-gray-50 dark:bg-dark-900/50 border border-gray-200 dark:border-dark-600">
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-2">{{ __('Release notes') }}</p>
                    <div class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $manifest['notes'] }}</div>
                </div>
            @endif

            @if($updateAvailable && $enabled)
                @if($backupBefore)
                    <div class="mt-6 p-4 rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 text-sm text-blue-800 dark:text-blue-200">
                        <p class="font-semibold flex items-center gap-2">
                            <i class="fas fa-shield-alt"></i>
                            {{ __('Automatic backup before update') }}
                        </p>
                        <p class="mt-2">
                            @if($backupRequired)
                                {{ __('Before installing, a backup (database and uploads) is created automatically under storage/app/backups. The update is cancelled if the backup fails.') }}
                            @else
                                {{ __('Before installing, a backup (database and uploads) is attempted automatically under storage/app/backups. The update continues even if the backup fails.') }}
                            @endif
                        </p>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.system-update.apply') }}" class="mt-6 space-y-4" onsubmit="return confirm(@json($backupBefore ? __('Run update now? The site may be briefly unavailable. A backup is created automatically first.') : __('Run update now? The site may be briefly unavailable.')));">
                    @csrf
                    <label class="flex items-start gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                        <input type="checkbox" name="confirm" value="1" class="mt-1 rounded border-gray-300 dark:border-dark-600 text-primary-600 focus:ring-primary-500" required>
                        <span>{{ $backupBefore ? __('I understand that config/ and .env stay untouched; uploads in storage/app/public and backups are preserved. An automatic backup runs before the update.') : __('I understand that config/ and .env stay untouched; uploads in storage/app/public and backups are preserved.') }}</span>
                    </label>
                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium">
                        <i class="fas fa-arrow-alt-circle-up"></i>
                        {{ __('Install update') }}
                    </button>
                </form>
            @elseif($updateAvailable && !$enabled)
                <p class="mt-6 text-sm text-gray-500 dark:text-gray-400">{{ __('Updates are disabled (CMS_UPDATE_ENABLED=false).') }}</p>
            @endif
        </div>
    @elseif($configured)
        <div class="mt-8 p-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-sm text-red-800 dark:text-red-200">
            {{ __('Manifest could not be loaded. Check URL, TLS, firewall and JSON format.') }}
        </div>
    @endif
@endsection
