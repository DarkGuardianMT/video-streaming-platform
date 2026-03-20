<?php include __DIR__ . '/../includes/header.php'; ?>
<?php require __DIR__ . '/../includes/db.php'; ?>

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
    echo "<p>DB error: " . htmlspecialchars($e->getMessage()) . "</p>";
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
        <source src="/video-streaming-platform/<?php echo htmlspecialchars($video['video_path']); ?>" type="video/mp4">
        Je browser ondersteunt geen video tag.
      </video>

      <a class="back-link" href="/video-streaming-platform/index.php">← Terug naar Home</a>
    </div>
  <?php endif; ?>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>

