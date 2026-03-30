<?php
declare(strict_types=1);
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'ADMIN') {
    header('Location: ../auth/login.php');
    exit;
}
$user = $_SESSION['user'];

// Filter parameters
$filterStatus = $_GET['status'] ?? 'ALL';
$filterPriority = $_GET['priority'] ?? 'ALL';

require_once __DIR__ . '/../../app/services/TicketService.php';
$ticketService = new TicketService();

$tickets = $ticketService->getAdminTickets($filterStatus, $filterPriority);

// Counters
$allTicketsForCount = $ticketService->getAdminTickets('ALL', 'ALL');
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
    <title>Tous les Tickets - Campus HelpDesk</title>
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
            <h2>Gestion des Tickets</h2>
            <a href="dashboard.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Retour</a>
        </div>
        
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <form method="GET" class="d-flex align-items-center gap-3 m-0">
                    <label class="fw-bold m-0"><i class="bi bi-funnel"></i> Filtres :</label>
                    <select name="status" class="form-select w-auto" onchange="this.form.submit()">
                        <option value="ALL" <?= $filterStatus === 'ALL' ? 'selected' : '' ?>>Tous (<?= count($allTicketsForCount) ?>)</option>
                        <option value="OPEN" <?= $filterStatus === 'OPEN' ? 'selected' : '' ?>>Ouverts (<?= $counts['OPEN'] ?>)</option>
                        <option value="IN_PROGRESS" <?= $filterStatus === 'IN_PROGRESS' ? 'selected' : '' ?>>En cours (<?= $counts['IN_PROGRESS'] ?>)</option>
                        <option value="RESOLVED" <?= $filterStatus === 'RESOLVED' ? 'selected' : '' ?>>Résolus (<?= $counts['RESOLVED'] ?>)</option>
                    </select>
                    
                    <select name="priority" class="form-select w-auto" onchange="this.form.submit()">
                        <option value="ALL" <?= $filterPriority === 'ALL' ? 'selected' : '' ?>>Toutes priorités</option>
                        <option value="HAUTE" <?= $filterPriority === 'HAUTE' ? 'selected' : '' ?>>Haute</option>
                        <option value="MOYENNE" <?= $filterPriority === 'MOYENNE' ? 'selected' : '' ?>>Moyenne</option>
                        <option value="BASSE" <?= $filterPriority === 'BASSE' ? 'selected' : '' ?>>Basse</option>
                    </select>
                    <noscript><button type="submit" class="btn btn-primary btn-sm">Filtrer</button></noscript>
                </form>
            </div>
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
                                        <i class="bi bi-inbox display-4 d-block mb-3 text-secondary"></i>
                                        <h5>Aucun ticket trouvé.</h5>
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
                                            <?= htmlspecialchars($ticket['auteur_nom'] ?? 'Inconnu') ?>
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
                                            <a href="ticket_detail.php?id=<?= $ticket['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i> Consulter</a>
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
