<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250221234327 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product ADD min_stock INT NOT NULL, ADD is_enabled TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE user ADD username VARCHAR(255) NOT NULL, ADD phone VARCHAR(15) DEFAULT NULL, ADD security_question VARCHAR(255) DEFAULT NULL, ADD security_answer VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649F85E0677 ON user (username)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product DROP min_stock, DROP is_enabled');
        $this->addSql('DROP INDEX UNIQ_8D93D649F85E0677 ON user');
        $this->addSql('ALTER TABLE user DROP username, DROP phone, DROP security_question, DROP security_answer');
    }
}
