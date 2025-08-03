<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250726210757 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout des catégories de forum par défaut dans la table categorie_forum';
    }

    public function up(Schema $schema): void
    {
        $now = date('Y-m-d');
        $this->addSql("INSERT INTO categorie_forum (id, name, ordre, slug, created_at) VALUES (1, 'Accueil', 1, 'accueil', '{$now}')");
        $this->addSql("INSERT INTO categorie_forum (id, name, ordre, slug, created_at) VALUES (2, 'Général', 2, 'general', '{$now}')");
        $this->addSql("INSERT INTO categorie_forum (id, name, ordre, slug, created_at) VALUES (3, 'Blabla', 3, 'blabla', '{$now}')");
        $this->addSql("INSERT INTO categorie_forum (id, name, ordre, slug, created_at) VALUES (4, 'Annonces', 4, 'annonces', '{$now}')");
        $this->addSql("INSERT INTO categorie_forum (id, name, ordre, slug, created_at) VALUES (5, 'Support', 5, 'support', '{$now}')");
        $this->addSql("INSERT INTO categorie_forum (id, name, ordre, slug, created_at) VALUES (6, 'Suggestions', 6, 'suggestions', '{$now}')");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DELETE FROM categorie_forum WHERE id IN (1,2,3,4,5,6)');
    }
}
