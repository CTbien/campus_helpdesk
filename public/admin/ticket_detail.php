<?php
declare(strict_types=1);
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'ADMIN') {
    header('Location: ../auth/login.php');
    exit;
}
$user = $_SESSION['user'];

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: tickets.php');
    exit;
}
$ticketId = (int)$_GET['id'];

require_once __DIR__ . '/../../app/services/TicketService.php';
$ticketService = new TicketService();

$error = '';
$success = '';

$ticket = $ticketService->getTicketDetails($ticketId, (int)$user['id'], $user['role']);
if (!$ticket) {
    header('Location: tickets.php?error=notfound');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'assign') {
        $result = $ticketService->assignToTech($ticketId, (int)$user['id'], $user['role']);
        if ($result['success']) $success = $result['message']; else $error = $result['error'];
    } elseif ($action === 'status_update') {
        $newStatus = $_POST['status'] ?? '';
        $result = $ticketService->updateStatus($ticketId, $newStatus, $user['role']);
        if ($result['success']) $success = $result['message']; else $error = $result['error'];
    } elseif ($action === 'add_message') {
        $message = $_POST['message'] ?? '';
        $result = $ticketService->addMessage($ticketId, (int)$user['id'], $user['role'], $message);
        if ($result['success']) $success = $result['message']; else $error = $result['error'];
    }

    if (empty($error)) {
        $ticket = $ticketService->getTicketDetails($ticketId, (int)$user['id'], $user['role']);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Traitement Ticket #<?= $ticket['id'] ?> - Campus HelpDesk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .navbar-brand { font-weight: 600; }
        .card { border: none; border-radius: 10px; }
        .ticket-info { background-color: #f1f8ff; border-left: 4px solid #0d6efd; padding: 15px; border-radius: 4px; }
        .bg-admin { background-color: #0d6efd; color: white; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="bi bi-hdd-network"></i> Campus HelpDesk
                <span class="badge bg-light text-primary ms-2">Admin</span>
            </a>
            <div class="d-flex align-items-center">
                <span class="text-white me-3"><i class="bi bi-person-circle"></i> <?= htmlspecialchars($user['nom'] ?? '') ?></span>
                <a href="../auth/logout.php" class="btn btn-sm btn-outline-light">Déconnexion</a>
            </div>
        </div>
    </nav>
    
    <div class="container py-5">
        <?php if ($error): ?>
            <div class="alert alert-danger mb-4"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success mb-4"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <div class="mb-4 d-flex justify-content-between align-items-center">
            <a href="tickets.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Liste des tickets</a>
            <div class="btn-group">
                <form method="POST" class="d-inline">
                    <input type="hidden" name="action" value="assign">
                    <button type="submit" class="btn btn-primary rounded-end-0 border-end border-light" <?= (int)$ticket['assigne_a'] === (int)$user['id'] ? 'disabled' : '' ?>><i class="bi bi-person-check"></i> Assigner à moi</button>
                </form>
                <form method="POST" class="d-inline">
                    <input type="hidden" name="action" value="status_update">
                    <input type="hidden" name="status" value="RESOLVED">
                    <button type="submit" class="btn btn-success rounded-start-0 border-start border-light" <?= $ticket['statut'] === 'RESOLVED' ? 'disabled' : '' ?>><i class="bi bi-check-lg"></i> Marquer Résolu</button>
                </form>
            </div>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Ticket #<?= $ticket['id'] ?> : <?= htmlspecialchars($ticket['titre']) ?></h4>
                        <div>
                            <?php if ($ticket['statut'] === 'OPEN'): ?>
                                <span class="badge bg-warning text-dark fs-6"><i class="bi bi-clock"></i> Ouvert</span>
                            <?php elseif ($ticket['statut'] === 'IN_PROGRESS'): ?>
                                <span class="badge bg-info fs-6"><i class="bi bi-tools"></i> En cours</span>
                            <?php elseif ($ticket['statut'] === 'RESOLVED'): ?>
                                <span class="badge bg-success fs-6"><i class="bi bi-check-circle"></i> Résolu</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <p style="white-space: pre-wrap; font-family: inherit; font-size: 1.1em; line-height: 1.6;"><?= htmlspecialchars($ticket['description']) ?></p>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="bi bi-chat-dots"></i> Espace d'échange</h5>
                    </div>
                    <div class="card-body p-4 bg-light">
                        <?php if (empty($ticket['messages'])): ?>
                            <p class="text-center text-muted mb-4">Aucun message pour le moment.</p>
                        <?php else: ?>
                            <div class="mb-4">
                                <?php foreach ($ticket['messages'] as $msg): ?>
                                    <div class="card mb-3 <?= $msg['auteur_role'] === 'ETUDIANT' ? '' : 'border-primary' ?>">
                                        <div class="card-header <?= $msg['auteur_role'] === 'ETUDIANT' ? 'bg-white' : 'bg-primary text-white' ?> py-2 d-flex justify-content-between">
                                            <strong><i class="<?= $msg['auteur_role'] === 'ETUDIANT' ? 'bi bi-person-fill' : 'bi bi-headset' ?>"></i> <?= htmlspecialchars($msg['auteur_nom']) ?> (<?= $msg['auteur_role'] ?>)</strong>
                                            <small><?= date('d/m/Y H:i', strtotime($msg['created_at'])) ?></small>
                                        </div>
                                        <div class="card-body py-2">
                                            <p class="mb-0" style="white-space: pre-wrap;"><?= htmlspecialchars($msg['message']) ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST">
                            <input type="hidden" name="action" value="add_message">
                            <div class="mb-3">
                                <label for="message" class="form-label fw-bold">Nouveau message</label>
                                <textarea class="form-control" id="message" name="message" rows="3" required></textarea>
                            </div>
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary"><i class="bi bi-send"></i> Envoyer</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-body ticket-info">
                        <h5 class="mb-4 text-dark"><i class="bi bi-info-circle"></i> Détails du Ticket</h5>
                        
                        <div class="mb-3">
                            <span class="text-muted d-block small fw-bold text-uppercase">Demandeur</span>
                            <div class="d-flex align-items-center mt-1">
                                <i class="bi bi-person-circle fs-4 text-secondary me-2"></i>
                                <div>
                                    <div class="fw-bold"><?= htmlspecialchars($ticket['auteur_nom'] ?? 'Inconnu') ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($ticket['auteur_email'] ?? '') ?></small>
                                </div>
                            </div>
                        </div>

                        <hr>
                        
                        <div class="mb-3">
                            <span class="text-muted d-block small fw-bold text-uppercase">Technicien/Admin Assigné</span>
                            <div class="mt-1">
                                <?php if ($ticket['assigne_nom']): ?>
                                    <i class="bi bi-person text-primary"></i> <?= htmlspecialchars($ticket['assigne_nom']) ?>
                                <?php else: ?>
                                    <span class="text-muted fst-italic">Non assigné</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <hr>

                        <div class="row align-items-center mb-3">
                            <div class="col-6">
                                <span class="text-muted d-block small fw-bold text-uppercase">Catégorie</span>
                                <span class="badge bg-secondary"><?= htmlspecialchars($ticket['categorie_nom'] ?? 'Général') ?></span>
                            </div>
                            <div class="col-6">
                                <span class="text-muted d-block small fw-bold text-uppercase">Priorité</span>
                                <?php if ($ticket['priorite'] === 'HAUTE'): ?>
                                    <span class="text-danger fw-bold"><i class="bi bi-arrow-up-circle"></i> Haute</span>
                                <?php elseif ($ticket['priorite'] === 'MOYENNE'): ?>
                                    <span class="text-warning text-dark fw-bold"><i class="bi bi-dash-circle"></i> Moyenne</span>
                                <?php else: ?>
                                    <span class="text-info text-dark fw-bold"><i class="bi bi-arrow-down-circle"></i> Basse</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <hr>
                        
                        <div>
                            <span class="text-muted d-block small fw-bold text-uppercase">Date de Création</span>
                            <i class="bi bi-calendar3"></i> <?= date('d/m/Y à H:i', strtotime($ticket['created_at'])) ?>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-body p-3">
                        <h6 class="mb-3 text-muted">Actions Rapides</h6>
                        <form method="POST">
                            <input type="hidden" name="action" value="status_update">
                            <select class="form-select mb-2" id="quick-status" name="status">
                                <option value="OPEN" <?= $ticket['statut'] === 'OPEN' ? 'selected' : '' ?>>Statut: Ouvert</option>
                                <option value="IN_PROGRESS" <?= $ticket['statut'] === 'IN_PROGRESS' ? 'selected' : '' ?>>Statut: En cours</option>
                                <option value="RESOLVED" <?= $ticket['statut'] === 'RESOLVED' ? 'selected' : '' ?>>Statut: Résolu</option>
                            </select>
                            <button type="submit" class="btn btn-outline-primary btn-sm w-100">Mettre à jour le statut</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
