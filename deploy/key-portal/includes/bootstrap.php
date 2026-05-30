<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    $secure = (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443);
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_name('keyportal_sid');
    session_start();
}

require_once __DIR__.'/store.php';
require_once __DIR__.'/auth.php';
