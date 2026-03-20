<?php
include __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/db.php';

$errors = [];
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';

  if ($username === '') $errors[] = "Gebruikersnaam is verplicht.";
  if ($email === '') $errors[] = "E-mail is verplicht.";
  if ($password === '') $errors[] = "Wachtwoord is verplicht.";
  if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "E-mail is ongeldig.";
  }

  // Unique check (email + username)
  if (empty($errors)) {
    $check = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ? LIMIT 1");
    $check->execute([$email, $username]);
    if ($check->fetch()) {
      $errors[] = "E-mail of gebruikersnaam bestaat al.";
    }
  }

  if (empty($errors)) {
    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO users (username, password, email, isAdmin) VALUES (?, ?, ?, 0)");
    $stmt->execute([$username, $hash, $email]);

    // Register tamam -> login sayfasına yönlendir
    header("Location: /video-streaming-platform/pages/login.php?msg=registered");
    exit;
  }
}
}
?>

<main class="page">
  <h2>Registreren</h2>

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

  <form method="post">
    <div class="field">
      <label for="username">Gebruikersnaam</label>
      <input id="username" name="username" required>
    </div>

    <div class="field">
      <label for="email">E-mail</label>
      <input id="email" name="email" type="email" required>
    </div>

    <div class="field">
      <label for="password">Wachtwoord</label>
      <input id="password" name="password" type="password" required>
    </div>

    <button class="btn btn--primary" type="submit">Account maken</button>
  </form>

  <p style="margin-top:12px;">
    Heb je al een account? <a href="/video-streaming-platform/pages/login.php">Inloggen</a>
  </p>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>