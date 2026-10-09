<?php require_once __DIR__ . '/../includes/bootstrap.php'; ?>
<?php require_once __DIR__ . '/../includes/media.php'; ?>
<?php require __DIR__ . '/../includes/db.php'; ?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<?php

$video = null;

$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
  try {
    
    
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("SELECT * FROM videos WHERE id = ?");
    $stmt->execute([$id]);
    $video = $stmt->fetch(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    error_log('NETFISH video query failed.');
    echo '<p>Video tijdelijk niet beschikbaar.</p>';
  }
}
?>


<main class="video-detail">
  <?php if (!$id): ?>
    <p>Geen video id meegegeven.</p>

  <?php elseif (!$video): ?>
    <p>Video niet gevonden.</p>

  <?php else: ?>
    <div class="video-card">
      <h2><?php echo htmlspecialchars($video['title']); ?></h2>

      <div class="video-meta">
        <span><?php echo htmlspecialchars($video['category'] ?? ''); ?></span>
        <span>•</span>
        <span>
          <?php
            $date = new DateTime($video['created_at']);
            echo htmlspecialchars($date->format('d-m-Y'));
          ?>
        </span>
      </div>

      <video class="video-player" controls>
        <source src="<?php echo htmlspecialchars(media_url($video['video_path'])); ?>" type="<?php echo video_mime($video['video_path']); ?>">
        Je browser ondersteunt geen video tag.
      </video>
      <?php if (resolved_media_path($video['video_path']) !== $video['video_path']): ?>
        <p>Het oorspronkelijke bestand is vervangen door een gelicentieerd demo-fragment van Big Buck Bunny.</p>
      <?php endif; ?>

      <a class="back-link" href="/video-streaming-platform/index.php">← Terug naar Home</a>
    </div>
  <?php endif; ?>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>

