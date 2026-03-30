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

  public function findAll(): array {
    $stmt = db()->query("SELECT id, nom, email, role, actif, created_at FROM utilisateurs ORDER BY nom ASC");
    return $stmt->fetchAll();
  }

  public function findById(int $id): ?array {
    $stmt = db()->prepare("SELECT id, nom, email, role, actif, created_at FROM utilisateurs WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $u = $stmt->fetch();
    return $u ?: null;
  }

  public function create(string $nom, string $email, string $mdpHash, string $role): int {
    $stmt = db()->prepare(
      "INSERT INTO utilisateurs (nom, email, mdp_hash, role, actif, created_at)
       VALUES (:nom, :email, :mdp_hash, :role, 1, NOW())"
    );
    $stmt->execute([
      'nom' => $nom,
      'email' => $email,
      'mdp_hash' => $mdpHash,
      'role' => $role
    ]);
    return (int)db()->lastInsertId();
  }

  public function update(int $id, string $nom, string $email, string $role): bool {
    $stmt = db()->prepare(
      "UPDATE utilisateurs SET nom = :nom, email = :email, role = :role WHERE id = :id"
    );
    return $stmt->execute([
      'nom' => $nom,
      'email' => $email,
      'role' => $role,
      'id' => $id
    ]);
  }
  
  public function updatePassword(int $id, string $mdpHash): bool {
    $stmt = db()->prepare("UPDATE utilisateurs SET mdp_hash = :mdp_hash WHERE id = :id");
    return $stmt->execute(['mdp_hash' => $mdpHash, 'id' => $id]);
  }

  public function toggleStatus(int $id): bool {
    $stmt = db()->prepare("UPDATE utilisateurs SET actif = NOT actif WHERE id = :id");
    return $stmt->execute(['id' => $id]);
  }
}