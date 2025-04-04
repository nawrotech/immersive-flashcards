<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250404041008 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TEMPORARY TABLE __temp__deck AS SELECT id, creator_id, name, lang FROM deck
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE deck
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE deck (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, creator_id INTEGER DEFAULT NULL, name VARCHAR(100) NOT NULL, lang VARCHAR(2) DEFAULT NULL, ulid VARCHAR(26) NOT NULL, CONSTRAINT FK_4FAC363761220EA6 FOREIGN KEY (creator_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO deck (id, creator_id, name, lang) SELECT id, creator_id, name, lang FROM __temp__deck
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE __temp__deck
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_4FAC363761220EA6 ON deck (creator_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_4FAC3637C288C859 ON deck (ulid)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TEMPORARY TABLE __temp__user AS SELECT id, email, roles, is_verified FROM user
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles CLOB NOT NULL --(DC2Type:json)
            , is_verified BOOLEAN NOT NULL)
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO user (id, email, roles, is_verified) SELECT id, email, roles, is_verified FROM __temp__user
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE __temp__user
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON user (email)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TEMPORARY TABLE __temp__deck AS SELECT id, creator_id, name, lang FROM deck
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE deck
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE deck (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, creator_id INTEGER DEFAULT NULL, name VARCHAR(100) NOT NULL, lang VARCHAR(2) DEFAULT NULL, CONSTRAINT FK_4FAC363761220EA6 FOREIGN KEY (creator_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO deck (id, creator_id, name, lang) SELECT id, creator_id, name, lang FROM __temp__deck
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE __temp__deck
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_4FAC363761220EA6 ON deck (creator_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TEMPORARY TABLE __temp__user AS SELECT id, email, roles, is_verified FROM "user"
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE "user"
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE "user" (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles CLOB NOT NULL --(DC2Type:json)
            , is_verified BOOLEAN DEFAULT FALSE NOT NULL)
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO "user" (id, email, roles, is_verified) SELECT id, email, roles, is_verified FROM __temp__user
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE __temp__user
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON "user" (email)
        SQL);
    }
}
