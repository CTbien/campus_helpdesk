<?php
// app/middleware/auth.php
declare(strict_types=1);

function requireLogin(): void {
  if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
  }
}

function requireRole(array $roles): void {
  $role = $_SESSION['user']['role'] ?? null;
  if (!$role || !in_array($role, $roles, true)) {
    http_response_code(403);
    echo "<h1>403 Interdit</h1><p>Accès refusé.</p>";
    exit;
  }
}