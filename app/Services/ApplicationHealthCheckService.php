<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ApplicationHealthCheckService
{
    /**
     * @return array{checks: list<array{name: string, status: string, message: string}>, summary: array{ok: int, warn: int, fail: int}}
     */
    public function run(): array
    {
        $checks = [];

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

        $publicStorageLink = public_path('storage');
        $hasStorageAccess = is_link($publicStorageLink) || is_dir($publicStorageLink);
        $checks[] = [
            'name' => __('Public storage path'),
            'status' => $hasStorageAccess ? 'ok' : 'warn',
            'message' => $hasStorageAccess
                ? __('public/storage is available.')
                : __('public/storage is missing. Fallback route is active, but a symlink is recommended.'),
        ];

        $isDebug = (bool) config('app.debug');
        $checks[] = [
            'name' => __('App debug mode'),
            'status' => $isDebug ? 'warn' : 'ok',
            'message' => $isDebug
                ? __('APP_DEBUG is enabled. Disable it in production.')
                : __('APP_DEBUG is disabled.'),
        ];

        $queueConnection = (string) config('queue.default');
        $checks[] = [
            'name' => __('Queue connection'),
            'status' => $queueConnection === 'sync' ? 'warn' : 'ok',
            'message' => $queueConnection === 'sync'
                ? __('Queue is set to sync. For production, a worker queue is recommended.')
                : __('Queue driver: :driver', ['driver' => $queueConnection]),
        ];

        return [
            'checks' => $checks,
            'summary' => [
                'ok' => collect($checks)->where('status', 'ok')->count(),
                'warn' => collect($checks)->where('status', 'warn')->count(),
                'fail' => collect($checks)->where('status', 'fail')->count(),
            ],
        ];
    }

    /**
     * @param  array{checks: list<array{name: string, status: string, message: string}>, summary: array{ok: int, warn: int, fail: int}}  $result
     */
    public function hasFailures(array $result): bool
    {
        return ($result['summary']['fail'] ?? 0) > 0;
    }

    /**
     * @param  array{checks: list<array{name: string, status: string, message: string}>, summary: array{ok: int, warn: int, fail: int}}  $result
     */
    public function formatForLog(array $result, ?string $context = null): string
    {
        $summary = $result['summary'];
        $prefix = $context !== null && $context !== ''
            ? $context.': '
            : '';

        $line = $prefix.__(':ok OK, :warn warnings, :fail failures', [
            'ok' => $summary['ok'],
            'warn' => $summary['warn'],
            'fail' => $summary['fail'],
        ]);

        $failures = collect($result['checks'] ?? [])
            ->where('status', 'fail')
            ->map(fn (array $check) => $check['name'].': '.$check['message'])
            ->values()
            ->all();

        if ($failures !== []) {
            $line .= ' | '.__('Failed checks: :list', [
                'list' => Str::limit(implode('; ', $failures), 400),
            ]);
        }

        return $line;
    }
}
