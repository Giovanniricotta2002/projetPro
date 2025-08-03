<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250720113327 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE machine ADD utilisateur_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE machine ADD CONSTRAINT FK_1505DF84FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_1505DF84FB88E14F ON machine (utilisateur_id)');
        $this->addSql('ALTER TABLE utilisateur DROP CONSTRAINT fk_1d1c63b3763ce8cc');
        $this->addSql('DROP INDEX idx_1d1c63b3763ce8cc');
        $this->addSql('ALTER TABLE utilisateur DROP creation_machine_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE utilisateur ADD creation_machine_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE utilisateur ADD CONSTRAINT fk_1d1c63b3763ce8cc FOREIGN KEY (creation_machine_id) REFERENCES machine (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_1d1c63b3763ce8cc ON utilisateur (creation_machine_id)');
        $this->addSql('ALTER TABLE machine DROP CONSTRAINT FK_1505DF84FB88E14F');
        $this->addSql('DROP INDEX IDX_1505DF84FB88E14F');
        $this->addSql('ALTER TABLE machine DROP utilisateur_id');
    }
}
