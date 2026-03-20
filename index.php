<?php
$base = '';
include __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/db.php'; 
?>

<main>

<?php $search = trim($_GET['search'] ?? '');?>
  <div id="zoeken">
    <h2>Welkom bij NetFish!</h2>
    <p>Bekijk onze nieuwste video's</p>


    <form class="search" action="index.php" method="get">
      <input type="search" name="search" id="search"
       value = "<?php echo htmlspecialchars($search); ?>"
       placeholder="Zoek videos..."
       />
      <button type="submit" id="zoekButton">Zoek</button>
    </form>
  </div>

  

  

  <?php
    $videos = [];
    

    try {
      if ($search !== '') {
        $stmt = $pdo->prepare("
          SELECT id, title, category, video_path, thumbnail_path, created_at
          FROM videos
          WHERE title LIKE ? OR category LIKE ?
          ORDER BY created_at DESC, id DESC
        ");
        $stmt->execute(['%' . $search . '%', '%' . $search . '%']);
      } else {
        $stmt = $pdo->query("
          SELECT id, title, category, video_path, thumbnail_path, created_at
          FROM videos
          ORDER BY created_at DESC, id DESC
        ");
      }

      while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $videos[] = $row;
      }

      if (empty($videos)) {
        echo ($search !== '') ? "Geen resultaten." : "No videos found.";
      }

    } catch (PDOException $e) {
      echo "Error: " . $e->getMessage();
    }
  ?>

  <?php if (!empty($videos)): ?>
  <section class="videos">
    <h3>Nieuwste video's</h3>

    <div class="video-grid">
      <?php foreach ($videos as $v): ?>
        <a class="video-card" href="/video-streaming-platform/pages/video.php?id=<?php echo (int)$v["id"]; ?>">
          <div class="thumb">
            <img src="/video-streaming-platform/images/<?php echo htmlspecialchars($v['thumbnail_path']); ?>" alt="thumbnail" >
          </div>
          <div class="info">
            <h4><?php echo htmlspecialchars($v["title"]); ?></h4>
            <p><?php echo htmlspecialchars($v["category"]);?></p>
            <p><?php 
              $date = new DateTime($v['created_at']);
              echo htmlspecialchars($date->format('d-m-Y'));
            ?></p>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>
</main>
  


  



<?php include __DIR__ . '/includes/footer.php'; ?>
