<?php
// app/repositories/UserRepo.php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

final class UserRepo {
  public function findByEmail(string $email): ?array {
    $stmt = db()->prepare(
      "SELECT id, nom, email, mdp_hash, role, actif
       FROM utilisateurs
       WHERE email = :email
       LIMIT 1"
    );
    $stmt->execute(['email' => $email]);
    $u = $stmt->fetch();
    return $u ?: null;
  }
}