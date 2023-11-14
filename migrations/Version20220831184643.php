<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220831184643 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE account_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE file_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE post_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE post_category_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE post_comment_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE account (id INT NOT NULL, username VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, discord_id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7D3656A4F85E0677 ON account (username)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7D3656A443349DE ON account (discord_id)');
        $this->addSql('CREATE TABLE file (id INT NOT NULL, account_id INT NOT NULL, name VARCHAR(255) NOT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, type VARCHAR(255) NOT NULL, hash VARCHAR(255) NOT NULL, private BOOLEAN NOT NULL, size BIGINT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_8C9F36109B6B5FBA ON file (account_id)');
        $this->addSql('COMMENT ON COLUMN file.date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE post (id INT NOT NULL, category_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, content TEXT NOT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, slug VARCHAR(255) NOT NULL, image VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_5A8A6C8D12469DE2 ON post (category_id)');
        $this->addSql('COMMENT ON COLUMN post.date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE post_category (id INT NOT NULL, name VARCHAR(255) NOT NULL, image VARCHAR(255) NOT NULL, color VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B9A190605E237E06 ON post_category (name)');
        $this->addSql('CREATE TABLE post_comment (id INT NOT NULL, post_id INT NOT NULL, account_id INT NOT NULL, content TEXT NOT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_A99CE55F4B89032C ON post_comment (post_id)');
        $this->addSql('CREATE INDEX IDX_A99CE55F9B6B5FBA ON post_comment (account_id)');
        $this->addSql('COMMENT ON COLUMN post_comment.date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE messenger_messages (id BIGSERIAL NOT NULL, body TEXT NOT NULL, headers TEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, available_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, delivered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
        $this->addSql('CREATE OR REPLACE FUNCTION notify_messenger_messages() RETURNS TRIGGER AS $$
            BEGIN
                PERFORM pg_notify(\'messenger_messages\', NEW.queue_name::text);
                RETURN NEW;
            END;
        $$ LANGUAGE plpgsql;');
        $this->addSql('DROP TRIGGER IF EXISTS notify_trigger ON messenger_messages;');
        $this->addSql('CREATE TRIGGER notify_trigger AFTER INSERT OR UPDATE ON messenger_messages FOR EACH ROW EXECUTE PROCEDURE notify_messenger_messages();');
        $this->addSql('ALTER TABLE file ADD CONSTRAINT FK_8C9F36109B6B5FBA FOREIGN KEY (account_id) REFERENCES account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8D12469DE2 FOREIGN KEY (category_id) REFERENCES post_category (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE post_comment ADD CONSTRAINT FK_A99CE55F4B89032C FOREIGN KEY (post_id) REFERENCES post (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE post_comment ADD CONSTRAINT FK_A99CE55F9B6B5FBA FOREIGN KEY (account_id) REFERENCES account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE account_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE file_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE post_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE post_category_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE post_comment_id_seq CASCADE');
        $this->addSql('ALTER TABLE file DROP CONSTRAINT FK_8C9F36109B6B5FBA');
        $this->addSql('ALTER TABLE post DROP CONSTRAINT FK_5A8A6C8D12469DE2');
        $this->addSql('ALTER TABLE post_comment DROP CONSTRAINT FK_A99CE55F4B89032C');
        $this->addSql('ALTER TABLE post_comment DROP CONSTRAINT FK_A99CE55F9B6B5FBA');
        $this->addSql('DROP TABLE account');
        $this->addSql('DROP TABLE file');
        $this->addSql('DROP TABLE post');
        $this->addSql('DROP TABLE post_category');
        $this->addSql('DROP TABLE post_comment');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
