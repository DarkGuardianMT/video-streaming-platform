<?php
require __DIR__ . '/../includes/auth.php';
require_admin();
require_once __DIR__ . '/../includes/uploads.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $title = trim(post_string('title'));
    $category = trim(post_string('category'));
    if ($title === '' || strlen($title) > 255) $errors[] = 'Titel is verplicht (maximaal 255 bytes).';
    if ($category === '' || strlen($category) > 100) $errors[] = 'Categorie is verplicht (maximaal 100 bytes).';

    if (!$errors) {
        $movedFiles = [];
        try {
            $video = $_FILES['video_file'] ?? [];
            $thumb = $_FILES['thumb_file'] ?? [];
            if (!is_array($video) || !is_array($thumb)) throw new RuntimeException('Ongeldige upload.');
            $videoExt = validate_upload($video);
            $thumbExt = validate_upload($thumb, true);
            $videoFile = upload_filename(post_string('video_name'), $videoExt);
            $thumbFile = upload_filename(post_string('thumb_name'), $thumbExt);
            $videoTarget = __DIR__ . '/../videos/' . $videoFile;
            $thumbTarget = __DIR__ . '/../images/thumbnails/' . $thumbFile;
            if (!move_uploaded_file($video['tmp_name'], $videoTarget)) throw new RuntimeException('Video opslaan mislukt.');
            $movedFiles[] = $videoTarget;
            if (!move_uploaded_file($thumb['tmp_name'], $thumbTarget)) throw new RuntimeException('Thumbnail opslaan mislukt.');
            $movedFiles[] = $thumbTarget;
            $stmt = $pdo->prepare('INSERT INTO videos (title, category, video_path, thumbnail_path, created_at) VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)');
            $stmt->execute([$title, $category, 'videos/' . $videoFile, 'thumbnails/' . $thumbFile]);
            header('Location: /video-streaming-platform/admin/index.php?msg=added');
            exit;
        } catch (PDOException $e) {
            error_log('NETFISH video insert failed.');
            $errors[] = 'Video kon niet worden opgeslagen.';
        } catch (RuntimeException $e) {
            $errors[] = $e->getMessage();
        }
        // Only paths created by this request can be removed here.
        foreach ($movedFiles as $movedFile) {
            if (is_file($movedFile)) unlink($movedFile);
        }
    }
}
include __DIR__ . '/../includes/header.php';
?>
<main class="admin">
  <header class="admin__top">
    <div>
      <h2 class="admin__title">Admin</h2>
      <p class="admin__subtitle">Video toevoegen</p>
    </div>
    <div class="admin__actions">
      <a class="btn btn--ghost" href="/video-streaming-platform/index.php">Terug naar Home</a>
      <button class="btn btn--primary" type="submit" form="videoForm">Opslaan</button>
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
      <h3>Nieuwe video</h3>
      
    </div>

    <form id="videoForm" class="form" method="post" enctype="multipart/form-data">
      <?php echo csrf_field(); ?>
      <div class="form__row">
        <div class="field">
          <label for="title">Titel</label>
          <input id="title" name="title" required>
        </div>
        <div class="field">
          <label for="category">Categorie</label>
          <input id="category" name="category" required>
        </div>
      </div>

      <div class="form__row">
        <div class="field">
          <label for="video_file">Video bestand</label>
          <input id="video_file" name="video_file" type="file" accept="video/mp4,video/webm,video/ogg" required>
          <p class="hint">Wordt opgeslagen in <code>/videos/</code></p>
        </div>
        <div class="field">
          <label for="video_name">Video bestandsnaam (zonder extensie)</label>
          <input id="video_name" name="video_name" placeholder="">
          <p class="hint"></p>
        </div>
      </div>

      <div class="form__row">
        <div class="field">
          <label for="thumb_file">Thumbnail bestand</label>
          <input id="thumb_file" name="thumb_file" type="file" accept="image/png,image/jpeg,image/webp" required>
          <p class="hint">Wordt opgeslagen in <code>/images/thumbnails/</code></p>
        </div>
        <div class="field">
          <label for="thumb_name">Thumbnail bestandsnaam (zonder extensie)</label>
          <input id="thumb_name" name="thumb_name" placeholder="">
          <p class="hint"></p>
        </div>
      </div>

      
    </form>
  </section>
</main>

<script>

function fillName(fileInputId, nameInputId) {
  const fileInput = document.getElementById(fileInputId);
  const nameInput = document.getElementById(nameInputId);

  fileInput.addEventListener('change', () => {
    const f = fileInput.files && fileInput.files[0];
    if (!f) return;
    const base = f.name.replace(/\.[^/.]+$/, ''); 
    
    if (!nameInput.value.trim()) nameInput.value = base;
  });
}

fillName('video_file', 'video_name');
fillName('thumb_file', 'thumb_name');
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>