<?php
declare(strict_types=1);
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: ../auth/login.php');
    exit;
}
$user = $_SESSION['user'];

require_once __DIR__ . '/../../app/services/TicketService.php';
$ticketService = new TicketService();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $ticketService->createTicket($_POST, (int)$user['id']);
    if (isset($result['success']) && $result['success']) {
        $success = $result['message'];
    } else {
        $error = $result['error'] ?? 'Une erreur est survenue.';
    }
}

$categories = $ticketService->getAllCategories();

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau Ticket - Campus HelpDesk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .navbar-brand { font-weight: 600; }
        .card { border: none; border-radius: 10px; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-success shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php"><i class="bi bi-hdd-network"></i> Campus HelpDesk</a>
            <div class="d-flex align-items-center">
                <span class="text-white me-3"><i class="bi bi-person-circle"></i> <?= htmlspecialchars($user['nom'] ?? 'Utilisateur') ?></span>
                <a href="../auth/logout.php" class="btn btn-sm btn-outline-light">Déconnexion</a>
            </div>
        </div>
    </nav>
    
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Créer un Nouveau Ticket</h2>
                    <a href="dashboard.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Retour au tableau de bord</a>
                </div>
                
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($success) ?>
                                <div class="mt-2">
                                    <a href="dashboard.php" class="btn btn-sm btn-success">Voir mes tickets</a>
                                </div>
                            </div>
                        <?php else: ?>
                        
                        <form method="POST" action="create_ticket.php">
                            <div class="mb-3">
                                <label for="titre" class="form-label fw-bold">Titre du problème <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="titre" name="titre" required placeholder="Ex: Problème de connexion Wi-Fi">
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="categorie_id" class="form-label fw-bold">Catégorie <span class="text-danger">*</span></label>
                                    <select class="form-select" id="categorie_id" name="categorie_id" required onchange="toggleNouvelleCategorie()">
                                        <option value="">Sélectionnez une catégorie...</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['libelle']) ?></option>
                                        <?php endforeach; ?>
                                        <option value="autre">Autre (préciser)...</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="priorite" class="form-label fw-bold">Priorité <span class="text-danger">*</span></label>
                                    <select class="form-select" id="priorite" name="priorite" required>
                                        <option value="BASSE">Faible - Ne bloque pas mon travail</option>
                                        <option value="MOYENNE" selected>Moyenne - Gênant mais contournable</option>
                                        <option value="HAUTE">Élevée - Bloque complètement mon travail</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-3 d-none" id="nouvelle_categorie_container">
                                <label for="nouvelle_categorie" class="form-label fw-bold">Nom de la nouvelle catégorie <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nouvelle_categorie" name="nouvelle_categorie" placeholder="Ex: Logiciel spécifique">
                            </div>
                            
                            <div class="mb-4">
                                <label for="description" class="form-label fw-bold">Description détaillée <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="description" name="description" rows="5" required placeholder="Décrivez votre problème avec le plus de détails possible (messages d'erreur, actions effectuées, etc.)"></textarea>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-success btn-lg"><i class="bi bi-send"></i> Soumettre le ticket</button>
                            </div>
                        </form>
                        
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function toggleNouvelleCategorie() {
            const categorieSelect = document.getElementById('categorie_id');
            const container = document.getElementById('nouvelle_categorie_container');
            const input = document.getElementById('nouvelle_categorie');
            
            if (categorieSelect.value === 'autre') {
                container.classList.remove('d-none');
                input.required = true;
            } else {
                container.classList.add('d-none');
                input.required = false;
                input.value = '';
            }
        }
    </script>
</body>
</html>
