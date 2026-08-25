<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CmsUpdateManager;
use App\Services\ApplicationHealthCheckService;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;
use RuntimeException;

class SystemUpdateController extends Controller
{
    public function __construct(
        protected CmsUpdateManager $updates,
        protected ApplicationHealthCheckService $healthCheck
    ) {}

    public function index(Request $request)
    {
        $installed = $this->updates->getInstalledVersion();
        $manifestUrl = $this->updates->manifestUrl();
        $manifest = $manifestUrl ? $this->updates->fetchManifest($request->boolean('refresh')) : null;
        $updateAvailable = $manifest !== null && $this->updates->isUpdateAvailable($manifest);
        $configured = $manifestUrl !== null;
        $enabled = (bool) config('cms.update_enabled', true);
        $backupBefore = (bool) config('cms.update_backup_before', true);
        $backupRequired = (bool) config('cms.update_backup_required', true);

        return view('admin.system-update.index', compact(
            'installed',
            'manifestUrl',
            'manifest',
            'updateAvailable',
            'configured',
            'enabled',
            'backupBefore',
            'backupRequired'
        ));
    }

    public function apply(Request $request)
    {
        $request->validate([
            'confirm' => 'required|accepted',
        ]);

        if (! config('cms.update_enabled', true)) {
            return back()->with('error', __('CMS updates are disabled on this installation.'));
        }

        $manifest = $this->updates->fetchManifest(true);
        if ($manifest === null) {
            return back()->with('error', __('Update manifest could not be loaded.'));
        }

        if (! $this->updates->isUpdateAvailable($manifest)) {
            return back()->with('info', __('You are already on the latest version.'));
        }

        try {
            $result = $this->updates->applyUpdate($manifest);
        } catch (RuntimeException $e) {
            ActivityLogger::log('system.update.failed', $e->getMessage());

            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            ActivityLogger::log('system.update.failed', $e->getMessage());

            return back()->with('error', __('Update failed: :msg', ['msg' => $e->getMessage()]));
        }

        $logMessage = __('CMS updated to version :v.', ['v' => (string) $manifest['version']]);
        if (! empty($result['backup_basename'])) {
            $logMessage .= ' '.__('Pre-update backup: :file', ['file' => $result['backup_basename']]);
        }

        $healthLog = $this->healthCheck->formatForLog(
            $result['health_check'],
            __('Post-update health check')
        );
        ActivityLogger::log('system.update.applied', $logMessage.' | '.$healthLog);

        if ($this->healthCheck->hasFailures($result['health_check'])) {
            ActivityLogger::log('system.update.health_check.failed', $healthLog);
        }

        $success = __('Update completed. Installed version: :v', ['v' => (string) $manifest['version']]);
        if (! empty($result['backup_basename'])) {
            $success = __('Update completed. Installed version: :v. Backup: :backup', [
                'v' => (string) $manifest['version'],
                'backup' => $result['backup_basename'],
            ]);
        }

        $healthSummary = __('Health check: :ok OK, :warn warnings, :fail failures', [
            'ok' => $result['health_check']['summary']['ok'],
            'warn' => $result['health_check']['summary']['warn'],
            'fail' => $result['health_check']['summary']['fail'],
        ]);
        $success .= ' '.$healthSummary;

        if ($this->healthCheck->hasFailures($result['health_check'])) {
            $success .= ' '.__('Some health checks failed — see Activity log or Health check page.');
        }

        return redirect()
            ->route('admin.system-update.index')
            ->with('success', $success);
    }
}
