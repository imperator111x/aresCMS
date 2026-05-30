<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2).'/includes/bootstrap.php';

if (! empty($_SESSION['key_admin'])) {
    header('Location: /admin/', true, 302);
    exit;
}

$error = '';
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    if (! keyportal_csrf_verify($_POST['_csrf'] ?? null)) {
        $error = 'Ungültiges Formular (CSRF).';
    } elseif (keyportal_login((string) ($_POST['password'] ?? ''))) {
        header('Location: /admin/', true, 302);
        exit;
    } else {
        $error = 'Anmeldung fehlgeschlagen.';
    }
}

require_once dirname(__DIR__, 2).'/includes/layout.php';
keyportal_layout_start('Anmeldung', []);
?>
<div class="card" style="max-width:22rem;margin:2rem auto;">
    <h1>Anmeldung</h1>
    <?php if (isset($_GET['installed'])): ?>
        <p style="color:#86efac;font-size:0.875rem;">Installation OK. Bitte einloggen – und <code>install.php</code> löschen.</p>
    <?php endif; ?>
    <?php if ($error !== ''): ?><p class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
    <form method="post">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars(keyportal_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
        <label>Passwort</label>
        <input type="password" name="password" required autocomplete="current-password">
        <p style="margin-top:1rem;"><button type="submit">Einloggen</button></p>
    </form>
</div>
<?php
keyportal_layout_end();
