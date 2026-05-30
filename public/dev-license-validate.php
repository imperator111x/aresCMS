<?php

/**
 * Nur für lokale Entwicklung (localhost / 127.0.0.1).
 * In Produktion diese Datei nicht auf den öffentlichen Server legen bzw. löschen.
 *
 * .env:
 *   CMS_LICENSE_KEY=LOCALHOST-DEV-KEY
 *   CMS_LICENSE_VALIDATE_URL=http://localhost/PFAD-ZU/public/dev-license-validate.php
 * (URL an XAMPP-Unterordner oder „php artisan serve“ anpassen)
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    echo json_encode(['valid' => false, 'message' => 'Method not allowed'], JSON_UNESCAPED_UNICODE);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw === false ? '' : $raw, true);
if (! is_array($data)) {
    http_response_code(400);
    echo json_encode(['valid' => false, 'message' => 'Invalid JSON'], JSON_UNESCAPED_UNICODE);
    exit;
}

$licenseKey = trim((string) ($data['license_key'] ?? ''));
$domain = strtolower(trim((string) ($data['domain'] ?? '')));

/** Muss derselbe Wert wie CMS_LICENSE_KEY in der lokalen .env sein */
const LOCAL_DEV_LICENSE_KEY = 'LOCALHOST-DEV-KEY';

$localDomains = ['localhost', '127.0.0.1', '[::1]', '::1'];

if ($licenseKey === '' || $domain === '') {
    echo json_encode(['valid' => false, 'message' => 'license_key or domain missing'], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($licenseKey !== LOCAL_DEV_LICENSE_KEY) {
    echo json_encode(['valid' => false, 'message' => 'Invalid key for local dev'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (! in_array($domain, $localDomains, true)) {
    echo json_encode(['valid' => false, 'message' => 'Only localhost domains allowed here'], JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode(['valid' => true], JSON_UNESCAPED_UNICODE);
