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
        return 'Ajoute trois utilisateurs de test (admin, user, editeur) avec mot de passe hashé pour le rendu.';
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
    }

    public function down(Schema $schema): void
    {
        // Suppression des utilisateurs initiaux
        $this->addSql("DELETE FROM utilisateur WHERE mail IN ('admin@test.com', 'user@test.com', 'editeur@test.com')");
    }
}
