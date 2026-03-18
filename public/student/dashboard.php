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
$mesTickets = $ticketService->getStudentTickets((int)$user['id']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Étudiant/Professeur - Campus HelpDesk</title>
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
    <nav class="navbar navbar-expand-lg navbar-dark bg-success shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="#"><i class="bi bi-hdd-network"></i> Campus HelpDesk</a>
            <div class="d-flex align-items-center">
                <span class="text-white me-3"><i class="bi bi-person-circle"></i> <?= htmlspecialchars($user['nom'] ?? 'Utilisateur') ?></span>
                <a href="../auth/logout.php" class="btn btn-sm btn-outline-light">Déconnexion</a>
            </div>
        </div>
    </nav>
    
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Mes Demandes d'Assistance</h2>
            <a href="create_ticket.php" class="btn btn-success"><i class="bi bi-plus-circle"></i> Nouveau Ticket</a>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card shadow-sm h-100">
                    <div class="card-body p-4">
                        <h5 class="card-title mb-4">Tickets récents</h5>
                        <?php if (empty($mesTickets)): ?>
                            <div class="text-center text-muted py-5">
                                <i class="bi bi-inbox display-4"></i>
                                <p class="mt-3">Vous n'avez aucune demande en cours.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>Sujet</th>
                                            <th>Catégorie</th>
                                            <th>Statut</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($mesTickets as $ticket): ?>
                                            <tr>
                                                <td><a href="ticket_detail.php?id=<?= $ticket['id'] ?>" class="text-decoration-none fw-bold">#<?= $ticket['id'] ?></a></td>
                                                <td><a href="ticket_detail.php?id=<?= $ticket['id'] ?>" class="text-decoration-none text-dark"><strong><?= htmlspecialchars($ticket['titre']) ?></strong></a></td>
                                                <td><span class="badge bg-secondary"><?= htmlspecialchars($ticket['categorie_nom'] ?? 'Général') ?></span></td>
                                                <td>
                                                    <?php if ($ticket['statut'] === 'OPEN'): ?>
                                                        <span class="badge bg-warning text-dark"><i class="bi bi-clock"></i> Ouvert</span>
                                                    <?php elseif ($ticket['statut'] === 'IN_PROGRESS'): ?>
                                                        <span class="badge bg-info"><i class="bi bi-tools"></i> En cours</span>
                                                    <?php elseif ($ticket['statut'] === 'RESOLVED'): ?>
                                                        <span class="badge bg-success"><i class="bi bi-check-circle"></i> Résolu</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= date('d/m/Y', strtotime($ticket['created_at'])) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body p-4">
                        <h5 class="card-title">Centre d'aide</h5>
                        <p class="text-muted small">Consultez nos articles avant d'ouvrir un ticket.</p>
                        <ul class="list-unstyled">
                            <li class="mb-3"><a href="#" class="text-decoration-none text-success"><i class="bi bi-book"></i> Comment se connecter au Wi-Fi ?</a></li>
                            <li class="mb-3"><a href="#" class="text-decoration-none text-success"><i class="bi bi-book"></i> Mot de passe ENT oublié</a></li>
                            <li class="mb-3"><a href="#" class="text-decoration-none text-success"><i class="bi bi-book"></i> Configurer l'imprimante</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
