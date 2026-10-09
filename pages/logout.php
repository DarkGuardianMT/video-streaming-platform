<?php
// pages/logout.php
require_once __DIR__ . '/../includes/bootstrap.php';
require_post();
require_csrf();

// Tüm session verilerini temizle
$_SESSION = [];

// Session cookie varsa sil (daha temiz logout)
if (ini_get("session.use_cookies")) {
  $params = session_get_cookie_params();
  setcookie(session_name(), '', [
    'expires' => time() - 42000,
    'path' => $params['path'], 'domain' => $params['domain'],
    'secure' => $params['secure'], 'httponly' => $params['httponly'],
    'samesite' => $params['samesite'],
  ]);
}

// Session’ı bitir
session_destroy();

// Login sayfasına dön
header("Location: /video-streaming-platform/pages/login.php?msg=loggedout");
exit;
