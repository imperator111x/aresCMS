<?php
/**
 * Lizenz-Endpunkt für key.luetcke.eu
 *
 * Ohne mod_rewrite: Document Root muss diese Datei enthalten als:
 *   api/validate/index.php
 * URL: https://key.luetcke.eu/api/validate/  (Slash am Ende oft nötig)
 *
 * Erwartet: POST, Content-Type: application/json
 * Body:     { "license_key": "…", "domain": "…" }
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

$licenses = [
    'LOCALHOST-DEV-KEY' => ['localhost', '127.0.0.1', '::1', '[::1]'],
    // 'DEIN-SCHLUESSEL' => ['localhost', '127.0.0.1', 'kunde.de', 'www.kunde.de'],
];

if ($licenseKey === '' || $domain === '') {
    echo json_encode(['valid' => false, 'message' => 'license_key or domain missing'], JSON_UNESCAPED_UNICODE);

    exit;
}

if (! isset($licenses[$licenseKey])) {
    echo json_encode(['valid' => false, 'message' => 'Unknown license key'], JSON_UNESCAPED_UNICODE);

    exit;
}

$allowedDomains = $licenses[$licenseKey];
if (! in_array($domain, $allowedDomains, true)) {
    echo json_encode(['valid' => false, 'message' => 'Domain not allowed for this key'], JSON_UNESCAPED_UNICODE);

    exit;
}

echo json_encode(['valid' => true], JSON_UNESCAPED_UNICODE);
