<?php
// pages/logout.php
session_start();

// Tüm session verilerini temizle
$_SESSION = [];

// Session cookie varsa sil (daha temiz logout)
if (ini_get("session.use_cookies")) {
  $params = session_get_cookie_params();
  setcookie(session_name(), '', time() - 42000,
    $params["path"], $params["domain"],
    $params["secure"], $params["httponly"]
  );
}

// Session’ı bitir
session_destroy();

// Login sayfasına dön
header("Location: /video-streaming-platform/pages/login.php?msg=loggedout");
exit;