<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2).'/includes/bootstrap.php';
keyportal_require_login();

$id = (string) ($_GET['id'] ?? $_POST['id'] ?? '');
$data = keyportal_store_read();
$idx = null;
$lic = null;
foreach ($data['licenses'] as $i => $row) {
    if (($row['id'] ?? '') === $id) {
        $idx = $i;
        $lic = $row;
        break;
    }
}
if ($lic === null) {
    http_response_code(404);
    echo 'Eintrag nicht gefunden.';
    exit;
}

$error = '';
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    if (! keyportal_csrf_verify($_POST['_csrf'] ?? null)) {
        $error = 'Ungültiges Formular.';
    } else {
        $label = trim((string) ($_POST['label'] ?? ''));
        $domainsText = (string) ($_POST['domains'] ?? '');
        $lines = preg_split('/\r\n|\r|\n/', $domainsText);
        $domains = [];
        foreach ($lines as $line) {
            $d = strtolower(trim($line));
            if ($d !== '') {
                $domains[] = $d;
            }
        }
        $domains = array_values(array_unique($domains));
        if ($domains === []) {
            $error = 'Mindestens eine Domain angeben.';
        } else {
            try {
                $data = keyportal_store_read();
                foreach ($data['licenses'] as $i => $row) {
                    if (($row['id'] ?? '') === $id) {
                        $data['licenses'][$i]['label'] = $label;
                        $data['licenses'][$i]['domains'] = $domains;
                        break;
                    }
                }
                keyportal_store_write($data);
                header('Location: /admin/', true, 302);
                exit;
            } catch (Throwable $e) {
                $error = $e->getMessage();
            }
        }
    }
}

$domainsText = implode("\n", $lic['domains'] ?? []);

require_once dirname(__DIR__, 2).'/includes/layout.php';
keyportal_layout_start('Schlüssel bearbeiten', [
    ['href' => '/admin/', 'label' => 'Übersicht'],
    ['href' => '/admin/key-new.php', 'label' => 'Neuer Schlüssel'],
    ['href' => '/admin/logout.php', 'label' => 'Abmelden'],
]);
?>
<div class="card">
    <h1>Schlüssel bearbeiten</h1>
    <p>Schlüssel: <code><?= htmlspecialchars((string) ($lic['license_key'] ?? ''), ENT_QUOTES, 'UTF-8') ?></code> (nicht änderbar)</p>
    <?php if ($error !== ''): ?><p class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
    <form method="post">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars(keyportal_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="id" value="<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>">
        <label>Bezeichnung</label>
        <input type="text" name="label" value="<?= htmlspecialchars((string) ($lic['label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        <label>Domains (eine pro Zeile)</label>
        <textarea name="domains"><?= htmlspecialchars($domainsText, ENT_QUOTES, 'UTF-8') ?></textarea>
        <p style="margin-top:1rem;"><button type="submit">Speichern</button> <a href="/admin/" class="btn secondary">Zurück</a></p>
    </form>
</div>
<?php
keyportal_layout_end();
