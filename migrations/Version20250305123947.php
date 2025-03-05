<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250305123947 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE cart (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, INDEX IDX_BA388B7A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE cart_product_order (id INT AUTO_INCREMENT NOT NULL, cart_id INT NOT NULL, orders_id INT DEFAULT NULL, product_id INT NOT NULL, quantity INT NOT NULL, date DATE NOT NULL, time TIME NOT NULL, INDEX IDX_606DBEAA1AD5CDBF (cart_id), INDEX IDX_606DBEAACFFE9AD6 (orders_id), INDEX IDX_606DBEAA4584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, code VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_64C19C15E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE favorite (id INT AUTO_INCREMENT NOT NULL, flag TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE machine (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, quantity INT NOT NULL, brand VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, price NUMERIC(10, 2) DEFAULT NULL, image LONGTEXT NOT NULL, is_new TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `order` (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE parts (id INT AUTO_INCREMENT NOT NULL, quantity INT DEFAULT NULL, name VARCHAR(255) NOT NULL, brand VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, price DOUBLE PRECISION NOT NULL, quantity INT NOT NULL, is_enabled TINYINT(1) NOT NULL, brand VARCHAR(255) NOT NULL, min_stock INT NOT NULL, image LONGTEXT DEFAULT NULL, part_number VARCHAR(255) NOT NULL, compatible_models LONGTEXT NOT NULL, dimensions VARCHAR(255) DEFAULT NULL, material VARCHAR(255) NOT NULL, weight NUMERIC(10, 2) DEFAULT NULL, load_capacity LONGTEXT DEFAULT NULL, estimated_life_hours INT DEFAULT NULL, mounting_type VARCHAR(255) DEFAULT NULL, installation_requirements LONGTEXT DEFAULT NULL, part_type VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, created_by VARCHAR(255) NOT NULL, updated_at DATETIME DEFAULT NULL, updated_by VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product_parts_machine (id INT AUTO_INCREMENT NOT NULL, product_id INT NOT NULL, parts_id INT DEFAULT NULL, machine_id INT DEFAULT NULL, quantity INT DEFAULT NULL, INDEX IDX_4837D6C34584665A (product_id), INDEX IDX_4837D6C34E81F03D (parts_id), INDEX IDX_4837D6C3F6B75B26 (machine_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE recipe_machine (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE recipe_machine_parts (recipe_machine_id INT NOT NULL, parts_id INT NOT NULL, INDEX IDX_D03A724BBFC9DD45 (recipe_machine_id), INDEX IDX_D03A724B4E81F03D (parts_id), PRIMARY KEY(recipe_machine_id, parts_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE recipe_machine_product (recipe_machine_id INT NOT NULL, product_id INT NOT NULL, INDEX IDX_BC41C171BFC9DD45 (recipe_machine_id), INDEX IDX_BC41C1714584665A (product_id), PRIMARY KEY(recipe_machine_id, product_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE recipe_product (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, parts JSON NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reservation (id INT AUTO_INCREMENT NOT NULL, seller_id INT NOT NULL, customer_id INT NOT NULL, product_id INT NOT NULL, quantity INT NOT NULL, created_at DATETIME NOT NULL, created_by VARCHAR(255) NOT NULL, updated_at DATETIME DEFAULT NULL, updated_by VARCHAR(255) DEFAULT NULL, expires_at DATETIME NOT NULL, status VARCHAR(255) NOT NULL, notes LONGTEXT DEFAULT NULL, INDEX IDX_42C849558DE820D9 (seller_id), INDEX IDX_42C849559395C3F3 (customer_id), INDEX IDX_42C849554584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE used_machinery (id INT AUTO_INCREMENT NOT NULL, model VARCHAR(255) NOT NULL, brand VARCHAR(255) NOT NULL, manufacturing_date DATETIME DEFAULT NULL, fuel_tank_capacity NUMERIC(10, 2) NOT NULL, technology LONGTEXT DEFAULT NULL, load_capacity NUMERIC(10, 2) DEFAULT NULL, transmission_system VARCHAR(20) NOT NULL, last_service DATETIME DEFAULT NULL, hours_of_use INT DEFAULT NULL, price NUMERIC(10, 2) DEFAULT NULL, category VARCHAR(255) NOT NULL, location VARCHAR(255) NOT NULL, image_filenames JSON NOT NULL, is_new TINYINT(1) NOT NULL, taxpayer_type VARCHAR(50) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, username VARCHAR(255) DEFAULT NULL, phone VARCHAR(15) DEFAULT NULL, security_question VARCHAR(255) DEFAULT NULL, security_answer VARCHAR(255) DEFAULT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, reset_token VARCHAR(255) DEFAULT NULL, created_at DATETIME DEFAULT NULL, is_guest TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_8D93D649F85E0677 (username), UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_favorite_product (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, favorite_id INT NOT NULL, product_id INT NOT NULL, machine_id INT DEFAULT NULL, INDEX IDX_F30BE81EA76ED395 (user_id), INDEX IDX_F30BE81EAA17481D (favorite_id), INDEX IDX_F30BE81E4584665A (product_id), INDEX IDX_F30BE81EF6B75B26 (machine_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE cart ADD CONSTRAINT FK_BA388B7A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE cart_product_order ADD CONSTRAINT FK_606DBEAA1AD5CDBF FOREIGN KEY (cart_id) REFERENCES cart (id)');
        $this->addSql('ALTER TABLE cart_product_order ADD CONSTRAINT FK_606DBEAACFFE9AD6 FOREIGN KEY (orders_id) REFERENCES `order` (id)');
        $this->addSql('ALTER TABLE cart_product_order ADD CONSTRAINT FK_606DBEAA4584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE product_parts_machine ADD CONSTRAINT FK_4837D6C34584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE product_parts_machine ADD CONSTRAINT FK_4837D6C34E81F03D FOREIGN KEY (parts_id) REFERENCES parts (id)');
        $this->addSql('ALTER TABLE product_parts_machine ADD CONSTRAINT FK_4837D6C3F6B75B26 FOREIGN KEY (machine_id) REFERENCES machine (id)');
        $this->addSql('ALTER TABLE recipe_machine_parts ADD CONSTRAINT FK_D03A724BBFC9DD45 FOREIGN KEY (recipe_machine_id) REFERENCES recipe_machine (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE recipe_machine_parts ADD CONSTRAINT FK_D03A724B4E81F03D FOREIGN KEY (parts_id) REFERENCES parts (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE recipe_machine_product ADD CONSTRAINT FK_BC41C171BFC9DD45 FOREIGN KEY (recipe_machine_id) REFERENCES recipe_machine (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE recipe_machine_product ADD CONSTRAINT FK_BC41C1714584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C849558DE820D9 FOREIGN KEY (seller_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C849559395C3F3 FOREIGN KEY (customer_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C849554584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE user_favorite_product ADD CONSTRAINT FK_F30BE81EA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_favorite_product ADD CONSTRAINT FK_F30BE81EAA17481D FOREIGN KEY (favorite_id) REFERENCES favorite (id)');
        $this->addSql('ALTER TABLE user_favorite_product ADD CONSTRAINT FK_F30BE81E4584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE user_favorite_product ADD CONSTRAINT FK_F30BE81EF6B75B26 FOREIGN KEY (machine_id) REFERENCES machine (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cart DROP FOREIGN KEY FK_BA388B7A76ED395');
        $this->addSql('ALTER TABLE cart_product_order DROP FOREIGN KEY FK_606DBEAA1AD5CDBF');
        $this->addSql('ALTER TABLE cart_product_order DROP FOREIGN KEY FK_606DBEAACFFE9AD6');
        $this->addSql('ALTER TABLE cart_product_order DROP FOREIGN KEY FK_606DBEAA4584665A');
        $this->addSql('ALTER TABLE product_parts_machine DROP FOREIGN KEY FK_4837D6C34584665A');
        $this->addSql('ALTER TABLE product_parts_machine DROP FOREIGN KEY FK_4837D6C34E81F03D');
        $this->addSql('ALTER TABLE product_parts_machine DROP FOREIGN KEY FK_4837D6C3F6B75B26');
        $this->addSql('ALTER TABLE recipe_machine_parts DROP FOREIGN KEY FK_D03A724BBFC9DD45');
        $this->addSql('ALTER TABLE recipe_machine_parts DROP FOREIGN KEY FK_D03A724B4E81F03D');
        $this->addSql('ALTER TABLE recipe_machine_product DROP FOREIGN KEY FK_BC41C171BFC9DD45');
        $this->addSql('ALTER TABLE recipe_machine_product DROP FOREIGN KEY FK_BC41C1714584665A');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C849558DE820D9');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C849559395C3F3');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C849554584665A');
        $this->addSql('ALTER TABLE user_favorite_product DROP FOREIGN KEY FK_F30BE81EA76ED395');
        $this->addSql('ALTER TABLE user_favorite_product DROP FOREIGN KEY FK_F30BE81EAA17481D');
        $this->addSql('ALTER TABLE user_favorite_product DROP FOREIGN KEY FK_F30BE81E4584665A');
        $this->addSql('ALTER TABLE user_favorite_product DROP FOREIGN KEY FK_F30BE81EF6B75B26');
        $this->addSql('DROP TABLE cart');
        $this->addSql('DROP TABLE cart_product_order');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE favorite');
        $this->addSql('DROP TABLE machine');
        $this->addSql('DROP TABLE `order`');
        $this->addSql('DROP TABLE parts');
        $this->addSql('DROP TABLE product');
        $this->addSql('DROP TABLE product_parts_machine');
        $this->addSql('DROP TABLE recipe_machine');
        $this->addSql('DROP TABLE recipe_machine_parts');
        $this->addSql('DROP TABLE recipe_machine_product');
        $this->addSql('DROP TABLE recipe_product');
        $this->addSql('DROP TABLE reservation');
        $this->addSql('DROP TABLE used_machinery');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_favorite_product');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
