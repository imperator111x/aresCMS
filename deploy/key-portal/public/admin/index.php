<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2).'/includes/bootstrap.php';
keyportal_require_login();

$data = keyportal_store_read();
$licenses = $data['licenses'];

require_once dirname(__DIR__, 2).'/includes/layout.php';
keyportal_layout_start('Lizenzschlüssel', [
    ['href' => '/admin/', 'label' => 'Übersicht'],
    ['href' => '/admin/key-new.php', 'label' => 'Neuer Schlüssel'],
    ['href' => '/admin/logout.php', 'label' => 'Abmelden'],
]);
?>
<div class="card">
    <h1>Lizenzschlüssel</h1>
    <p style="color:var(--muted);font-size:0.875rem;">Validierung: <code>POST /validate-license.php</code></p>
    <?php if ($licenses === []): ?>
        <p>Noch keine Schlüssel. <a href="/admin/key-new.php" style="color:var(--accent);">Ersten Schlüssel anlegen</a></p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Schlüssel</th>
                    <th>Bezeichnung</th>
                    <th>Domains</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($licenses as $lic): ?>
                    <tr>
                        <td><code><?= htmlspecialchars((string) ($lic['license_key'] ?? ''), ENT_QUOTES, 'UTF-8') ?></code></td>
                        <td><?= htmlspecialchars((string) ($lic['label'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars(implode(', ', $lic['domains'] ?? []), ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="row-actions">
                            <a class="btn secondary" href="/admin/key-edit.php?id=<?= urlencode((string) ($lic['id'] ?? '')) ?>">Bearbeiten</a>
                            <form method="post" action="/admin/key-delete.php" style="display:inline;" onsubmit="return confirm('Schlüssel wirklich löschen?');">
                                <input type="hidden" name="_csrf" value="<?= htmlspecialchars(keyportal_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="id" value="<?= htmlspecialchars((string) ($lic['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                <button type="submit" class="danger">Löschen</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?php
keyportal_layout_end();
