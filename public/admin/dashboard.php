<?php
declare(strict_types=1);
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'ADMIN') {
    header('Location: ../auth/login.php');
    exit;
}
$user = $_SESSION['user'];

require_once __DIR__ . '/../../app/services/TicketService.php';
$ticketService = new TicketService();
$tickets = $ticketService->getAdminTickets(); // Could be limited, but let's just get all for now
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Administrateur - Campus HelpDesk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .navbar-brand { font-weight: 600; }
        .card { transition: transform 0.2s; border: none; border-radius: 10px; }
        .card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="#"><i class="bi bi-hdd-network"></i> Campus HelpDesk <span class="badge bg-light text-primary ms-2">Admin</span></a>
            <div class="d-flex align-items-center">
                <span class="text-white me-3"><i class="bi bi-person-circle"></i> <?= htmlspecialchars($user['nom'] ?? 'Admin') ?></span>
                <a href="../auth/logout.php" class="btn btn-sm btn-outline-light">Déconnexion</a>
            </div>
        </div>
    </nav>
    
    <div class="container py-5">
        <h2 class="mb-4">Tableau de bord Administrateur</h2>
        
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body text-center p-4">
                        <div class="display-4 text-primary mb-3"><i class="bi bi-people"></i></div>
                        <h5 class="card-title">Utilisateurs</h5>
                        <p class="text-muted small">Gérer les comptes, les rôles et les accès de la plateforme.</p>
                        <a href="#" class="btn btn-primary w-100">Gérer les utilisateurs</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body text-center p-4">
                        <div class="display-4 text-primary mb-3"><i class="bi bi-gear"></i></div>
                        <h5 class="card-title">Paramètres</h5>
                        <p class="text-muted small">Configuration globale et paramètres du HelpDesk.</p>
                        <a href="#" class="btn btn-outline-primary w-100">Configurer</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body text-center p-4">
                        <div class="display-4 text-primary mb-3"><i class="bi bi-bar-chart"></i></div>
                        <h5 class="card-title">Statistiques</h5>
                        <p class="text-muted small">Consulter les rapports et l'activité globale du centre de support.</p>
                        <a href="#" class="btn btn-outline-primary w-100">Voir les Stats</a>
                    </div>
                </div>
            </div>
        </div>

        <h3 class="mb-3 mt-5">Aperçu des Tickets Récents</h3>
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Sujet</th>
                                <th>Auteur</th>
                                <th>Priorité</th>
                                <th>Statut</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($tickets)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">Aucun ticket dans le système.</td>
                                </tr>
                            <?php else: ?>
                                <?php $recentTickets = array_slice($tickets, 0, 5); // Show top 5 recent ?>
                                <?php foreach ($recentTickets as $ticket): ?>
                                    <tr>
                                        <td>#<?= $ticket['id'] ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($ticket['titre']) ?></strong><br>
                                            <span class="badge bg-secondary rounded-pill" style="font-size: 0.7em;"><?= htmlspecialchars($ticket['categorie_nom'] ?? 'Général') ?></span>
                                        </td>
                                        <td><?= htmlspecialchars($ticket['auteur_nom'] ?? 'Inconnu') ?></td>
                                        <td>
                                            <?php if ($ticket['priorite'] === 'HAUTE'): ?>
                                                <span class="badge bg-danger">Haute</span>
                                            <?php elseif ($ticket['priorite'] === 'MOYENNE'): ?>
                                                <span class="badge bg-warning text-dark">Moyenne</span>
                                            <?php else: ?>
                                                <span class="badge bg-info text-dark">Basse</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($ticket['statut'] === 'OPEN'): ?>
                                                <span class="badge bg-warning text-dark"><i class="bi bi-clock"></i> Ouvert</span>
                                            <?php elseif ($ticket['statut'] === 'IN_PROGRESS'): ?>
                                                <span class="badge bg-info"><i class="bi bi-tools"></i> En cours</span>
                                            <?php elseif ($ticket['statut'] === 'RESOLVED'): ?>
                                                <span class="badge bg-success"><i class="bi bi-check-circle"></i> Résolu</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= date('d/m/Y H:i', strtotime($ticket['created_at'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer text-end bg-white">
                <a href="#" class="btn btn-sm btn-outline-primary">Voir tous les tickets</a>
            </div>
        </div>
    </div>
</body>
</html>
