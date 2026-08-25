# Sync geänderte aresCMS-Dateien nach C:\xampp\htdocs\extra (Deploy-Paket)
# Ausführen: powershell -ExecutionPolicy Bypass -File scripts\sync-to-extra.ps1

$ErrorActionPreference = 'Stop'
$src = Split-Path $PSScriptRoot -Parent
$dst = 'C:\xampp\htdocs\extra'

$files = @(
    'app\Support\LegalUrl.php',
    'app\Support\PageHeroThemes.php',
    'app\Support\PageBlockRenderer.php',
    'app\Support\PhpCliBinary.php',
    'app\helpers.php',
    'app\Services\ThemeManager.php',
    'app\Services\ApplicationBackupService.php',
    'app\Services\ApplicationHealthCheckService.php',
    'app\Services\CmsUpdateManager.php',
    'app\Console\Commands\BackupApplication.php',
    'app\Http\Controllers\Admin\OperationsController.php',
    'app\Http\Controllers\Admin\SystemUpdateController.php',
    'app\Http\Middleware\AdminMiddleware.php',
    'app\Http\Middleware\SecurityHeaders.php',
    'config\backup.php',
    'routes\web.php',
    'app\Providers\AppServiceProvider.php',
    'app\Providers\RouteServiceProvider.php',
    'app\Http\Controllers\NewsController.php',
    'routes\web.php',
    'resources\views\layouts\_public-shell.blade.php',
    'resources\views\layouts\admin.blade.php',
    'resources\views\layouts\guest.blade.php',
    'resources\views\partials\html-source-banner.blade.php',
    'resources\branding\html-source-banner.txt',
    'resources\views\errors\404.blade.php',
    'resources\views\errors\maintenance.blade.php',
    'resources\views\license\activate.blade.php',
    'resources\views\auth\maintenance-admin-login.blade.php',
    'resources\views\page\show.blade.php',
    'resources\views\team.blade.php',
    'resources\views\auth\login.blade.php',
    'resources\views\admin\operations\index.blade.php',
    'resources\views\admin\operations\index.blade.php',
    'resources\views\admin\system-update\index.blade.php',
    'config\cms.php',
    'resources\lang\de.json',
    'resources\lang\en.json',
    'public\themes\handwerk\theme.css',
    'themes\handwerk\theme.json',
    'themes\handwerk\views\layouts\app.blade.php',
    'themes\handwerk\views\news\home.blade.php',
    'themes\handwerk\views\news\index.blade.php',
    'themes\handwerk\views\news\show.blade.php',
    'themes\handwerk\views\team.blade.php',
    'themes\handwerk\views\partials\news-card.blade.php',
    'themes\handwerk\views\partials\document-wrap-open.blade.php',
    'themes\handwerk\views\partials\document-wrap-close.blade.php',
    'themes\handwerk\views\partials\auth-wrap-open.blade.php',
    'themes\handwerk\views\partials\auth-wrap-close.blade.php',
    'themes\handwerk\views\auth\login.blade.php',
    'themes\handwerk\views\legal\imprint.blade.php',
    'themes\handwerk\views\legal\privacy.blade.php',
    'themes\handwerk\views\legal\terms.blade.php'
)

if (-not (Test-Path $dst)) {
    New-Item -ItemType Directory -Path $dst -Force | Out-Null
}

$copied = 0
foreach ($rel in $files) {
    $from = Join-Path $src $rel
    if (-not (Test-Path $from)) { continue }
    $to = Join-Path $dst $rel
    $dir = Split-Path $to -Parent
    if (-not (Test-Path $dir)) { New-Item -ItemType Directory -Path $dir -Force | Out-Null }
    Copy-Item $from $to -Force
    $copied++
}

Write-Host "Sync fertig: $copied Dateien -> $dst"
