<?php
declare(strict_types=1);
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: ../auth/login.php');
    exit;
}
$user = $_SESSION['user'];

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: dashboard.php');
    exit;
}
$ticketId = (int)$_GET['id'];

require_once __DIR__ . '/../../app/services/TicketService.php';
$ticketService = new TicketService();

$error = '';
$success = '';

// 1. Initial fetch to validate access
$ticket = $ticketService->getTicketDetails($ticketId, (int)$user['id'], $user['role']);

if (!$ticket) {
    // Intentionally generic error or redirect for security
    header('Location: dashboard.php?error=notfound');
    exit;
}

// 2. Handle POST actions now that access is confirmed
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add_message') {
        $message = $_POST['message'] ?? '';
        $result = $ticketService->addMessage($ticketId, (int)$user['id'], $user['role'], $message);
        if ($result['success']) {
            $success = $result['message'];
            // Re-fetch to get new message
            $ticket = $ticketService->getTicketDetails($ticketId, (int)$user['id'], $user['role']);
        } else {
            $error = $result['error'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détail du Ticket - Campus HelpDesk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .navbar-brand { font-weight: 600; }
        .card { border: none; border-radius: 10px; }
        .ticket-meta { background-color: #edf2f7; border-radius: 8px; padding: 15px; }
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
        <?php if ($error): ?>
            <div class="alert alert-danger mb-4"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success mb-4"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <div class="mb-4">
            <a href="dashboard.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Retour au tableau de bord</a>
        </div>
        
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
                <div class="row mb-4">
                    <div class="col-md-8">
                        <h5 class="text-muted mb-3"><i class="bi bi-card-text"></i> Description</h5>
                        <div class="p-3 bg-light rounded" style="white-space: pre-wrap; font-family: inherit;"><?= htmlspecialchars($ticket['description']) ?></div>
                <div class="card shadow-sm mt-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="bi bi-chat-dots"></i> Espace d'échange</h5>
                    </div>
                    <div class="card-body p-4 bg-light">
                        <?php if (empty($ticket['messages'])): ?>
                            <p class="text-center text-muted mb-4">Aucun message pour le moment.</p>
                        <?php else: ?>
                            <div class="mb-4">
                                <?php foreach ($ticket['messages'] as $msg): ?>
                                    <div class="card mb-3 <?= $msg['auteur_role'] === 'ETUDIANT' ? '' : 'border-success' ?>">
                                        <div class="card-header <?= $msg['auteur_role'] === 'ETUDIANT' ? 'bg-white' : 'bg-success text-white' ?> py-2 d-flex justify-content-between">
                                            <strong>
                                                <i class="<?= $msg['auteur_role'] === 'ETUDIANT' ? 'bi bi-person-fill' : 'bi bi-headset' ?>"></i> 
                                                <?= htmlspecialchars($msg['auteur_nom']) ?>
                                                <?php if ($msg['auteur_role'] !== 'ETUDIANT'): ?><span class="badge bg-light text-success ms-1">Support</span><?php endif; ?>
                                            </strong>
                                            <small><?= date('d/m/Y H:i', strtotime($msg['created_at'])) ?></small>
                                        </div>
                                        <div class="card-body py-2">
                                            <p class="mb-0" style="white-space: pre-wrap;"><?= htmlspecialchars($msg['message']) ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($ticket['statut'] !== 'RESOLVED'): ?>
                            <form method="POST">
                                <input type="hidden" name="action" value="add_message">
                                <div class="mb-3">
                                    <label for="message" class="form-label fw-bold">Ajouter un commentaire</label>
                                    <textarea class="form-control" id="message" name="message" rows="3" required placeholder="Tapez votre message ici..."></textarea>
                                </div>
                                <div class="text-end">
                                    <button type="submit" class="btn btn-success"><i class="bi bi-send"></i> Envoyer</button>
                                </div>
                            </form>
                        <?php else: ?>
                            <div class="alert alert-secondary text-center">
                                <i class="bi bi-lock-fill"></i> Ce ticket est résolu, vous ne pouvez plus y ajouter de commentaires.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                        <div class="ticket-meta">
                            <h5 class="text-muted mb-3"><i class="bi bi-info-circle"></i> Informations</h5>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2"><strong>Date de création :</strong> <br><?= date('d/m/Y à H:i', strtotime($ticket['created_at'])) ?></li>
                                <li class="mb-2"><strong>Catégorie :</strong> <br><span class="badge bg-secondary"><?= htmlspecialchars($ticket['categorie_nom'] ?? 'Général') ?></span></li>
                                <li class="mb-2"><strong>Priorité :</strong> <br>
                                    <?php if ($ticket['priorite'] === 'HAUTE'): ?>
                                        <span class="text-danger fw-bold"><i class="bi bi-arrow-up-circle"></i> Haute</span>
                                    <?php elseif ($ticket['priorite'] === 'MOYENNE'): ?>
                                        <span class="text-warning text-dark fw-bold"><i class="bi bi-dash-circle"></i> Moyenne</span>
                                    <?php else: ?>
                                        <span class="text-info fw-bold"><i class="bi bi-arrow-down-circle"></i> Basse</span>
                                    <?php endif; ?>
                                </li>
                                <li class="mb-2"><strong>Assigné à :</strong> <br><?= htmlspecialchars($ticket['assigne_nom'] ?? 'Non assigné') ?></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
