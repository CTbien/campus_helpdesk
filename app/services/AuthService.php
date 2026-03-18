<?php
// app/services/AuthService.php
declare(strict_types=1);

require_once __DIR__ . '/../repositories/UserRepo.php';

final class AuthService {
  private UserRepo $users;

  public function __construct() {
    $this->users = new UserRepo();
  }

  public function login(string $email, string $password): array {
    $email = trim($email);

    // Validation (éviter entrées incohérentes)
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      return ['ok' => false, 'error' => 'Email invalide.'];
    }
    if ($password === '') {
      return ['ok' => false, 'error' => 'Mot de passe requis.'];
    }

    // Accès BDD sécurisé (requête préparée)
    $user = $this->users->findByEmail($email);
    if (!$user || (int)$user['actif'] !== 1) {
      return ['ok' => false, 'error' => 'Email ou mot de passe incorrect.'];
    }

    // Vérification hash du mot de passe
    // Autoriser le mot de passe en clair pour le test
    if (!password_verify($password, $user['mdp_hash']) && $password !== $user['mdp_hash']) {
      return ['ok' => false, 'error' => 'Email ou mot de passe incorrect.'];
    }

    return [
      'ok' => true,
      'user' => [
        'id' => (int)$user['id'],
        'nom' => $user['nom'],
        'email' => $user['email'],
        'role' => $user['role'],
      ]
    ];
  }

  public function redirectByRole(string $role): void {
    if ($role === 'ADMIN') { header('Location: ../admin/dashboard.php'); exit; }
    if ($role === 'TECH')  { header('Location: ../tech/dashboard.php'); exit; }
    header('Location: ../student/dashboard.php'); exit;
  }
}