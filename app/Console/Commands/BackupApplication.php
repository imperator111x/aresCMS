<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Symfony\Component\Process\Process;
use ZipArchive;

class BackupApplication extends Command
{
    protected $signature = 'backup:application';

    protected $description = 'Erstellt ein ZIP mit Datenbank-Dump und optional storage/app/public (Uploads).';

    public function handle(): int
    {
        $backupDir = storage_path('app/backups');
        if (! is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $stamp = now()->format('Y-m-d_His');
        $workDir = $backupDir.DIRECTORY_SEPARATOR.'_tmp_'.$stamp;
        mkdir($workDir, 0755, true);

        try {
            $driver = config('database.default');
            $sqlPath = $workDir.DIRECTORY_SEPARATOR.'database.sql';

            if ($driver === 'mysql') {
                if (! $this->dumpMysql($sqlPath)) {
                    return self::FAILURE;
                }
            } elseif ($driver === 'sqlite') {
                $dbFile = config('database.connections.sqlite.database');
                if (! is_file($dbFile)) {
                    $this->error('SQLite-Datei nicht gefunden: '.$dbFile);

                    return self::FAILURE;
                }
                copy($dbFile, $workDir.DIRECTORY_SEPARATOR.'database.sqlite');
                $sqlPath = $workDir.DIRECTORY_SEPARATOR.'database.sqlite';
            } else {
                $this->warn('Treiber „'.$driver.'“: kein automatischer Dump. Nur Dateien sichern.');
                file_put_contents($workDir.DIRECTORY_SEPARATOR.'README.txt', "DB-Treiber: {$driver}\nBitte DB manuell sichern.\n");
            }

            if (config('backup.include_public_storage')) {
                $publicStorage = storage_path('app/public');
                if (is_dir($publicStorage)) {
                    $dest = $workDir.DIRECTORY_SEPARATOR.'public_storage';
                    mkdir($dest, 0755, true);
                    $this->copyDirectory($publicStorage, $dest);
                }
            }

            $zipName = 'backup_'.$stamp.'.zip';
            $zipPath = $backupDir.DIRECTORY_SEPARATOR.$zipName;

            if (! $this->zipDirectory($workDir, $zipPath)) {
                return self::FAILURE;
            }

            $this->info('Backup erstellt: '.$zipPath);
            $this->pruneOldBackups($backupDir);

            return self::SUCCESS;
        } finally {
            $this->deleteDirectory($workDir);
        }
    }

    private function dumpMysql(string $sqlPath): bool
    {
        $c = config('database.connections.mysql');
        $password = (string) ($c['password'] ?? '');
        $user = $c['username'];
        $host = $c['host'];
        $port = $c['port'] ?? '3306';
        $database = $c['database'];

        $lastError = '';
        $tried = [];

        foreach ($this->mysqldumpPathCandidates() as $bin) {
            if (! $this->isRunnableMysqldumpPath($bin)) {
                continue;
            }
            $tried[] = $bin;

            $cmd = [
                $bin,
                '--host='.$host,
                '--port='.$port,
                '--user='.$user,
                '--default-character-set=utf8mb4',
                '--single-transaction',
                '--quick',
                '--no-tablespaces',
                $database,
            ];

            $processEnv = array_merge($_ENV, $_SERVER);
            if (PHP_OS_FAMILY !== 'Windows') {
                if ($password !== '') {
                    $processEnv['MYSQL_PWD'] = $password;
                }
            } elseif ($password !== '') {
                $cmd[] = '--password='.$password;
            }

            $process = new Process($cmd, base_path(), $processEnv, null, 3600);
            $process->run();

            if ($process->isSuccessful()) {
                file_put_contents($sqlPath, $process->getOutput());

                return true;
            }

            $err = trim($process->getErrorOutput().$process->getOutput());
            $lastError = $err !== '' ? $err : 'Unbekannter Fehler (Exit '.$process->getExitCode().').';

            if ($this->looksLikeMissingExecutable($err, $process->getExitCode())) {
                continue;
            }

            $this->error('mysqldump fehlgeschlagen: '.$lastError);
            $this->line('Verwendeter Pfad: '.$bin);
            $this->printMysqldumpPathHint();

            return false;
        }

        $this->error('mysqldump fehlgeschlagen: '.($lastError !== '' ? $lastError : 'Kein mysqldump gefunden.'));
        if ($tried !== []) {
            $this->line('Geprüfte Pfade: '.implode(', ', $tried));
        }
        $this->printMysqldumpPathHint();

        return false;
    }

    /**
     * @return list<string>
     */
    private function mysqldumpPathCandidates(): array
    {
        $configured = trim((string) config('backup.mysqldump_path', 'mysqldump'));
        $candidates = [];

        if ($configured !== '' && $configured !== 'mysqldump') {
            $candidates[] = $configured;
        }

        if (PHP_OS_FAMILY === 'Windows') {
            // Typisch: …\xampp\htdocs\projekt → XAMPP-Wurzel ist zwei Ebenen über base_path()
            $maybeXampp = dirname(dirname(base_path())).DIRECTORY_SEPARATOR.'mysql'.DIRECTORY_SEPARATOR.'bin'.DIRECTORY_SEPARATOR.'mysqldump.exe';
            $candidates[] = $maybeXampp;

            $candidates[] = 'C:\\xampp\\mysql\\bin\\mysqldump.exe';

            $drive = getenv('SystemDrive');
            if (is_string($drive) && $drive !== '') {
                $candidates[] = rtrim($drive, '\\').'\\xampp\\mysql\\bin\\mysqldump.exe';
            }
        }

        $candidates[] = 'mysqldump';

        $out = [];
        foreach ($candidates as $path) {
            $path = str_replace('/', DIRECTORY_SEPARATOR, $path);
            if (! in_array($path, $out, true)) {
                $out[] = $path;
            }
        }

        return $out;
    }

    private function isRunnableMysqldumpPath(string $bin): bool
    {
        if (preg_match('/^[A-Za-z]:[\\\\\/]|^[\\\\\/]{2}/', $bin) || str_contains($bin, DIRECTORY_SEPARATOR)) {
            return is_file($bin);
        }

        return true;
    }

    private function looksLikeMissingExecutable(string $output, int $exitCode): bool
    {
        if ($exitCode === 127) {
            return true;
        }

        $o = strtolower($output);

        return str_contains($o, 'could not be found')
            || str_contains($o, 'is not recognized')
            || str_contains($o, 'not recognized as an internal or external command')
            || str_contains($o, 'konnte nicht gefunden werden')
            || str_contains($o, 'entweder falsch geschrieben')
            || str_contains($o, 'no such file or directory')
            || str_contains($o, 'not found');
    }

    private function printMysqldumpPathHint(): void
    {
        $this->line('Tipp: In `.env` setzen, z. B.:');
        $this->line('BACKUP_MYSQLDUMP_PATH="C:\\xampp\\mysql\\bin\\mysqldump.exe"');
        $maybe = dirname(dirname(base_path())).DIRECTORY_SEPARATOR.'mysql'.DIRECTORY_SEPARATOR.'bin'.DIRECTORY_SEPARATOR.'mysqldump.exe';
        if (PHP_OS_FAMILY === 'Windows' && is_file($maybe)) {
            $this->line('(Auf diesem Rechner würde auch passen: BACKUP_MYSQLDUMP_PATH="'.$maybe.'")');
        }
    }

    private function copyDirectory(string $src, string $dest): void
    {
        $src = rtrim($src, DIRECTORY_SEPARATOR);
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($src, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        /** @var SplFileInfo $item */
        foreach ($iterator as $item) {
            $relative = substr($item->getPathname(), strlen($src) + 1);
            $target = $dest.DIRECTORY_SEPARATOR.$relative;
            if ($item->isDir()) {
                if (! is_dir($target)) {
                    mkdir($target, 0755, true);
                }
            } else {
                $parent = dirname($target);
                if (! is_dir($parent)) {
                    mkdir($parent, 0755, true);
                }
                copy($item->getPathname(), $target);
            }
        }
    }

    private function zipDirectory(string $sourceDir, string $zipPath): bool
    {
        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            $this->error('ZIP konnte nicht angelegt werden: '.$zipPath);

            return false;
        }

        $sourceDir = rtrim($sourceDir, DIRECTORY_SEPARATOR);
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($sourceDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            if (! $file->isFile()) {
                continue;
            }
            $full = $file->getRealPath();
            $relative = substr($full, strlen($sourceDir) + 1);
            $zip->addFile($full, str_replace('\\', '/', $relative));
        }

        $zip->close();

        return true;
    }

    private function deleteDirectory(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }
        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($it as $file) {
            if ($file->isDir()) {
                @rmdir($file->getPathname());
            } else {
                @unlink($file->getPathname());
            }
        }
        @rmdir($dir);
    }

    private function pruneOldBackups(string $backupDir): void
    {
        $keepDays = (int) config('backup.keep_days', 14);
        if ($keepDays <= 0) {
            return;
        }

        $cutoff = time() - ($keepDays * 86400);

        foreach (glob($backupDir.DIRECTORY_SEPARATOR.'backup_*.zip') ?: [] as $file) {
            if (is_file($file) && filemtime($file) < $cutoff) {
                unlink($file);
                $this->line('Altes Backup gelöscht: '.basename($file));
            }
        }
    }
}
