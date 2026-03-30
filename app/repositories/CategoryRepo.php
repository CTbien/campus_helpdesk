<?php
// app/repositories/CategoryRepo.php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

final class CategoryRepo {
    public function getAllCategories(): array {
        $stmt = db()->query("SELECT id, libelle FROM categories ORDER BY libelle ASC");
        return $stmt->fetchAll();
    }

    public function getOrCreateCategory(string $name): int {
        $name = trim($name);
        // Check if category exists
        $stmt = db()->prepare("SELECT id FROM categories WHERE libelle = :name LIMIT 1");
        $stmt->execute(['name' => $name]);
        $cat = $stmt->fetch();
        
        if ($cat) {
            return (int)$cat['id'];
        }
        
        // Create new category
        $stmt = db()->prepare("INSERT INTO categories (libelle) VALUES (:name)");
        $stmt->execute(['name' => $name]);
        return (int)db()->lastInsertId();
    }

    public function findById(int $id): ?array {
        $stmt = db()->prepare("SELECT id, libelle FROM categories WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $cat = $stmt->fetch();
        return $cat ?: null;
    }

    public function update(int $id, string $name): bool {
        $name = trim($name);
        $stmt = db()->prepare("UPDATE categories SET libelle = :name WHERE id = :id");
        return $stmt->execute(['name' => $name, 'id' => $id]);
    }

    public function delete(int $id): bool {
        // Can only delete if no tickets are attached. Foreign key will restrict this naturally, but we handle it.
        try {
            $stmt = db()->prepare("DELETE FROM categories WHERE id = :id");
            return $stmt->execute(['id' => $id]);
        } catch (PDOException $e) {
            // Cannot delete because of constraints
            return false;
        }
    }
}
