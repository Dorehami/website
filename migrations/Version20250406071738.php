<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250406071738 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP SEQUENCE comment_id_seq1 CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP SEQUENCE post_id_seq1 CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP SEQUENCE post_vote_id_seq1 CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE report_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE report (id INT NOT NULL, reported_by_id INT NOT NULL, entity_type VARCHAR(255) NOT NULL, entity_id INT NOT NULL, ai_result JSON DEFAULT NULL, ai_flagged BOOLEAN NOT NULL, report_processed BOOLEAN NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_C42F778471CE806 ON report (reported_by_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE report ADD CONSTRAINT FK_C42F778471CE806 FOREIGN KEY (reported_by_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            DROP SEQUENCE report_id_seq CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE comment_id_seq1 INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE post_id_seq1 INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE post_vote_id_seq1 INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE report DROP CONSTRAINT FK_C42F778471CE806
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE report
        SQL);
    }
}
