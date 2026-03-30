<?php
// app/repositories/TicketRepo.php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

final class TicketRepo {
    public function createTicket(string $title, string $description, int $categoryId, string $priority, int $userId): int {
        $stmt = db()->prepare(
            "INSERT INTO tickets (titre, description, categorie_id, priorite, cree_par, statut, created_at)
             VALUES (:title, :description, :cat_id, :priority, :user_id, 'OPEN', NOW())"
        );
        $stmt->execute([
            'title' => $title,
            'description' => $description,
            'cat_id' => $categoryId,
            'priority' => $priority,
            'user_id' => $userId
        ]);
        return (int)db()->lastInsertId();
    }

    public function getTicketsByUser(int $userId): array {
        $stmt = db()->prepare(
            "SELECT t.*, c.libelle as categorie_nom
             FROM tickets t
             LEFT JOIN categories c ON t.categorie_id = c.id
             WHERE t.cree_par = :user_id
             ORDER BY t.created_at DESC"
        );
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function getTicketsForTech(?string $status = null, ?string $priority = null): array {
        $sql = "SELECT t.*, c.libelle as categorie_nom, u.nom as auteur_nom, u.email as auteur_email
                FROM tickets t
                LEFT JOIN categories c ON t.categorie_id = c.id
                LEFT JOIN utilisateurs u ON t.cree_par = u.id
                WHERE 1=1";
        $params = [];

        if ($status && $status !== 'ALL') {
            $sql .= " AND t.statut = :status";
            $params['status'] = $status;
        }

        if ($priority && $priority !== 'ALL') {
            $sql .= " AND t.priorite = :priority";
            $params['priority'] = $priority;
        }

        $sql .= " ORDER BY 
                CASE t.statut
                    WHEN 'OPEN' THEN 1
                    WHEN 'IN_PROGRESS' THEN 2
                    WHEN 'RESOLVED' THEN 3
                    ELSE 4
                END,
                t.created_at DESC";

        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getTicketsForAdmin(?string $status = null, ?string $priority = null): array {
        // Admin sees all tickets similar to Tech
        return $this->getTicketsForTech($status, $priority);
    }

    public function getTicketById(int $id): ?array {
        $stmt = db()->prepare(
            "SELECT t.*, c.libelle as categorie_nom, u.nom as auteur_nom, u.email as auteur_email,
                    a.nom as assigne_nom
             FROM tickets t
             LEFT JOIN categories c ON t.categorie_id = c.id
             LEFT JOIN utilisateurs u ON t.cree_par = u.id
             LEFT JOIN utilisateurs a ON t.assigne_a = a.id
             WHERE t.id = :id"
        );
        $stmt->execute(['id' => $id]);
        $ticket = $stmt->fetch();
        return $ticket ?: null;
    }

    public function updateTicketStatus(int $id, string $status): bool {
        $stmt = db()->prepare("UPDATE tickets SET statut = :status WHERE id = :id");
        return $stmt->execute(['status' => $status, 'id' => $id]);
    }

    public function assignTicket(int $id, int $techId): bool {
        $stmt = db()->prepare("UPDATE tickets SET assigne_a = :tech_id, statut = 'IN_PROGRESS' WHERE id = :id");
        return $stmt->execute(['tech_id' => $techId, 'id' => $id]);
    }

    public function addMessage(int $ticketId, int $userId, string $message): bool {
        $stmt = db()->prepare(
            "INSERT INTO messages_ticket (ticket_id, user_id, message) VALUES (:ticket_id, :user_id, :msg)"
        );
        return $stmt->execute([
            'ticket_id' => $ticketId,
            'user_id' => $userId,
            'msg' => $message
        ]);
    }

    public function getMessagesByTicket(int $ticketId): array {
        $stmt = db()->prepare(
            "SELECT m.*, u.nom as auteur_nom, u.role as auteur_role
             FROM messages_ticket m
             JOIN utilisateurs u ON m.user_id = u.id
             WHERE m.ticket_id = :ticket_id
             ORDER BY m.created_at ASC"
        );
        $stmt->execute(['ticket_id' => $ticketId]);
        return $stmt->fetchAll();
    }

    public function getStatistics(): array {
        $stats = [
            'total' => 0,
            'by_status' => [],
            'by_priority' => [],
            'by_category' => []
        ];

        // Total
        $stmt = db()->query("SELECT COUNT(*) as cnt FROM tickets");
        $stats['total'] = (int)$stmt->fetch()['cnt'];

        // By Status
        $stmt = db()->query("SELECT statut, COUNT(*) as cnt FROM tickets GROUP BY statut");
        foreach ($stmt->fetchAll() as $row) {
            $stats['by_status'][$row['statut']] = (int)$row['cnt'];
        }

        // By Priority
        $stmt = db()->query("SELECT priorite, COUNT(*) as cnt FROM tickets GROUP BY priorite");
        foreach ($stmt->fetchAll() as $row) {
            $stats['by_priority'][$row['priorite']] = (int)$row['cnt'];
        }

        // By Category
        $stmt = db()->query(
            "SELECT c.libelle, COUNT(t.id) as cnt 
             FROM categories c 
             LEFT JOIN tickets t ON c.id = t.categorie_id 
             GROUP BY c.id, c.libelle"
        );
        foreach ($stmt->fetchAll() as $row) {
            $stats['by_category'][$row['libelle']] = (int)$row['cnt'];
        }

        return $stats;
    }
}
