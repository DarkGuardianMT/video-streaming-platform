<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$errors = [];
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  require_csrf();
  $username = trim(post_string('username'));
  $email = trim(post_string('email'));
  $password = post_string('password');

  if ($username === '') $errors[] = "Gebruikersnaam is verplicht.";
  if ($email === '') $errors[] = "E-mail is verplicht.";
  if ($password === '') $errors[] = "Wachtwoord is verplicht.";
  if (strlen($username) > 100 || strlen($email) > 254) $errors[] = 'Naam of e-mail te lang.';
  if (strlen($password) < 12 || strlen($password) > 72) $errors[] = 'Gebruik een wachtwoord van 12 tot 72 bytes.';
  if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "E-mail is ongeldig.";
  }

  // Unique check (email + username)
  if (empty($errors)) {
    require_once __DIR__ . '/../includes/db.php';
    $check = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ? LIMIT 1");
    $check->execute([$email, $username]);
    if ($check->fetch()) {
      $errors[] = "E-mail of gebruikersnaam bestaat al.";
    }
  }

  if (empty($errors)) {
    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO users (username, password, email, isAdmin) VALUES (?, ?, ?, 0)");
    try {
      $stmt->execute([$username, $hash, $email]);
    } catch (PDOException $e) {
      http_response_code(409);
      exit('Account kon niet worden aangemaakt. Probeer andere gegevens.');
    }

    // Register tamam -> login sayfasına yönlendir
    header("Location: /video-streaming-platform/pages/login.php?msg=registered");
    exit;
  }
}
include __DIR__ . '/../includes/header.php';
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
    <?php echo csrf_field(); ?>
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
      <input id="password" name="password" type="password" minlength="12" maxlength="72" required>
    </div>

    <button class="btn btn--primary" type="submit">Account maken</button>
  </form>

  <p style="margin-top:12px;">
    Heb je al een account? <a href="/video-streaming-platform/pages/login.php">Inloggen</a>
  </p>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
