<?php
require __DIR__ . '/../includes/auth.php';
require_admin();
require_post();
require_csrf();

$id = (int)filter_var(post_string('id'), FILTER_VALIDATE_INT);
if ($id <= 0) {
  header('Location: /video-streaming-platform/admin/index.php');
  exit;
}

try {
  // Delete the record only; media can still be referenced by other records.
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
