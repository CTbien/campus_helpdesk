<?php
declare(strict_types=1);
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'ADMIN') {
    header('Location: ../auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: users.php');
    exit;
}

require_once __DIR__ . '/../../app/services/UserService.php';
$userService = new UserService();

$userId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : null;

if ($userId) {
    if ((int)$_SESSION['user']['id'] === $userId) {
        header('Location: users.php?error=' . urlencode('Vous ne pouvez pas désactiver votre propre compte.'));
        exit;
    }
    
    $result = $userService->toggleUserStatus($userId);
    if ($result['success']) {
        header('Location: users.php?msg=' . urlencode($result['message']));
    } else {
        header('Location: users.php?error=' . urlencode($result['error']));
    }
} else {
    header('Location: users.php?error=' . urlencode('ID utilisateur manquant.'));
}
exit;
