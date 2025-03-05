<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250303144406 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product ADD part_type VARCHAR(255) NOT NULL, ADD created_at DATETIME NOT NULL, ADD created_by VARCHAR(255) NOT NULL, ADD updated_at DATETIME DEFAULT NULL, ADD updated_by VARCHAR(255) DEFAULT NULL, CHANGE quantity quantity INT NOT NULL, CHANGE price price DOUBLE PRECISION NOT NULL, CHANGE description description LONGTEXT NOT NULL, CHANGE image image LONGTEXT DEFAULT NULL, CHANGE part_number part_number VARCHAR(255) NOT NULL, CHANGE material material VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE user ADD created_at DATETIME DEFAULT NULL, ADD is_guest TINYINT(1) NOT NULL, CHANGE username username VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product DROP part_type, DROP created_at, DROP created_by, DROP updated_at, DROP updated_by, CHANGE description description VARCHAR(255) NOT NULL, CHANGE price price NUMERIC(10, 2) DEFAULT NULL, CHANGE quantity quantity INT DEFAULT NULL, CHANGE image image LONGTEXT NOT NULL, CHANGE part_number part_number VARCHAR(100) NOT NULL, CHANGE material material VARCHAR(100) NOT NULL');
        $this->addSql('ALTER TABLE user DROP created_at, DROP is_guest, CHANGE username username VARCHAR(255) NOT NULL');
    }
}
