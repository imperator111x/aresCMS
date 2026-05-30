<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2).'/includes/bootstrap.php';
keyportal_require_login();

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: /admin/', true, 302);
    exit;
}

if (! keyportal_csrf_verify($_POST['_csrf'] ?? null)) {
    http_response_code(403);
    echo 'CSRF ungültig.';
    exit;
}

$id = (string) ($_POST['id'] ?? '');
try {
    $data = keyportal_store_read();
    $data['licenses'] = array_values(array_filter(
        $data['licenses'],
        static fn ($row) => ($row['id'] ?? '') !== $id
    ));
    keyportal_store_write($data);
} catch (Throwable) {
    http_response_code(500);
    echo 'Löschen fehlgeschlagen.';
    exit;
}

header('Location: /admin/', true, 302);
