<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250328145735 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE comment_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL
        );
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE post_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL
        );
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE post_vote_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL
        );
        $this->addSql(<<<'SQL'
            CREATE TABLE comment (id INT NOT NULL, author_id INT NOT NULL, post_id INT NOT NULL, content TEXT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL
        );
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_9474526CF675F31B ON comment (author_id)
        SQL
        );
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_9474526C4B89032C ON comment (post_id)
        SQL
        );
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN comment.created_at IS '(DC2Type:datetime_immutable)'
        SQL
        );
        $this->addSql(<<<'SQL'
            CREATE TABLE post (id INT NOT NULL, author_id INT NOT NULL, title VARCHAR(255) NOT NULL, url VARCHAR(255) NOT NULL, domain VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL
        );
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_5A8A6C8DF675F31B ON post (author_id)
        SQL
        );
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN post.created_at IS '(DC2Type:datetime_immutable)'
        SQL
        );
        $this->addSql(<<<'SQL'
            CREATE TABLE post_vote (id INT NOT NULL, user_id INT NOT NULL, post_id INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL
        );
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_9345E26FA76ED395 ON post_vote (user_id)
        SQL
        );
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_9345E26F4B89032C ON post_vote (post_id)
        SQL
        );
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX unique_user_post_vote ON post_vote (user_id, post_id)
        SQL
        );
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN post_vote.created_at IS '(DC2Type:datetime_immutable)'
        SQL
        );
        $this->addSql(<<<'SQL'
            ALTER TABLE comment ADD CONSTRAINT FK_9474526CF675F31B FOREIGN KEY (author_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL
        );
        $this->addSql(<<<'SQL'
            ALTER TABLE comment ADD CONSTRAINT FK_9474526C4B89032C FOREIGN KEY (post_id) REFERENCES post (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL
        );
        $this->addSql(<<<'SQL'
            ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8DF675F31B FOREIGN KEY (author_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL
        );
        $this->addSql(<<<'SQL'
            ALTER TABLE post_vote ADD CONSTRAINT FK_9345E26FA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL
        );
        $this->addSql(<<<'SQL'
            ALTER TABLE post_vote ADD CONSTRAINT FK_9345E26F4B89032C FOREIGN KEY (post_id) REFERENCES post (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL
        );
        $this->addSql(<<<'SQL'
            DROP SEQUENCE comment_id_seq CASCADE
        SQL
        );
        $this->addSql(<<<'SQL'
            DROP SEQUENCE post_id_seq CASCADE
        SQL
        );
        $this->addSql(<<<'SQL'
            DROP SEQUENCE post_vote_id_seq CASCADE
        SQL
        );
        $this->addSql(<<<'SQL'
            ALTER TABLE comment DROP CONSTRAINT FK_9474526CF675F31B
        SQL
        );
        $this->addSql(<<<'SQL'
            ALTER TABLE comment DROP CONSTRAINT FK_9474526C4B89032C
        SQL
        );
        $this->addSql(<<<'SQL'
            ALTER TABLE post DROP CONSTRAINT FK_5A8A6C8DF675F31B
        SQL
        );
        $this->addSql(<<<'SQL'
            ALTER TABLE post_vote DROP CONSTRAINT FK_9345E26FA76ED395
        SQL
        );
        $this->addSql(<<<'SQL'
            ALTER TABLE post_vote DROP CONSTRAINT FK_9345E26F4B89032C
        SQL
        );
        $this->addSql(<<<'SQL'
            DROP TABLE comment
        SQL
        );
        $this->addSql(<<<'SQL'
            DROP TABLE post
        SQL
        );
        $this->addSql(<<<'SQL'
            DROP TABLE post_vote
        SQL
        );
    }
}
