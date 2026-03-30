<?php
declare(strict_types=1);
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'ADMIN') {
    header('Location: ../auth/login.php');
    exit;
}
$currentUser = $_SESSION['user'];

require_once __DIR__ . '/../../app/services/UserService.php';
$userService = new UserService();

$userId = isset($_GET['id']) ? (int)$_GET['id'] : null;
$userToEdit = null;
$isEdit = false;

if ($userId) {
    $userToEdit = $userService->getUserById($userId);
    if ($userToEdit) {
        $isEdit = true;
    } else {
        header('Location: users.php?error=' . urlencode('Utilisateur non trouvé.'));
        exit;
    }
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'] ?? '';
    $email = $_POST['email'] ?? '';
    $role = $_POST['role'] ?? 'ETUDIANT';
    $password = $_POST['password'] ?? '';

    if ($isEdit) {
        // En édition, le mot de passe est optionnel
        $data = [
            'nom' => $nom,
            'email' => $email,
            'role' => $role
        ];
        if (!empty($password)) {
            $data['password'] = $password;
        }
        
        $result = $userService->updateUser($userId, $data);
        if ($result['success']) {
            header('Location: users.php?msg=' . urlencode($result['message']));
            exit;
        } else {
            $error = $result['error'];
        }
    } else {
        // En création, le mot de passe est obligatoire
        $data = [
            'nom' => $nom,
            'email' => $email,
            'role' => $role,
            'password' => $password
        ];
        
        $result = $userService->createUser($data);
        if ($result['success']) {
            header('Location: users.php?msg=' . urlencode($result['message']));
            exit;
        } else {
            $error = $result['error'];
        }
    }
}

// Preserve form data on error
$formNom = $_POST['nom'] ?? ($userToEdit['nom'] ?? '');
$formEmail = $_POST['email'] ?? ($userToEdit['email'] ?? '');
$formRole = $_POST['role'] ?? ($userToEdit['role'] ?? 'ETUDIANT');

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isEdit ? 'Éditer' : 'Créer' ?> Utilisateur - Campus HelpDesk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .navbar-brand { font-weight: 600; }
        .card { border: none; border-radius: 10px; max-width: 600px; margin: 0 auto; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php"><i class="bi bi-hdd-network"></i> Campus HelpDesk <span class="badge bg-light text-primary ms-2">Admin</span></a>
            <div class="d-flex align-items-center">
                <span class="text-white me-3"><i class="bi bi-person-circle"></i> <?= htmlspecialchars($currentUser['nom'] ?? 'Admin') ?></span>
                <a href="../auth/logout.php" class="btn btn-sm btn-outline-light">Déconnexion</a>
            </div>
        </div>
    </nav>
    
    <div class="container py-5">
        <h2 class="text-center mb-4"><?= $isEdit ? 'Éditer un Utilisateur' : 'Créer un Nouvel Utilisateur' ?></h2>
        
        <div class="card p-4">
            <?php if ($error): ?>
                <div class="alert alert-danger" role="alert">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="mb-3">
                    <label for="nom" class="form-label">Nom Complet</label>
                    <input type="text" class="form-control" id="nom" name="nom" value="<?= htmlspecialchars($formNom) ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="email" class="form-label">Adresse Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($formEmail) ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="role" class="form-label">Rôle</label>
                    <select class="form-select" id="role" name="role" required>
                        <option value="ETUDIANT" <?= $formRole === 'ETUDIANT' ? 'selected' : '' ?>>Étudiant</option>
                        <option value="TECH" <?= $formRole === 'TECH' ? 'selected' : '' ?>>Technicien</option>
                        <option value="ADMIN" <?= $formRole === 'ADMIN' ? 'selected' : '' ?>>Administrateur</option>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label for="password" class="form-label">Mot de Passe <?= $isEdit ? '<small class="text-muted">(Laissez vide pour ne pas modifier)</small>' : '' ?></label>
                    <input type="password" class="form-control" id="password" name="password" <?= $isEdit ? '' : 'required' ?>>
                </div>
                
                <div class="d-flex justify-content-between">
                    <a href="users.php" class="btn btn-outline-secondary">Annuler</a>
                    <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Mettre à jour' : 'Créer l\'utilisateur' ?></button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
