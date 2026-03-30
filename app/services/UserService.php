<?php
// app/services/UserService.php
declare(strict_types=1);

require_once __DIR__ . '/../repositories/UserRepo.php';

final class UserService {
    private UserRepo $userRepo;

    public function __construct() {
        $this->userRepo = new UserRepo();
    }

    public function getAllUsers(): array {
        return $this->userRepo->findAll();
    }

    public function getUserById(int $id): ?array {
        return $this->userRepo->findById($id);
    }

    public function createUser(array $data): array {
        $nom = trim($data['nom'] ?? '');
        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';
        $role = $data['role'] ?? 'ETUDIANT';

        if (empty($nom) || empty($email) || empty($password)) {
            return ['success' => false, 'error' => 'Tous les champs obligatoires doivent être remplis.'];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'error' => 'L\'adresse email est invalide.'];
        }

        $validRoles = ['ETUDIANT', 'TECH', 'ADMIN'];
        if (!in_array($role, $validRoles)) {
            $role = 'ETUDIANT';
        }

        if ($this->userRepo->findByEmail($email)) {
            return ['success' => false, 'error' => 'Un utilisateur avec cet email existe déjà.'];
        }

        $mdpHash = password_hash($password, PASSWORD_DEFAULT);

        try {
            $userId = $this->userRepo->create($nom, $email, $mdpHash, $role);
            return [
                'success' => true,
                'user_id' => $userId,
                'message' => 'Utilisateur créé avec succès.'
            ];
        } catch (PDOException $e) {
            error_log("DB Error in createUser: " . $e->getMessage());
            return ['success' => false, 'error' => 'Erreur lors de la création de l\'utilisateur.'];
        }
    }

    public function updateUser(int $id, array $data): array {
        $nom = trim($data['nom'] ?? '');
        $email = trim($data['email'] ?? '');
        $role = $data['role'] ?? 'ETUDIANT';
        
        if (empty($nom) || empty($email)) {
            return ['success' => false, 'error' => 'Tous les champs obligatoires doivent être remplis.'];
        }

        $validRoles = ['ETUDIANT', 'TECH', 'ADMIN'];
        if (!in_array($role, $validRoles)) {
            $role = 'ETUDIANT';
        }

        $existingUser = $this->userRepo->findByEmail($email);
        if ($existingUser && (int)$existingUser['id'] !== $id) {
            return ['success' => false, 'error' => 'Un autre utilisateur utilise déjà cet email.'];
        }

        try {
            $success = $this->userRepo->update($id, $nom, $email, $role);
            if ($success) {
                // Handle optional password update
                if (!empty($data['password'])) {
                    $mdpHash = password_hash($data['password'], PASSWORD_DEFAULT);
                    $this->userRepo->updatePassword($id, $mdpHash);
                }
                return ['success' => true, 'message' => 'Utilisateur mis à jour.'];
            }
            return ['success' => false, 'error' => 'Erreur lors de la mise à jour.'];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => 'Une erreur base de données est survenue.'];
        }
    }

    public function toggleUserStatus(int $id): array {
        try {
            $success = $this->userRepo->toggleStatus($id);
            if ($success) {
                return ['success' => true, 'message' => 'Statut de l\'utilisateur modifié.'];
            }
            return ['success' => false, 'error' => 'Erreur lors du changement de statut.'];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => 'Une erreur est survenue.'];
        }
    }
}
