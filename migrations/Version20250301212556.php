<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250301212556 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE used_machinery ADD manufacturing_date DATETIME DEFAULT NULL, ADD fuel_tank_capacity NUMERIC(10, 2) NOT NULL, ADD technology LONGTEXT DEFAULT NULL, ADD load_capacity NUMERIC(10, 2) DEFAULT NULL, ADD transmission_system VARCHAR(20) NOT NULL, ADD is_price_on_request TINYINT(1) NOT NULL, ADD location VARCHAR(255) NOT NULL, ADD image_filenames JSON NOT NULL, ADD is_enabled TINYINT(1) NOT NULL, DROP years_old, DROP months, DROP image_filename, CHANGE hours_of_use hours_of_use INT DEFAULT NULL, CHANGE last_service last_service DATETIME DEFAULT NULL, CHANGE price price NUMERIC(10, 2) DEFAULT NULL, CHANGE machinery_name model VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE used_machinery ADD machinery_name VARCHAR(255) NOT NULL, ADD years_old INT NOT NULL, ADD months INT NOT NULL, ADD image_filename VARCHAR(255) DEFAULT NULL, DROP model, DROP manufacturing_date, DROP fuel_tank_capacity, DROP technology, DROP load_capacity, DROP transmission_system, DROP is_price_on_request, DROP location, DROP image_filenames, DROP is_enabled, CHANGE last_service last_service DATE NOT NULL, CHANGE hours_of_use hours_of_use INT NOT NULL, CHANGE price price DOUBLE PRECISION NOT NULL');
    }
}
