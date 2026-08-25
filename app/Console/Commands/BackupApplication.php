<?php

namespace App\Console\Commands;

use App\Services\ApplicationBackupService;
use Illuminate\Console\Command;
use RuntimeException;

class BackupApplication extends Command
{
    protected $signature = 'backup:application';

    protected $description = 'Erstellt ein ZIP mit Datenbank-Dump und optional storage/app/public (Uploads).';

    public function handle(ApplicationBackupService $backupService): int
    {
        try {
            $path = $backupService->create();
            $this->info('Backup erstellt: '.$path);

            return self::SUCCESS;
        } catch (RuntimeException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
