<?php
require __DIR__ . '/../includes/auth.php';
require_login();
include __DIR__ . '/../includes/header.php';
?>

<main class="profile">
  <div class="profile-card">
    <div class="profile-card__top">
      <h2 class="profile-title">Profiel</h2>
      <span class="profile-badge">
        <?php echo (!empty($_SESSION['isAdmin']) && (int)$_SESSION['isAdmin']===1) ? 'Admin' : 'User'; ?>
      </span>
    </div>

    <div class="profile-grid">
      <div class="profile-item">
        <small>Gebruikersnaam</small>
        <strong><?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?></strong>
      </div>

      <div class="profile-item">
        <small>E-mail</small>
        <strong><?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?></strong>
      </div>
    </div>

    <div class="profile-actions">
      <a class="btn btn--ghost" href="/video-streaming-platform/index.php">Home</a>
      <form method="post" action="/video-streaming-platform/pages/logout.php">
        <?php echo csrf_field(); ?>
        <button class="btn btn--danger" type="submit">Uitloggen</button>
      </form>
    </div>
  </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
