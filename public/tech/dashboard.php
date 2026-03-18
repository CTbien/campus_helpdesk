<?php
declare(strict_types=1);
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'TECH') {
    header('Location: ../auth/login.php');
    exit;
}
$user = $_SESSION['user'];

// Get filter parameters
$filterStatus = $_GET['status'] ?? 'OPEN'; // Default to open tickets for techs
$filterPriority = $_GET['priority'] ?? 'ALL';

require_once __DIR__ . '/../../app/services/TicketService.php';
$ticketService = new TicketService();

// Fetch filtered tickets for main table
$tickets = $ticketService->getTechTickets($filterStatus, $filterPriority);

// Fetch ALL tickets to accurately count the badges regardless of current filter
$allTicketsForCount = $ticketService->getTechTickets('ALL', 'ALL');
$counts = ['OPEN' => 0, 'IN_PROGRESS' => 0, 'RESOLVED' => 0];
foreach ($allTicketsForCount as $t) {
    if (isset($counts[$t['statut']])) {
        $counts[$t['statut']]++;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Technicien - Campus HelpDesk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .navbar-brand { font-weight: 600; }
        .card { transition: transform 0.2s; border: none; border-radius: 10px; }
        .card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important; }
        /* Style spécifique pour nav info text foncé pour être lisible */
        .bg-tech { background-color: #17a2b8; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-tech shadow-sm">
        <div class="container">
            <a class="navbar-brand text-white" href="#"><i class="bi bi-hdd-network"></i> Campus HelpDesk <span class="badge bg-light text-info ms-2">Tech</span></a>
            <div class="d-flex align-items-center">
                <span class="text-white me-3"><i class="bi bi-person-circle"></i> <?= htmlspecialchars($user['nom'] ?? 'Technicien') ?></span>
                <a href="../auth/logout.php" class="btn btn-sm btn-outline-light">Déconnexion</a>
            </div>
        </div>
    </nav>
    
    <div class="container py-5">
        <div class="row mb-4 align-items-center">
            <div class="col-md-4">
                <h2>File d'Attente</h2>
            </div>
            <div class="col-md-8">
                <form method="GET" class="d-flex justify-content-end gap-2 align-items-center">
                    <label class="fw-bold me-1">Filtrer par :</label>
                    <select name="status" class="form-select w-auto" onchange="this.form.submit()">
                        <option value="ALL" <?= $filterStatus === 'ALL' ? 'selected' : '' ?>>Tous les statuts</option>
                        <option value="OPEN" <?= $filterStatus === 'OPEN' ? 'selected' : '' ?>>Ouverts (<?= $counts['OPEN'] ?>)</option>
                        <option value="IN_PROGRESS" <?= $filterStatus === 'IN_PROGRESS' ? 'selected' : '' ?>>En cours (<?= $counts['IN_PROGRESS'] ?>)</option>
                        <option value="RESOLVED" <?= $filterStatus === 'RESOLVED' ? 'selected' : '' ?>>Résolus (<?= $counts['RESOLVED'] ?>)</option>
                    </select>
                    
                    <select name="priority" class="form-select w-auto" onchange="this.form.submit()">
                        <option value="ALL" <?= $filterPriority === 'ALL' ? 'selected' : '' ?>>Toutes les priorités</option>
                        <option value="HAUTE" <?= $filterPriority === 'HAUTE' ? 'selected' : '' ?>>Haute</option>
                        <option value="MOYENNE" <?= $filterPriority === 'MOYENNE' ? 'selected' : '' ?>>Moyenne</option>
                        <option value="BASSE" <?= $filterPriority === 'BASSE' ? 'selected' : '' ?>>Basse</option>
                    </select>
                    
                    <noscript><button type="submit" class="btn btn-primary">Filtrer</button></noscript>
                </form>
            </div>
        </div>
        
        <div class="card shadow-sm mb-4">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Sujet</th>
                                <th>Demandeur</th>
                                <th>Priorité</th>
                                <th>Statut</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($tickets)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="bi bi-check2-circle display-4 d-block mb-3 text-success"></i>
                                        <h5>Aucun ticket dans la file d'attente.</h5>
                                        <p>Vous êtes à jour ! Excellent travail.</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($tickets as $ticket): ?>
                                    <tr>
                                        <td>#<?= $ticket['id'] ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($ticket['titre']) ?></strong><br>
                                            <span class="badge bg-secondary rounded-pill" style="font-size: 0.7em;"><?= htmlspecialchars($ticket['categorie_nom'] ?? 'Général') ?></span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="bg-light rounded-circle p-2 me-2"><i class="bi bi-person text-muted"></i></div>
                                                <div>
                                                    <div class="fw-bold fs-7"><?= htmlspecialchars($ticket['auteur_nom'] ?? 'Inconnu') ?></div>
                                                </div>
                                            </div>
                                        </td>
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
                                        <td>
                                            <a href="ticket_detail.php?id=<?= $ticket['id'] ?>" class="btn btn-sm btn-outline-info" title="Voir les détails"><i class="bi bi-eye"></i></a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
