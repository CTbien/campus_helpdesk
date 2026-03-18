<?php
// public/login.php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../../app/services/AuthService.php';

if (isset($_SESSION['user'])) {
  (new AuthService())->redirectByRole($_SESSION['user']['role']);
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = (string) filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
  $password = (string)($_POST['password'] ?? '');

  $auth = new AuthService();
  $res = $auth->login($email, $password);

  if ($res['ok']) {
    // Anti session fixation
    session_regenerate_id(true);

    $_SESSION['user'] = $res['user'];
    $auth->redirectByRole($_SESSION['user']['role']);
  } else {
    $error = $res['error'];
  }
}
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Connexion — Campus HelpDesk</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm">
          <div class="card-body p-4">
            <h1 class="h4 fw-bold mb-3">Connexion</h1>
            <?php if ($error): ?>
              <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="post" autocomplete="off">
              <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Mot de passe</label>
                <input type="password" name="password" class="form-control" required>
              </div>
              <button class="btn btn-primary w-100">Se connecter</button>
              <p class="text-muted small mt-3 mb-0">Comptes test : student@campus.local / tech@campus.local / admin@campus.local</p>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>