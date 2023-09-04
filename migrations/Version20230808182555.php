<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230808182555 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_societe (user_id INT NOT NULL, societe_id INT NOT NULL, INDEX IDX_416823B7A76ED395 (user_id), INDEX IDX_416823B7FCF77503 (societe_id), PRIMARY KEY(user_id, societe_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_societe ADD CONSTRAINT FK_416823B7A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_societe ADD CONSTRAINT FK_416823B7FCF77503 FOREIGN KEY (societe_id) REFERENCES societe (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_societe DROP FOREIGN KEY FK_416823B7A76ED395');
        $this->addSql('ALTER TABLE user_societe DROP FOREIGN KEY FK_416823B7FCF77503');
        $this->addSql('DROP TABLE user_societe');
    }
}
