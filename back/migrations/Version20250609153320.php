<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250609153320 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initial schema with forum, categories, posts, messages, users, machines, rights, logs, moderations';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE categorie_forum (id SERIAL NOT NULL, forum_id INT DEFAULT NULL, name VARCHAR(30) NOT NULL, ordre SMALLINT NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_7053531D29CCBAD0 ON categorie_forum (forum_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE droit (id SERIAL NOT NULL, libelle VARCHAR(30) NOT NULL, description TEXT DEFAULT NULL, role_name VARCHAR(30) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE droit_utilisateur (droit_id INT NOT NULL, utilisateur_id INT NOT NULL, PRIMARY KEY(droit_id, utilisateur_id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_E7C0E0215AA93370 ON droit_utilisateur (droit_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_E7C0E021FB88E14F ON droit_utilisateur (utilisateur_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE forum (id SERIAL NOT NULL, utilisateur_id INT DEFAULT NULL, titre VARCHAR(30) NOT NULL, date_creation TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, date_cloture TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, description TEXT DEFAULT NULL, ordre_affichage INT NOT NULL, visible BOOLEAN NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_852BBECDFB88E14F ON forum (utilisateur_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE info_machine (id SERIAL NOT NULL, machine_id INT DEFAULT NULL, text TEXT NOT NULL, type VARCHAR(30) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_97C0F61AF6B75B26 ON info_machine (machine_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE log_login (id SERIAL NOT NULL, date TIMESTAMP(0) WITH TIME ZONE NOT NULL, login VARCHAR(30) NOT NULL, ip_public VARCHAR(20) NOT NULL, success BOOLEAN NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE machine (id SERIAL NOT NULL, forum_id INT DEFAULT NULL, uuid UUID NOT NULL, name VARCHAR(30) NOT NULL, date_creation TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, date_modif TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, visible BOOLEAN NOT NULL, image BYTEA NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_1505DF8429CCBAD0 ON machine (forum_id)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN machine.uuid IS '(DC2Type:uuid)'
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE message (id SERIAL NOT NULL, utilisateur_id INT DEFAULT NULL, post_id INT DEFAULT NULL, text TEXT NOT NULL, date_creation TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, date_modification TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, date_suppresion TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, visible BOOLEAN NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_B6BD307FFB88E14F ON message (utilisateur_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_B6BD307F4B89032C ON message (post_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE moderations (id SERIAL NOT NULL, moderateur_id INT DEFAULT NULL, cible_id INT DEFAULT NULL, type_action VARCHAR(30) NOT NULL, raison VARCHAR(100) NOT NULL, date_action TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_CA75429020A01F78 ON moderations (moderateur_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_CA754290A96E5E09 ON moderations (cible_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE post (
                id SERIAL NOT NULL,
                forum_id INT DEFAULT NULL,
                utilisateur_id INT DEFAULT NULL,
                titre VARCHAR(30) NOT NULL,
                date_creation TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                vues INT NOT NULL,
                verrouille BOOLEAN DEFAULT NULL,
                epingle BOOLEAN DEFAULT NULL,
                PRIMARY KEY(id)
            )
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_5A8A6C8D29CCBAD0 ON post (forum_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_5A8A6C8DFB88E14F ON post (utilisateur_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE utilisateur (id SERIAL NOT NULL, username VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, date_creation DATE NOT NULL, anonimus BOOLEAN NOT NULL, last_visit TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, mail VARCHAR(100) DEFAULT NULL, status VARCHAR(255) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_IDENTIFIER_USERNAME ON utilisateur (username)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE categorie_forum ADD CONSTRAINT FK_7053531D29CCBAD0 FOREIGN KEY (forum_id) REFERENCES forum (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE droit_utilisateur ADD CONSTRAINT FK_E7C0E0215AA93370 FOREIGN KEY (droit_id) REFERENCES droit (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE droit_utilisateur ADD CONSTRAINT FK_E7C0E021FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE forum ADD CONSTRAINT FK_852BBECDFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE info_machine ADD CONSTRAINT FK_97C0F61AF6B75B26 FOREIGN KEY (machine_id) REFERENCES machine (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE machine ADD CONSTRAINT FK_1505DF8429CCBAD0 FOREIGN KEY (forum_id) REFERENCES forum (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE message ADD CONSTRAINT FK_B6BD307FFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE message ADD CONSTRAINT FK_B6BD307F4B89032C FOREIGN KEY (post_id) REFERENCES post (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE moderations ADD CONSTRAINT FK_CA75429020A01F78 FOREIGN KEY (moderateur_id) REFERENCES utilisateur (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE moderations ADD CONSTRAINT FK_CA754290A96E5E09 FOREIGN KEY (cible_id) REFERENCES utilisateur (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8D29CCBAD0 FOREIGN KEY (forum_id) REFERENCES forum (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8DFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE categorie_forum DROP CONSTRAINT FK_7053531D29CCBAD0
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE droit_utilisateur DROP CONSTRAINT FK_E7C0E0215AA93370
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE droit_utilisateur DROP CONSTRAINT FK_E7C0E021FB88E14F
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE forum DROP CONSTRAINT FK_852BBECDFB88E14F
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE info_machine DROP CONSTRAINT FK_97C0F61AF6B75B26
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE machine DROP CONSTRAINT FK_1505DF8429CCBAD0
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE message DROP CONSTRAINT FK_B6BD307FFB88E14F
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE message DROP CONSTRAINT FK_B6BD307F4B89032C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE moderations DROP CONSTRAINT FK_CA75429020A01F78
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE moderations DROP CONSTRAINT FK_CA754290A96E5E09
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post DROP CONSTRAINT FK_5A8A6C8D29CCBAD0
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post DROP CONSTRAINT FK_5A8A6C8DFB88E14F
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IF EXISTS IDX_5A8A6C8DFB88E14F
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE categorie_forum
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE droit
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE droit_utilisateur
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE forum
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE info_machine
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE log_login
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE machine
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE message
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE moderations
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE post
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE utilisateur
        SQL);
    }
}
