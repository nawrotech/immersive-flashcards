<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250405140806 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TEMPORARY TABLE __temp__flashcard AS SELECT id, deck_id, front, back, image_type, result FROM flashcard
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE flashcard
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE flashcard (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, deck_id INTEGER DEFAULT NULL, front VARCHAR(255) NOT NULL, back VARCHAR(255) NOT NULL, image_type VARCHAR(10) NOT NULL, result VARCHAR(255) NOT NULL, author_name VARCHAR(255) DEFAULT NULL, author_profile_url VARCHAR(255) DEFAULT NULL, CONSTRAINT FK_70511A09111948DC FOREIGN KEY (deck_id) REFERENCES deck (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO flashcard (id, deck_id, front, back, image_type, result) SELECT id, deck_id, front, back, image_type, result FROM __temp__flashcard
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE __temp__flashcard
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_70511A09111948DC ON flashcard (deck_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TEMPORARY TABLE __temp__flashcard AS SELECT id, deck_id, front, back, image_type, result FROM flashcard
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE flashcard
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE flashcard (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, deck_id INTEGER DEFAULT NULL, front VARCHAR(255) NOT NULL, back VARCHAR(255) NOT NULL, image_type VARCHAR(10) NOT NULL, result VARCHAR(255) NOT NULL, attribution CLOB DEFAULT NULL --(DC2Type:json)
            , CONSTRAINT FK_70511A09111948DC FOREIGN KEY (deck_id) REFERENCES deck (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO flashcard (id, deck_id, front, back, image_type, result) SELECT id, deck_id, front, back, image_type, result FROM __temp__flashcard
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE __temp__flashcard
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_70511A09111948DC ON flashcard (deck_id)
        SQL);
    }
}
