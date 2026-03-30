<?php
declare(strict_types=1);
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'ADMIN') {
    header('Location: ../auth/login.php');
    exit;
}
$user = $_SESSION['user'];

require_once __DIR__ . '/../../app/services/UserService.php';
$userService = new UserService();
$usersList = $userService->getAllUsers();

$msg = $_GET['msg'] ?? '';
$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Utilisateurs - Campus HelpDesk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .navbar-brand { font-weight: 600; }
        .card { border: none; border-radius: 10px; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php"><i class="bi bi-hdd-network"></i> Campus HelpDesk <span class="badge bg-light text-primary ms-2">Admin</span></a>
            <div class="d-flex align-items-center">
                <span class="text-white me-3"><i class="bi bi-person-circle"></i> <?= htmlspecialchars($user['nom'] ?? 'Admin') ?></span>
                <a href="../auth/logout.php" class="btn btn-sm btn-outline-light">Déconnexion</a>
            </div>
        </div>
    </nav>
    
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Gestion des Utilisateurs</h2>
            <a href="user_form.php" class="btn btn-primary"><i class="bi bi-person-plus"></i> Nouvel Utilisateur</a>
        </div>
        
        <?php if ($msg): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($msg) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Nom</th>
                                <th>Email</th>
                                <th>Rôle</th>
                                <th>Statut</th>
                                <th>Date de création</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($usersList)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">Aucun utilisateur trouvé.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($usersList as $u): ?>
                                    <tr>
                                        <td><?= $u['id'] ?></td>
                                        <td><strong><?= htmlspecialchars($u['nom']) ?></strong></td>
                                        <td><?= htmlspecialchars($u['email']) ?></td>
                                        <td>
                                            <?php if ($u['role'] === 'ADMIN'): ?>
                                                <span class="badge bg-danger"><i class="bi bi-shield-lock"></i> Admin</span>
                                            <?php elseif ($u['role'] === 'TECH'): ?>
                                                <span class="badge bg-warning text-dark"><i class="bi bi-tools"></i> Tech</span>
                                            <?php else: ?>
                                                <span class="badge bg-info text-dark"><i class="bi bi-mortarboard"></i> Étudiant</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($u['actif']): ?>
                                                <span class="badge bg-success">Actif</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Inactif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
                                        <td>
                                            <a href="user_form.php?id=<?= $u['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i> Éditer</a>
                                            <?php if ((int)$u['id'] !== (int)$user['id']): ?>
                                                <form action="user_toggle.php" method="POST" class="d-inline">
                                                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                                    <?php if ($u['actif']): ?>
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Êtes-vous sûr de vouloir désactiver cet utilisateur ?');"><i class="bi bi-person-x"></i> Désactiver</button>
                                                    <?php else: ?>
                                                        <button type="submit" class="btn btn-sm btn-outline-success"><i class="bi bi-person-check"></i> Activer</button>
                                                    <?php endif; ?>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer text-start bg-white d-flex align-items-center">
                <a href="dashboard.php" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Retour au tableau de bord</a>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
