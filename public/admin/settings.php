<?php
declare(strict_types=1);
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'ADMIN') {
    header('Location: ../auth/login.php');
    exit;
}
$user = $_SESSION['user'];

require_once __DIR__ . '/../../app/repositories/CategoryRepo.php';
$categoryRepo = new CategoryRepo();

$msg = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create') {
        $name = trim($_POST['name'] ?? '');
        if (!empty($name)) {
            $categoryRepo->getOrCreateCategory($name);
            $msg = "Catégorie créée avec succès.";
        } else {
            $error = "Le nom de la catégorie ne peut pas être vide.";
        }
    } elseif ($action === 'edit') {
        $id = (int)($_POST['category_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        if ($id > 0 && !empty($name)) {
            $categoryRepo->update($id, $name);
            $msg = "Catégorie modifiée avec succès.";
        } else {
            $error = "Nom invalide ou identifiant manquant.";
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['category_id'] ?? 0);
        if ($id > 0) {
            $success = $categoryRepo->delete($id);
            if ($success) {
                $msg = "Catégorie supprimée avec succès.";
            } else {
                $error = "Impossible de supprimer cette catégorie car elle est utilisée par des tickets.";
            }
        }
    }
}

$categories = $categoryRepo->getAllCategories();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paramètres - Campus HelpDesk</title>
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
            <h2>Paramètres Généraux</h2>
            <a href="dashboard.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Retour</a>
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

        <div class="row">
            <!-- Gestion des catégories -->
            <div class="col-md-8 mx-auto">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                        <h4 class="card-title"><i class="bi bi-tags"></i> Catégories de Tickets</h4>
                    </div>
                    <div class="card-body">
                        <form action="" method="POST" class="mb-4 d-flex">
                            <input type="hidden" name="action" value="create">
                            <input type="text" name="name" class="form-control me-2" placeholder="Nouvelle catégorie..." required>
                            <button type="submit" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Ajouter</button>
                        </form>

                        <ul class="list-group">
                            <?php if (empty($categories)): ?>
                                <li class="list-group-item text-muted">Aucune catégorie existante.</li>
                            <?php else: ?>
                                <?php foreach ($categories as $cat): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <!-- Affichage -->
                                        <div class="view-mode" id="view-<?= $cat['id'] ?>">
                                            <?= htmlspecialchars($cat['libelle']) ?>
                                        </div>
                                        <!-- Formulaire d'édition (caché par défaut) -->
                                        <form action="" method="POST" class="edit-mode flex-grow-1 me-3 d-none" id="edit-<?= $cat['id'] ?>">
                                            <input type="hidden" name="action" value="edit">
                                            <input type="hidden" name="category_id" value="<?= $cat['id'] ?>">
                                            <div class="input-group input-group-sm">
                                                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($cat['libelle']) ?>" required>
                                                <button type="submit" class="btn btn-success"><i class="bi bi-check"></i></button>
                                                <button type="button" class="btn btn-secondary" onclick="toggleEdit(<?= $cat['id'] ?>, false)"><i class="bi bi-x"></i></button>
                                            </div>
                                        </form>

                                        <div class="actions-mode" id="actions-<?= $cat['id'] ?>">
                                            <button type="button" class="btn btn-sm btn-outline-primary me-1" onclick="toggleEdit(<?= $cat['id'] ?>, true)">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form action="" method="POST" class="d-inline" onsubmit="return confirm('Supprimer cette catégorie ?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="category_id" value="<?= $cat['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                            </form>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function toggleEdit(id, show) {
            if (show) {
                document.getElementById('view-' + id).classList.add('d-none');
                document.getElementById('actions-' + id).classList.add('d-none');
                document.getElementById('edit-' + id).classList.remove('d-none');
            } else {
                document.getElementById('view-' + id).classList.remove('d-none');
                document.getElementById('actions-' + id).classList.remove('d-none');
                document.getElementById('edit-' + id).classList.add('d-none');
            }
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
