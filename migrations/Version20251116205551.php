<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251116205551 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE chambre (id SERIAL NOT NULL, hotel_id INT DEFAULT NULL, classement_id INT NOT NULL, number INT NOT NULL, floor INT NOT NULL, area DOUBLE PRECISION NOT NULL, price_per_night DOUBLE PRECISION NOT NULL, is_available BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C509E4FF3243BB18 ON chambre (hotel_id)');
        $this->addSql('CREATE INDEX IDX_C509E4FFA513A63E ON chambre (classement_id)');
        $this->addSql('CREATE TABLE chambre_service (chambre_id INT NOT NULL, service_id INT NOT NULL, PRIMARY KEY(chambre_id, service_id))');
        $this->addSql('CREATE INDEX IDX_428BC92E9B177F54 ON chambre_service (chambre_id)');
        $this->addSql('CREATE INDEX IDX_428BC92EED5CA9E6 ON chambre_service (service_id)');
        $this->addSql('CREATE TABLE classement_h (id SERIAL NOT NULL, name VARCHAR(100) NOT NULL, description TEXT DEFAULT NULL, base_price DOUBLE PRECISION NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE hotel (id SERIAL NOT NULL, name VARCHAR(255) NOT NULL, adress VARCHAR(255) NOT NULL, city VARCHAR(100) NOT NULL, description TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN hotel.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE service (id SERIAL NOT NULL, name VARCHAR(100) NOT NULL, description TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE chambre ADD CONSTRAINT FK_C509E4FF3243BB18 FOREIGN KEY (hotel_id) REFERENCES hotel (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE chambre ADD CONSTRAINT FK_C509E4FFA513A63E FOREIGN KEY (classement_id) REFERENCES classement_h (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE chambre_service ADD CONSTRAINT FK_428BC92E9B177F54 FOREIGN KEY (chambre_id) REFERENCES chambre (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE chambre_service ADD CONSTRAINT FK_428BC92EED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE chambre DROP CONSTRAINT FK_C509E4FF3243BB18');
        $this->addSql('ALTER TABLE chambre DROP CONSTRAINT FK_C509E4FFA513A63E');
        $this->addSql('ALTER TABLE chambre_service DROP CONSTRAINT FK_428BC92E9B177F54');
        $this->addSql('ALTER TABLE chambre_service DROP CONSTRAINT FK_428BC92EED5CA9E6');
        $this->addSql('DROP TABLE chambre');
        $this->addSql('DROP TABLE chambre_service');
        $this->addSql('DROP TABLE classement_h');
        $this->addSql('DROP TABLE hotel');
        $this->addSql('DROP TABLE service');
    }
}
