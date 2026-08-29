<?php

namespace App\Console\Commands;

use App\Services\CmsUpdateManager;
use Illuminate\Console\Command;
use InvalidArgumentException;
use ZipArchive;

class CreateCmsUpdate extends Command
{
    protected $signature = 'cms:create-update
                            {--release=1.0.1 : Versionsnummer im Manifest (muss höher sein als installiert)}
                            {--package-base= : Basis-URL für package_url (z. B. https://update.example.com — ZIP dort hochladen)}
                            {--notes= : Release-Notes (einzeilig; Zeilenumbruch als \n in Anführungszeichen)}
                            {--notes-file= : Datei mit Release-Notes (relativ zum Projektroot, UTF-8 — überschreibt --notes)}
                            {--force : Auch erzeugen, wenn Version nicht höher als installiert}';

    protected $description = 'Erzeugt public/update/manifest.json + ZIP für den Admin-Updater (lokal oder zum Upload auf Update-Server)';

    public function handle(CmsUpdateManager $updates): int
    {
        $version = trim((string) $this->option('release'));
        if ($version === '') {
            $this->error('Version darf nicht leer sein.');

            return self::FAILURE;
        }

        $installed = $updates->getInstalledVersion();
        if (! $this->option('force') && version_compare($version, $installed, '<=')) {
            $this->error("Version „{$version}“ ist nicht höher als installiert ({$installed}).");
            $this->line('Hinweis: Höhere Version wählen, z. B. --release=1.0.2, oder storage/app/cms/installed_version anpassen / löschen.');
            $this->line('Mit --force trotzdem erzeugen.');

            return self::FAILURE;
        }

        $dir = public_path('update');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $safeFile = preg_replace('/[^a-zA-Z0-9._-]+/', '-', $version);
        $zipName = 'news-portal-update-'.$safeFile.'.zip';
        $zipPath = $dir.DIRECTORY_SEPARATOR.$zipName;

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            $this->error('ZIP konnte nicht angelegt werden: '.$zipPath);

            return self::FAILURE;
        }

        $packageBaseOpt = trim((string) $this->option('package-base'));
        $usesRemotePackage = $packageBaseOpt !== '';

        if ($usesRemotePackage) {
            $added = $this->addFullProjectToZip($zip, base_path());
            if ($added < 1) {
                $zip->close();
                @unlink($zipPath);
                $this->error('Keine Projektdateien ins ZIP übernommen — Pfade prüfen.');

                return self::FAILURE;
            }
            $this->info("ZIP: {$added} Dateien (ohne Entwicklungs-/Upload-Pfade).");
        }

        $marker = "aresCMS CMS-Update\n";
        $marker .= 'Version: '.$version."\n";
        $marker .= 'Erzeugt: '.now()->toIso8601String()."\n";
        $marker .= "Nach Installation sollte diese Datei unter public/ liegen.\n";

        $zip->addFromString('public/cms-update-verification.txt', $marker);
        $zip->close();

        $sha256 = hash_file('sha256', $zipPath);

        if ($usesRemotePackage) {
            $packageBase = rtrim($packageBaseOpt, '/');
        } else {
            $packageBase = rtrim((string) config('app.url'), '/').'/update';
        }
        $packageUrl = $packageBase.'/'.$zipName;

        $appUrl = rtrim((string) config('app.url'), '/');
        try {
            $notes = $this->resolveReleaseNotes($version, $usesRemotePackage);
        } catch (InvalidArgumentException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $manifest = [
            'version' => $version,
            'min_php' => '8.1.0',
            'package_url' => $packageUrl,
            'sha256' => $sha256,
            'notes' => $notes,
        ];

        $manifestPath = $dir.DIRECTORY_SEPARATOR.'manifest.json';
        file_put_contents(
            $manifestPath,
            json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)."\n"
        );

        $this->info('Update-Paket erstellt.');
        $this->table(
            ['Datei', 'Pfad'],
            [
                ['ZIP', $zipPath],
                ['Manifest', $manifestPath],
            ]
        );
        $this->line('SHA-256: '.$sha256);
        $this->newLine();

        if ($usesRemotePackage) {
            $this->warn('Upload auf den Update-Server:');
            $this->line('1. '.$zipName.' → muss erreichbar sein unter: '.$packageUrl);
            $this->line('2. manifest.json → z. B. '.$packageBase.'/manifest.json');
            $this->newLine();
            $this->line('Auf jeder Installation in .env:');
            $this->line('CMS_UPDATE_MANIFEST_URL='.$packageBase.'/manifest.json');
        } else {
            $this->warn('Lokal — in .env (APP_URL = Browser-URL):');
            $this->line('CMS_UPDATE_MANIFEST_URL='.$appUrl.'/update/manifest.json');
            $this->newLine();
            $this->line('Für eigenen Update-Host (ZIP + Manifest dort ablegen):');
            $this->line('php artisan cms:create-update --release='.$version.' --package-base=https://update.example.com --force');
        }

        $this->newLine();
        $this->line('Admin → System updates: prüfen und ggf. installieren.');
        $this->line('Release-Notes: --notes="…" oder --notes-file=deploy/… (UTF-8, z. B. RELEASE_NOTES_1.0.1.txt)');

        return self::SUCCESS;
    }

    /**
     * Vollständiges Release-ZIP (für Update-Server). Ohne --package-base bleibt es beim kleinen Prüf-Paket.
     */
    private function addFullProjectToZip(ZipArchive $zip, string $basePath): int
    {
        $realBase = realpath($basePath);
        if ($realBase === false) {
            return 0;
        }
        $realBase = str_replace('\\', '/', $realBase);

        $dirIterator = new \RecursiveDirectoryIterator(
            $realBase,
            \FilesystemIterator::SKIP_DOTS
        );

        $filter = new \RecursiveCallbackFilterIterator(
            $dirIterator,
            function (\SplFileInfo $current) use ($realBase) {
                $path = $current->getPathname();
                $norm = str_replace('\\', '/', $path);
                if (! str_starts_with($norm, $realBase.'/')) {
                    return false;
                }
                $rel = substr($norm, strlen($realBase) + 1);

                return $this->shouldIncludeRelativePath($rel, $current->isDir());
            }
        );

        $iterator = new \RecursiveIteratorIterator(
            $filter,
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        $count = 0;
        foreach ($iterator as $fileInfo) {
            if (! $fileInfo instanceof \SplFileInfo || ! $fileInfo->isFile()) {
                continue;
            }
            $full = str_replace('\\', '/', $fileInfo->getRealPath() ?: $fileInfo->getPathname());
            if (! str_starts_with($full, $realBase.'/')) {
                continue;
            }
            $rel = substr($full, strlen($realBase) + 1);
            if (! $this->shouldIncludeRelativePath($rel, false)) {
                continue;
            }
            $entryName = str_replace('\\', '/', $rel);
            if (! $zip->addFile($fileInfo->getPathname(), $entryName)) {
                continue;
            }
            $count++;
        }

        return $count;
    }

    private function shouldIncludeRelativePath(string $relative, bool $isDirectory): bool
    {
        $rel = strtolower(str_replace('\\', '/', ltrim($relative, '/')));

        $blockedPrefixes = [
            '.git/',
            'node_modules/',
            '.idea/',
            '.vscode/',
            '.cursor/',
            '.claude/',
            'storage/logs/',
            'storage/app/public/',
            'storage/app/backups/',
            'storage/app/cms/',
            'storage/framework/cache/data/',
            'storage/framework/sessions/',
            'storage/framework/views/',
            'public/update/',
            'public/storage/',
            'tests/',
        ];

        foreach ($blockedPrefixes as $prefix) {
            $p = strtolower($prefix);
            if ($rel === rtrim($p, '/') || str_starts_with($rel, $p)) {
                return false;
            }
        }

        $blockedExact = [
            '.env',
            '.env.backup',
            'htdocs.lnk',
            'mkdir',
            'public/hot',
            '.cursorignore',
            '.cursorrules',
            'agents.md',
        ];
        foreach ($blockedExact as $name) {
            if ($rel === $name) {
                return false;
            }
        }

        if (str_ends_with($rel, '/.env') || str_ends_with($rel, '/.env.backup')) {
            return false;
        }

        if (str_contains($rel, '/.git/')) {
            return false;
        }

        return true;
    }

    private function resolveReleaseNotes(string $version, bool $usesRemotePackage): string
    {
        $fileOpt = trim((string) $this->option('notes-file'));
        if ($fileOpt !== '') {
            $path = base_path($fileOpt);
            if (! is_readable($path)) {
                throw new InvalidArgumentException('Release-Notes-Datei nicht lesbar: '.$path);
            }
            $content = (string) file_get_contents($path);

            return str_replace(["\r\n", "\r"], "\n", $content);
        }

        $notesOpt = $this->option('notes');
        if ($notesOpt !== null && trim((string) $notesOpt) !== '') {
            $raw = (string) $notesOpt;
            $raw = str_replace(['\r\n', '\r'], "\n", $raw);

            return str_replace('\\n', "\n", $raw);
        }

        return $usesRemotePackage
            ? "Release {$version}.\nNach Installation prüfen: public/cms-update-verification.txt"
            : "Update {$version} (lokal).\nPrüfen: public/cms-update-verification.txt\nRelease-Notes: --notes-file=… oder --notes=\"Zeile1\\nZeile2\"";
    }
}
