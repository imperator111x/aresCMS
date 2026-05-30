<?php

/**
 * Öffentlicher Lizenz-Endpunkt (POST JSON) – aresCMS.
 * Keine Session, keine DB – liest nur data/licenses.json
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');

require_once dirname(__DIR__).'/includes/store.php';

if (! keyportal_store_exists()) {
    http_response_code(503);
    echo json_encode(['valid' => false, 'message' => 'License server not installed'], JSON_UNESCAPED_UNICODE);
    exit;
}

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

if ($licenseKey === '' || $domain === '') {
    echo json_encode(['valid' => false, 'message' => 'license_key or domain missing'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $lic = keyportal_find_license($licenseKey);
    if (! $lic) {
        echo json_encode(['valid' => false, 'message' => 'Unknown license key'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    if (! keyportal_domain_allowed($lic, $domain)) {
        echo json_encode(['valid' => false, 'message' => 'Domain not allowed for this key'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    echo json_encode(['valid' => true], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['valid' => false, 'message' => 'Server error'], JSON_UNESCAPED_UNICODE);
}
