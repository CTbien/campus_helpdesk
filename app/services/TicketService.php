<?php
// app/services/TicketService.php
declare(strict_types=1);

require_once __DIR__ . '/../repositories/TicketRepo.php';
require_once __DIR__ . '/../repositories/CategoryRepo.php';

final class TicketService {
    private TicketRepo $ticketRepo;
    private CategoryRepo $categoryRepo;

    public function __construct() {
        $this->ticketRepo = new TicketRepo();
        $this->categoryRepo = new CategoryRepo();
    }

    public function getAllCategories(): array {
        return $this->categoryRepo->getAllCategories();
    }

    public function createTicket(array $data, int $userId): array {
        try {
            $title = trim($data['titre'] ?? '');
            $description = trim($data['description'] ?? '');
            $categoryId = $data['categorie_id'] ?? null;
            $newCategory = trim($data['nouvelle_categorie'] ?? '');
            $priority = $data['priorite'] ?? 'MOYENNE';

            if (empty($title) || empty($description)) {
                return ['success' => false, 'error' => 'Le titre et la description sont obligatoires.'];
            }

            // Ensure priority is valid
            $validPriorities = ['BASSE', 'MOYENNE', 'HAUTE'];
            if (!in_array($priority, $validPriorities)) {
                $priority = 'MOYENNE';
            }

            // Handle category
            if ($categoryId === 'autre' && !empty($newCategory)) {
                $catId = $this->categoryRepo->getOrCreateCategory($newCategory);
            } elseif (is_numeric($categoryId)) {
                $catId = (int)$categoryId;
            } else {
                return ['success' => false, 'error' => 'La catégorie est invalide.'];
            }

            $ticketId = $this->ticketRepo->createTicket($title, $description, $catId, $priority, $userId);

            return [
                'success' => true,
                'ticket_id' => $ticketId,
                'message' => 'Ticket créé avec succès.'
            ];
        } catch (PDOException $e) {
            error_log("DB Error in createTicket: " . $e->getMessage());
            return ['success' => false, 'error' => 'Erreur lors de la création du ticket.'];
        }
    }

    public function getStudentTickets(int $userId): array {
        return $this->ticketRepo->getTicketsByUser($userId);
    }

    public function getTechTickets(?string $status = null, ?string $priority = null): array {
        return $this->ticketRepo->getTicketsForTech($status, $priority);
    }

    public function getAdminTickets(?string $status = null, ?string $priority = null): array {
        return $this->ticketRepo->getTicketsForAdmin($status, $priority);
    }

    public function getTicketDetails(int $ticketId, int $userId, string $userRole): ?array {
        $ticket = $this->ticketRepo->getTicketById($ticketId);
        
        if (!$ticket) {
            return null;
        }

        // Validate access
        if ($userRole === 'ETUDIANT' && (int)$ticket['cree_par'] !== $userId) {
            return null; // Don't allow students to see other students' tickets
        }

        $messages = $this->ticketRepo->getMessagesByTicket($ticketId);
        $ticket['messages'] = $messages;

        return $ticket;
    }

    public function updateStatus(int $ticketId, string $status, string $userRole): array {
        if ($userRole === 'ETUDIANT') {
            return ['success' => false, 'error' => 'Action non autorisée.'];
        }

        $validStatuses = ['OPEN', 'IN_PROGRESS', 'RESOLVED'];
        if (!in_array($status, $validStatuses)) {
            return ['success' => false, 'error' => 'Statut invalide.'];
        }

        $success = $this->ticketRepo->updateTicketStatus($ticketId, $status);
        if ($success) {
            return ['success' => true, 'message' => 'Statut mis à jour.'];
        }
        return ['success' => false, 'error' => 'Erreur lors de la mise à jour.'];
    }

    public function assignToTech(int $ticketId, int $techId, string $userRole): array {
        if ($userRole === 'ETUDIANT') {
            return ['success' => false, 'error' => 'Action non autorisée.'];
        }

        $success = $this->ticketRepo->assignTicket($ticketId, $techId);
        if ($success) {
            return ['success' => true, 'message' => 'Ticket assigné avec succès.'];
        }
        return ['success' => false, 'error' => 'Erreur lors de l\'assignation.'];
    }

    public function addMessage(int $ticketId, int $userId, string $userRole, string $message): array {
        if ($userRole === 'ETUDIANT') {
            $ticket = $this->ticketRepo->getTicketById($ticketId);
            if (!$ticket || (int)$ticket['cree_par'] !== $userId) {
                return ['success' => false, 'error' => 'Action non autorisée.'];
            }
        }

        $message = trim($message);
        if (empty($message)) {
            return ['success' => false, 'error' => 'Le message ne peut pas être vide.'];
        }

        $success = $this->ticketRepo->addMessage($ticketId, $userId, $message);
        if ($success) {
            return ['success' => true, 'message' => 'Message envoyé.'];
        }
        return ['success' => false, 'error' => 'Erreur lors de l\'envoi du message.'];
    }
}
