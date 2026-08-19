<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

admin_start_session();

if (!empty($_SESSION['admin_user_id'])) {
    header('Location: product-sources.php');
    exit;
}

$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = 'Enter your username and password.';
    } else {
        $pdo = admin_pdo();

        $statement = $pdo->prepare(
            'SELECT id, username, password_hash, role
             FROM admin_users
             WHERE username = ?
               AND active = 1
             LIMIT 1'
        );

        $statement->execute([$username]);
        $user = $statement->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);

            $_SESSION['admin_user_id'] = (int) $user['id'];
            $_SESSION['admin_username'] = $user['username'];
            $_SESSION['admin_role'] = $user['role'];

            $updateStatement = $pdo->prepare(
                'UPDATE admin_users
                 SET last_login = NOW()
                 WHERE id = ?'
            );

            $updateStatement->execute([(int) $user['id']]);

            header('Location: product-sources.php');
            exit;
        }

        $error = 'Invalid username or password.';
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login | DR Technology</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../style.css">
  <link rel="icon" type="image/png" href="/Media/Favicon.png">
</head>
<body>
  <main id="main-content">
    <section class="hero">
      <div class="container">
        <div class="section-heading hero-copy">
          <p class="eyebrow">Internal access</p>
          <h1>DR Technology admin</h1>
          <p class="hero-text">Sign in to access internal product sourcing information.</p>
        </div>
      </div>
    </section>

    <section class="section">
      <div class="container">
        <article class="service-card shop-card" style="max-width: 36rem;">
          <div>
            <?php if ($error !== ''): ?>
              <p style="color: #ff8a8a;"><?= admin_escape($error) ?></p>
            <?php endif; ?>

            <form method="post" action="login.php" id="quote-request-form">
              <div class="form-group">
                <label for="username">Username</label>
                <input
                  type="text"
                  id="username"
                  name="username"
                  value="<?= admin_escape($username) ?>"
                  autocomplete="username"
                  required
                >
              </div>

              <div class="form-group">
                <label for="password">Password</label>
                <input
                  type="password"
                  id="password"
                  name="password"
                  autocomplete="current-password"
                  required
                >
              </div>

              <button type="submit" class="btn btn-primary">Sign in</button>
            </form>
          </div>
        </article>
      </div>
    </section>
  </main>
</body>
</html>