<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250629134712 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE categorie_forum ADD slug VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE categorie_forum ADD created_at DATE NOT NULL');
        $this->addSql('ALTER TABLE categorie_forum ADD updated_at DATE NOT NULL');
        $this->addSql('ALTER TABLE droit ADD scope VARCHAR(30) NOT NULL');
        $this->addSql('ALTER TABLE droit ADD created_at DATE NOT NULL');
        $this->addSql('ALTER TABLE forum ADD slug VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE forum ADD created_at DATE NOT NULL');
        $this->addSql('ALTER TABLE forum ADD updated_at DATE NOT NULL');
        $this->addSql('ALTER TABLE forum ADD deleted_at DATE NOT NULL');
        $this->addSql('ALTER TABLE utilisateur ADD updated_at DATE NOT NULL');
        $this->addSql('ALTER TABLE utilisateur ADD deleted_at DATE NOT NULL');
        $this->addSql('ALTER TABLE utilisateur ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
        $this->addSql('COMMENT ON COLUMN utilisateur.created_at IS \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE utilisateur DROP updated_at');
        $this->addSql('ALTER TABLE utilisateur DROP deleted_at');
        $this->addSql('ALTER TABLE utilisateur DROP created_at');
        $this->addSql('ALTER TABLE droit DROP scope');
        $this->addSql('ALTER TABLE droit DROP created_at');
        $this->addSql('ALTER TABLE categorie_forum DROP slug');
        $this->addSql('ALTER TABLE categorie_forum DROP created_at');
        $this->addSql('ALTER TABLE categorie_forum DROP updated_at');
        $this->addSql('ALTER TABLE forum DROP slug');
        $this->addSql('ALTER TABLE forum DROP created_at');
        $this->addSql('ALTER TABLE forum DROP updated_at');
        $this->addSql('ALTER TABLE forum DROP deleted_at');
    }
}
