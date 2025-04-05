<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250331072532 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE comment (id SERIAL NOT NULL, author_id INT NOT NULL, post_id INT NOT NULL, moderated_by_id INT DEFAULT NULL, content TEXT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, visible BOOLEAN DEFAULT NULL, moderation_reason TEXT DEFAULT NULL, moderated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))
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
            CREATE INDEX IDX_9474526C8EDA19B0 ON comment (moderated_by_id)
        SQL
        );
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN comment.created_at IS '(DC2Type:datetime_immutable)'
        SQL
        );
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN comment.moderated_at IS '(DC2Type:datetime_immutable)'
        SQL
        );
        $this->addSql(<<<'SQL'
            CREATE TABLE post (id SERIAL NOT NULL, author_id INT NOT NULL, title VARCHAR(255) NOT NULL, url VARCHAR(255) NOT NULL, domain VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, normalized_url VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))
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
            CREATE TABLE post_vote (id SERIAL NOT NULL, user_id INT NOT NULL, post_id INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
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
            CREATE TABLE "user" (id SERIAL NOT NULL, banned_by_id INT DEFAULT NULL, email VARCHAR(180) DEFAULT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, discord_id VARCHAR(255) DEFAULT NULL, discord_username VARCHAR(255) DEFAULT NULL, avatar_url VARCHAR(255) DEFAULT NULL, banned BOOLEAN DEFAULT NULL, banned_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, ban_reason TEXT DEFAULT NULL, joined_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))
        SQL
        );
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_8D93D649386B8E7 ON "user" (banned_by_id)
        SQL
        );
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN "user".banned_at IS '(DC2Type:datetime_immutable)'
        SQL
        );
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN "user".joined_at IS '(DC2Type:datetime_immutable)'
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
            ALTER TABLE comment ADD CONSTRAINT FK_9474526C8EDA19B0 FOREIGN KEY (moderated_by_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
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
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" ADD CONSTRAINT FK_8D93D649386B8E7 FOREIGN KEY (banned_by_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
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
            ALTER TABLE comment DROP CONSTRAINT FK_9474526CF675F31B
        SQL
        );
        $this->addSql(<<<'SQL'
            ALTER TABLE comment DROP CONSTRAINT FK_9474526C4B89032C
        SQL
        );
        $this->addSql(<<<'SQL'
            ALTER TABLE comment DROP CONSTRAINT FK_9474526C8EDA19B0
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
            ALTER TABLE "user" DROP CONSTRAINT FK_8D93D649386B8E7
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
        $this->addSql(<<<'SQL'
            DROP TABLE "user"
        SQL
        );
    }
}
