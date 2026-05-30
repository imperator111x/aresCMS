<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PluginManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;
use ZipArchive;

class PluginController extends Controller
{
    public function index(PluginManager $pluginManager): View
    {
        $plugins = $pluginManager->all();

        return view('admin.plugins.index', compact('plugins'));
    }

    public function upload(Request $request, PluginManager $pluginManager): RedirectResponse
    {
        $request->validate([
            'plugin_zip' => 'required|file|mimes:zip|max:20480',
        ]);

        $file = $request->file('plugin_zip');
        if (! $file) {
            return back()->with('error', __('Plugin upload failed.'));
        }

        $zip = new ZipArchive();
        $opened = $zip->open($file->getRealPath());
        if ($opened !== true) {
            return back()->with('error', __('Plugin ZIP could not be opened.'));
        }

        $entries = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = (string) $zip->getNameIndex($i);
            $name = str_replace('\\', '/', $name);
            $name = ltrim($name, '/');
            if ($name === '') {
                continue;
            }
            $entries[] = $name;
        }

        if (empty($entries)) {
            $zip->close();

            return back()->with('error', __('Plugin ZIP is empty.'));
        }

        foreach ($entries as $entry) {
            if (str_contains($entry, '..') || preg_match('/^[A-Za-z]:/', $entry)) {
                $zip->close();

                return back()->with('error', __('Plugin ZIP contains invalid paths.'));
            }
        }

        $baseRoot = rtrim($pluginManager->pluginRootPath(), DIRECTORY_SEPARATOR);
        File::ensureDirectoryExists($baseRoot);

        $singleRoot = $this->singleRootDirectory($entries);
        $targetDirectory = $singleRoot ?: Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        if ($targetDirectory === '') {
            $targetDirectory = 'plugin-'.date('YmdHis');
        }

        $targetPath = $baseRoot.DIRECTORY_SEPARATOR.$targetDirectory;
        if (is_dir($targetPath)) {
            $zip->close();

            return back()->with('error', __('A plugin with this directory already exists.'));
        }

        File::ensureDirectoryExists($targetPath);
        $manifestName = (string) config('plugins.manifest', 'plugin.json');

        foreach ($entries as $entry) {
            $relative = $singleRoot ? Str::after($entry, $singleRoot.'/') : $entry;
            if ($singleRoot && ($relative === $entry || $relative === '')) {
                continue;
            }

            $destination = $targetPath.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relative);
            $normalized = realpath(dirname($destination));
            if ($normalized !== false && ! str_starts_with($normalized, $targetPath)) {
                File::deleteDirectory($targetPath);
                $zip->close();

                return back()->with('error', __('Plugin ZIP contains invalid paths.'));
            }

            if (str_ends_with($entry, '/')) {
                File::ensureDirectoryExists($destination);
                continue;
            }

            File::ensureDirectoryExists(dirname($destination));
            $stream = $zip->getStream($entry);
            if ($stream === false) {
                File::deleteDirectory($targetPath);
                $zip->close();

                return back()->with('error', __('Plugin upload failed.'));
            }

            $out = fopen($destination, 'wb');
            if ($out === false) {
                fclose($stream);
                File::deleteDirectory($targetPath);
                $zip->close();

                return back()->with('error', __('Plugin upload failed.'));
            }

            stream_copy_to_stream($stream, $out);
            fclose($stream);
            fclose($out);
        }

        $zip->close();

        if (! is_file($targetPath.DIRECTORY_SEPARATOR.$manifestName)) {
            File::deleteDirectory($targetPath);

            return back()->with('error', __('Plugin manifest is missing (plugin.json).'));
        }

        return back()->with('success', __('Plugin uploaded successfully.'));
    }

    public function toggle(Request $request, PluginManager $pluginManager, string $directory): RedirectResponse
    {
        $enabled = $request->boolean('enabled');
        $updated = $pluginManager->setEnabledByDirectory($directory, $enabled);
        if (! $updated) {
            return back()->with('error', __('Plugin state could not be updated.'));
        }

        return back()->with('success', $enabled ? __('Plugin enabled.') : __('Plugin disabled.'));
    }

    /**
     * @param array<int, string> $entries
     */
    protected function singleRootDirectory(array $entries): ?string
    {
        $roots = [];
        foreach ($entries as $entry) {
            $root = Str::before($entry, '/');
            if ($root === '') {
                return null;
            }
            $roots[$root] = true;
            if (count($roots) > 1) {
                return null;
            }
        }

        $root = array_key_first($roots);
        if (! is_string($root) || $root === '') {
            return null;
        }

        return $root;
    }
}

