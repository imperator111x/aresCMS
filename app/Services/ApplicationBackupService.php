<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileInfo;
use Symfony\Component\Process\Process;
use ZipArchive;

class ApplicationBackupService
{
    /**
     * @throws RuntimeException
     */
    public function create(): string
    {
        $backupDir = storage_path('app/backups');
        if (! is_dir($backupDir) && ! mkdir($backupDir, 0755, true) && ! is_dir($backupDir)) {
            throw new RuntimeException(__('Backup directory could not be created.'));
        }

        $stamp = now()->format('Y-m-d_His');
        $workDir = $backupDir.DIRECTORY_SEPARATOR.'_tmp_'.$stamp;
        if (! mkdir($workDir, 0755, true) && ! is_dir($workDir)) {
            throw new RuntimeException(__('Temporary backup directory could not be created.'));
        }

        try {
            $driver = config('database.default');
            $sqlPath = $workDir.DIRECTORY_SEPARATOR.'database.sql';

            if ($driver === 'mysql') {
                $this->dumpMysql($sqlPath);
            } elseif ($driver === 'sqlite') {
                $dbFile = config('database.connections.sqlite.database');
                if (! is_file($dbFile)) {
                    throw new RuntimeException(__('SQLite database file not found: :path', ['path' => (string) $dbFile]));
                }
                copy($dbFile, $workDir.DIRECTORY_SEPARATOR.'database.sqlite');
            } else {
                file_put_contents(
                    $workDir.DIRECTORY_SEPARATOR.'README.txt',
                    "DB driver: {$driver}\nPlease back up the database manually.\n"
                );
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
                throw new RuntimeException(__('Backup ZIP could not be created.'));
            }

            $this->pruneOldBackups($backupDir);

            Log::info('Application backup created', ['path' => $zipPath]);

            return $zipPath;
        } finally {
            $this->deleteDirectory($workDir);
        }
    }

    /**
     * @throws RuntimeException
     */
    private function dumpMysql(string $sqlPath): void
    {
        $c = config('database.connections.mysql');
        $password = (string) ($c['password'] ?? '');
        $user = $c['username'];
        $host = $c['host'];
        $port = $c['port'] ?? '3306';
        $database = $c['database'];

        $lastError = '';

        foreach ($this->mysqldumpPathCandidates() as $bin) {
            if (! $this->isRunnableMysqldumpPath($bin)) {
                continue;
            }

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

                return;
            }

            $err = trim($process->getErrorOutput().$process->getOutput());
            $lastError = $err !== '' ? $err : 'Unknown error (exit '.$process->getExitCode().').';

            if ($this->looksLikeMissingExecutable($err, $process->getExitCode())) {
                continue;
            }

            throw new RuntimeException(__('mysqldump failed: :error', ['error' => $lastError]));
        }

        $hint = PHP_OS_FAMILY === 'Windows'
            ? ' BACKUP_MYSQLDUMP_PATH="C:\\xampp\\mysql\\bin\\mysqldump.exe"'
            : '';

        throw new RuntimeException(__('mysqldump failed: :error. Set :hint in .env if needed.', [
            'error' => $lastError !== '' ? $lastError : __('mysqldump not found'),
            'hint' => 'BACKUP_MYSQLDUMP_PATH'.$hint,
        ]));
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
                @unlink($file);
            }
        }
    }

    /**
     * @return array{database: bool, public_storage: bool, safety_backup: string|null}
     *
     * @throws RuntimeException
     */
    public function restore(string $zipPath): array
    {
        if (! is_file($zipPath)) {
            throw new RuntimeException(__('Backup file not found.'));
        }

        $realZip = realpath($zipPath);
        if ($realZip === false) {
            throw new RuntimeException(__('Backup file not found.'));
        }

        $safetyBackup = null;
        if (config('backup.restore_safety_backup', true)) {
            try {
                $safetyBackup = $this->create();
            } catch (RuntimeException $e) {
                throw new RuntimeException(__('Safety backup before restore failed: :msg', [
                    'msg' => $e->getMessage(),
                ]), 0, $e);
            }
        }

        $workDir = storage_path('app/backups'.DIRECTORY_SEPARATOR.'_restore_'.now()->format('Y-m-d_His'));
        if (! mkdir($workDir, 0755, true) && ! is_dir($workDir)) {
            throw new RuntimeException(__('Temporary restore directory could not be created.'));
        }

        try {
            if (! $this->extractZip($realZip, $workDir)) {
                throw new RuntimeException(__('Backup ZIP could not be extracted.'));
            }

            $databaseRestored = false;
            $publicStorageRestored = false;

            $sqlPath = $workDir.DIRECTORY_SEPARATOR.'database.sql';
            $sqlitePath = $workDir.DIRECTORY_SEPARATOR.'database.sqlite';

            if (is_file($sqlPath)) {
                if (config('database.default') !== 'mysql') {
                    throw new RuntimeException(__('Backup contains MySQL data but this installation uses :driver.', [
                        'driver' => (string) config('database.default'),
                    ]));
                }
                $this->restoreMysql($sqlPath);
                $databaseRestored = true;
            } elseif (is_file($sqlitePath)) {
                $this->restoreSqlite($sqlitePath);
                $databaseRestored = true;
            } else {
                throw new RuntimeException(__('No database dump found in backup (expected database.sql or database.sqlite).'));
            }

            $publicStorageSource = $workDir.DIRECTORY_SEPARATOR.'public_storage';
            if (is_dir($publicStorageSource)) {
                $this->restorePublicStorage($publicStorageSource);
                $publicStorageRestored = true;
            }

            Log::info('Application backup restored', [
                'source' => basename($realZip),
                'database' => $databaseRestored,
                'public_storage' => $publicStorageRestored,
                'safety_backup' => $safetyBackup !== null ? basename($safetyBackup) : null,
            ]);

            return [
                'database' => $databaseRestored,
                'public_storage' => $publicStorageRestored,
                'safety_backup' => $safetyBackup,
            ];
        } finally {
            $this->deleteDirectory($workDir);
        }
    }

    /**
     * @throws RuntimeException
     */
    public function resolveExistingBackupPath(string $basename): string
    {
        if (! preg_match('/^backup_[0-9]{4}-[0-9]{2}-[0-9]{2}_[0-9]{6}\.zip$/', $basename)) {
            throw new RuntimeException(__('Invalid backup file name.'));
        }

        $backupDir = realpath(storage_path('app/backups'));
        if ($backupDir === false) {
            throw new RuntimeException(__('Backup directory not found.'));
        }

        $path = $backupDir.DIRECTORY_SEPARATOR.$basename;
        $realPath = realpath($path);
        if ($realPath === false || ! is_file($realPath)) {
            throw new RuntimeException(__('Backup file not found.'));
        }

        if (! str_starts_with($realPath, $backupDir.DIRECTORY_SEPARATOR)) {
            throw new RuntimeException(__('Invalid backup file path.'));
        }

        return $realPath;
    }

    private function extractZip(string $zipPath, string $destination): bool
    {
        $zip = new ZipArchive;
        if ($zip->open($zipPath) !== true) {
            return false;
        }

        if (! is_dir($destination) && ! mkdir($destination, 0755, true) && ! is_dir($destination)) {
            $zip->close();

            return false;
        }

        $extracted = $zip->extractTo($destination);
        $zip->close();

        return $extracted;
    }

    /**
     * @throws RuntimeException
     */
    private function restoreMysql(string $sqlPath): void
    {
        $c = config('database.connections.mysql');
        $password = (string) ($c['password'] ?? '');
        $user = $c['username'];
        $host = $c['host'];
        $port = $c['port'] ?? '3306';
        $database = $c['database'];

        $lastError = '';

        foreach ($this->mysqlPathCandidates() as $bin) {
            if (! $this->isRunnableMysqlPath($bin)) {
                continue;
            }

            $cmd = [
                $bin,
                '--host='.$host,
                '--port='.$port,
                '--user='.$user,
                '--default-character-set=utf8mb4',
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

            $process = new Process($cmd, base_path(), $processEnv, file_get_contents($sqlPath), 3600);
            $process->run();

            if ($process->isSuccessful()) {
                return;
            }

            $err = trim($process->getErrorOutput().$process->getOutput());
            $lastError = $err !== '' ? $err : 'Unknown error (exit '.$process->getExitCode().').';

            if ($this->looksLikeMissingExecutable($err, $process->getExitCode())) {
                continue;
            }

            throw new RuntimeException(__('mysql restore failed: :error', ['error' => $lastError]));
        }

        $hint = PHP_OS_FAMILY === 'Windows'
            ? ' BACKUP_MYSQL_PATH="C:\\xampp\\mysql\\bin\\mysql.exe"'
            : '';

        throw new RuntimeException(__('mysql restore failed: :error. Set :hint in .env if needed.', [
            'error' => $lastError !== '' ? $lastError : __('mysql client not found'),
            'hint' => 'BACKUP_MYSQL_PATH'.$hint,
        ]));
    }

    /**
     * @throws RuntimeException
     */
    private function restoreSqlite(string $sqliteBackupPath): void
    {
        $driver = config('database.default');
        if ($driver !== 'sqlite') {
            throw new RuntimeException(__('Backup contains SQLite data but this installation uses :driver.', [
                'driver' => (string) $driver,
            ]));
        }

        $dbFile = (string) config('database.connections.sqlite.database');
        if ($dbFile === '' || $dbFile === ':memory:') {
            throw new RuntimeException(__('SQLite database path is not configured.'));
        }

        $dbDir = dirname($dbFile);
        if (! is_dir($dbDir) && ! mkdir($dbDir, 0755, true) && ! is_dir($dbDir)) {
            throw new RuntimeException(__('SQLite database directory could not be created.'));
        }

        DB::disconnect();

        if (is_file($dbFile)) {
            @unlink($dbFile);
        }

        if (! copy($sqliteBackupPath, $dbFile)) {
            throw new RuntimeException(__('SQLite database could not be restored.'));
        }
    }

    /**
     * @throws RuntimeException
     */
    private function restorePublicStorage(string $sourceDir): void
    {
        $target = storage_path('app/public');
        if (! is_dir($target) && ! mkdir($target, 0755, true) && ! is_dir($target)) {
            throw new RuntimeException(__('Public storage directory could not be created.'));
        }

        $this->deleteDirectoryContents($target);
        $this->copyDirectory(rtrim($sourceDir, DIRECTORY_SEPARATOR), $target);
    }

    private function deleteDirectoryContents(string $dir): void
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
    }

    /**
     * @return list<string>
     */
    private function mysqlPathCandidates(): array
    {
        $configured = trim((string) config('backup.mysql_path', 'mysql'));
        $candidates = [];

        if ($configured !== '' && $configured !== 'mysql') {
            $candidates[] = $configured;
        }

        if (PHP_OS_FAMILY === 'Windows') {
            $maybeXampp = dirname(dirname(base_path())).DIRECTORY_SEPARATOR.'mysql'.DIRECTORY_SEPARATOR.'bin'.DIRECTORY_SEPARATOR.'mysql.exe';
            $candidates[] = $maybeXampp;
            $candidates[] = 'C:\\xampp\\mysql\\bin\\mysql.exe';

            $drive = getenv('SystemDrive');
            if (is_string($drive) && $drive !== '') {
                $candidates[] = rtrim($drive, '\\').'\\xampp\\mysql\\bin\\mysql.exe';
            }
        }

        $candidates[] = 'mysql';

        $out = [];
        foreach ($candidates as $path) {
            $path = str_replace('/', DIRECTORY_SEPARATOR, $path);
            if (! in_array($path, $out, true)) {
                $out[] = $path;
            }
        }

        return $out;
    }

    private function isRunnableMysqlPath(string $bin): bool
    {
        if (preg_match('/^[A-Za-z]:[\\\\\/]|^[\\\\\/]{2}/', $bin) || str_contains($bin, DIRECTORY_SEPARATOR)) {
            return is_file($bin);
        }

        return true;
    }
}
