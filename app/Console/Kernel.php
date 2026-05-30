<?php

namespace App\Console;

use App\Models\Setting;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * @return array<string, array<string, mixed>>
     */
    private function loadScheduledJobsStatus(): array
    {
        $raw = Setting::getValue('scheduled_jobs_status', null);
        if (! is_string($raw) || $raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @param  array<string, mixed>  $status
     */
    private function writeScheduledJobStatus(string $jobKey, array $status): void
    {
        try {
            $all = $this->loadScheduledJobsStatus();
            $all[$jobKey] = array_merge($all[$jobKey] ?? [], $status);
            Setting::setValue('scheduled_jobs_status', json_encode($all, JSON_UNESCAPED_UNICODE));
        } catch (\Throwable) {
            // Ignore status persistence errors to avoid breaking scheduler execution.
        }
    }

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $definitions = [
            'backup' => 'backup:application',
            'cache_clear' => 'optimize:clear',
            'queue_restart' => 'queue:restart',
        ];

        $raw = Setting::getValue('scheduled_jobs', null);
        $configured = [];
        if (is_string($raw) && $raw !== '') {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $configured = $decoded;
            }
        }

        foreach ($definitions as $jobKey => $command) {
            $config = is_array($configured[$jobKey] ?? null) ? $configured[$jobKey] : [];
            $enabled = (bool) ($config['enabled'] ?? false);
            if (! $enabled) {
                continue;
            }

            $frequency = (string) ($config['frequency'] ?? 'daily');
            $time = (string) ($config['time'] ?? '02:00');
            $day = strtolower((string) ($config['day'] ?? 'monday'));

            $event = $schedule->command($command)->withoutOverlapping();
            $startedAt = null;
            $event->before(function () use ($jobKey, &$startedAt): void {
                $startedAt = microtime(true);
                $this->writeScheduledJobStatus($jobKey, [
                    'last_run_at' => now()->toDateTimeString(),
                    'last_status' => 'running',
                ]);
            });
            $event->onSuccess(function () use ($jobKey, &$startedAt): void {
                $duration = $startedAt !== null ? (int) round((microtime(true) - $startedAt) * 1000) : null;
                $this->writeScheduledJobStatus($jobKey, [
                    'last_exit_code' => 0,
                    'last_duration_ms' => $duration,
                    'last_status' => 'success',
                ]);
            });
            $event->onFailure(function () use ($jobKey, &$startedAt): void {
                $duration = $startedAt !== null ? (int) round((microtime(true) - $startedAt) * 1000) : null;
                $this->writeScheduledJobStatus($jobKey, [
                    'last_exit_code' => 1,
                    'last_duration_ms' => $duration,
                    'last_status' => 'failed',
                ]);
            });

            if ($frequency === 'hourly') {
                $event->hourly();
                continue;
            }

            if (! preg_match('/^\d{2}:\d{2}$/', $time)) {
                $time = '02:00';
            }

            if ($frequency === 'weekly') {
                $weekdayMap = [
                    'sunday' => 0,
                    'monday' => 1,
                    'tuesday' => 2,
                    'wednesday' => 3,
                    'thursday' => 4,
                    'friday' => 5,
                    'saturday' => 6,
                ];
                $event->weeklyOn($weekdayMap[$day] ?? 1, $time);
                continue;
            }

            $event->dailyAt($time);
        }

        // Legacy fallback to env-configured backup schedule.
        if (config('backup.schedule_enabled') && empty($configured)) {
            $schedule->command('backup:application')
                ->dailyAt((string) config('backup.schedule_time', '02:00'))
                ->withoutOverlapping();
        }
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
