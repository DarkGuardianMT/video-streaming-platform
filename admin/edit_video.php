<?php
include __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/auth.php';
require_admin();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
  echo "<p>Ongeldige video id.</p>";
  include __DIR__ . '/../includes/footer.php';
  exit;
}

// 1) Videoyu çek
$stmt = $pdo->prepare("SELECT id, title, category, video_path, thumbnail_path FROM videos WHERE id = ?");
$stmt->execute([$id]);
$video = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$video) {
  echo "<p>Video niet gevonden.</p>";
  include __DIR__ . '/../includes/footer.php';
  exit;
}

$errors = [];
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = trim($_POST['title'] ?? '');
  $category = trim($_POST['category'] ?? '');
  $video_path = trim($_POST['video_path'] ?? '');
  $thumbnail_path = trim($_POST['thumbnail_path'] ?? '');

  if ($title === '') $errors[] = "Titel is verplicht.";
  if ($category === '') $errors[] = "Categorie is verplicht.";
  if ($video_path === '') $errors[] = "Video pad is verplicht.";
  if ($thumbnail_path === '') $errors[] = "Thumbnail pad is verplicht.";

  if ($video_path !== '' && !str_starts_with($video_path, 'videos/')) {
    $errors[] = "Video pad moet starten met videos/.";
  }

  if (empty($errors)) {
    $up = $pdo->prepare("
      UPDATE videos
      SET title = ?, category = ?, video_path = ?, thumbnail_path = ?
      WHERE id = ?
    ");
    $up->execute([$title, $category, $video_path, $thumbnail_path, $id]);

    header("Location: /video-streaming-platform/admin/index.php?msg=updated");
    exit;
  }

  
  $video['title'] = $title;
  $video['category'] = $category;
  $video['video_path'] = $video_path;
  $video['thumbnail_path'] = $thumbnail_path;
}
?>

<main class="admin">
  <header class="admin__top">
    <div>
      <h2 class="admin__title">Wijzigen</h2>
      <p class="admin__subtitle">Video bewerken ( <?php echo htmlspecialchars($video['title']); ?>)</p>
    </div>

    <div class="admin__actions">
      <a class="btn btn--ghost" href="/video-streaming-platform/admin/index.php">Terug</a>
      <button class="btn btn--primary" type="submit" form="editForm">Opslaan</button>
    </div>
  </header>

  <?php if (!empty($errors)): ?>
    <div class="alert alert-error">
      <ul>
        <?php foreach ($errors as $e): ?>
          <li><?php echo htmlspecialchars($e); ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <section class="card">
    <div class="card__head">
      <h3>Gegevens</h3>
      <p>Pas titel en categorie aan en klik op Opslaan.</p>
    </div>

    <form id="editForm" class="form" method="post">
      <div class="form__row">
        <div class="field">
          <label for="title">Titel</label>
          <input id="title" name="title" value="<?php echo htmlspecialchars($video['title']); ?>" required>
        </div>

        <div class="field">
          <label for="category">Categorie</label>
          <input id="category" name="category" value="<?php echo htmlspecialchars($video['category']); ?>" required>
        </div>

        <div class="field">
            <label for="video_path">Video path</label>
            <input id="video_path" name="video_path"
                    value="<?php echo htmlspecialchars($video['video_path']); ?>"
                    placeholder="videos/voorbeeld.mp4" required>
        </div>

        <div class="field">
            <label for="thumbnail_path">Thumbnail path</label>
            <input id="thumbnail_path" name="thumbnail_path"
                    value="<?php echo htmlspecialchars($video['thumbnail_path']); ?>"
                    placeholder="thumbnails/voorbeeld.jpg" required>
        </div>
      </div>
    </form>
  </section>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>