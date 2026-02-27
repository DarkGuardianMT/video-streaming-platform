<?php include __DIR__ . '/../includes/header.php'; ?>

<?php

$video = null;

$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
  try {
    
    $pdo = new PDO('mysql:host=localhost;dbname=netfish;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("SELECT * FROM videos WHERE id = ?");
    $stmt->execute([$id]);
    $video = $stmt->fetch(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    echo "<p>DB error: " . htmlspecialchars($e->getMessage()) . "</p>";
  }
}
?>


<main class="page">
  

  <?php if (!$id): ?>
    <p>Geen video id meegegeven.</p>

  <?php elseif (!$video): ?>
    <p>Video niet gevonden.</p>

  <?php else: ?>
    <h3><?php echo htmlspecialchars($video['title']); ?></h3>
    <p><?php echo htmlspecialchars($video['category'] ?? ''); ?></p>
    <p><?php 
    $date = new DateTime($video['created_at']);
    echo htmlspecialchars($date->format('d-m-Y'));
    ?></p>
    

    <video class="video-player" controls width="640">
      <source src="/video-streaming-platform/<?php echo htmlspecialchars($video['video_path']); ?>" type="video/mp4">
      Je browser ondersteunt geen video tag.
    </video>
  <?php endif; ?>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>

