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
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "netfish";

    try{
    $pdo = new PDO('mysql:host=localhost;dbname=netfish', 'root', '');
        // set the PDO error mode to exception
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  
      } catch(PDOException $e) {
      echo "Connection failed: " . $e->getMessage();
    }
  ?>

  <?php

  $videos = [];

  try {
    $sql = "SELECT id, title, category, video_path, thumbnail_path, created_at FROM videos";
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

  


  



<?php include __DIR__ . '/includes/footer.php'; ?>
