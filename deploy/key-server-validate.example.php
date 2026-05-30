<?php
/**
 * Beispiel-Endpunkt für key.luetcke.eu
 *
 * CMS-Standard-URL: https://key.luetcke.eu/api/validate  (per Rewrite auf diese Datei zeigen)
 *
 * Erwartet: POST, Content-Type: application/json
 * Body:     { "license_key": "…", "domain": "…" }
 * Antwort:  HTTP 200, { "valid": true } oder { "valid": false, "message": "…" }
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

/*
 * Hier deine echte Logik: Datenbank, JSON-Datei o. Ä.
 * Domains exakt wie das CMS sendet (ohne Port): localhost, 127.0.0.1, www.example.de, …
 *
 * Beispiel localhost (gleicher Key wie public/dev-license-validate.php zum lokalen Testen):
 */
$licenses = [
    'LOCALHOST-DEV-KEY' => ['localhost', '127.0.0.1', '::1', '[::1]'],
    // 'DEIN-SCHLUESSEL-STRING' => ['localhost', '127.0.0.1', 'kunde.de', 'www.kunde.de'],
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
