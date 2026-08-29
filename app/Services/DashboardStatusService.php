<?php

namespace App\Services;

use App\Services\CmsUpdateManager;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class DashboardStatusService
{
    public function __construct(
        private readonly CmsUpdateManager $updates,
        private readonly LaravelReleaseService $laravelRelease,
    ) {
    }

    /**
     * @return list<array{key:string,label:string,status:string,message:string,href:?string}>
     */
    public function indicators(): array
    {
        return [
            $this->phpIndicator(),
            $this->laravelIndicator(),
            $this->backupIndicator(),
            $this->updateIndicator(),
            $this->diskIndicator(),
        ];
    }

    /**
     * @return array{key:string,label:string,status:string,message:string,href:?string}
     */
    private function phpIndicator(): array
    {
        $version = PHP_VERSION;
        $ok = version_compare($version, '8.1.0', '>=');
        $status = $ok ? 'ok' : 'fail';

        return [
            'key' => 'php',
            'label' => __('PHP version'),
            'status' => $status,
            'message' => $ok
                ? __('Running PHP :version', ['version' => $version])
                : __('PHP :version is below recommended 8.1+', ['version' => $version]),
            'href' => route('admin.operations.server-info'),
        ];
    }

    /**
     * @return array{key:string,label:string,status:string,message:string,href:?string}
     */
    private function laravelIndicator(): array
    {
        $installed = $this->laravelRelease->installedVersion();
        $href = route('admin.operations.index');

        if (! config('cms.laravel_version_check_enabled', true)) {
            return [
                'key' => 'laravel',
                'label' => __('Laravel framework'),
                'status' => 'ok',
                'message' => __('Running Laravel :version', ['version' => $installed]),
                'href' => $href,
            ];
        }

        $latest = $this->laravelRelease->latestStableVersion();

        if ($latest === null) {
            return [
                'key' => 'laravel',
                'label' => __('Laravel framework'),
                'status' => 'warn',
                'message' => __('Could not check Laravel releases. Installed: :version', ['version' => $installed]),
                'href' => $href,
            ];
        }

        if (version_compare($latest, $installed, '>')) {
            return [
                'key' => 'laravel',
                'label' => __('Laravel framework'),
                'status' => 'warn',
                'message' => __('New Laravel version available: :remote (installed :installed)', [
                    'remote' => $latest,
                    'installed' => $installed,
                ]),
                'href' => $href,
            ];
        }

        return [
            'key' => 'laravel',
            'label' => __('Laravel framework'),
            'status' => 'ok',
            'message' => __('Laravel up to date (:version)', ['version' => $installed]),
            'href' => $href,
        ];
    }

    /**
     * @return array{key:string,label:string,status:string,message:string,href:?string}
     */
    private function backupIndicator(): array
    {
        $latest = $this->latestBackupMeta();
        $href = route('admin.operations.index');

        if ($latest === null) {
            return [
                'key' => 'backup',
                'label' => __('Backup'),
                'status' => 'fail',
                'message' => __('No backup found.'),
                'href' => $href,
            ];
        }

        $ageDays = (int) $latest['mtime']->diffInDays(now());
        if ($ageDays <= 2) {
            $status = 'ok';
        } elseif ($ageDays <= 7) {
            $status = 'warn';
        } else {
            $status = 'fail';
        }

        return [
            'key' => 'backup',
            'label' => __('Backup'),
            'status' => $status,
            'message' => __('Last backup: :when (:file)', [
                'when' => $latest['mtime']->diffForHumans(),
                'file' => $latest['name'],
            ]),
            'href' => $href,
        ];
    }

    /**
     * @return array{key:string,label:string,status:string,message:string,href:?string}
     */
    private function updateIndicator(): array
    {
        $href = route('admin.system-update.index');
        $installed = $this->updates->getInstalledVersion();

        try {
            $manifest = $this->updates->fetchManifest();
            $available = $this->updates->isUpdateAvailable($manifest);
            $remote = is_array($manifest) ? (string) ($manifest['version'] ?? '') : '';
        } catch (\Throwable $e) {
            return [
                'key' => 'update',
                'label' => __('System update'),
                'status' => 'warn',
                'message' => __('Could not check updates (:msg). Installed: :version', [
                    'msg' => Str::limit($e->getMessage(), 80),
                    'version' => $installed,
                ]),
                'href' => $href,
            ];
        }

        if ($available) {
            return [
                'key' => 'update',
                'label' => __('System update'),
                'status' => 'warn',
                'message' => __('Update available: :remote (installed :installed)', [
                    'remote' => $remote !== '' ? $remote : '?',
                    'installed' => $installed,
                ]),
                'href' => $href,
            ];
        }

        return [
            'key' => 'update',
            'label' => __('System update'),
            'status' => 'ok',
            'message' => __('Up to date (:version)', ['version' => $installed]),
            'href' => $href,
        ];
    }

    /**
     * @return array{key:string,label:string,status:string,message:string,href:?string}
     */
    private function diskIndicator(): array
    {
        $path = storage_path('app');
        $free = @disk_free_space($path);
        $total = @disk_total_space($path);
        $href = route('admin.operations.server-info');

        if ($free === false || $total === false || $total <= 0) {
            return [
                'key' => 'disk',
                'label' => __('Disk space'),
                'status' => 'warn',
                'message' => __('Could not determine free disk space.'),
                'href' => $href,
            ];
        }

        $freePct = ($free / $total) * 100;
        $freeGb = $free / (1024 ** 3);

        if ($freePct < 5 || $freeGb < 0.5) {
            $status = 'fail';
        } elseif ($freePct < 15 || $freeGb < 2) {
            $status = 'warn';
        } else {
            $status = 'ok';
        }

        return [
            'key' => 'disk',
            'label' => __('Disk space'),
            'status' => $status,
            'message' => __(':free free of :total (:pct%)', [
                'free' => $this->formatBytes((float) $free),
                'total' => $this->formatBytes((float) $total),
                'pct' => number_format($freePct, 1),
            ]),
            'href' => $href,
        ];
    }

    /**
     * @return array{name:string,mtime:Carbon}|null
     */
    private function latestBackupMeta(): ?array
    {
        $dir = storage_path('app/backups');
        if (! is_dir($dir)) {
            return null;
        }

        $latest = null;
        $latestMtime = 0;
        foreach (glob($dir.DIRECTORY_SEPARATOR.'backup_*.zip') ?: [] as $file) {
            if (! is_file($file)) {
                continue;
            }
            $mtime = (int) filemtime($file);
            if ($mtime >= $latestMtime) {
                $latestMtime = $mtime;
                $latest = $file;
            }
        }

        if ($latest === null) {
            return null;
        }

        return [
            'name' => basename($latest),
            'mtime' => Carbon::createFromTimestamp($latestMtime),
        ];
    }

    private function formatBytes(float $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return number_format($bytes, $i === 0 ? 0 : 1).' '.$units[$i];
    }
}
