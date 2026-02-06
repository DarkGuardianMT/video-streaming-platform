<?php
$base = '';
include __DIR__ . '/includes/header.php';
?>

<div id="zoeken">
  <h2>Welkom bij NetFish!</h2>
  <p>Bekijk onze nieuwste video`s</p>

  <form class="search" action="index.php" method="get">
    <input type="search" name="search" id="search" placeholder="Zoek videos..." />
    <button type="submit" id="zoekButton">Zoek</button>
  </form>

  <?php
  $videos = [
    ["id" => 1, "title" => "PHP Login Demo", "date" => "05-02-2026"],
    ["id" => 2, "title" => "MySQL Connect Test", "date" => "04-02-2026"],
    ["id" => 3, "title" => "React Comments UI", "date" => "03-02-2026"],
  ];
  ?>

  <section class="videos">
    <h3>Nieuwste video's</h3>

    <div class="video-grid">
      <?php foreach ($videos as $v): ?>
        <a class="video-card" href="/video-streaming-platform/pages/video.php?id=<?php echo urlencode($v["id"]); ?>">

          <div class="thumb">Thumbnail</div>
          <div class="info">
            <h4><?php echo htmlspecialchars($v["title"]); ?></h4>
            <p><?php echo htmlspecialchars($v["date"]); ?></p>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </section>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
