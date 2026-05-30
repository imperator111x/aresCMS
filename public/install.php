<?php

/**
 * Webbasierter Installer für Shared Hosting (ohne SSH).
 * Nach erfolgreicher Installation: diese Datei vom Server löschen.
 *
 * Aufruf: https://deine-domain.de/install.php
 */

declare(strict_types=1);

$basePath = dirname(__DIR__);
$envPath = $basePath.'/.env';
$envExamplePath = $basePath.'/.env.example';
$lockPath = $basePath.'/storage/app/installer.lock';

session_start();

header('Content-Type: text/html; charset=UTF-8');

if (is_file($lockPath)) {
    http_response_code(403);
    echo '<!DOCTYPE html><html lang="de"><head><meta charset="utf-8"><title>Installer</title></head><body>';
    echo '<p>Die Installation ist abgeschlossen. Der Installer ist gesperrt.</p>';
    echo '<p>Bitte lösche <code>public/install.php</code> auf dem Server.</p>';
    echo '<p><a href="/">Zur Startseite</a></p></body></html>';
    exit;
}

if (! is_file($basePath.'/vendor/autoload.php')) {
    http_response_code(503);
    echo '<p>Ordner <code>vendor/</code> fehlt. Auf deinem PC <code>composer install --no-dev --optimize-autoloader</code> ausführen und das Projekt erneut hochladen.</p>';
    exit;
}

if (! is_readable($envExamplePath)) {
    http_response_code(500);
    echo '<p>Datei <code>.env.example</code> fehlt.</p>';
    exit;
}

$requiredExtensions = ['openssl', 'pdo', 'pdo_mysql', 'mbstring', 'tokenizer', 'xml', 'ctype', 'json', 'bcmath', 'fileinfo'];
$missingExt = array_filter($requiredExtensions, static fn ($e) => ! extension_loaded($e));

function h(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function env_escape_value(string $value): string
{
    if ($value === '') {
        return '""';
    }
    if (preg_match('/[\s#"\'\\\\]/', $value)) {
        return '"'.str_replace(['\\', '"'], ['\\\\', '\\"'], $value).'"';
    }

    return $value;
}

function build_env_from_example(string $examplePath, array $replacements): string
{
    $raw = file_get_contents($examplePath);
    if ($raw === false) {
        return '';
    }
    if (str_starts_with($raw, "\xEF\xBB\xBF")) {
        $raw = substr($raw, 3);
    }
    $raw = str_replace(["\r\n", "\r"], "\n", $raw);
    $lines = $raw === '' ? [] : explode("\n", $raw);
    if ($lines !== [] && $lines[array_key_last($lines)] === '') {
        array_pop($lines);
    }
    $out = [];
    $replaced = array_fill_keys(array_keys($replacements), false);

    foreach ($lines as $line) {
        if (preg_match('/^([A-Z][A-Z0-9_]*)=(.*)$/', $line, $m)) {
            $key = $m[1];
            if (array_key_exists($key, $replacements)) {
                $out[] = $key.'='.env_escape_value($replacements[$key]);
                $replaced[$key] = true;

                continue;
            }
        }
        $out[] = $line;
    }

    foreach ($replacements as $key => $val) {
        if (empty($replaced[$key])) {
            $out[] = $key.'='.env_escape_value($val);
        }
    }

    return implode("\n", $out)."\n";
}

// —— Schritt 2: Migrationen (nach .env) ——
if (isset($_GET['step']) && $_GET['step'] === 'run') {
    if (empty($_SESSION['install_pending_run'])) {
        http_response_code(403);
        echo '<p>Ungültiger Aufruf. Bitte Installation von vorn starten.</p>';
        exit;
    }

    if (! is_file($envPath)) {
        http_response_code(500);
        echo '<p>Datei <code>.env</code> fehlt.</p>';
        exit;
    }

    $frameworkProbe = $basePath.'/vendor/laravel/framework/src/Illuminate/Session/Console/SessionTableCommand.php';
    if (! is_file($frameworkProbe)) {
        http_response_code(503);
        echo '<!DOCTYPE html><html lang="de"><head><meta charset="utf-8"><title>Installer</title></head><body>';
        echo '<p><strong>Ordner <code>vendor/</code> ist unvollständig.</strong> Es fehlen Framework-Dateien aus <code>vendor/</code> (z. B. <code>SessionTableCommand.php</code>). ';
        echo 'ZIP/FTP-Upload oft abgebrochen oder Ausschluss von Unterordnern.</p>';
        echo '<p>Lokal <code>composer install --no-dev --optimize-autoloader</code> ausführen und den kompletten Ordner <code>vendor/</code> erneut hochladen, oder auf dem Server per SSH im Projektroot <code>composer install --no-dev --optimize-autoloader</code>.</p>';
        echo '</body></html>';
        exit;
    }

    foreach (['services.php', 'packages.php', 'config.php'] as $cacheName) {
        $cacheFile = $basePath.'/bootstrap/cache/'.$cacheName;
        if (is_file($cacheFile)) {
            @unlink($cacheFile);
        }
    }

    try {
        require_once $basePath.'/vendor/autoload.php';
        $app = require_once $basePath.'/bootstrap/app.php';
        /** @var \Illuminate\Contracts\Console\Kernel $kernel */
        $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
        $kernel->bootstrap();

        $kernel->call('migrate', ['--force' => true]);

        $useSeeder = ! empty($_SESSION['install_use_seeder']);
        if ($useSeeder) {
            $kernel->call('db:seed', ['--class' => 'Database\\Seeders\\AdminSeeder', '--force' => true]);
        } else {
            $email = (string) ($_SESSION['install_admin_email'] ?? 'admin@example.com');
            $name = (string) ($_SESSION['install_admin_name'] ?? 'Admin');
            $plain = (string) ($_SESSION['install_admin_password'] ?? '');
            $userClass = \App\Models\User::class;
            $userClass::query()->where('email', $email)->delete();
            $user = $userClass::create([
                'name' => $name,
                'email' => $email,
                'password' => $plain,
                'is_admin' => true,
            ]);
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        $kernel->call('storage:link', ['--force' => true]);
        $kernel->call('config:clear');
        $kernel->call('route:clear');
        $kernel->call('view:clear');

        if (! is_dir(dirname($lockPath))) {
            mkdir(dirname($lockPath), 0755, true);
        }
        file_put_contents($lockPath, date('c'));

        unset($_SESSION['install_pending_run'], $_SESSION['install_use_seeder'], $_SESSION['install_admin_email'], $_SESSION['install_admin_name'], $_SESSION['install_admin_password'], $_SESSION['install_csrf']);

        echo '<!DOCTYPE html><html lang="de"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Installation fertig</title>';
        echo '<style>body{font-family:system-ui,sans-serif;background:#0f172a;color:#e2e8f0;max-width:36rem;margin:2rem auto;padding:1rem;} .ok{background:#14532d;border:1px solid #166534;padding:1rem;border-radius:.5rem;}</style></head><body>';
        echo '<div class="ok"><h1 style="margin-top:0;">Installation abgeschlossen</h1>';
        echo '<p>Bitte <strong>public/install.php</strong> jetzt auf dem Server <strong>löschen</strong>.</p>';
        echo '<p><a href="/" style="color:#93c5fd;">Zur Website</a> · <a href="/admin" style="color:#93c5fd;">Zum Admin</a></p></div></body></html>';
    } catch (Throwable $e) {
        http_response_code(500);
        echo '<!DOCTYPE html><html lang="de"><head><meta charset="utf-8"><title>Fehler</title></head><body>';
        echo '<p><strong>Fehler bei der Installation:</strong></p><pre style="white-space:pre-wrap;">'.h($e->getMessage()).'</pre>';
        echo '<p>Prüfe die Datenbank-Zugangsdaten in der <code>.env</code>. Bei Bedarf <code>.env</code> löschen und den Installer erneut starten.</p>';
        if (str_contains($e->getMessage(), 'SessionTableCommand') || str_contains($e->getMessage(), 'not found')) {
            echo '<p>Hinweis: Meldungen wie <code>Class … not found</code> deuten meist auf ein <strong>unvollständiges <code>vendor/</code></strong> hin – Ordner vollständig neu hochladen oder <code>composer install --no-dev --optimize-autoloader</code> auf dem Server.</p>';
        }
        echo '</body></html>';
    }
    exit;
}

// —— Schritt 1: Formular POST → .env schreiben ——
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['_csrf'] ?? '';
    if (! is_string($token) || empty($_SESSION['install_csrf']) || ! hash_equals($_SESSION['install_csrf'], $token)) {
        http_response_code(403);
        echo '<p>Ungültiges Formular (CSRF).</p>';
        exit;
    }

    $appUrl = rtrim(trim((string) ($_POST['app_url'] ?? '')), '/');
    $dbHost = trim((string) ($_POST['db_host'] ?? '127.0.0.1'));
    $dbPort = trim((string) ($_POST['db_port'] ?? '3306'));
    $dbName = trim((string) ($_POST['db_database'] ?? ''));
    $dbUser = trim((string) ($_POST['db_username'] ?? ''));
    $dbPass = (string) ($_POST['db_password'] ?? '');
    $appDebug = ! empty($_POST['app_debug']) ? 'true' : 'false';
    $adminEmail = trim((string) ($_POST['admin_email'] ?? 'admin@example.com'));
    $adminName = trim((string) ($_POST['admin_name'] ?? 'Admin'));
    $adminPassword = (string) ($_POST['admin_password'] ?? '');
    $licenseKey = trim((string) ($_POST['cms_license_key'] ?? ''));
    $licenseUrl = trim((string) ($_POST['cms_license_validate_url'] ?? 'https://key.luetcke.eu/validate-license.php'));

    $errors = [];
    if ($appUrl === '' || ! filter_var($appUrl, FILTER_VALIDATE_URL)) {
        $errors[] = 'Gültige APP_URL (https://…) erforderlich.';
    }
    if ($dbName === '' || $dbUser === '') {
        $errors[] = 'Datenbankname und Benutzername erforderlich.';
    }
    if ($adminEmail === '' || ! filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Gültige Admin-E-Mail erforderlich.';
    }
    if ($adminPassword !== '' && strlen($adminPassword) < 10) {
        $errors[] = 'Admin-Passwort mindestens 10 Zeichen oder leer lassen für Standard (password).';
    }

    if ($errors !== []) {
        $_SESSION['install_errors'] = $errors;
        $_SESSION['install_old'] = $_POST;
        header('Location: install.php', true, 303);
        exit;
    }

    $appKey = 'base64:'.base64_encode(random_bytes(32));

    $replacements = [
        'APP_NAME' => 'aresCMS',
        'APP_ENV' => 'production',
        'APP_KEY' => $appKey,
        'APP_DEBUG' => $appDebug,
        'APP_URL' => $appUrl,
        'DB_CONNECTION' => 'mysql',
        'DB_HOST' => $dbHost,
        'DB_PORT' => $dbPort,
        'DB_DATABASE' => $dbName,
        'DB_USERNAME' => $dbUser,
        'DB_PASSWORD' => $dbPass,
        'CMS_LICENSE_KEY' => $licenseKey,
        'CMS_LICENSE_VALIDATE_URL' => $licenseUrl,
    ];

    $envContent = build_env_from_example($envExamplePath, $replacements);

    if (is_file($envPath) && empty($_POST['overwrite_env'])) {
        $_SESSION['install_errors'] = ['Die Datei .env existiert bereits. Aktiviere „Vorhandene .env überschreiben“ oder lösche die Datei per FTP.'];
        $_SESSION['install_old'] = $_POST;
        header('Location: install.php', true, 303);
        exit;
    }

    if (file_put_contents($envPath, $envContent) === false) {
        http_response_code(500);
        echo '<p><code>.env</code> konnte nicht geschrieben werden. Ordnerrechte prüfen.</p>';
        exit;
    }

    $_SESSION['install_pending_run'] = true;
    $_SESSION['install_use_seeder'] = ($adminPassword === '');
    $_SESSION['install_admin_email'] = $adminEmail;
    $_SESSION['install_admin_name'] = $adminName;
    $_SESSION['install_admin_password'] = $adminPassword;

    header('Location: install.php?step=run', true, 303);
    exit;
}

// —— GET: Formular ——
$_SESSION['install_csrf'] = bin2hex(random_bytes(32));
$errors = $_SESSION['install_errors'] ?? [];
$old = $_SESSION['install_old'] ?? [];
unset($_SESSION['install_errors'], $_SESSION['install_old']);

$scheme = (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$suggestedUrl = $scheme.'://'.($_SERVER['HTTP_HOST'] ?? 'localhost');
if (! empty($_SERVER['REQUEST_URI'])) {
    $script = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    if ($script !== '' && $script !== '/') {
        $suggestedUrl = rtrim($suggestedUrl.$script, '/');
    }
}

?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>aresCMS installieren</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: system-ui, sans-serif; background: #0f172a; color: #e2e8f0; margin: 0; padding: 1.5rem; }
        .wrap { max-width: 32rem; margin: 0 auto; }
        h1 { font-size: 1.25rem; }
        label { display: block; margin-top: 1rem; font-size: 0.875rem; color: #94a3b8; }
        input[type="text"], input[type="password"], input[type="url"] {
            width: 100%; padding: 0.5rem 0.75rem; border-radius: 0.375rem; border: 1px solid #475569;
            background: #1e293b; color: #f8fafc; margin-top: 0.25rem;
        }
        .hint { font-size: 0.8125rem; color: #64748b; margin-top: 0.25rem; }
        .err { background: #7f1d1d; border: 1px solid #991b1b; padding: 0.75rem; border-radius: 0.5rem; margin: 1rem 0; font-size: 0.875rem; }
        button { margin-top: 1.25rem; padding: 0.65rem 1.25rem; border: none; border-radius: 0.375rem; background: #6366f1; color: #fff; font-weight: 600; cursor: pointer; }
        .check { margin-top: 1rem; display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; }
        code { background: #1e293b; padding: 0.1rem 0.35rem; border-radius: 0.25rem; }
        ul.req { font-size: 0.875rem; color: #94a3b8; }
    </style>
</head>
<body>
<div class="wrap">
    <h1>aresCMS – Installation</h1>
    <p style="color:#94a3b8;font-size:0.875rem;">Voraussetzung: <code>vendor/</code> per <code>composer install --no-dev</code> lokal erzeugt und mit hochgeladen. Frontend: lokal <code>npm run build</code>, Ordner <code>public/build/</code> mit hochladen.</p>

    <?php if ($missingExt !== []): ?>
        <div class="err">Fehlende PHP-Erweiterungen: <?= h(implode(', ', $missingExt)) ?></div>
    <?php endif; ?>

    <?php if (! is_writable($basePath.'/storage') || ! is_writable($basePath.'/bootstrap/cache')): ?>
        <div class="err">Ordner <code>storage/</code> und <code>bootstrap/cache/</code> müssen für den Webserver beschreibbar sein.</div>
    <?php endif; ?>

    <?php foreach ($errors as $e): ?>
        <div class="err"><?= h($e) ?></div>
    <?php endforeach; ?>

    <form method="post" action="install.php">
        <input type="hidden" name="_csrf" value="<?= h($_SESSION['install_csrf']) ?>">

        <label>Website-URL (APP_URL)</label>
        <input type="url" name="app_url" required value="<?= h((string) ($old['app_url'] ?? $suggestedUrl)) ?>" placeholder="https://deine-domain.de">
        <p class="hint">Exakt die URL, unter der die Seite erreichbar ist (mit https).</p>

        <label>Datenbank-Host</label>
        <input type="text" name="db_host" value="<?= h((string) ($old['db_host'] ?? '127.0.0.1')) ?>">
        <p class="hint">Auf <strong>Shared Hosting</strong> fast nie <code>localhost</code> oder <code>127.0.0.1</code> – dort steht meist <strong>Connection refused</strong>. Den exakten MySQL-Host nimmst du aus dem Hosting-Panel (z. B. Lima-City: oft <code>mysql.lima-city.de</code> oder <code>…lima-db.de</code>).</p>
        <label>Datenbank-Port</label>
        <input type="text" name="db_port" value="<?= h((string) ($old['db_port'] ?? '3306')) ?>">
        <label>Datenbank-Name</label>
        <input type="text" name="db_database" required value="<?= h((string) ($old['db_database'] ?? '')) ?>">
        <label>Datenbank-Benutzer</label>
        <input type="text" name="db_username" required value="<?= h((string) ($old['db_username'] ?? '')) ?>">
        <label>Datenbank-Passwort</label>
        <input type="password" name="db_password" value="<?= h((string) ($old['db_password'] ?? '')) ?>" autocomplete="new-password">

        <label>Admin-E-Mail</label>
        <input type="text" name="admin_email" required value="<?= h((string) ($old['admin_email'] ?? 'admin@example.com')) ?>">
        <label>Admin-Anzeigename</label>
        <input type="text" name="admin_name" value="<?= h((string) ($old['admin_name'] ?? 'Admin')) ?>">
        <label>Admin-Passwort</label>
        <input type="password" name="admin_password" value="<?= h((string) ($old['admin_password'] ?? '')) ?>" placeholder="Leer = Standard password (Seeder)" autocomplete="new-password">
        <p class="hint">Mindestens 10 Zeichen, oder leer lassen – dann Login <code>admin@example.com</code> / <code>password</code> (bitte sofort ändern).</p>

        <label>Lizenzschlüssel (optional)</label>
        <input type="text" name="cms_license_key" value="<?= h((string) ($old['cms_license_key'] ?? '')) ?>">
        <label>Lizenz-URL</label>
        <input type="url" name="cms_license_validate_url" value="<?= h((string) ($old['cms_license_validate_url'] ?? 'https://key.luetcke.eu/validate-license.php')) ?>">

        <label class="check"><input type="checkbox" name="app_debug" value="1" <?= ! empty($old['app_debug']) ? 'checked' : '' ?>> APP_DEBUG aktivieren (nur bei Problemen, danach aus)</label>

        <?php if (is_file($envPath)): ?>
            <label class="check"><input type="checkbox" name="overwrite_env" value="1" required> Vorhandene <code>.env</code> überschreiben</label>
        <?php endif; ?>

        <p><button type="submit" <?= ($missingExt !== []) ? 'disabled' : '' ?>>Installieren</button></p>
    </form>

    <p class="hint">Ausführliche Anleitung: <code>docs/INSTALLATION_WEBSPACE.md</code></p>
</div>
</body>
</html>
