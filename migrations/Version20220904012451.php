<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220904012451 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE file DROP CONSTRAINT FK_8C9F36109B6B5FBA');
        $this->addSql('ALTER TABLE file ALTER account_id DROP NOT NULL');
        $this->addSql('ALTER TABLE file ADD CONSTRAINT FK_8C9F36109B6B5FBA FOREIGN KEY (account_id) REFERENCES account (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE post_comment DROP CONSTRAINT FK_A99CE55F9B6B5FBA');
        $this->addSql('ALTER TABLE post_comment ALTER account_id DROP NOT NULL');
        $this->addSql('ALTER TABLE post_comment ADD CONSTRAINT FK_A99CE55F9B6B5FBA FOREIGN KEY (account_id) REFERENCES account (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE post_comment DROP CONSTRAINT fk_a99ce55f9b6b5fba');
        $this->addSql('ALTER TABLE post_comment ALTER account_id SET NOT NULL');
        $this->addSql('ALTER TABLE post_comment ADD CONSTRAINT fk_a99ce55f9b6b5fba FOREIGN KEY (account_id) REFERENCES account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE file DROP CONSTRAINT fk_8c9f36109b6b5fba');
        $this->addSql('ALTER TABLE file ALTER account_id SET NOT NULL');
        $this->addSql('ALTER TABLE file ADD CONSTRAINT fk_8c9f36109b6b5fba FOREIGN KEY (account_id) REFERENCES account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
