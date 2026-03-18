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
}
