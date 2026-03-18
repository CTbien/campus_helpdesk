-- db/seed.sql
USE campus_helpdesk;

INSERT INTO categories (libelle) VALUES
('Réseau / Wi‑Fi'),
('Matériel'),
('Logiciel'),
('Compte / Accès');

-- Comptes de test : mot de passe conseillé Demo1234!
INSERT INTO utilisateurs (nom, email, mdp_hash, role) VALUES
('Etudiant Test', 'student@campus.local', '$2y$10$REPLACE_ME_STUDENT_HASH', 'ETUDIANT'),
('Tech Test',     'tech@campus.local',    '$2y$10$REPLACE_ME_TECH_HASH',    'TECH'),
('Admin Test',    'admin@campus.local',   '$2y$10$REPLACE_ME_ADMIN_HASH',   'ADMIN');

-- Tickets d'exemple (créés par l'étudiant id=1)
INSERT INTO tickets (titre, description, priorite, statut, categorie_id, cree_par, assigne_a) VALUES
('Wi‑Fi instable salle 105', 'Coupures toutes les 5 minutes.', 'MOYENNE', 'OPEN', 1, 1, NULL),
('Projecteur HS salle 204', 'Plus d\'image depuis ce matin.', 'HAUTE', 'IN_PROGRESS', 2, 1, 2);

INSERT INTO messages_ticket (ticket_id, user_id, message) VALUES
(1, 1, 'Bonjour, le Wi‑Fi se coupe souvent.'),
(2, 2, 'Je prends en charge le ticket. Je passe dans la journée.');