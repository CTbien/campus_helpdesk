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
$stats = $ticketService->getStatistics();

// Prepare data for charts
$statusLabels = json_encode(array_keys($stats['by_status']));
$statusData = json_encode(array_values($stats['by_status']));

$priorityLabels = json_encode(array_keys($stats['by_priority']));
$priorityData = json_encode(array_values($stats['by_priority']));

$categoryLabels = json_encode(array_keys($stats['by_category']));
$categoryData = json_encode(array_values($stats['by_category']));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiques - Campus HelpDesk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { background-color: #f8f9fa; }
        .navbar-brand { font-weight: 600; }
        .stat-card { transition: transform 0.2s; border: none; border-radius: 10px; }
        .stat-card:hover { transform: translateY(-3px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important; }
        .chart-container { position: relative; height: 300px; width: 100%; border: none; border-radius: 10px; background: white; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
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
            <h2>Statistiques des Tickets</h2>
            <a href="dashboard.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Retour</a>
        </div>
        
        <!-- Key Metrics -->
        <div class="row g-4 mb-5">
            <div class="col-md-3">
                <div class="card stat-card shadow-sm h-100 bg-primary text-white">
                    <div class="card-body text-center p-4">
                        <div class="display-4 fw-bold mb-2"><?= $stats['total'] ?></div>
                        <h6 class="text-uppercase mb-0">Total des Tickets</h6>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card shadow-sm h-100 border-start border-warning border-4">
                    <div class="card-body text-center p-4">
                        <div class="display-4 fw-bold text-warning mb-2"><?= $stats['by_status']['OPEN'] ?? 0 ?></div>
                        <h6 class="text-muted text-uppercase mb-0">Tickets Ouverts</h6>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card shadow-sm h-100 border-start border-info border-4">
                    <div class="card-body text-center p-4">
                        <div class="display-4 fw-bold text-info mb-2"><?= $stats['by_status']['IN_PROGRESS'] ?? 0 ?></div>
                        <h6 class="text-muted text-uppercase mb-0">En Cours</h6>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card shadow-sm h-100 border-start border-success border-4">
                    <div class="card-body text-center p-4">
                        <div class="display-4 fw-bold text-success mb-2"><?= ($stats['by_status']['RESOLVED'] ?? 0) + ($stats['by_status']['CLOSED'] ?? 0) ?></div>
                        <h6 class="text-muted text-uppercase mb-0">Résolus / Fermés</h6>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="row g-4">
            <div class="col-md-4">
                <div class="chart-container">
                    <h5 class="text-center text-muted mb-3">Répartition par Statut</h5>
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
            <div class="col-md-4">
                <div class="chart-container">
                    <h5 class="text-center text-muted mb-3">Répartition par Priorité</h5>
                    <canvas id="priorityChart"></canvas>
                </div>
            </div>
            <div class="col-md-4">
                <div class="chart-container">
                    <h5 class="text-center text-muted mb-3">Répartition par Catégorie</h5>
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Common palette
        const colors = [
            'rgba(54, 162, 235, 0.7)',
            'rgba(255, 206, 86, 0.7)',
            'rgba(75, 192, 192, 0.7)',
            'rgba(255, 99, 132, 0.7)',
            'rgba(153, 102, 255, 0.7)'
        ];
        
        const borderColors = [
            'rgba(54, 162, 235, 1)',
            'rgba(255, 206, 86, 1)',
            'rgba(75, 192, 192, 1)',
            'rgba(255, 99, 132, 1)',
            'rgba(153, 102, 255, 1)'
        ];

        // Chart defaults
        Chart.defaults.font.family = '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif';
        Chart.defaults.color = '#6c757d';
        
        const commonOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } }
        };

        // Status Chart
        new Chart(document.getElementById('statusChart'), {
            type: 'doughnut',
            data: {
                labels: <?= $statusLabels ?>,
                datasets: [{
                    data: <?= $statusData ?>,
                    backgroundColor: colors,
                    borderColor: borderColors,
                    borderWidth: 1
                }]
            },
            options: commonOptions
        });

        // Priority Chart
        new Chart(document.getElementById('priorityChart'), {
            type: 'pie',
            data: {
                labels: <?= $priorityLabels ?>,
                datasets: [{
                    data: <?= $priorityData ?>,
                    backgroundColor: colors,
                    borderColor: borderColors,
                    borderWidth: 1
                }]
            },
            options: commonOptions
        });

        // Category Chart
        new Chart(document.getElementById('categoryChart'), {
            type: 'bar',
            data: {
                labels: <?= $categoryLabels ?>,
                datasets: [{
                    label: 'Nombre de tickets',
                    data: <?= $categoryData ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.7)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                ...commonOptions,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
