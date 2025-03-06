<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250306124842 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE product_movement (id INT AUTO_INCREMENT NOT NULL, product_id INT NOT NULL, movement_type VARCHAR(20) NOT NULL, quantity INT NOT NULL, previous_stock INT NOT NULL, new_stock INT NOT NULL, description LONGTEXT DEFAULT NULL, performed_by VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_3F6DFF604584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE product_movement ADD CONSTRAINT FK_3F6DFF604584665A FOREIGN KEY (product_id) REFERENCES product (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product_movement DROP FOREIGN KEY FK_3F6DFF604584665A');
        $this->addSql('DROP TABLE product_movement');
    }
}
