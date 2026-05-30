<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2).'/includes/bootstrap.php';
keyportal_require_login();

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
            $error = 'Mindestens eine Domain angeben (z. B. localhost oder beispiel.de).';
        } else {
            try {
                $data = keyportal_store_read();
                $newKey = strtoupper(bin2hex(random_bytes(16)));
                $data['licenses'][] = [
                    'id' => bin2hex(random_bytes(8)),
                    'license_key' => $newKey,
                    'label' => $label,
                    'domains' => $domains,
                    'created_at' => (new DateTimeImmutable('now', new DateTimeZone('UTC')))->format(DateTimeInterface::ATOM),
                ];
                keyportal_store_write($data);
                header('Location: /admin/', true, 302);
                exit;
            } catch (Throwable $e) {
                $error = $e->getMessage();
            }
        }
    }
}

require_once dirname(__DIR__, 2).'/includes/layout.php';
keyportal_layout_start('Neuer Schlüssel', [
    ['href' => '/admin/', 'label' => 'Übersicht'],
    ['href' => '/admin/key-new.php', 'label' => 'Neuer Schlüssel'],
    ['href' => '/admin/logout.php', 'label' => 'Abmelden'],
]);
?>
<div class="card">
    <h1>Neuer Lizenzschlüssel</h1>
    <p style="color:var(--muted);font-size:0.875rem;">Der Schlüssel wird automatisch erzeugt (32 Zeichen Hex).</p>
    <?php if ($error !== ''): ?><p class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
    <form method="post">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars(keyportal_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
        <label>Bezeichnung (optional)</label>
        <input type="text" name="label" value="<?= htmlspecialchars((string) ($_POST['label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="z. B. Kunde Müller">
        <label>Domains (eine pro Zeile)</label>
        <textarea name="domains" placeholder="localhost&#10;127.0.0.1&#10;www.beispiel.de"><?= htmlspecialchars((string) ($_POST['domains'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
        <p style="margin-top:1rem;"><button type="submit">Anlegen</button> <a href="/admin/" class="btn secondary" style="margin-left:0.5rem;">Abbrechen</a></p>
    </form>
</div>
<?php
keyportal_layout_end();
