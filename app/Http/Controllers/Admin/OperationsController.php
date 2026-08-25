<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\ActivityLogger;
use App\Support\PhpCliBinary;
use Dompdf\Dompdf;
use Illuminate\Contracts\Foundation\MaintenanceMode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Symfony\Component\Process\Process;

class OperationsController extends Controller
{
    /**
     * @return array<string, array{command:string,default_frequency:string,default_time:string,default_day:string,label:string,description:string}>
     */
    private function scheduledJobDefinitions(): array
    {
        return [
            'backup' => [
                'command' => 'backup:application',
                'default_frequency' => 'daily',
                'default_time' => '02:00',
                'default_day' => 'monday',
                'label' => __('Application backup'),
                'description' => __('Creates a full application backup ZIP.'),
            ],
            'cache_clear' => [
                'command' => 'optimize:clear',
                'default_frequency' => 'daily',
                'default_time' => '03:00',
                'default_day' => 'monday',
                'label' => __('Clear cache'),
                'description' => __('Clears config, route, view and app cache.'),
            ],
            'queue_restart' => [
                'command' => 'queue:restart',
                'default_frequency' => 'hourly',
                'default_time' => '00:00',
                'default_day' => 'monday',
                'label' => __('Restart queue workers'),
                'description' => __('Signals queue workers to restart gracefully.'),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function schedulerFrequencies(): array
    {
        return [
            'hourly' => __('Hourly'),
            'daily' => __('Daily'),
            'weekly' => __('Weekly'),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function schedulerDays(): array
    {
        return [
            'monday' => __('Monday'),
            'tuesday' => __('Tuesday'),
            'wednesday' => __('Wednesday'),
            'thursday' => __('Thursday'),
            'friday' => __('Friday'),
            'saturday' => __('Saturday'),
            'sunday' => __('Sunday'),
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function loadScheduledJobsConfig(): array
    {
        $definitions = $this->scheduledJobDefinitions();
        $raw = Setting::getValue('scheduled_jobs', null);
        $decoded = [];
        if (is_string($raw) && $raw !== '') {
            $parsed = json_decode($raw, true);
            if (is_array($parsed)) {
                $decoded = $parsed;
            }
        }

        $result = [];
        foreach ($definitions as $key => $definition) {
            $config = is_array($decoded[$key] ?? null) ? $decoded[$key] : [];
            $result[$key] = [
                'enabled' => (bool) ($config['enabled'] ?? false),
                'frequency' => (string) ($config['frequency'] ?? $definition['default_frequency']),
                'time' => (string) ($config['time'] ?? $definition['default_time']),
                'day' => (string) ($config['day'] ?? $definition['default_day']),
            ];
        }

        return $result;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function loadScheduledJobsStatus(): array
    {
        $definitions = $this->scheduledJobDefinitions();
        $raw = Setting::getValue('scheduled_jobs_status', null);
        $decoded = [];
        if (is_string($raw) && $raw !== '') {
            $parsed = json_decode($raw, true);
            if (is_array($parsed)) {
                $decoded = $parsed;
            }
        }

        $result = [];
        foreach (array_keys($definitions) as $jobKey) {
            $status = is_array($decoded[$jobKey] ?? null) ? $decoded[$jobKey] : [];
            $result[$jobKey] = [
                'last_run_at' => $status['last_run_at'] ?? null,
                'last_exit_code' => $status['last_exit_code'] ?? null,
                'last_duration_ms' => $status['last_duration_ms'] ?? null,
                'last_status' => $status['last_status'] ?? null,
            ];
        }

        return $result;
    }

    public function index()
    {
        $maintenanceActive = app()->isDownForMaintenance();
        $maintenanceSecret = null;
        if ($maintenanceActive) {
            try {
                $data = app(MaintenanceMode::class)->data();
                $maintenanceSecret = $data['secret'] ?? null;
            } catch (\Throwable) {
                $maintenanceSecret = null;
            }
        }

        $backupDir = storage_path('app/backups');
        $paths = glob($backupDir.DIRECTORY_SEPARATOR.'backup_*.zip') ?: [];
        rsort($paths, SORT_STRING);
        $backups = [];
        foreach (array_slice($paths, 0, 30) as $path) {
            $backups[] = [
                'name' => basename($path),
                'size' => @filesize($path) ?: 0,
                'mtime' => @filemtime($path) ?: 0,
            ];
        }

        return view('admin.operations.index', compact(
            'maintenanceActive',
            'maintenanceSecret',
            'backups'
        ));
    }

    public function runBackup(Request $request)
    {
        @set_time_limit(0);

        $exitCode = Artisan::call('backup:application');
        $output = trim(Artisan::output());

        if ($exitCode !== 0) {
            ActivityLogger::log('operations.backup.failed', Str::limit($output, 400));

            return back()
                ->with('error', __('Backup failed.') . ($output !== '' ? ' '.$output : ''));
        }

        ActivityLogger::log('operations.backup.created', __('Application backup created (admin).'));

        return back()
            ->with('success', __('Backup created successfully.'))
            ->with('backup_cli_output', $output !== '' ? $output : null);
    }

    public function runMigrate(Request $request)
    {
        $request->validate([
            'confirm_migrate' => ['required', 'accepted'],
        ]);

        @set_time_limit(0);

        try {
            $exitCode = Artisan::call('migrate', ['--force' => true]);
            $output = trim(Artisan::output());
        } catch (\Throwable $e) {
            ActivityLogger::log('operations.migrate.failed', Str::limit($e->getMessage(), 400));

            return back()
                ->with('error', __('Migration failed: :msg', ['msg' => Str::limit($e->getMessage(), 180)]))
                ->with('migrate_cli_output', $e->getMessage());
        }

        if ($exitCode !== 0) {
            ActivityLogger::log('operations.migrate.failed', Str::limit($output, 400));

            return back()
                ->with('error', __('Migration failed.'))
                ->with('migrate_cli_output', $output !== '' ? $output : null);
        }

        ActivityLogger::log('operations.migrate.completed', __('Database migration completed (admin).'));

        return back()
            ->with('success', __('Migration completed successfully.'))
            ->with('migrate_cli_output', $output !== '' ? $output : __('No changes detected.'));
    }

    public function runCacheClear(Request $request)
    {
        $request->validate([
            'confirm_cache_clear' => ['required', 'accepted'],
        ]);

        @set_time_limit(0);

        try {
            $exitCode = Artisan::call('optimize:clear');
            $output = trim(Artisan::output());
        } catch (\Throwable $e) {
            ActivityLogger::log('operations.cache_clear.failed', Str::limit($e->getMessage(), 400));

            return back()
                ->with('error', __('Cache clear failed: :msg', ['msg' => Str::limit($e->getMessage(), 180)]))
                ->with('cache_clear_output', $e->getMessage());
        }

        if ($exitCode !== 0) {
            ActivityLogger::log('operations.cache_clear.failed', Str::limit($output, 400));

            return back()
                ->with('error', __('Cache clear failed.'))
                ->with('cache_clear_output', $output !== '' ? $output : null);
        }

        ActivityLogger::log('operations.cache_clear.completed', __('Cache cleared successfully (admin).'));

        return back()
            ->with('success', __('Cache cleared successfully.'))
            ->with('cache_clear_output', $output !== '' ? $output : __('No output.'));
    }

    public function runFrontendBuild(Request $request)
    {
        $request->validate([
            'confirm_frontend_build' => ['required', 'accepted'],
        ]);

        @set_time_limit(0);

        $steps = [
            'npm_install' => [$this->npmBinary(), 'install'],
            'npm_build' => [$this->npmBinary(), 'run', 'build'],
        ];

        $logs = [];
        foreach ($steps as $stepName => $command) {
            try {
                $process = new Process($command, base_path(), $this->processEnv(), null, 1800);
                $process->run();
            } catch (\Throwable $e) {
                ActivityLogger::log('operations.frontend_build.failed', Str::limit($e->getMessage(), 400));

                return back()
                    ->with('error', __('Frontend build failed: :msg', ['msg' => Str::limit($e->getMessage(), 180)]))
                    ->with('frontend_build_output', $e->getMessage());
            }

            $output = trim($process->getOutput()."\n".$process->getErrorOutput());
            $logs[] = '>>> '.$stepName.PHP_EOL.($output !== '' ? $output : __('No output.'));

            if (! $process->isSuccessful()) {
                ActivityLogger::log('operations.frontend_build.failed', Str::limit($output, 450));

                return back()
                    ->with('error', __('Frontend build failed in step: :step', ['step' => $stepName]))
                    ->with('frontend_build_output', implode(PHP_EOL.PHP_EOL, $logs));
            }
        }

        try {
            Artisan::call('optimize:clear');
            $artisanOutput = trim(Artisan::output());
            $logs[] = '>>> optimize_clear'.PHP_EOL.($artisanOutput !== '' ? $artisanOutput : 'OK');
        } catch (\Throwable $e) {
            $logs[] = '>>> optimize_clear'.PHP_EOL.'WARN: '.$e->getMessage();
            ActivityLogger::log('operations.frontend_build.optimize_clear_warn', Str::limit($e->getMessage(), 300));
        }

        ActivityLogger::log('operations.frontend_build.completed', __('Frontend build completed successfully (admin).'));

        return back()
            ->with('success', __('Frontend build completed successfully.'))
            ->with('frontend_build_output', implode(PHP_EOL.PHP_EOL, $logs));
    }

    public function cliConsole()
    {
        if (! auth()->user()?->isOwner()) {
            return redirect()->route('admin.operations.index')
                ->with('error', __('Only owner can access the CLI console.'));
        }

        $artisanCommands = collect(Artisan::all())
            ->keys()
            ->sort()
            ->values()
            ->all();

        $commonCliCommands = [
            'php artisan optimize:clear',
            'php artisan migrate --force',
            'php artisan route:list',
            'php artisan queue:restart',
            'php artisan schedule:list',
            'composer install',
            'composer update',
            'composer audit',
            'npm install',
            'npm run build',
            'npm audit fix',
        ];

        return view('admin.operations.cli-console', compact('artisanCommands', 'commonCliCommands'));
    }

    public function schedule()
    {
        $jobs = $this->scheduledJobDefinitions();
        $frequencies = $this->schedulerFrequencies();
        $days = $this->schedulerDays();
        $configs = $this->loadScheduledJobsConfig();
        $statuses = $this->loadScheduledJobsStatus();

        return view('admin.operations.schedule', compact('jobs', 'frequencies', 'days', 'configs', 'statuses'));
    }

    public function updateSchedule(Request $request)
    {
        $jobs = $this->scheduledJobDefinitions();
        $frequencies = array_keys($this->schedulerFrequencies());
        $days = array_keys($this->schedulerDays());

        $rules = [];
        foreach (array_keys($jobs) as $jobKey) {
            $rules["jobs.$jobKey.enabled"] = ['nullable', 'boolean'];
            $rules["jobs.$jobKey.frequency"] = ['required', Rule::in($frequencies)];
            $rules["jobs.$jobKey.time"] = ['required', 'regex:/^\d{2}:\d{2}$/'];
            $rules["jobs.$jobKey.day"] = ['required', Rule::in($days)];
        }

        $validated = $request->validate($rules);
        $inputJobs = is_array($validated['jobs'] ?? null) ? $validated['jobs'] : [];

        $store = [];
        foreach ($jobs as $jobKey => $definition) {
            $row = is_array($inputJobs[$jobKey] ?? null) ? $inputJobs[$jobKey] : [];
            $store[$jobKey] = [
                'enabled' => (bool) ($row['enabled'] ?? false),
                'frequency' => (string) ($row['frequency'] ?? $definition['default_frequency']),
                'time' => (string) ($row['time'] ?? $definition['default_time']),
                'day' => (string) ($row['day'] ?? $definition['default_day']),
            ];
        }

        Setting::setValue('scheduled_jobs', json_encode($store, JSON_UNESCAPED_UNICODE));
        ActivityLogger::log('operations.schedule.updated', __('Scheduled jobs updated (admin).'));

        return back()->with('success', __('Scheduled jobs saved.'));
    }

    public function runScheduledJob(string $job)
    {
        $jobs = $this->scheduledJobDefinitions();
        if (! array_key_exists($job, $jobs)) {
            return back()->with('error', __('Unknown scheduled job.'));
        }

        @set_time_limit(0);
        $command = (string) $jobs[$job]['command'];
        $start = microtime(true);
        $runAt = now()->toDateTimeString();

        try {
            $exitCode = Artisan::call($command);
            $output = trim(Artisan::output());
        } catch (\Throwable $e) {
            $durationMs = (int) round((microtime(true) - $start) * 1000);
            $statuses = $this->loadScheduledJobsStatus();
            $statuses[$job] = [
                'last_run_at' => $runAt,
                'last_exit_code' => 1,
                'last_duration_ms' => $durationMs,
                'last_status' => 'failed',
            ];
            Setting::setValue('scheduled_jobs_status', json_encode($statuses, JSON_UNESCAPED_UNICODE));

            ActivityLogger::log('operations.schedule.run.failed', Str::limit($command.' | '.$e->getMessage(), 450));

            return back()
                ->with('error', __('Job run failed: :msg', ['msg' => Str::limit($e->getMessage(), 180)]))
                ->with('schedule_job_output', $e->getMessage());
        }

        $durationMs = (int) round((microtime(true) - $start) * 1000);
        $statuses = $this->loadScheduledJobsStatus();
        $statuses[$job] = [
            'last_run_at' => $runAt,
            'last_exit_code' => $exitCode,
            'last_duration_ms' => $durationMs,
            'last_status' => $exitCode === 0 ? 'success' : 'failed',
        ];
        Setting::setValue('scheduled_jobs_status', json_encode($statuses, JSON_UNESCAPED_UNICODE));

        if ($exitCode !== 0) {
            ActivityLogger::log('operations.schedule.run.failed', Str::limit($command.' | '.$output, 450));

            return back()
                ->with('error', __('Scheduled job failed.'))
                ->with('schedule_job_output', $output !== '' ? $output : null);
        }

        ActivityLogger::log('operations.schedule.run.success', Str::limit($command, 200));

        return back()
            ->with('success', __('Scheduled job executed successfully.'))
            ->with('schedule_job_output', $output !== '' ? $output : __('No output.'));
    }

    public function exportSystemReportPdf()
    {
        if (! class_exists(\Dompdf\Dompdf::class)) {
            return back()->with('error', __('PDF export is currently unavailable. Please install dompdf via Composer.'));
        }

        $jobs = $this->scheduledJobDefinitions();
        $configs = $this->loadScheduledJobsConfig();
        $statuses = $this->loadScheduledJobsStatus();
        $generatedAt = now();

        $checks = [];
        try {
            DB::selectOne('select 1 as ok');
            $checks[] = ['name' => __('Database connection'), 'status' => 'ok', 'message' => __('Connection successful.')];
        } catch (\Throwable $e) {
            $checks[] = ['name' => __('Database connection'), 'status' => 'fail', 'message' => __('Connection failed: :msg', ['msg' => Str::limit($e->getMessage(), 140)])];
        }

        try {
            $cacheDriver = (string) (config('cache.default') ?: 'unknown');
            $key = 'report:'.Str::random(10);
            Cache::put($key, 'ok', now()->addMinutes(1));
            $ok = Cache::get($key) === 'ok';
            Cache::forget($key);
            $checks[] = [
                'name' => __('Cache store'),
                'status' => $ok ? 'ok' : 'fail',
                'message' => $ok
                    ? __('Read/write successful. Driver: :driver', ['driver' => $cacheDriver])
                    : __('Read/write failed. Driver: :driver', ['driver' => $cacheDriver]),
            ];
        } catch (\Throwable $e) {
            $checks[] = ['name' => __('Cache store'), 'status' => 'fail', 'message' => __('Read/write failed: :msg', ['msg' => Str::limit($e->getMessage(), 140)])];
        }

        $storagePath = public_path('storage');
        $hasStorage = is_link($storagePath) || is_dir($storagePath);
        $checks[] = [
            'name' => __('Public storage path'),
            'status' => $hasStorage ? 'ok' : 'warn',
            'message' => $hasStorage ? __('public/storage is available.') : __('public/storage is missing. Fallback route is active, but a symlink is recommended.'),
        ];

        $isDebug = (bool) config('app.debug');
        $checks[] = [
            'name' => __('App debug mode'),
            'status' => $isDebug ? 'warn' : 'ok',
            'message' => $isDebug ? __('APP_DEBUG is enabled. Disable it in production.') : __('APP_DEBUG is disabled.'),
        ];

        $queueConnection = (string) config('queue.default');
        $checks[] = [
            'name' => __('Queue connection'),
            'status' => $queueConnection === 'sync' ? 'warn' : 'ok',
            'message' => $queueConnection === 'sync'
                ? __('Queue is set to sync. For production, a worker queue is recommended.')
                : __('Queue driver: :driver', ['driver' => $queueConnection]),
        ];

        $summary = [
            'ok' => collect($checks)->where('status', 'ok')->count(),
            'warn' => collect($checks)->where('status', 'warn')->count(),
            'fail' => collect($checks)->where('status', 'fail')->count(),
        ];

        $serverInfo = [
            'app_env' => config('app.env'),
            'app_debug' => config('app.debug') ? 'true' : 'false',
            'app_url' => config('app.url'),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? php_sapi_name(),
            'database_connection' => config('database.default'),
            'timezone' => config('app.timezone'),
            'locale' => app()->getLocale(),
        ];

        $html = view('admin.operations.report-pdf', compact(
            'generatedAt',
            'checks',
            'summary',
            'serverInfo',
            'jobs',
            'configs',
            'statuses'
        ))->render();
        $dompdf = new Dompdf;
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4');
        $dompdf->render();

        ActivityLogger::log('operations.report.pdf', __('System report exported as PDF (admin).'));

        $fileName = 'system-report-'.$generatedAt->format('Ymd-His').'.pdf';

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ]);
    }

    public function runCliCommand(Request $request)
    {
        if (! auth()->user()?->isOwner()) {
            return redirect()->route('admin.operations.index')
                ->with('error', __('Only owner can access the CLI console.'));
        }

        $request->validate([
            'command' => ['required', 'string', 'max:300'],
        ]);

        @set_time_limit(0);

        $command = trim((string) $request->input('command'));
        if (! $this->isCliCommandAllowed($command)) {
            return back()->with('error', __('Command is not allowed for security reasons.'))
                ->with('cli_command', $command);
        }

        try {
            $process = Process::fromShellCommandline($command, base_path(), $this->processEnv(), null, 300);
            $process->run();
        } catch (\Throwable $e) {
            ActivityLogger::log('operations.cli.failed', Str::limit($e->getMessage(), 400));

            return back()
                ->with('error', __('CLI command failed: :msg', ['msg' => Str::limit($e->getMessage(), 180)]))
                ->with('cli_command', $command)
                ->with('cli_output', $e->getMessage());
        }

        $output = trim($process->getOutput()."\n".$process->getErrorOutput());
        $finalOutput = $output !== '' ? $output : __('No output.');

        if (! $process->isSuccessful()) {
            ActivityLogger::log('operations.cli.failed', Str::limit($command.' | '.$finalOutput, 450));

            return back()
                ->with('error', __('CLI command failed.'))
                ->with('cli_command', $command)
                ->with('cli_output', $finalOutput);
        }

        ActivityLogger::log('operations.cli.executed', Str::limit($command, 180));

        return back()
            ->with('success', __('CLI command executed successfully.'))
            ->with('cli_command', $command)
            ->with('cli_output', $finalOutput);
    }

    public function maintenanceEnable(Request $request)
    {
        if (app()->isDownForMaintenance()) {
            return back()->with('info', __('The site is already in maintenance mode.'));
        }

        $options = [];

        if ($request->boolean('bypass_secret')) {
            $options['--with-secret'] = true;
        }

        // Kein --render: Wartungsseite kommt dynamisch aus errors.maintenance (Middleware),
        // damit z. B. Admin-Link immer aktuell ist und kein veraltetes HTML in storage/framework/down liegt.
        $exitCode = Artisan::call('down', $options);

        if ($exitCode !== 0) {
            $out = trim(Artisan::output());

            return back()->with('error', __('Could not enable maintenance mode.') . ($out !== '' ? ' '.$out : ''));
        }

        ActivityLogger::log('operations.maintenance.enabled', __('Maintenance mode enabled (admin).'));

        $bypassUrl = null;
        if ($request->boolean('bypass_secret') && app()->isDownForMaintenance()) {
            try {
                $secret = app(MaintenanceMode::class)->data()['secret'] ?? null;
                if (is_string($secret) && $secret !== '') {
                    $bypassUrl = url($secret);
                }
            } catch (\Throwable) {
                // ignore
            }
        }

        return back()
            ->with('success', __('Maintenance mode has been enabled.'))
            ->with('maintenance_bypass_url', $bypassUrl);
    }

    public function maintenanceDisable()
    {
        if (! app()->isDownForMaintenance()) {
            return back()->with('info', __('Maintenance mode is not active.'));
        }

        $exitCode = Artisan::call('up');

        if ($exitCode !== 0) {
            $out = trim(Artisan::output());

            return back()->with('error', __('Could not disable maintenance mode.') . ($out !== '' ? ' '.$out : ''));
        }

        ActivityLogger::log('operations.maintenance.disabled', __('Maintenance mode disabled (admin).'));

        return back()->with('success', __('Maintenance mode has been disabled. The site is live again.'));
    }

    public function updateDependencies(Request $request)
    {
        $request->validate([
            'confirm_dependency_update' => ['required', 'accepted'],
        ]);

        @set_time_limit(0);

        $phpCli = PhpCliBinary::resolve();

        $steps = [
            'composer_update' => is_file(base_path('composer.phar'))
                ? [$phpCli, base_path('composer.phar'), 'update', '--with-all-dependencies', '--no-interaction']
                : ['composer', 'update', '--with-all-dependencies', '--no-interaction'],
            'composer_audit' => is_file(base_path('composer.phar'))
                ? [$phpCli, base_path('composer.phar'), 'audit', '--no-interaction']
                : ['composer', 'audit', '--no-interaction'],
            'npm_install' => [$this->npmBinary(), 'install'],
            'npm_audit_fix' => [$this->npmBinary(), 'audit', 'fix'],
            'npm_build' => [$this->npmBinary(), 'run', 'build'],
        ];

        $logs = [];
        foreach ($steps as $stepName => $command) {
            $process = new Process($command, base_path(), $this->processEnv(), null, 1800);
            $process->run();

            $output = trim($process->getOutput()."\n".$process->getErrorOutput());
            $logs[] = '>>> '.$stepName.PHP_EOL.$output;

            if (! $process->isSuccessful()) {
                ActivityLogger::log('operations.dependencies.failed', Str::limit($output, 450));

                return back()
                    ->with('error', __('Dependency update failed in step: :step', ['step' => $stepName]))
                    ->with('dependency_update_output', implode(PHP_EOL.PHP_EOL, $logs));
            }
        }

        // Best effort: in some hosting environments PHP_BINARY points to php-fpm and
        // cannot execute CLI commands directly. This should not fail the whole update.
        try {
            Artisan::call('optimize:clear');
            $artisanOutput = trim(Artisan::output());
            $logs[] = '>>> optimize_clear'.PHP_EOL.($artisanOutput !== '' ? $artisanOutput : 'OK');
        } catch (\Throwable $e) {
            $logs[] = '>>> optimize_clear'.PHP_EOL.'WARN: '.$e->getMessage();
            ActivityLogger::log('operations.dependencies.optimize_clear_warn', Str::limit($e->getMessage(), 300));
        }

        ActivityLogger::log('operations.dependencies.updated', __('Dependencies updated successfully (admin).'));

        return back()
            ->with('success', __('Dependencies updated successfully.'))
            ->with('dependency_update_output', implode(PHP_EOL.PHP_EOL, $logs));
    }

    public function serverInfo()
    {
        $databaseVersion = null;
        try {
            $row = DB::selectOne('select version() as version');
            $databaseVersion = $row->version ?? null;
        } catch (\Throwable) {
            $databaseVersion = null;
        }

        $info = [
            'app_env' => config('app.env'),
            'app_debug' => config('app.debug') ? 'true' : 'false',
            'app_url' => config('app.url'),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? php_sapi_name(),
            'database_connection' => config('database.default'),
            'database_version' => $databaseVersion,
            'timezone' => config('app.timezone'),
            'locale' => app()->getLocale(),
            'memory_limit' => ini_get('memory_limit') ?: 'n/a',
            'upload_max_filesize' => ini_get('upload_max_filesize') ?: 'n/a',
            'post_max_size' => ini_get('post_max_size') ?: 'n/a',
            'max_execution_time' => (string) (ini_get('max_execution_time') ?: 'n/a'),
        ];

        $extensions = [
            'curl' => extension_loaded('curl'),
            'mbstring' => extension_loaded('mbstring'),
            'openssl' => extension_loaded('openssl'),
            'pdo' => extension_loaded('pdo'),
            'pdo_mysql' => extension_loaded('pdo_mysql'),
            'json' => extension_loaded('json'),
            'fileinfo' => extension_loaded('fileinfo'),
            'gd' => extension_loaded('gd'),
            'zip' => extension_loaded('zip'),
        ];

        return view('admin.operations.server-info', compact('info', 'extensions'));
    }

    public function healthCheck()
    {
        $checks = [];

        // Database connection
        try {
            DB::selectOne('select 1 as ok');
            $checks[] = [
                'name' => __('Database connection'),
                'status' => 'ok',
                'message' => __('Connection successful.'),
            ];
        } catch (\Throwable $e) {
            $checks[] = [
                'name' => __('Database connection'),
                'status' => 'fail',
                'message' => __('Connection failed: :msg', ['msg' => Str::limit($e->getMessage(), 140)]),
            ];
        }

        // Cache write/read
        try {
            $cacheDriver = (string) (
                config('cache.default')
                ?: env('CACHE_STORE')
                ?: env('CACHE_DRIVER')
                ?: 'unknown'
            );

            if (strtolower($cacheDriver) === 'file') {
                File::ensureDirectoryExists(storage_path('framework/cache/data'));
            }

            $key = 'healthcheck:'.Str::random(12);
            Cache::put($key, 'ok', now()->addMinutes(1));
            $readBack = Cache::get($key) === 'ok';
            Cache::forget($key);

            $status = $readBack ? 'ok' : 'fail';
            $message = $readBack
                ? __('Read/write successful. Driver: :driver', ['driver' => $cacheDriver])
                : __('Read/write failed. Driver: :driver', ['driver' => $cacheDriver]);

            if (! $readBack && in_array(strtolower($cacheDriver), ['null', 'noop'], true)) {
                $status = 'warn';
                $message = __('Cache driver ":driver" does not persist values (expected behavior).', ['driver' => $cacheDriver]);
            }

            $checks[] = [
                'name' => __('Cache store'),
                'status' => $status,
                'message' => $message,
            ];
        } catch (\Throwable $e) {
            $cacheDriver = (string) (
                config('cache.default')
                ?: env('CACHE_STORE')
                ?: env('CACHE_DRIVER')
                ?: 'unknown'
            );
            $cachePath = storage_path('framework/cache/data');
            $pathHint = '';
            if (strtolower($cacheDriver) === 'file') {
                $pathHint = ' | '.__('Path: :path | exists: :exists | writable: :writable', [
                    'path' => $cachePath,
                    'exists' => is_dir($cachePath) ? 'yes' : 'no',
                    'writable' => is_writable($cachePath) ? 'yes' : 'no',
                ]);
            }

            $checks[] = [
                'name' => __('Cache store'),
                'status' => 'fail',
                'message' => __('Read/write failed: :msg', ['msg' => Str::limit($e->getMessage(), 140)]).$pathHint,
            ];
        }

        // Storage write/read (public disk)
        try {
            $path = 'health/check-'.Str::random(12).'.txt';
            Storage::disk('public')->put($path, 'ok');
            $exists = Storage::disk('public')->exists($path);
            Storage::disk('public')->delete($path);
            $checks[] = [
                'name' => __('Public storage disk'),
                'status' => $exists ? 'ok' : 'fail',
                'message' => $exists ? __('Read/write successful.') : __('Read/write failed.'),
            ];
        } catch (\Throwable $e) {
            $checks[] = [
                'name' => __('Public storage disk'),
                'status' => 'fail',
                'message' => __('Read/write failed: :msg', ['msg' => Str::limit($e->getMessage(), 140)]),
            ];
        }

        // Public storage link/route fallback indicator
        $publicStorageLink = public_path('storage');
        $hasStorageAccess = is_link($publicStorageLink) || is_dir($publicStorageLink);
        $checks[] = [
            'name' => __('Public storage path'),
            'status' => $hasStorageAccess ? 'ok' : 'warn',
            'message' => $hasStorageAccess
                ? __('public/storage is available.')
                : __('public/storage is missing. Fallback route is active, but a symlink is recommended.'),
        ];

        // App debug
        $isDebug = (bool) config('app.debug');
        $checks[] = [
            'name' => __('App debug mode'),
            'status' => $isDebug ? 'warn' : 'ok',
            'message' => $isDebug
                ? __('APP_DEBUG is enabled. Disable it in production.')
                : __('APP_DEBUG is disabled.'),
        ];

        // Queue mode hint
        $queueConnection = (string) config('queue.default');
        $checks[] = [
            'name' => __('Queue connection'),
            'status' => $queueConnection === 'sync' ? 'warn' : 'ok',
            'message' => $queueConnection === 'sync'
                ? __('Queue is set to sync. For production, a worker queue is recommended.')
                : __('Queue driver: :driver', ['driver' => $queueConnection]),
        ];

        $summary = [
            'ok' => collect($checks)->where('status', 'ok')->count(),
            'warn' => collect($checks)->where('status', 'warn')->count(),
            'fail' => collect($checks)->where('status', 'fail')->count(),
        ];

        return view('admin.operations.health-check', compact('checks', 'summary'));
    }

    private function npmBinary(): string
    {
        return str_starts_with(strtoupper(PHP_OS_FAMILY), 'WINDOWS') ? 'npm.cmd' : 'npm';
    }

    private function isCliCommandAllowed(string $command): bool
    {
        if ($command === '') {
            return false;
        }

        // Disallow shell chaining/redirection for safer execution.
        if (preg_match('/[;&|><`]/', $command)) {
            return false;
        }

        $allowedPrefixes = [
            'php artisan ',
            'php ',
            'composer ',
            'npm ',
            'npm.cmd ',
        ];

        $normalized = strtolower($command);
        foreach ($allowedPrefixes as $prefix) {
            if (str_starts_with($normalized, strtolower($prefix))) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<string, string>
     */
    private function processEnv(): array
    {
        $env = $_ENV;

        $home = (string) (
            getenv('HOME')
            ?: getenv('USERPROFILE')
            ?: ($env['HOME'] ?? '')
            ?: ($env['USERPROFILE'] ?? '')
        );
        if ($home !== '') {
            $env['HOME'] = $home;
        }

        $composerHome = (string) (getenv('COMPOSER_HOME') ?: ($env['COMPOSER_HOME'] ?? ''));
        if ($composerHome === '') {
            $composerHome = storage_path('app/composer-home');
        }
        if (! is_dir($composerHome)) {
            @mkdir($composerHome, 0755, true);
        }
        $env['COMPOSER_HOME'] = $composerHome;

        // Prefer CLI php for nested composer/npm scripts (avoid php-fpm from PHP_BINARY).
        $phpCli = PhpCliBinary::resolve();
        $env['PHP_CLI_BINARY'] = $phpCli;
        $phpDir = dirname($phpCli);
        if ($phpDir !== '' && $phpDir !== '.' && is_dir($phpDir)) {
            $path = (string) ($env['PATH'] ?? getenv('PATH') ?: '');
            $env['PATH'] = $phpDir.PATH_SEPARATOR.$path;
        }

        return array_map(static fn ($value) => (string) $value, $env);
    }
}
