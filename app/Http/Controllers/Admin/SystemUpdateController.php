<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CmsUpdateManager;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;
use RuntimeException;

class SystemUpdateController extends Controller
{
    public function __construct(
        protected CmsUpdateManager $updates
    ) {}

    public function index(Request $request)
    {
        $installed = $this->updates->getInstalledVersion();
        $manifestUrl = $this->updates->manifestUrl();
        $manifest = $manifestUrl ? $this->updates->fetchManifest($request->boolean('refresh')) : null;
        $updateAvailable = $manifest !== null && $this->updates->isUpdateAvailable($manifest);
        $configured = $manifestUrl !== null;
        $enabled = (bool) config('cms.update_enabled', true);

        return view('admin.system-update.index', compact(
            'installed',
            'manifestUrl',
            'manifest',
            'updateAvailable',
            'configured',
            'enabled'
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
            $this->updates->applyUpdate($manifest);
        } catch (RuntimeException $e) {
            ActivityLogger::log('system.update.failed', $e->getMessage());

            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            ActivityLogger::log('system.update.failed', $e->getMessage());

            return back()->with('error', __('Update failed: :msg', ['msg' => $e->getMessage()]));
        }

        ActivityLogger::log(
            'system.update.applied',
            __('CMS updated to version :v.', ['v' => (string) $manifest['version']])
        );

        return redirect()
            ->route('admin.system-update.index')
            ->with('success', __('Update completed. Installed version: :v', ['v' => (string) $manifest['version']]));
    }
}
