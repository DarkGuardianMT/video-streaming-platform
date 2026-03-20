<?php
include __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/auth.php';
require_admin();

function slugify($text) {
  $text = strtolower(trim($text));
  $text = preg_replace('/[^a-z0-9\-_]+/', '-', $text);
  $text = trim($text, '-');
  return $text ?: 'file';
}

function uniqueName($dir, $base, $ext) {
  $name = $base . '.' . $ext;
  $i = 2;
  while (file_exists($dir . $name)) {
    $name = $base . '-' . $i . '.' . $ext;
    $i++;
  }
  return $name;
}

$errors = [];
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = trim($_POST['title'] ?? '');
  $category = trim($_POST['category'] ?? '');

  $video_name = trim($_POST['video_name'] ?? '');       
  $thumb_name = trim($_POST['thumb_name'] ?? '');      

  if ($title === '') $errors[] = "Titel is verplicht.";
  if ($category === '') $errors[] = "Categorie is verplicht.";

  
  if (empty($_FILES['video_file']['name'])) $errors[] = "Video bestand is verplicht.";
  if (empty($_FILES['thumb_file']['name'])) $errors[] = "Thumbnail bestand is verplicht.";

  if (empty($errors)) {
    
    $videoDir = __DIR__ . '/../videos/';
    $thumbDir = __DIR__ . '/../images/thumbnails/';

    if (!is_dir($videoDir)) mkdir($videoDir, 0777, true);
    if (!is_dir($thumbDir)) mkdir($thumbDir, 0777, true);

    // --- VIDEO ---
    $videoTmp = $_FILES['video_file']['tmp_name'];
    $videoExt = strtolower(pathinfo($_FILES['video_file']['name'], PATHINFO_EXTENSION));

    $allowedVideo = ['mp4','webm','ogg'];
    if (!in_array($videoExt, $allowedVideo, true)) $errors[] = "Video type niet toegestaan. Alleen: mp4/webm/ogg";

    
    if ($video_name === '') {
      $video_name = pathinfo($_FILES['video_file']['name'], PATHINFO_FILENAME);
    }
    $videoBase = slugify($video_name);
    $videoFile = uniqueName($videoDir, $videoBase, $videoExt);

    // --- THUMBNAIL ---
    $thumbTmp = $_FILES['thumb_file']['tmp_name'];
    $thumbExt = strtolower(pathinfo($_FILES['thumb_file']['name'], PATHINFO_EXTENSION));

    $allowedImg = ['jpg','jpeg','png','webp'];
    if (!in_array($thumbExt, $allowedImg, true)) $errors[] = "Thumbnail type niet toegestaan. Alleen: jpg/png/webp";

    if ($thumb_name === '') {
      $thumb_name = pathinfo($_FILES['thumb_file']['name'], PATHINFO_FILENAME);
    }
    $thumbBase = slugify($thumb_name);
    $thumbFile = uniqueName($thumbDir, $thumbBase, $thumbExt);

    if (empty($errors)) {
      
      if (!move_uploaded_file($videoTmp, $videoDir . $videoFile)) $errors[] = "Upload video mislukt.";
      if (!move_uploaded_file($thumbTmp, $thumbDir . $thumbFile)) $errors[] = "Upload thumbnail mislukt.";

      if (empty($errors)) {
        
        $video_path = 'videos/' . $videoFile;
        $thumbnail_path = 'thumbnails/' . $thumbFile; 

        $stmt = $pdo->prepare("
          INSERT INTO videos (title, category, video_path, thumbnail_path, created_at)
          VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$title, $category, $video_path, $thumbnail_path]);

        $success = "✅ Video succesvol toegevoegd!";
      }
    }
  }
}
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

  <?php if ($success): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
  <?php endif; ?>

  <section class="card">
    <div class="card__head">
      <h3>Nieuwe video</h3>
      
    </div>

    <form id="videoForm" class="form" method="post" enctype="multipart/form-data">
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