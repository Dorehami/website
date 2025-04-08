<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250408075855 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE comment_id_seq
        SQL);
        $this->addSql(<<<'SQL'
            SELECT setval('comment_id_seq', (SELECT MAX(id) FROM comment))
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE comment ALTER id SET DEFAULT nextval('comment_id_seq')
        SQL);
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE post_id_seq
        SQL);
        $this->addSql(<<<'SQL'
            SELECT setval('post_id_seq', (SELECT MAX(id) FROM post))
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post ALTER id SET DEFAULT nextval('post_id_seq')
        SQL);
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE post_vote_id_seq
        SQL);
        $this->addSql(<<<'SQL'
            SELECT setval('post_vote_id_seq', (SELECT MAX(id) FROM post_vote))
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post_vote ALTER id SET DEFAULT nextval('post_vote_id_seq')
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE report ALTER human_processed DROP DEFAULT
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE comment ALTER id DROP DEFAULT
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post_vote ALTER id DROP DEFAULT
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE report ALTER human_processed SET DEFAULT false
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post ALTER id DROP DEFAULT
        SQL);
    }
}
