<?php
require_once __DIR__ . '/bootstrap.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="/video-streaming-platform/css/style.css">


  <title>NETFISH</title>
</head>
<body>

<header>
  <nav>
    <ul>
      <li id="navnaam">
        <a href="/video-streaming-platform/index.php" class="logo">Netfish</a>
      </li>
    </ul>

    <ul>
      <?php if (empty($_SESSION['user_id'])): ?>
        <li id="login">
          <a href="/video-streaming-platform/pages/login.php">Inloggen</a>
        </li>
        <li>
          <a href="/video-streaming-platform/pages/register.php">Registreren</a>
        </li>
      <?php else: ?>
        <li>
          <span class="nav-welcome">
            Welkom, <?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?>
          </span>
        </li>
        <li>
          <a href="/video-streaming-platform/pages/profile.php">Profiel</a>
        </li>

        <?php if (!empty($_SESSION['isAdmin']) && (int)$_SESSION['isAdmin'] === 1): ?>
          <li>
            <a href="/video-streaming-platform/admin/index.php">Admin</a>
          </li>
        <?php endif; ?>

        <li>
          <form method="post" action="/video-streaming-platform/pages/logout.php">
            <?php echo csrf_field(); ?>
            <button class="nav-logout" type="submit">Uitloggen</button>
          </form>
        </li>
      <?php endif; ?>
    </ul>
  </nav>
</header>
