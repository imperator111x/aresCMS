<?php
/**
 * Standalone-Build für cms:create-update (ohne Laravel-Bootstrap).
 * Nutzen wenn PHP < 8.4 (z. B. XAMPP 8.2) — artisan cms:create-update braucht PHP 8.4+.
 *
 * C:\xampp\php\php.exe scripts\build-cms-update.php --release=1.0.2 --package-base=https://update.luetcke.eu --notes-file=deploy/system-upgrade/RELEASE_NOTES_1.0.2.txt
 */

declare(strict_types=1);

if (PHP_VERSION_ID < 80100) {
    fwrite(STDERR, "PHP 8.1+ erforderlich.\n");
    exit(1);
}

if (! class_exists(ZipArchive::class)) {
    fwrite(STDERR, "PHP-Erweiterung zip fehlt (in php.ini aktivieren).\n");
    exit(1);
}

$basePath = dirname(__DIR__);
chdir($basePath);

$options = getopt('', ['release:', 'package-base:', 'notes:', 'notes-file:', 'force', 'help']);

if (isset($options['help'])) {
    echo "Optionen:\n";
    echo "  --release=1.0.2\n";
    echo "  --package-base=https://update.luetcke.eu\n";
    echo "  --notes-file=deploy/system-upgrade/RELEASE_NOTES_1.0.2.txt\n";
    echo "  --notes=\"Zeile1\\nZeile2\"\n";
    exit(0);
}

$version = trim((string) ($options['release'] ?? '1.0.2'));
if ($version === '') {
    fwrite(STDERR, "Version darf nicht leer sein.\n");
    exit(1);
}

$packageBaseOpt = trim((string) ($options['package-base'] ?? ''));
$usesRemotePackage = $packageBaseOpt !== '';

$dir = $basePath.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'update';
if (! is_dir($dir) && ! mkdir($dir, 0755, true) && ! is_dir($dir)) {
    fwrite(STDERR, "Ordner konnte nicht angelegt werden: {$dir}\n");
    exit(1);
}

$safeFile = preg_replace('/[^a-zA-Z0-9._-]+/', '-', $version) ?: 'release';
$zipName = 'news-portal-update-'.$safeFile.'.zip';
$zipPath = $dir.DIRECTORY_SEPARATOR.$zipName;

$zip = new ZipArchive;
if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    fwrite(STDERR, "ZIP konnte nicht angelegt werden: {$zipPath}\n");
    exit(1);
}

$added = 0;
if ($usesRemotePackage) {
    $added = addFullProjectToZip($zip, $basePath);
    if ($added < 1) {
        $zip->close();
        @unlink($zipPath);
        fwrite(STDERR, "Keine Projektdateien ins ZIP übernommen.\n");
        exit(1);
    }
    echo "ZIP: {$added} Dateien.\n";
}

$marker = "aresCMS CMS-Update\n";
$marker .= 'Version: '.$version."\n";
$marker .= 'Erzeugt: '.date('c')."\n";
$marker .= "Nach Installation sollte diese Datei unter public/ liegen.\n";
$zip->addFromString('public/cms-update-verification.txt', $marker);
$zip->close();

$sha256 = hash_file('sha256', $zipPath);
$packageBase = $usesRemotePackage
    ? rtrim($packageBaseOpt, '/')
    : rtrim((string) (getenv('APP_URL') ?: 'http://localhost'), '/').'/update';
$packageUrl = $packageBase.'/'.$zipName;

$notes = resolveReleaseNotes($basePath, $options, $version, $usesRemotePackage);

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

$deployDir = $basePath.DIRECTORY_SEPARATOR.'deploy'.DIRECTORY_SEPARATOR.'system-upgrade';
if (! is_dir($deployDir)) {
    mkdir($deployDir, 0755, true);
}
copy($manifestPath, $deployDir.DIRECTORY_SEPARATOR.'manifest.json');
copy($zipPath, $deployDir.DIRECTORY_SEPARATOR.$zipName);

echo "Update-Paket erstellt.\n";
echo "ZIP:      {$zipPath}\n";
echo "Manifest: {$manifestPath}\n";
echo "Deploy:   {$deployDir}\n";
echo "SHA-256:  {$sha256}\n";
echo "\nUpload auf Update-Server:\n";
echo "1. {$zipName} → {$packageUrl}\n";
echo "2. manifest.json → {$packageBase}/manifest.json\n";

function resolveReleaseNotes(string $basePath, array $options, string $version, bool $usesRemotePackage): string
{
    $fileOpt = trim((string) ($options['notes-file'] ?? ''));
    if ($fileOpt !== '') {
        $path = $basePath.DIRECTORY_SEPARATOR.str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $fileOpt);
        if (! is_readable($path)) {
            fwrite(STDERR, "Release-Notes-Datei nicht lesbar: {$path}\n");
            exit(1);
        }

        return str_replace(["\r\n", "\r"], "\n", (string) file_get_contents($path));
    }

    $notesOpt = $options['notes'] ?? null;
    if ($notesOpt !== null && trim((string) $notesOpt) !== '') {
        $raw = str_replace(['\r\n', '\r'], "\n", (string) $notesOpt);

        return str_replace('\\n', "\n", $raw);
    }

    return $usesRemotePackage
        ? "Release {$version}.\nNach Installation prüfen: public/cms-update-verification.txt"
        : "Update {$version} (lokal).\nPrüfen: public/cms-update-verification.txt";
}

function addFullProjectToZip(ZipArchive $zip, string $basePath): int
{
    $realBase = realpath($basePath);
    if ($realBase === false) {
        return 0;
    }
    $realBase = str_replace('\\', '/', $realBase);

    $dirIterator = new RecursiveDirectoryIterator(
        $realBase,
        FilesystemIterator::SKIP_DOTS
    );

    $filter = new RecursiveCallbackFilterIterator(
        $dirIterator,
        static function (SplFileInfo $current) use ($realBase) {
            $path = $current->getPathname();
            $norm = str_replace('\\', '/', $path);
            if (! str_starts_with($norm, $realBase.'/')) {
                return false;
            }
            $rel = substr($norm, strlen($realBase) + 1);

            return shouldIncludeRelativePath($rel, $current->isDir());
        }
    );

    $iterator = new RecursiveIteratorIterator(
        $filter,
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    $count = 0;
    foreach ($iterator as $fileInfo) {
        if (! $fileInfo instanceof SplFileInfo || ! $fileInfo->isFile()) {
            continue;
        }
        $full = str_replace('\\', '/', $fileInfo->getRealPath() ?: $fileInfo->getPathname());
        if (! str_starts_with($full, $realBase.'/')) {
            continue;
        }
        $rel = substr($full, strlen($realBase) + 1);
        if (! shouldIncludeRelativePath($rel, false)) {
            continue;
        }
        $entryName = str_replace('\\', '/', $rel);
        if ($zip->addFile($fileInfo->getPathname(), $entryName)) {
            $count++;
        }
    }

    return $count;
}

function shouldIncludeRelativePath(string $relative, bool $isDirectory): bool
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
        'filezilla-upload/',
        'filezilla-upload-handwerk/',
        'filezilla-upload-hotfix/',
        'filezilla-upload-themes/',
        'deploy/system-upgrade/',
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
        'php',
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
