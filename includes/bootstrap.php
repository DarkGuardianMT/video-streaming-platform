<?php
set_exception_handler(function (Throwable $error): void {
    error_log('NETFISH request failed: ' . get_class($error));
    http_response_code(500);
    exit('Er is een fout opgetreden. Probeer later opnieuw.');
});
// Load this before output, including from endpoints that do not render a header.
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_trans_sid', '0');
    ini_set('session.upload_progress.enabled', '0');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/video-streaming-platform/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function post_string(string $key): string {
    return isset($_POST[$key]) && is_string($_POST[$key]) ? $_POST[$key] : '';
}

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

function valid_csrf_token(): bool {
    $token = post_string('csrf_token');
    return $token !== '' && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

function require_csrf(): void {
    if (!valid_csrf_token()) {
        http_response_code(403);
        exit('Ongeldig formulier. Vernieuw de pagina en probeer opnieuw.');
    }
}

function require_post(): void {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        header('Allow: POST');
        http_response_code(405);
        exit('Alleen POST toegestaan.');
    }
}
