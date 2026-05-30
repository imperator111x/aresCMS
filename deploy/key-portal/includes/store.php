<?php

declare(strict_types=1);

function keyportal_data_dir(): string
{
    return dirname(__DIR__).'/data';
}

function keyportal_store_path(): string
{
    return keyportal_data_dir().'/licenses.json';
}

/**
 * @return array{version: int, admin_password_hash: string, licenses: list<array<string, mixed>>}
 */
function keyportal_store_read(): array
{
    $path = keyportal_store_path();
    if (! is_readable($path)) {
        throw new RuntimeException('Store nicht gefunden. Bitte install.php ausführen.');
    }
    $raw = file_get_contents($path);
    if ($raw === false) {
        throw new RuntimeException('Store nicht lesbar.');
    }
    $data = json_decode($raw, true);
    if (! is_array($data) || ! isset($data['licenses'], $data['admin_password_hash'])) {
        throw new RuntimeException('Ungültige Store-Datei.');
    }
    if (! is_array($data['licenses'])) {
        $data['licenses'] = [];
    }
    $data['version'] = (int) ($data['version'] ?? 1);

    return $data;
}

/**
 * @param  array{version?: int, admin_password_hash: string, licenses: list<array<string, mixed>>}  $data
 */
function keyportal_store_write(array $data): void
{
    $dir = keyportal_data_dir();
    if (! is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    $path = keyportal_store_path();
    $tmp = $path.'.tmp';
    $data['version'] = (int) ($data['version'] ?? 1);
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    if ($json === false) {
        throw new RuntimeException('JSON-Kodierung fehlgeschlagen.');
    }
    if (file_put_contents($tmp, $json, LOCK_EX) === false) {
        throw new RuntimeException('Store konnte nicht geschrieben werden.');
    }
    if (! rename($tmp, $path)) {
        @unlink($tmp);
        throw new RuntimeException('Store konnte nicht finalisiert werden.');
    }
}

function keyportal_store_exists(): bool
{
    return is_file(keyportal_store_path());
}

/**
 * @return array<string, mixed>|null
 */
function keyportal_find_license(string $licenseKey): ?array
{
    if (! keyportal_store_exists()) {
        return null;
    }
    $data = keyportal_store_read();
    foreach ($data['licenses'] as $lic) {
        if (($lic['license_key'] ?? '') === $licenseKey) {
            return $lic;
        }
    }

    return null;
}

function keyportal_domain_allowed(array $license, string $domain): bool
{
    $domain = strtolower(trim($domain));
    foreach ($license['domains'] ?? [] as $d) {
        if (strtolower(trim((string) $d)) === $domain) {
            return true;
        }
    }

    return false;
}
