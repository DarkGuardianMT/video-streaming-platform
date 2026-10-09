<?php
require_once __DIR__ . '/bootstrap.php';

function is_logged_in(): bool {
  return !empty($_SESSION['user_id']);
}

function is_admin(): bool {
  return !empty($_SESSION['isAdmin']) && (int)$_SESSION['isAdmin'] === 1;
}

function require_login(): void {
  if (!is_logged_in()) {
    header("Location: /video-streaming-platform/pages/login.php");
    exit;
  }
}

function require_admin(): void {
  if (!is_logged_in()) {
    header("Location: /video-streaming-platform/index.php");
    exit;
  }
  // Recheck the database role so a revoked admin session cannot keep access.
  global $pdo;
  require_once __DIR__ . '/db.php';
  $stmt = $pdo->prepare('SELECT isAdmin FROM users WHERE id = ?');
  $stmt->execute([(int)$_SESSION['user_id']]);
  $role = $stmt->fetchColumn();
  $_SESSION['isAdmin'] = $role !== false ? (int)$role : 0;
  if (!is_admin()) {
    http_response_code(403);
    exit('Geen toegang.');
  }
}
