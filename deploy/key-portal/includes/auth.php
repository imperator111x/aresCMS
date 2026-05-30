<?php

declare(strict_types=1);

function keyportal_csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['_csrf'];
}

function keyportal_csrf_verify(?string $token): bool
{
    return is_string($token)
        && isset($_SESSION['_csrf'])
        && hash_equals($_SESSION['_csrf'], $token);
}

function keyportal_require_login(): void
{
    if (empty($_SESSION['key_admin'])) {
        header('Location: /admin/login.php', true, 302);
        exit;
    }
}

function keyportal_login(string $password): bool
{
    if (! keyportal_store_exists()) {
        return false;
    }
    try {
        $data = keyportal_store_read();
    } catch (Throwable) {
        return false;
    }
    $hash = (string) ($data['admin_password_hash'] ?? '');
    if ($hash === '' || ! password_verify($password, $hash)) {
        return false;
    }
    session_regenerate_id(true);
    $_SESSION['key_admin'] = true;
    $_SESSION['_csrf'] = bin2hex(random_bytes(32));

    return true;
}

function keyportal_logout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}
