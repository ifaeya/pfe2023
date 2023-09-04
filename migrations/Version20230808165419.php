<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230808165419 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE document_operation (document_id INT NOT NULL, operation_id INT NOT NULL, INDEX IDX_BB98ECCC33F7837 (document_id), INDEX IDX_BB98ECC44AC3583 (operation_id), PRIMARY KEY(document_id, operation_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reservation_document (reservation_id INT NOT NULL, document_id INT NOT NULL, INDEX IDX_45DC6862B83297E7 (reservation_id), INDEX IDX_45DC6862C33F7837 (document_id), PRIMARY KEY(reservation_id, document_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE document_operation ADD CONSTRAINT FK_BB98ECCC33F7837 FOREIGN KEY (document_id) REFERENCES document (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE document_operation ADD CONSTRAINT FK_BB98ECC44AC3583 FOREIGN KEY (operation_id) REFERENCES operation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reservation_document ADD CONSTRAINT FK_45DC6862B83297E7 FOREIGN KEY (reservation_id) REFERENCES reservation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reservation_document ADD CONSTRAINT FK_45DC6862C33F7837 FOREIGN KEY (document_id) REFERENCES document (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE archive_coursdevises ADD cours_id INT DEFAULT NULL, ADD date_archivage DATETIME NOT NULL, ADD valeurachat NUMERIC(10, 2) NOT NULL, ADD valeurvente NUMERIC(10, 2) NOT NULL, ADD profit NUMERIC(10, 2) DEFAULT NULL, ADD created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE archive_coursdevises ADD CONSTRAINT FK_D72BA9DA7ECF78B0 FOREIGN KEY (cours_id) REFERENCES coursdevises (id)');
        $this->addSql('CREATE INDEX IDX_D72BA9DA7ECF78B0 ON archive_coursdevises (cours_id)');
        $this->addSql('ALTER TABLE caisse ADD devises_id INT DEFAULT NULL, ADD libelle VARCHAR(120) NOT NULL, ADD solde DOUBLE PRECISION NOT NULL, ADD fermee TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE caisse ADD CONSTRAINT FK_B2A353C8658D0DB4 FOREIGN KEY (devises_id) REFERENCES devises (id)');
        $this->addSql('CREATE INDEX IDX_B2A353C8658D0DB4 ON caisse (devises_id)');
        $this->addSql('ALTER TABLE client ADD nom VARCHAR(255) NOT NULL, ADD prenom VARCHAR(255) NOT NULL, ADD numcin VARCHAR(255) NOT NULL, ADD datecin DATE NOT NULL, ADD numpasseport VARCHAR(255) NOT NULL, ADD datepasseport DATE NOT NULL, ADD datevalidpasport DATE NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C74404552FE66620 ON client (numcin)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C74404559E09A4CC ON client (numpasseport)');
        $this->addSql('ALTER TABLE coursdevises ADD devises_id INT NOT NULL, ADD valeurachat DOUBLE PRECISION NOT NULL, ADD valeurvente DOUBLE PRECISION NOT NULL, ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE coursdevises ADD CONSTRAINT FK_9B921B01658D0DB4 FOREIGN KEY (devises_id) REFERENCES devises (id)');
        $this->addSql('CREATE INDEX IDX_9B921B01658D0DB4 ON coursdevises (devises_id)');
        $this->addSql('ALTER TABLE devises ADD code VARCHAR(100) NOT NULL, ADD libelle VARCHAR(255) NOT NULL, ADD abreviation VARCHAR(255) NOT NULL, ADD unite INT NOT NULL, ADD image VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE document ADD libelle VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE mouvement ADD operation_id INT DEFAULT NULL, ADD devise_id INT DEFAULT NULL, ADD caisse_id INT DEFAULT NULL, ADD sens_mouvement VARCHAR(120) NOT NULL, ADD valeur_devise DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE mouvement ADD CONSTRAINT FK_5B51FC3E44AC3583 FOREIGN KEY (operation_id) REFERENCES operation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE mouvement ADD CONSTRAINT FK_5B51FC3EF4445056 FOREIGN KEY (devise_id) REFERENCES devises (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE mouvement ADD CONSTRAINT FK_5B51FC3E27B4FEBF FOREIGN KEY (caisse_id) REFERENCES caisse (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_5B51FC3E44AC3583 ON mouvement (operation_id)');
        $this->addSql('CREATE INDEX IDX_5B51FC3EF4445056 ON mouvement (devise_id)');
        $this->addSql('CREATE INDEX IDX_5B51FC3E27B4FEBF ON mouvement (caisse_id)');
        $this->addSql('ALTER TABLE operation ADD client_id INT DEFAULT NULL, ADD typeoperation_id INT DEFAULT NULL, ADD created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE operation ADD CONSTRAINT FK_1981A66D19EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE operation ADD CONSTRAINT FK_1981A66D510850EC FOREIGN KEY (typeoperation_id) REFERENCES typeoperation (id)');
        $this->addSql('CREATE INDEX IDX_1981A66D19EB6921 ON operation (client_id)');
        $this->addSql('CREATE INDEX IDX_1981A66D510850EC ON operation (typeoperation_id)');
        $this->addSql('ALTER TABLE reservation ADD devises_id INT DEFAULT NULL, ADD type_id INT DEFAULT NULL, ADD user_id INT DEFAULT NULL, ADD montant DOUBLE PRECISION NOT NULL, ADD created_at DATETIME NOT NULL, ADD confirmation VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C84955658D0DB4 FOREIGN KEY (devises_id) REFERENCES devises (id)');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C84955C54C8C93 FOREIGN KEY (type_id) REFERENCES typeoperation (id)');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C84955A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_42C84955658D0DB4 ON reservation (devises_id)');
        $this->addSql('CREATE INDEX IDX_42C84955C54C8C93 ON reservation (type_id)');
        $this->addSql('CREATE INDEX IDX_42C84955A76ED395 ON reservation (user_id)');
        $this->addSql('ALTER TABLE societe ADD raisonsociale LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE stock ADD devise_id INT DEFAULT NULL, ADD caisse_id INT DEFAULT NULL, ADD name VARCHAR(255) NOT NULL, ADD quantity DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE stock ADD CONSTRAINT FK_4B365660F4445056 FOREIGN KEY (devise_id) REFERENCES devises (id)');
        $this->addSql('ALTER TABLE stock ADD CONSTRAINT FK_4B36566027B4FEBF FOREIGN KEY (caisse_id) REFERENCES caisse (id)');
        $this->addSql('CREATE INDEX IDX_4B365660F4445056 ON stock (devise_id)');
        $this->addSql('CREATE INDEX IDX_4B36566027B4FEBF ON stock (caisse_id)');
        $this->addSql('ALTER TABLE typeoperation ADD libelle VARCHAR(120) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE document_operation DROP FOREIGN KEY FK_BB98ECCC33F7837');
        $this->addSql('ALTER TABLE document_operation DROP FOREIGN KEY FK_BB98ECC44AC3583');
        $this->addSql('ALTER TABLE reservation_document DROP FOREIGN KEY FK_45DC6862B83297E7');
        $this->addSql('ALTER TABLE reservation_document DROP FOREIGN KEY FK_45DC6862C33F7837');
        $this->addSql('DROP TABLE document_operation');
        $this->addSql('DROP TABLE reservation_document');
        $this->addSql('ALTER TABLE archive_coursdevises DROP FOREIGN KEY FK_D72BA9DA7ECF78B0');
        $this->addSql('DROP INDEX IDX_D72BA9DA7ECF78B0 ON archive_coursdevises');
        $this->addSql('ALTER TABLE archive_coursdevises DROP cours_id, DROP date_archivage, DROP valeurachat, DROP valeurvente, DROP profit, DROP created_at');
        $this->addSql('ALTER TABLE caisse DROP FOREIGN KEY FK_B2A353C8658D0DB4');
        $this->addSql('DROP INDEX IDX_B2A353C8658D0DB4 ON caisse');
        $this->addSql('ALTER TABLE caisse DROP devises_id, DROP libelle, DROP solde, DROP fermee');
        $this->addSql('DROP INDEX UNIQ_C74404552FE66620 ON client');
        $this->addSql('DROP INDEX UNIQ_C74404559E09A4CC ON client');
        $this->addSql('ALTER TABLE client DROP nom, DROP prenom, DROP numcin, DROP datecin, DROP numpasseport, DROP datepasseport, DROP datevalidpasport');
        $this->addSql('ALTER TABLE coursdevises DROP FOREIGN KEY FK_9B921B01658D0DB4');
        $this->addSql('DROP INDEX IDX_9B921B01658D0DB4 ON coursdevises');
        $this->addSql('ALTER TABLE coursdevises DROP devises_id, DROP valeurachat, DROP valeurvente, DROP created_at, DROP updated_at');
        $this->addSql('ALTER TABLE devises DROP code, DROP libelle, DROP abreviation, DROP unite, DROP image');
        $this->addSql('ALTER TABLE document DROP libelle');
        $this->addSql('ALTER TABLE mouvement DROP FOREIGN KEY FK_5B51FC3E44AC3583');
        $this->addSql('ALTER TABLE mouvement DROP FOREIGN KEY FK_5B51FC3EF4445056');
        $this->addSql('ALTER TABLE mouvement DROP FOREIGN KEY FK_5B51FC3E27B4FEBF');
        $this->addSql('DROP INDEX IDX_5B51FC3E44AC3583 ON mouvement');
        $this->addSql('DROP INDEX IDX_5B51FC3EF4445056 ON mouvement');
        $this->addSql('DROP INDEX IDX_5B51FC3E27B4FEBF ON mouvement');
        $this->addSql('ALTER TABLE mouvement DROP operation_id, DROP devise_id, DROP caisse_id, DROP sens_mouvement, DROP valeur_devise');
        $this->addSql('ALTER TABLE operation DROP FOREIGN KEY FK_1981A66D19EB6921');
        $this->addSql('ALTER TABLE operation DROP FOREIGN KEY FK_1981A66D510850EC');
        $this->addSql('DROP INDEX IDX_1981A66D19EB6921 ON operation');
        $this->addSql('DROP INDEX IDX_1981A66D510850EC ON operation');
        $this->addSql('ALTER TABLE operation DROP client_id, DROP typeoperation_id, DROP created_at');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C84955658D0DB4');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C84955C54C8C93');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C84955A76ED395');
        $this->addSql('DROP INDEX IDX_42C84955658D0DB4 ON reservation');
        $this->addSql('DROP INDEX IDX_42C84955C54C8C93 ON reservation');
        $this->addSql('DROP INDEX IDX_42C84955A76ED395 ON reservation');
        $this->addSql('ALTER TABLE reservation DROP devises_id, DROP type_id, DROP user_id, DROP montant, DROP created_at, DROP confirmation');
        $this->addSql('ALTER TABLE societe DROP raisonsociale');
        $this->addSql('ALTER TABLE stock DROP FOREIGN KEY FK_4B365660F4445056');
        $this->addSql('ALTER TABLE stock DROP FOREIGN KEY FK_4B36566027B4FEBF');
        $this->addSql('DROP INDEX IDX_4B365660F4445056 ON stock');
        $this->addSql('DROP INDEX IDX_4B36566027B4FEBF ON stock');
        $this->addSql('ALTER TABLE stock DROP devise_id, DROP caisse_id, DROP name, DROP quantity');
        $this->addSql('ALTER TABLE typeoperation DROP libelle');
    }
}
