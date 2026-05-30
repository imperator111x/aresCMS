<?php

namespace App\Console\Commands;

use App\Services\LicenseService;
use Illuminate\Console\Command;

class LicenseCheckCommand extends Command
{
    protected $signature = 'license:check {--clear-cache : Lizenz-Cache leeren und danach prüfen}';

    protected $description = 'Prüft CMS_LICENSE_KEY gegen den Lizenzserver (Host aus APP_URL)';

    public function handle(LicenseService $license): int
    {
        if ($this->option('clear-cache')) {
            $license->forgetCacheForCurrentConfig();
            $this->info('Lizenz-Cache geleert.');
        }

        if (! config('license.enabled', true)) {
            $this->warn('CMS_LICENSE_ENABLED=false — die HTTP-Lizenzprüfung ist deaktiviert.');

            return self::SUCCESS;
        }

        if ($license->getEffectiveKey() === '') {
            $this->error('Kein Lizenzschlüssel: weder CMS_LICENSE_KEY in der .env noch gespeicherte Lizenz (Admin-Formular /license).');

            return self::FAILURE;
        }

        if ($license->validateForCli()) {
            $this->info('Lizenz gültig (Prüfung für Host aus APP_URL).');

            return self::SUCCESS;
        }

        $this->error($license->getLastError() ?? 'Lizenz ungültig.');

        return self::FAILURE;
    }
}
