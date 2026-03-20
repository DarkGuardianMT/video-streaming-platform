<?php
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/auth.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: /video-streaming-platform/admin/index.php');
  exit;
}

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
  header('Location: /video-streaming-platform/admin/index.php');
  exit;
}

try {
  // select paths
  $stmt = $pdo->prepare("SELECT video_path, thumbnail_path FROM videos WHERE id = ?");
  $stmt->execute([$id]);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);

  // delete from db
  $del = $pdo->prepare("DELETE FROM videos WHERE id = ?");
  $del->execute([$id]);

  

  header('Location: /video-streaming-platform/admin/index.php?msg=deleted');
  exit;

} catch (PDOException $e) {
  // error return dashboard
  $msg = urlencode('error');
  header("Location: /video-streaming-platform/admin/index.php?msg=$msg");
  exit;
}