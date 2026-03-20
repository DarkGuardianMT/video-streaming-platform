<?php
include __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/db.php';

$errors = [];
$msg = $_GET['msg'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';

  if ($email === '') $errors[] = "E-mail is verplicht.";
  if ($password === '') $errors[] = "Wachtwoord is verplicht.";

  if (empty($errors)) {
    $stmt = $pdo->prepare("SELECT id, username, email, password, isAdmin FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
      $errors[] = "Onjuiste e-mail of wachtwoord.";
    } else {
      if (password_verify($password, $user['password'])) {
        // session already started in header.php
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['isAdmin'] = (int)$user['isAdmin'];

        
        // Redirect
        if ((int)$user['isAdmin'] === 1) {
          header("Location: /video-streaming-platform/admin/index.php");
          exit;
        } else {
          header("Location: /video-streaming-platform/index.php");
          exit;
        }
      } else {
        $errors[] = "Onjuiste e-mail of wachtwoord.";
      }
    }
  }
}
?>

<main class="page">
  <h2>Inloggen</h2>

  <?php if ($msg === 'registered'): ?>
    <div class="alert alert-success">✅ Account aangemaakt! Je kunt nu inloggen.</div>
  <?php endif; ?>

  <?php if (!empty($errors)): ?>
    <div class="alert alert-error">
      <ul>
        <?php foreach ($errors as $e): ?>
          <li><?php echo htmlspecialchars($e); ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <form method="post">
    <div class="field">
      <label for="email">E-mail</label>
      <input id="email" name="email" type="email" required>
    </div>

    <div class="field">
      <label for="password">Wachtwoord</label>
      <input id="password" name="password" type="password" required>
    </div>

    <button class="btn btn--primary" type="submit">Inloggen</button>
  </form>

  <p style="margin-top:12px;">
    Nog geen account? <a href="/video-streaming-platform/pages/register.php">Registreren</a>
  </p>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>

