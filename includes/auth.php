<?php
// includes/auth.php

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
  if (!is_logged_in() || !is_admin()) {
    header("Location: /video-streaming-platform/index.php");
    exit;
  }
}