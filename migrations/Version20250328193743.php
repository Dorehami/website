<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250328193743 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE comment ADD moderated_by_id INT DEFAULT NULL
        SQL
        );
        $this->addSql(<<<'SQL'
            ALTER TABLE comment ADD visible BOOLEAN DEFAULT NULL
        SQL
        );
        $this->addSql(<<<'SQL'
            ALTER TABLE comment ADD moderation_reason TEXT DEFAULT NULL
        SQL
        );
        $this->addSql(<<<'SQL'
            ALTER TABLE comment ADD moderated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL
        SQL
        );
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN comment.moderated_at IS '(DC2Type:datetime_immutable)'
        SQL
        );
        $this->addSql(<<<'SQL'
            ALTER TABLE comment ADD CONSTRAINT FK_9474526C8EDA19B0 FOREIGN KEY (moderated_by_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL
        );
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_9474526C8EDA19B0 ON comment (moderated_by_id)
        SQL
        );
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" ADD banned_by_id INT DEFAULT NULL
        SQL
        );
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" ADD banned BOOLEAN DEFAULT NULL
        SQL
        );
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" ADD banned_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL
        SQL
        );
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" ADD ban_reason TEXT DEFAULT NULL
        SQL
        );
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN "user".banned_at IS '(DC2Type:datetime_immutable)'
        SQL
        );
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" ADD CONSTRAINT FK_8D93D649386B8E7 FOREIGN KEY (banned_by_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL
        );
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_8D93D649386B8E7 ON "user" (banned_by_id)
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
            ALTER TABLE comment DROP CONSTRAINT FK_9474526C8EDA19B0
        SQL
        );
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_9474526C8EDA19B0
        SQL
        );
        $this->addSql(<<<'SQL'
            ALTER TABLE comment DROP moderated_by_id
        SQL
        );
        $this->addSql(<<<'SQL'
            ALTER TABLE comment DROP visible
        SQL
        );
        $this->addSql(<<<'SQL'
            ALTER TABLE comment DROP moderation_reason
        SQL
        );
        $this->addSql(<<<'SQL'
            ALTER TABLE comment DROP moderated_at
        SQL
        );
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" DROP CONSTRAINT FK_8D93D649386B8E7
        SQL
        );
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_8D93D649386B8E7
        SQL
        );
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" DROP banned_by_id
        SQL
        );
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" DROP banned
        SQL
        );
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" DROP banned_at
        SQL
        );
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" DROP ban_reason
        SQL
        );
    }
}
