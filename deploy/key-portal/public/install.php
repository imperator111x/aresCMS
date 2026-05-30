<?php

/**
 * Einmalig ausführen, danach auf dem Server löschen.
 */

declare(strict_types=1);

require_once dirname(__DIR__).'/includes/store.php';

if (keyportal_store_exists()) {
    header('Content-Type: text/html; charset=UTF-8');
    echo '<p>Installation bereits vorhanden. Diese Datei (<code>install.php</code>) bitte löschen.</p>';
    echo '<p><a href="/admin/login.php">Zum Login</a></p>';
    exit;
}

$error = '';
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $p1 = (string) ($_POST['password'] ?? '');
    $p2 = (string) ($_POST['password2'] ?? '');
    if (strlen($p1) < 10) {
        $error = 'Passwort mindestens 10 Zeichen.';
    } elseif ($p1 !== $p2) {
        $error = 'Passwörter stimmen nicht überein.';
    } else {
        try {
            keyportal_store_write([
                'version' => 1,
                'admin_password_hash' => password_hash($p1, PASSWORD_DEFAULT),
                'licenses' => [],
            ]);
            header('Location: /admin/login.php?installed=1', true, 302);
            exit;
        } catch (Throwable $e) {
            $error = 'Schreibfehler: '.$e->getMessage();
        }
    }
}

header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Installation – Key-Portal</title>
    <style>
        body { font-family: system-ui, sans-serif; background: #0f172a; color: #e2e8f0; max-width: 24rem; margin: 3rem auto; padding: 1rem; }
        .card { background: #1e293b; border: 1px solid #334155; border-radius: 0.75rem; padding: 1.5rem; }
        label { display: block; margin-top: 1rem; font-size: 0.875rem; color: #94a3b8; }
        input { width: 100%; padding: 0.5rem; margin-top: 0.25rem; border-radius: 0.375rem; border: 1px solid #475569; background: #0f172a; color: #fff; }
        button { margin-top: 1.25rem; width: 100%; padding: 0.65rem; border: none; border-radius: 0.375rem; background: #6366f1; color: #fff; font-weight: 600; cursor: pointer; }
        .err { color: #f87171; margin-top: 1rem; font-size: 0.875rem; }
    </style>
</head>
<body>
<div class="card">
    <h1 style="margin-top:0;font-size:1.1rem;">Key-Portal einrichten</h1>
    <p style="color:#94a3b8;font-size:0.875rem;">Legt <code>data/licenses.json</code> an. Danach diese Datei löschen.</p>
    <?php if ($error !== ''): ?><p class="err"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
    <form method="post">
        <label>Admin-Passwort (min. 10 Zeichen)</label>
        <input type="password" name="password" required minlength="10" autocomplete="new-password">
        <label>Wiederholen</label>
        <input type="password" name="password2" required minlength="10" autocomplete="new-password">
        <button type="submit">Installieren</button>
    </form>
</div>
</body>
</html>
