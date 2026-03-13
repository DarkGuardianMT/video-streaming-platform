<?php
include __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/db.php';

// Flash message (redirect sonrası)
$msg = $_GET['msg'] ?? '';
$flash = '';
if ($msg === 'added') $flash = '✅ Video succesvol toegevoegd!';
if ($msg === 'updated') $flash = '✅ Video succesvol bijgewerkt!';
if ($msg === 'deleted') $flash = '✅ Video succesvol verwijderd!';

// Videoları çek (en yeni üstte)
$videos = [];

  try {
    $sql = "SELECT id, title, category, video_path, thumbnail_path, created_at FROM videos ORDER BY created_at DESC";
    $result = $pdo->query($sql);

    if ($result->rowCount() > 0) {

      while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        $videos[] = $row;
      }

    } else {
      echo "No videos found.";
    }

  } catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
  }
  ?>

<main class="admin">
  <header class="admin__top">
    <div>
      <h2 class="admin__title">Admin Dashboard</h2>
      <p class="admin__subtitle">Overzicht van alle video's</p>
    </div>

    <div class="admin__actions">
      <a class="btn btn--ghost" href="/video-streaming-platform/index.php">Terug naar Home</a>
      <a class="btn btn--primary" href="/video-streaming-platform/admin/add_video.php"> Video toevoegen</a>
    </div>
  </header>

  <?php if ($flash): ?>
    <div class="alert <?php echo (str_starts_with($flash, '❌') ? 'alert-error' : 'alert-success'); ?>">
      <?php echo htmlspecialchars($flash); ?>
    </div>
  <?php endif; ?>

  <section class="card">
    <div class="card__head">
      <h3>Video's</h3>
      <p><?php echo count($videos); ?> items</p>
    </div>

    <?php if (empty($videos)): ?>
      <p>Geen video's gevonden. Klik op <strong>+ Video toevoegen</strong> om te starten.</p>
    <?php else: ?>
      <div class="admin-list">
        <?php foreach ($videos as $v): ?>
          <?php
            $id = (int)$v['id'];
            $title = $v['title'] ?? '';
            $cat = $v['category'] ?? '';
            $thumb = $v['thumbnail_path'] ?? '';
            $dateStr = '';
            if (!empty($v['created_at'])) {
              $d = new DateTime($v['created_at']);
              $dateStr = $d->format('d-m-Y');
            }
            $thumbUrl = "/video-streaming-platform/images/" . $thumb;
            $watchUrl = "/video-streaming-platform/pages/video.php?id=" . $id;
            $editUrl  = "/video-streaming-platform/admin/edit_video.php?id=" . $id;
          ?>

          <div class="admin-row">
            <a class="admin-row__thumb" href="<?php echo htmlspecialchars($watchUrl); ?>" title="Bekijk video">
              <img src="<?php echo htmlspecialchars($thumbUrl); ?>" alt="thumbnail">
            </a>

            <div class="admin-row__info">
              <a class="admin-row__title" href="<?php echo htmlspecialchars($watchUrl); ?>">
                <?php echo htmlspecialchars($title); ?>
              </a>
              <div class="admin-row__meta">
                <span><?php echo htmlspecialchars($cat); ?></span>
                <?php if ($dateStr): ?><span>• <?php echo htmlspecialchars($dateStr); ?></span><?php endif; ?>
              </div>
            </div>

            <div class="admin-row__actions">
              <a class="btn btn--ghost btn--edit" href="<?php echo htmlspecialchars($editUrl); ?>">Wijzigen</a>

              <form method="post" action="/video-streaming-platform/admin/delete_video.php"
                    onsubmit="return confirm('Weet je zeker dat je deze video wilt verwijderen?');">
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                <button class="btn btn--danger" type="submit">Verwijderen</button>
              </form>
            </div>
          </div>

        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>