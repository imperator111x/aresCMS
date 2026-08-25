@extends('layouts.admin')

@section('title', __('Operations'))

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Operations') }}</h1>
        <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">{{ __('Backups and maintenance mode (also available via CLI, see docs/BETRIEB.md).') }}</p>
    </div>

    @if(session('maintenance_bypass_url'))
        <div class="mb-4 p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
            <p class="text-sm font-medium text-amber-900 dark:text-amber-100 mb-2">{{ __('Bypass URL (save this — opens the public site during maintenance):') }}</p>
            <a href="{{ session('maintenance_bypass_url') }}" class="text-sm text-primary-600 dark:text-primary-400 break-all underline font-mono" target="_blank" rel="noopener">{{ session('maintenance_bypass_url') }}</a>
        </div>
    @endif

    @if(session('backup_cli_output'))
        <div class="mb-4 p-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-lg">
            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-2">{{ __('Command output') }}</p>
            <pre class="text-xs text-gray-700 dark:text-gray-300 whitespace-pre-wrap font-mono">{{ session('backup_cli_output') }}</pre>
        </div>
    @endif

    @if(session('dependency_update_output'))
        <div class="mb-4 p-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-lg">
            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-2">{{ __('Dependency update output') }}</p>
            <pre class="text-xs text-gray-700 dark:text-gray-300 whitespace-pre-wrap font-mono">{{ session('dependency_update_output') }}</pre>
        </div>
    @endif

    @if(session('migrate_cli_output'))
        <div class="mb-4 p-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-lg">
            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-2">{{ __('Migration output') }}</p>
            <pre class="text-xs text-gray-700 dark:text-gray-300 whitespace-pre-wrap font-mono">{{ session('migrate_cli_output') }}</pre>
        </div>
    @endif

    @if(session('cache_clear_output'))
        <div class="mb-4 p-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-lg">
            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-2">{{ __('Cache clear output') }}</p>
            <pre class="text-xs text-gray-700 dark:text-gray-300 whitespace-pre-wrap font-mono">{{ session('cache_clear_output') }}</pre>
        </div>
    @endif

    @if(session('frontend_build_output'))
        <div class="mb-4 p-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-lg">
            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-2">{{ __('Frontend build output') }}</p>
            <pre class="text-xs text-gray-700 dark:text-gray-300 whitespace-pre-wrap font-mono">{{ session('frontend_build_output') }}</pre>
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Wartungsmodus --}}
        <div class="bg-white dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-tools text-amber-500"></i>
                {{ __('Maintenance mode') }}
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                @if($maintenanceActive)
                    <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-md bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-200 text-xs font-medium">
                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                        {{ __('Active — visitors see the maintenance page.') }}
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-md bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200 text-xs font-medium">
                        <span class="w-2 h-2 rounded-full bg-green-500"></span>
                        {{ __('Off — site is publicly available.') }}
                    </span>
                @endif
            </p>
            @if($maintenanceActive && $maintenanceSecret)
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-3">
                    {{ __('Existing bypass URL:') }}
                    <a href="{{ url($maintenanceSecret) }}" class="text-primary-600 dark:text-primary-400 underline break-all font-mono" target="_blank" rel="noopener">{{ url($maintenanceSecret) }}</a>
                </p>
            @endif
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-3 leading-relaxed">
                {{ __('While logged in as admin, you can use the whole site (public pages and admin) during maintenance. Guests see the maintenance page.') }}
            </p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                {{ __('Direct admin login (for guests on the maintenance page):') }}
                <a href="{{ route('maintenance.admin.login') }}" class="text-primary-600 dark:text-primary-400 underline font-mono break-all" target="_blank" rel="noopener">{{ url('/wartung/admin-anmeldung') }}</a>
            </p>
            <div class="mt-6 flex flex-wrap gap-3">
                @if(!$maintenanceActive)
                    <form method="POST" action="{{ route('admin.operations.maintenance.enable') }}" class="space-y-4 w-full" onsubmit="return confirm(@json(__('Enable maintenance mode? The public site will show the maintenance page.')));">
                        @csrf
                        <label class="flex items-start gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                            <input type="checkbox" name="bypass_secret" value="1" class="mt-1 rounded border-gray-300 dark:border-dark-600 text-primary-600 focus:ring-primary-500">
                            <span>{{ __('Also generate a secret bypass URL for the public site (bookmark it before activating).') }}</span>
                        </label>
                        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium">
                            <i class="fas fa-pause-circle"></i>
                            {{ __('Enable maintenance mode') }}
                        </button>
                    </form>
                @else
                    <form method="POST" action="{{ route('admin.operations.maintenance.disable') }}" onsubmit="return confirm(@json(__('Disable maintenance mode and make the site public again?')));">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-medium">
                            <i class="fas fa-play-circle"></i>
                            {{ __('Disable maintenance mode') }}
                        </button>
                    </form>
                @endif
            </div>
        </div>

        {{-- Backup --}}
        <div class="bg-white dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-database text-primary-500"></i>
                {{ __('Backup') }}
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                {{ __('Creates a ZIP in storage/app/backups (database + public uploads). May take a while.') }}
            </p>
            <form method="POST" action="{{ route('admin.operations.backup') }}" class="mt-6" onsubmit="return confirm(@json(__('Start backup now? This can take several minutes.')));">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium">
                    <i class="fas fa-file-archive"></i>
                    {{ __('Create backup now') }}
                </button>
            </form>

            <div class="mt-8 pt-6 border-t border-gray-200 dark:border-dark-700">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="fas fa-undo text-amber-500"></i>
                    {{ __('Restore backup') }}
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                    {{ __('Restore database and public uploads from a backup ZIP. A safety backup is created automatically before restore.') }}
                </p>
                <p class="text-xs text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-md px-3 py-2 mt-3">
                    {{ __('This overwrites the current database and replaces files in storage/app/public. The site is briefly put in maintenance mode during restore.') }}
                </p>

                <form method="POST" action="{{ route('admin.operations.backup.restore') }}" enctype="multipart/form-data" class="mt-5 space-y-4" onsubmit="return confirm(@json(__('Restore backup now? Current database and uploads will be overwritten.')));">
                    @csrf
                    @if($backups !== [])
                        <div>
                            <label for="backup_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Existing backup') }}</label>
                            <select name="backup_name" id="backup_name" class="w-full rounded-lg border-gray-300 dark:border-dark-600 dark:bg-dark-900 text-sm">
                                <option value="">{{ __('— or upload a ZIP below —') }}</option>
                                @foreach($backups as $b)
                                    <option value="{{ $b['name'] }}">{{ $b['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div>
                        <label for="backup_upload" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Upload backup ZIP') }}</label>
                        <input type="file" name="backup_upload" id="backup_upload" accept=".zip,application/zip" class="block w-full text-sm text-gray-600 dark:text-gray-300 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-primary-50 file:text-primary-700 dark:file:bg-primary-900/30 dark:file:text-primary-300">
                    </div>
                    <label class="flex items-start gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                        <input type="checkbox" name="confirm_restore" value="1" class="mt-1 rounded border-gray-300 dark:border-dark-600 text-primary-600 focus:ring-primary-500" required>
                        <span>{{ __('I understand this overwrites the current database and public uploads.') }}</span>
                    </label>
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium">
                        <i class="fas fa-undo"></i>
                        {{ __('Restore backup') }}
                    </button>
                </form>
            </div>
        </div>

        <div class="bg-white dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-database text-emerald-500"></i>
                {{ __('Database migration') }}
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                {{ __('Runs php artisan migrate --force to apply pending migrations.') }}
            </p>
            <form method="POST" action="{{ route('admin.operations.migrate') }}" class="mt-5 space-y-4" onsubmit="return confirm(@json(__('Run database migrations now?')));">
                @csrf
                <label class="flex items-start gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                    <input type="checkbox" name="confirm_migrate" value="1" class="mt-1 rounded border-gray-300 dark:border-dark-600 text-primary-600 focus:ring-primary-500" required>
                    <span>{{ __('I understand this changes the database schema.') }}</span>
                </label>
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium">
                    <i class="fas fa-play"></i>
                    {{ __('Run migration now') }}
                </button>
            </form>
            @if(optional(auth()->user())->isOwner())
                <a href="{{ route('admin.operations.cli') }}" class="mt-3 inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-slate-700 hover:bg-slate-800 text-white text-sm font-medium">
                    <i class="fas fa-terminal"></i>
                    {{ __('Open CLI Console') }}
                </a>
            @endif
        </div>

        <div class="bg-white dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-broom text-cyan-500"></i>
                {{ __('Clear cache') }}
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                {{ __('Runs php artisan optimize:clear to clear config, route, view and application cache.') }}
            </p>
            <form method="POST" action="{{ route('admin.operations.cache-clear') }}" class="mt-5 space-y-4" onsubmit="return confirm(@json(__('Clear cache now?')));">
                @csrf
                <label class="flex items-start gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                    <input type="checkbox" name="confirm_cache_clear" value="1" class="mt-1 rounded border-gray-300 dark:border-dark-600 text-primary-600 focus:ring-primary-500" required>
                    <span>{{ __('I understand this clears runtime caches and may affect the next request time briefly.') }}</span>
                </label>
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-cyan-600 hover:bg-cyan-700 text-white text-sm font-medium">
                    <i class="fas fa-trash-alt"></i>
                    {{ __('Clear cache now') }}
                </button>
            </form>
        </div>

        <div class="bg-white dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-clock text-violet-500"></i>
                {{ __('Scheduled jobs') }}
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                {{ __('Configure recurring maintenance tasks for the scheduler.') }}
            </p>
            <a href="{{ route('admin.operations.schedule') }}" class="mt-5 inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-violet-600 hover:bg-violet-700 text-white text-sm font-medium">
                <i class="fas fa-calendar-check"></i>
                {{ __('Scheduled jobs') }}
            </a>
            <a href="{{ route('admin.operations.report.pdf') }}" class="mt-3 inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-sm font-medium">
                <i class="fas fa-file-pdf"></i>
                {{ __('Export system report (PDF)') }}
            </a>
        </div>

        <div class="bg-white dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                <i class="fab fa-npm text-red-500"></i>
                {{ __('Frontend build') }}
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                {{ __('Runs npm install and npm run build to rebuild frontend assets for deployment.') }}
            </p>
            @if(\Illuminate\Support\Facades\Route::has('admin.operations.frontend-build'))
                <form method="POST" action="{{ route('admin.operations.frontend-build') }}" class="mt-5 space-y-4" onsubmit="return confirm(@json(__('Start frontend build now? This can take several minutes.')));">
                    @csrf
                    <label class="flex items-start gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                        <input type="checkbox" name="confirm_frontend_build" value="1" class="mt-1 rounded border-gray-300 dark:border-dark-600 text-primary-600 focus:ring-primary-500" required>
                        <span>{{ __('I understand this runs npm install and npm run build on the server.') }}</span>
                    </label>
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white text-sm font-medium">
                        <i class="fas fa-hammer"></i>
                        {{ __('Run frontend build') }}
                    </button>
                </form>
            @else
                <div class="mt-5 p-3 rounded-lg border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20 text-sm text-amber-900 dark:text-amber-100">
                    {{ __('Frontend build is unavailable: the route is missing or route cache is outdated. Deploy the latest routes/web.php, then run php artisan route:clear (or php artisan route:cache after deployment).') }}
                </div>
            @endif
        </div>

        <div class="bg-white dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 p-6 lg:col-span-2">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-boxes-stacked text-indigo-500"></i>
                {{ __('Dependency updates') }}
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                {{ __('Runs composer update, composer audit, npm install, npm audit fix and rebuilds frontend assets.') }}
            </p>
            <p class="text-xs text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-md px-3 py-2 mt-3">
                {{ __('This may take several minutes and can temporarily increase server load. Use only as admin during low-traffic periods.') }}
            </p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                {{ __('Dependency updates are limited to a few runs per hour per admin account. Wait before retrying if you see a rate limit error.') }}
            </p>
            <form method="POST" action="{{ route('admin.operations.dependencies.update') }}" class="mt-5 space-y-4" id="dependency-update-form" onsubmit="return confirm(@json(__('Start dependency update now? This can take several minutes.')));">
                @csrf
                <label class="flex items-start gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                    <input type="checkbox" name="confirm_dependency_update" value="1" class="mt-1 rounded border-gray-300 dark:border-dark-600 text-primary-600 focus:ring-primary-500" required>
                    <span>{{ __('I understand this updates dependency versions and rebuilds assets.') }}</span>
                </label>
                <button type="submit" id="dependency-update-btn" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium disabled:opacity-60 disabled:cursor-not-allowed">
                    <i class="fas fa-download"></i>
                    {{ __('Run dependency update') }}
                </button>
            </form>
            <script>
                document.getElementById('dependency-update-form')?.addEventListener('submit', function () {
                    var btn = document.getElementById('dependency-update-btn');
                    if (btn) {
                        btn.disabled = true;
                        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> {{ __('Running…') }}';
                    }
                });
            </script>
        </div>
    </div>

    <div class="mt-8 bg-white dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-dark-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Recent backups') }}</h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('Files in storage/app/backups') }}</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 dark:bg-dark-700/50 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3">{{ __('File') }}</th>
                        <th class="px-4 py-3">{{ __('Size') }}</th>
                        <th class="px-4 py-3">{{ __('Date') }}</th>
                        <th class="px-4 py-3 text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-dark-700">
                    @forelse($backups as $b)
                        <tr class="hover:bg-gray-50 dark:hover:bg-dark-700/30">
                            <td class="px-4 py-3 font-mono text-xs text-gray-900 dark:text-white">{{ $b['name'] }}</td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-300">
                                @php
                                    $s = $b['size'];
                                    if ($s >= 1048576) {
                                        echo number_format($s / 1048576, 2) . ' MB';
                                    } elseif ($s >= 1024) {
                                        echo number_format($s / 1024, 1) . ' KB';
                                    } else {
                                        echo $s . ' B';
                                    }
                                @endphp
                            </td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-300 whitespace-nowrap">
                                {{ $b['mtime'] ? \Carbon\Carbon::createFromTimestamp($b['mtime'])->timezone(config('app.timezone'))->format('d.m.Y H:i') : '—' }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <form method="POST" action="{{ route('admin.operations.backup.restore') }}" class="inline" onsubmit="return confirm(@json(__('Restore :file now? Current database and uploads will be overwritten.', ['file' => $b['name']])));">
                                    @csrf
                                    <input type="hidden" name="backup_name" value="{{ $b['name'] }}">
                                    <input type="hidden" name="confirm_restore" value="1">
                                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-200 text-xs font-medium hover:bg-amber-200 dark:hover:bg-amber-900/50">
                                        <i class="fas fa-undo"></i>
                                        {{ __('Restore') }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-10 text-center text-gray-500 dark:text-gray-400">{{ __('No backups yet. Run a backup above or use the scheduler.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
