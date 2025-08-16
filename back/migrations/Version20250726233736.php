<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250726233736 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "Ajoute des données de test : trois utilisateurs (admin, user, editeur) avec mot de passe hashé, un forum, plusieurs machines, des infos machines et un post d'exemple pour le rendu.";
    }

    /**
     * Création des utilisateurs initiaux
     * ! ATTENTION : Les mots de passe en clair sont indiqués ci-dessous UNIQUEMENT pour la correction du rendu.
     * Ils ne sont pas stockés en clair en base, seuls les hash sont insérés.
     * admin@test.com : TestAdmin123!
     * user@test.com : TestUser123!
     * editeur@test.com : TestEditeur123!
     */
    public function up(Schema $schema): void
    {
        $adminPassword = password_hash('TestAdmin123!', PASSWORD_BCRYPT);
        $userPassword = password_hash('TestUser123!', PASSWORD_BCRYPT);
        $editeurPassword = password_hash('TestEditeur123!', PASSWORD_BCRYPT);
        $this->addSql(
            "INSERT INTO utilisateur (username, mail, password, roles, date_creation, anonimus, status, last_visit, created_at) VALUES\n" .
            "('admin', 'admin@test.com', '" . $adminPassword . "', '[\"ROLE_ADMIN\"]', '2025-07-27', false, 'active', '2025-07-27', '2025-07-27 10:00:00'),\n" .
            "('user', 'user@test.com', '" . $userPassword . "', '[\"ROLE_USER\"]', '2025-07-27', false, 'active', '2025-07-27', '2025-07-27 10:00:00'),\n" .
            "('editeur', 'editeur@test.com', '" . $editeurPassword . "', '[\"ROLE_EDITEUR\"]', '2025-07-27', false, 'active', '2025-07-27', '2025-07-27 10:00:00')"
        );

        // Ajout du forum de test
        $this->addSql(
            'INSERT INTO forum (utilisateur_id, titre, date_creation, date_cloture, description, ordre_affichage, visible, slug, created_at, updated_at, deleted_at) VALUES ' .
            "((SELECT id FROM utilisateur WHERE username = 'admin'), 'fezfez', '2025-08-03 20:20:48', NULL, '', 1, TRUE, '', '2025-08-03', NULL, NULL)"
        );

        // Ajout des machines
        $this->addSql("INSERT INTO machine (forum_id, uuid, name, date_creation, date_modif, visible, image, description, utilisateur_id) VALUES
            (NULL, '019870e7-ec64-77fa-9909-6dc7379243de', 'Haltères', '2025-08-03 19:08:24', '2025-08-03 19:28:08', TRUE, 'https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Fimages-eu.ssl-images-amazon.com%2Fimages%2FI%2F51MPR-165YL._AC_US500_QL65_.jpg&f=1&nofb=1&ipt=90eb650cc3f8a66bf375c3e82fade3818b4a74120b14a5fb41fecb6a0259a81f', 'Les haltères sont des équipements de musculation compacts et essentiels pour le renforcement musculaire à domicile ou en salle. Utilisables pour une large variété d''exercices (biceps, triceps, épaules, pectoraux, dos, jambes), ils permettent de travailler en charge libre, favorisant la coordination, l''équilibre et le gain de force.  Disponibles en versions fixes (poids défini) ou réglables (disques interchangeables ou mécanisme rotatif), ils s''adaptent à tous les niveaux : débutants comme confirmés', NULL),
            (NULL, '019870ee-3773-78aa-8523-5fe15a868d8b', 'Appareil de musculation Press', '2025-08-03 19:15:16', NULL, TRUE, 'https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Fwww.carefitness.com%2F1637-superlarge_default%2Fpresse-de-musculation-a-cuisses-45.jpg&f=1&nofb=1&ipt=2195a7020c4a429470a7e020c6d6a415d5ad97f2b8ef578ac1abd49f7796bbc6', 'L''appareil de musculation Press est conçu pour développer efficacement la force et la masse musculaire, tout en assurant un mouvement guidé et sécurisé. Idéal pour cibler des groupes musculaires spécifiques sans surcharge articulaire, il convient aussi bien aux débutants qu''aux sportifs expérimentés.  Selon le modèle (presse à jambes, chest press, shoulder press, etc.), il permet de travailler en isolation les muscles des jambes, pectoraux, épaules ou triceps.', NULL),
            (NULL, '019870f3-9205-7fe4-825c-ba83fae007d7', 'Home Gym', '2025-08-03 19:21:07', NULL, TRUE, 'https://www.fitnessboutique.fr/cdn/shop/files/powphg1000x_principale_f.jpg?v=1734605450&width=1214', 'Le Home Gym est une station de musculation multifonctions conçue pour offrir un entraînement complet du corps, directement depuis chez vous. Compact, polyvalent et robuste, il regroupe plusieurs appareils en un seul pour travailler tous les groupes musculaires : bras, pectoraux, dos, jambes, abdominaux…  Parfait pour les utilisateurs de tous niveaux, il permet d''enchaîner les exercices sans changer de machine : poulies hautes et basses, développé assis (chest press), leg curl, butterfly, tirage horizontal, et plus encore.', NULL),
            (NULL, '01987115-f9a2-794e-a1ad-6c9d87691603', 'Tapis de course', '2025-08-03 19:58:42', NULL, TRUE, 'https://contents.mediadecathlon.com/m18540130/k$76ae13ebcbd806ee306763d0a23bbea4/sq/m7000-tapis-de-course.jpg', 'Le tapis de course électrique est un équipement incontournable pour pratiquer la course à pied ou la marche rapide chez soi. Que vous soyez débutant ou sportif confirmé, il vous permet d''améliorer votre endurance, brûler des calories et maintenir votre forme tout au long de l''année, sans contraintes météo.', NULL)
        ");

        // Ajout des infos machines - Version optimisée pour PostgreSQL 15
        $this->addSql("INSERT INTO info_machine (machine_id, text, type) VALUES
            -- Haltères (019870e7-ec64-77fa-9909-6dc7379243de)
            ((SELECT id FROM machine WHERE uuid = '019870e7-ec64-77fa-9909-6dc7379243de'), 'Polyvalent : travaille tous les groupes musculaires', 'carac'),
            ((SELECT id FROM machine WHERE uuid = '019870e7-ec64-77fa-9909-6dc7379243de'), 'Compact et peu encombrant', 'carac'),
            ((SELECT id FROM machine WHERE uuid = '019870e7-ec64-77fa-9909-6dc7379243de'), 'Adapté au renforcement, tonification et prise de masse', 'carac'),
            ((SELECT id FROM machine WHERE uuid = '019870e7-ec64-77fa-9909-6dc7379243de'), 'Excellent pour circuits training et entraînements fonctionnels', 'carac'),
            ((SELECT id FROM machine WHERE uuid = '019870e7-ec64-77fa-9909-6dc7379243de'), 'Matériaux variés : fonte, acier chromé, caoutchouc, néoprène', 'autre'),
            ((SELECT id FROM machine WHERE uuid = '019870e7-ec64-77fa-9909-6dc7379243de'), 'Favorise coordination et équilibre musculaire', 'carac'),
            ((SELECT id FROM machine WHERE uuid = '019870e7-ec64-77fa-9909-6dc7379243de'), 'Disponible en version fixe ou réglable', 'autre'),
            
            -- Appareil de musculation Press (019870ee-3773-78aa-8523-5fe15a868d8b)
            ((SELECT id FROM machine WHERE uuid = '019870ee-3773-78aa-8523-5fe15a868d8b'), 'Mouvement guidé pour un entraînement sécurisé', 'sécurité'),
            ((SELECT id FROM machine WHERE uuid = '019870ee-3773-78aa-8523-5fe15a868d8b'), 'Ciblage précis des groupes musculaires', 'carac'),
            ((SELECT id FROM machine WHERE uuid = '019870ee-3773-78aa-8523-5fe15a868d8b'), 'Réduction significative du risque de blessure', 'sécurité'),
            ((SELECT id FROM machine WHERE uuid = '019870ee-3773-78aa-8523-5fe15a868d8b'), 'Progression contrôlée en charge', 'carac'),
            ((SELECT id FROM machine WHERE uuid = '019870ee-3773-78aa-8523-5fe15a868d8b'), 'Confort optimal avec réglages ergonomiques', 'confort'),
            ((SELECT id FROM machine WHERE uuid = '019870ee-3773-78aa-8523-5fe15a868d8b'), 'Structure robuste en acier renforcé', 'carac'),
            ((SELECT id FROM machine WHERE uuid = '019870ee-3773-78aa-8523-5fe15a868d8b'), 'Siège multi-positions ajustable', 'confort'),
            ((SELECT id FROM machine WHERE uuid = '019870ee-3773-78aa-8523-5fe15a868d8b'), 'Système de résistance par charge guidée', 'carac'),
            ((SELECT id FROM machine WHERE uuid = '019870ee-3773-78aa-8523-5fe15a868d8b'), 'Poignées ergonomiques antidérapantes', 'confort'),
            
            -- Home Gym (019870f3-9205-7fe4-825c-ba83fae007d7)
            ((SELECT id FROM machine WHERE uuid = '019870f3-9205-7fe4-825c-ba83fae007d7'), 'Station tout-en-un pour entraînement complet', 'carac'),
            ((SELECT id FROM machine WHERE uuid = '019870f3-9205-7fe4-825c-ba83fae007d7'), 'Optimisation maximale de l''espace disponible', 'autre'),
            ((SELECT id FROM machine WHERE uuid = '019870f3-9205-7fe4-825c-ba83fae007d7'), 'Multi-exercices : poulies, chest press, leg curl, butterfly', 'carac'),
            ((SELECT id FROM machine WHERE uuid = '019870f3-9205-7fe4-825c-ba83fae007d7'), 'Construction robuste et durable', 'carac'),
            ((SELECT id FROM machine WHERE uuid = '019870f3-9205-7fe4-825c-ba83fae007d7'), 'Adapté à tous niveaux : débutant à confirmé', 'usage'),
            ((SELECT id FROM machine WHERE uuid = '019870f3-9205-7fe4-825c-ba83fae007d7'), 'Transition fluide entre exercices sans changement de machine', 'confort'),
            
            -- Tapis de course (01987115-f9a2-794e-a1ad-6c9d87691603)
            ((SELECT id FROM machine WHERE uuid = '01987115-f9a2-794e-a1ad-6c9d87691603'), 'Entraînement cardiovasculaire complet', 'carac'),
            ((SELECT id FROM machine WHERE uuid = '01987115-f9a2-794e-a1ad-6c9d87691603'), 'Utilisation toute l''année sans contraintes météo', 'autre'),
            ((SELECT id FROM machine WHERE uuid = '01987115-f9a2-794e-a1ad-6c9d87691603'), 'Amélioration endurance et combustion optimale des calories', 'carac'),
            ((SELECT id FROM machine WHERE uuid = '01987115-f9a2-794e-a1ad-6c9d87691603'), 'Système d''amortissement avancé pour protection articulaire', 'confort'),
            ((SELECT id FROM machine WHERE uuid = '01987115-f9a2-794e-a1ad-6c9d87691603'), 'Vitesse et inclinaison entièrement réglables', 'confort'),
            ((SELECT id FROM machine WHERE uuid = '01987115-f9a2-794e-a1ad-6c9d87691603'), 'Adapté du débutant au sportif confirmé', 'usage'),
            ((SELECT id FROM machine WHERE uuid = '01987115-f9a2-794e-a1ad-6c9d87691603'), 'Interface utilisateur intuitive et conviviale', 'confort'),
            ((SELECT id FROM machine WHERE uuid = '01987115-f9a2-794e-a1ad-6c9d87691603'), 'Programmes d''entraînement prédéfinis', 'autre')
        ");

        // Ajout du post de test
        $this->addSql("INSERT INTO post (forum_id, titre, date_creation, vues, verrouille, epingle, utilisateur_id) VALUES
            ((SELECT id FROM forum WHERE titre = 'fezfez'), 'efzfez', '2025-08-03 20:33:00', 0, FALSE, FALSE, (SELECT id FROM utilisateur WHERE username = 'admin'))
        ");
    }

    public function down(Schema $schema): void
    {
        // Suppression des infos machines
        $this->addSql("DELETE FROM info_machine WHERE machine_id IN (
            SELECT id FROM machine WHERE uuid IN (
                '019870e7-ec64-77fa-9909-6dc7379243de',
                '019870ee-3773-78aa-8523-5fe15a868d8b', 
                '019870f3-9205-7fe4-825c-ba83fae007d7',
                '01987115-f9a2-794e-a1ad-6c9d87691603'
            )
        )");

        // Suppression du post de test
        $this->addSql("DELETE FROM post WHERE titre = 'efzfez'");

        // Suppression des machines
        $this->addSql("DELETE FROM machine WHERE uuid IN (
            '019870e7-ec64-77fa-9909-6dc7379243de',
            '019870ee-3773-78aa-8523-5fe15a868d8b', 
            '019870f3-9205-7fe4-825c-ba83fae007d7',
            '01987115-f9a2-794e-a1ad-6c9d87691603'
        )");

        // Suppression du forum de test
        $this->addSql("DELETE FROM forum WHERE titre = 'fezfez'");

        // Suppression des utilisateurs de test
        $this->addSql("DELETE FROM utilisateur WHERE mail IN ('admin@test.com', 'user@test.com', 'editeur@test.com')");
    }
}
