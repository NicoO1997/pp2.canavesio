-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Versión del servidor:         8.0.30 - MySQL Community Server - GPL
-- SO del servidor:              Win64
-- HeidiSQL Versión:             12.1.0.6537
-- --------------------------------------------------------
SELECT CONCAT('[', DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:%s'), '] Starting database import - User: SantiAragon') AS log;
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Volcando estructura de base de datos para canavesio2
CREATE DATABASE IF NOT EXISTS `canavesio2` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `canavesio2`;

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';

-- Limpiar todas las tablas antes de importar
SELECT CONCAT('[', DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:%s'), '] Cleaning tables - User: SantiAragon') AS log;

TRUNCATE TABLE user_favorite_product;
TRUNCATE TABLE cart_product_order;
TRUNCATE TABLE product_movement;
TRUNCATE TABLE product_parts_machine;
TRUNCATE TABLE purchase_item;
TRUNCATE TABLE recipe_machine_parts;
TRUNCATE TABLE recipe_machine_product;
TRUNCATE TABLE reservation;
TRUNCATE TABLE used_machinery;
TRUNCATE TABLE cart;
TRUNCATE TABLE category;
TRUNCATE TABLE favorite;
TRUNCATE TABLE machine;
TRUNCATE TABLE parts;
TRUNCATE TABLE product;
TRUNCATE TABLE purchase;
TRUNCATE TABLE recipe_machine;
TRUNCATE TABLE recipe_product;
TRUNCATE TABLE user;

-- Reiniciar todos los auto_increment
SELECT CONCAT('[', DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:%s'), '] Resetting auto_increment values - User: SantiAragon') AS LOG;

ALTER TABLE category AUTO_INCREMENT = 1;
ALTER TABLE used_machinery AUTO_INCREMENT = 1;
ALTER TABLE user AUTO_INCREMENT = 1;
-- Volcando estructura para tabla canavesio2.cart
CREATE TABLE IF NOT EXISTS `cart` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_BA388B7A76ED395` (`user_id`),
  CONSTRAINT `FK_BA388B7A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla canavesio2.cart: ~0 rows (aproximadamente)

-- Volcando estructura para tabla canavesio2.cart_product_order
CREATE TABLE IF NOT EXISTS `cart_product_order` (
  `id` int NOT NULL AUTO_INCREMENT,
  `cart_id` int NOT NULL,
  `orders_id` int DEFAULT NULL,
  `product_id` int NOT NULL,
  `is_from_reservation` tinyint(1) NOT NULL DEFAULT '0',
  `quantity` int NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_606DBEAA1AD5CDBF` (`cart_id`),
  KEY `IDX_606DBEAACFFE9AD6` (`orders_id`),
  KEY `IDX_606DBEAA4584665A` (`product_id`),
  CONSTRAINT `FK_606DBEAA1AD5CDBF` FOREIGN KEY (`cart_id`) REFERENCES `cart` (`id`),
  CONSTRAINT `FK_606DBEAA4584665A` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`),
  CONSTRAINT `FK_606DBEAACFFE9AD6` FOREIGN KEY (`orders_id`) REFERENCES `order` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla canavesio2.cart_product_order: ~0 rows (aproximadamente)

-- Volcando estructura para tabla canavesio2.category
CREATE TABLE IF NOT EXISTS `category` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_64C19C15E237E06` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=UTF8MB4_UNICODE_CI;

-- Volcando estructura para tabla canavesio2.doctrine_migration_versions
CREATE TABLE IF NOT EXISTS `doctrine_migration_versions` (
  `version` varchar(191) COLLATE utf8mb3_unicode_ci NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int DEFAULT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- Volcando estructura para tabla canavesio2.favorite
CREATE TABLE IF NOT EXISTS `favorite` (
  `id` int NOT NULL AUTO_INCREMENT,
  `flag` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla canavesio2.favorite: ~0 rows (aproximadamente)

-- Volcando estructura para tabla canavesio2.machine
CREATE TABLE IF NOT EXISTS `machine` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` int NOT NULL,
  `brand` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `image` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_new` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla canavesio2.machine: ~0 rows (aproximadamente)

-- Volcando estructura para tabla canavesio2.messenger_messages
CREATE TABLE IF NOT EXISTS `messenger_messages` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `body` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `headers` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue_name` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `available_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `delivered_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  PRIMARY KEY (`id`),
  KEY `IDX_75EA56E0FB7336F0` (`queue_name`),
  KEY `IDX_75EA56E0E3BD61CE` (`available_at`),
  KEY `IDX_75EA56E016BA31DB` (`delivered_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla canavesio2.messenger_messages: ~0 rows (aproximadamente)

-- Volcando estructura para tabla canavesio2.order
CREATE TABLE IF NOT EXISTS `order` (
  `id` int NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla canavesio2.order: ~0 rows (aproximadamente)

-- Volcando estructura para tabla canavesio2.parts
CREATE TABLE IF NOT EXISTS `parts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `quantity` int DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `brand` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla canavesio2.parts: ~0 rows (aproximadamente)

-- Volcando estructura para tabla canavesio2.product
CREATE TABLE IF NOT EXISTS `product` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` double NOT NULL,
  `quantity` int NOT NULL,
  `is_enabled` tinyint(1) NOT NULL,
  `brand` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `min_stock` int NOT NULL,
  `image` longtext COLLATE utf8mb4_unicode_ci,
  `part_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `compatible_models` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `dimensions` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `material` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `weight` decimal(10,2) DEFAULT NULL,
  `load_capacity` longtext COLLATE utf8mb4_unicode_ci,
  `estimated_life_hours` int DEFAULT NULL,
  `mounting_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `installation_requirements` longtext COLLATE utf8mb4_unicode_ci,
  `part_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `created_by` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_deleted` tinyint(1) NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla canavesio2.product: ~0 rows (aproximadamente)

-- Volcando estructura para tabla canavesio2.product_movement
CREATE TABLE IF NOT EXISTS `product_movement` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int DEFAULT NULL,
  `movement_type` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` int NOT NULL,
  `previous_stock` int NOT NULL,
  `new_stock` int NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `performed_by` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `order_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_3F6DFF604584665A` (`product_id`),
  CONSTRAINT `FK_3F6DFF604584665A` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla canavesio2.product_movement: ~0 rows (aproximadamente)

-- Volcando estructura para tabla canavesio2.product_parts_machine
CREATE TABLE IF NOT EXISTS `product_parts_machine` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `parts_id` int DEFAULT NULL,
  `machine_id` int DEFAULT NULL,
  `quantity` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_4837D6C34584665A` (`product_id`),
  KEY `IDX_4837D6C34E81F03D` (`parts_id`),
  KEY `IDX_4837D6C3F6B75B26` (`machine_id`),
  CONSTRAINT `FK_4837D6C34584665A` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`),
  CONSTRAINT `FK_4837D6C34E81F03D` FOREIGN KEY (`parts_id`) REFERENCES `parts` (`id`),
  CONSTRAINT `FK_4837D6C3F6B75B26` FOREIGN KEY (`machine_id`) REFERENCES `machine` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla canavesio2.product_parts_machine: ~0 rows (aproximadamente)

-- Volcando estructura para tabla canavesio2.purchase
CREATE TABLE IF NOT EXISTS `purchase` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `total_price` double NOT NULL,
  `purchase_date` datetime NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `transaction_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_details` json NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_6117D13BA76ED395` (`user_id`),
  CONSTRAINT `FK_6117D13BA76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla canavesio2.purchase: ~0 rows (aproximadamente)

-- Volcando estructura para tabla canavesio2.purchase_item
CREATE TABLE IF NOT EXISTS `purchase_item` (
  `id` int NOT NULL AUTO_INCREMENT,
  `purchase_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL,
  `price` double NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_6FA8ED7D558FBEB9` (`purchase_id`),
  KEY `IDX_6FA8ED7D4584665A` (`product_id`),
  CONSTRAINT `FK_6FA8ED7D4584665A` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_6FA8ED7D558FBEB9` FOREIGN KEY (`purchase_id`) REFERENCES `purchase` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla canavesio2.purchase_item: ~0 rows (aproximadamente)

-- Volcando estructura para tabla canavesio2.recipe_machine
CREATE TABLE IF NOT EXISTS `recipe_machine` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla canavesio2.recipe_machine: ~0 rows (aproximadamente)

-- Volcando estructura para tabla canavesio2.recipe_machine_parts
CREATE TABLE IF NOT EXISTS `recipe_machine_parts` (
  `recipe_machine_id` int NOT NULL,
  `parts_id` int NOT NULL,
  PRIMARY KEY (`recipe_machine_id`,`parts_id`),
  KEY `IDX_D03A724BBFC9DD45` (`recipe_machine_id`),
  KEY `IDX_D03A724B4E81F03D` (`parts_id`),
  CONSTRAINT `FK_D03A724B4E81F03D` FOREIGN KEY (`parts_id`) REFERENCES `parts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_D03A724BBFC9DD45` FOREIGN KEY (`recipe_machine_id`) REFERENCES `recipe_machine` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla canavesio2.recipe_machine_parts: ~0 rows (aproximadamente)

-- Volcando estructura para tabla canavesio2.recipe_machine_product
CREATE TABLE IF NOT EXISTS `recipe_machine_product` (
  `recipe_machine_id` int NOT NULL,
  `product_id` int NOT NULL,
  PRIMARY KEY (`recipe_machine_id`,`product_id`),
  KEY `IDX_BC41C171BFC9DD45` (`recipe_machine_id`),
  KEY `IDX_BC41C1714584665A` (`product_id`),
  CONSTRAINT `FK_BC41C1714584665A` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_BC41C171BFC9DD45` FOREIGN KEY (`recipe_machine_id`) REFERENCES `recipe_machine` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla canavesio2.recipe_machine_product: ~0 rows (aproximadamente)

-- Volcando estructura para tabla canavesio2.recipe_product
CREATE TABLE IF NOT EXISTS `recipe_product` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `parts` json NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla canavesio2.recipe_product: ~0 rows (aproximadamente)

-- Volcando estructura para tabla canavesio2.reservation
CREATE TABLE IF NOT EXISTS `reservation` (
  `id` int NOT NULL AUTO_INCREMENT,
  `seller_id` int NOT NULL,
  `customer_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL,
  `created_at` datetime NOT NULL,
  `created_by` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expires_at` datetime NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notes` longtext COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `IDX_42C849558DE820D9` (`seller_id`),
  KEY `IDX_42C849559395C3F3` (`customer_id`),
  KEY `IDX_42C849554584665A` (`product_id`),
  CONSTRAINT `FK_42C849554584665A` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_42C849558DE820D9` FOREIGN KEY (`seller_id`) REFERENCES `user` (`id`),
  CONSTRAINT `FK_42C849559395C3F3` FOREIGN KEY (`customer_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla canavesio2.reservation: ~0 rows (aproximadamente)

-- Volcando estructura para tabla canavesio2.used_machinery
CREATE TABLE IF NOT EXISTS `used_machinery` (
  `id` int NOT NULL AUTO_INCREMENT,
  `category_id` int NOT NULL,
  `model` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `brand` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `manufacturing_date` datetime DEFAULT NULL,
  `fuel_tank_capacity` decimal(10,2) NOT NULL,
  `technology` longtext COLLATE utf8mb4_unicode_ci,
  `transmission_system` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_service` datetime DEFAULT NULL,
  `hours_of_use` int DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image_filenames` json NOT NULL,
  `is_new` tinyint(1) NOT NULL,
  `taxpayer_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `load_capacity` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_7A29C47012469DE2` (`category_id`),
  CONSTRAINT `FK_7A29C47012469DE2` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=UTF8MB4_UNICODE_CI;

CREATE TABLE IF NOT EXISTS `user` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `security_question` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `security_answer` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `roles` json NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reset_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `is_guest` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_IDENTIFIER_EMAIL` (`email`),
  UNIQUE KEY `UNIQ_8D93D649F85E0677` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=UTF8MB4_UNICODE_CI;

-- Volcando estructura para tabla canavesio2.user_favorite_product
CREATE TABLE IF NOT EXISTS `user_favorite_product` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `favorite_id` int NOT NULL,
  `product_id` int NOT NULL,
  `machine_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_F30BE81EA76ED395` (`user_id`),
  KEY `IDX_F30BE81EAA17481D` (`favorite_id`),
  KEY `IDX_F30BE81E4584665A` (`product_id`),
  KEY `IDX_F30BE81EF6B75B26` (`machine_id`),
  CONSTRAINT `FK_F30BE81E4584665A` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_F30BE81EA76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
  CONSTRAINT `FK_F30BE81EAA17481D` FOREIGN KEY (`favorite_id`) REFERENCES `favorite` (`id`),
  CONSTRAINT `FK_F30BE81EF6B75B26` FOREIGN KEY (`machine_id`) REFERENCES `machine` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


SELECT CONCAT('[', DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:%s'), '] Starting data insertion - User: SantiAragon') AS log;
-- Insertar los datos sin especificar IDs
INSERT INTO `category` (`name`, `code`, `image`) VALUES
('Tractores', '123', 'tractorcategoria-67cdd52da289a.jpg'),
('Pulverizadoras', '12345', 'pulverizadoracategoria-67cdd584950b6.webp'),
('Cosechadoras', '1235645', 'cosechadoracategoria-67cdd6f6847af.webp'),
('Sembradoras', '4565687', 'sembradoracategoria-67cdd72faf29e.jpg');
INSERT INTO `user` (`email`, `username`, `phone`, `security_question`, `security_answer`, `roles`, `password`, `reset_token`, `created_at`, `is_guest`) VALUES
	('VENDEDOR@canavesio.org.ar', 'VENDEDOR', '1234557', 'nombre_mascota', 'Garu', '["ROLE_VENDEDOR"]', '$2y$13$BRV83ZpxH3bNC05ygrfS9OeVj8IEEmq5wozUe9J7ByMSgofoY4hOq', NULL, '2025-03-09 17:49:11', 0);
-- Volcando datos para la tabla canavesio2.used_machinery: ~0 rows (aproximadamente)
INSERT INTO `used_machinery` (`category_id`, `model`, `brand`, `manufacturing_date`, `fuel_tank_capacity`, `technology`, `transmission_system`, `last_service`, `hours_of_use`, `price`, `location`, `image_filenames`, `is_new`, `taxpayer_type`, `load_capacity`) VALUES
	(1, 'Puma 200', 'Case Ih', NULL, 395.00, 'AFS Pro 700, AccuGuide', 'manual', NULL, NULL, 142500.00, 'Rosario Argentina', '["puma-200--1-67cdda96e4b50.png", "puma-200--2-67cdda96e5065.png", "puma-200--3-67cdda96e55b4.png", "puma-200--4-67cdda96e5956.png"]', 1, 'responsable_inscripto', NULL),
	(1, 'MF 7719 S', 'Massey Ferguson', NULL, 430.00, 'Datatronic 5, Auto-Guide', 'cvt', NULL, NULL, 138800.00, 'Pergamino Argentina', '["modelo-mf7719-S--1-67cde9eec98bd.png", "modelo-mf7719-S--2-67cde9eec9cda.png", "modelo-mf7719-S--3-67cde9eeca066.png", "modelo-mf7719-S--4-67cde9eeca3d4.png", "modelo-mf7719-S--5-67cde9eeca7e1.png"]', 1, 'responsable_inscripto', NULL),
	(1, 'Axion 870', 'Claas', NULL, 455.00, 'CEBIS, GPS Pilot', 'cvt', NULL, NULL, 155000.00, 'Mendoza Argentina', '["axion-870--1-67cdeae91c763.png", "axion-870--2-67cdeae91cc6f.png", "axion-870--3-67cdeae91d258.png", "axion-870--4-67cdeae91d6cb.png", "axion-870--5-67cdeae91dafb.png"]', 1, 'exento', NULL),
	(1, '6130R', 'John Deere', NULL, 270.00, 'GreenStar 3 2630', 'mecanica', NULL, NULL, 85000.00, 'Tucumán Argentina', '["6130R--1-67cdebda8231c.png", "6130R--2-67cdebda8286b.png", "6130R--3-67cdebda82c94.png", "6130R--4-67cdebda831b2.png"]', 1, 'monotributista', NULL),
	(1, '6130R', 'John Deere', '2018-06-05 00:00:00', 270.00, 'GreenStar 3 2630', 'mecanica', '2025-01-15 00:00:00', 3200, 85000.00, 'Tucumán Argentina', '["6130R--1-67cdebf1b7857.png", "6130R--2-67cdebf1b7d2d.png", "6130R--3-67cdebf1b8140.png", "6130R--4-67cdebf1b8560.png"]', 0, 'monotributista', NULL),
	(1, 'Maxxum 125', 'Case Ih', '2005-11-03 00:00:00', 250.00, 'AFS Pro 700 básico', 'automatica', '2024-11-05 00:00:00', 4500, 70000.00, 'Santa Fe Argentina', '["Maxxum-125--1-67cdecf15b9f2.png", "Maxxum-125--2-67cdecf15bfd1.png", "Maxxum-125--3-67cdecf15c507.png", "Maxxum-125--4-67cdecf15ca89.png"]', 0, 'responsable_inscripto', NULL),
	(1, 'MF 6713', 'Massey Ferguson', '2016-04-19 00:00:00', 230.00, 'Datatronic 4', 'mecanica', '2024-12-22 00:00:00', 5800, 55000.00, 'San Luis Argentina', '["mf-6713--1-67cdedf990b08.png", "mf-6713--2-67cdedf9910d7.png", "mf-6713--3-67cdedf9914c9.png", "mf-6713--4-67cdedf99193a.png", "mf-6713--5-67cdedf991e6e.png"]', 0, 'monotributista', NULL),
	(7, 'S790', 'John Deere', NULL, 14100.00, 'Harvest Smart, AutoTrac', 'hidrostatica', NULL, NULL, 580000.00, 'Salta Argentina', '["S790--1-67cdeee8060d5.png", "S790--2-67cdeee80675e.png", "S790--3-67cdeee806c79.png", "S790--4-67cdeee80729a.png", "S790--5-67cdeee807768.png"]', 1, 'responsable_inscripto', NULL),
	(7, 'LEXION 8700', 'Claas', NULL, 320.00, 'CEMOS AUTOMATIC, CEBIS', 'cvt', NULL, NULL, 595000.00, 'Córdoba Argentina', '["lexion-8700--1-67cdf037cee35.png", "lexion-8700--2-67cdf037cf271.png", "lexion-8700--3-67cdf037cf5a0.png", "lexion-8700--4-67cdf037cf96e.png", "lexion-8700--5-67cdf037cfdfa.png"]', 1, 'responsable_inscripto', NULL),
	(7, 'IDEAL 9T', 'Fendt', NULL, 270.00, 'IDEALharvest, VarioGuide', 'hidrostatica', NULL, NULL, 625000.00, 'Buenos Aires Argentina', '["ideal-9T--1-67cdf0e72bf7f.png", "ideal-9T--2-67cdf0e72c336.png", "ideal-9T--3-67cdf0e72c665.png", "ideal-9T--4-67cdf0e72c915.png", "ideal-9T--5-67cdf0e72cbd0.png"]', 1, 'responsable_inscripto', NULL),
	(7, 'S680', 'John Deere', '2019-11-05 00:00:00', 280.00, 'GreenStar 3, AutoTrac', 'hidrostatica', '2024-12-15 00:00:00', 1800, 320000.00, 'Córdoba Argentina', '["s680--1-67cdf1b7c33bd.png", "s680--2-67cdf1b7c3972.png"]', 0, 'responsable_inscripto', NULL),
	(7, 'LEXION 760', 'Claas', '2018-06-16 00:00:00', 315.00, 'CEMOS, CEBIS', 'hidrostatica', '2025-02-10 00:00:00', 2200, 280000.00, 'Santa Fe Argentina', '["lexion-760--1-67cdf2bd1ebd5.png", "lexion-760--2-67cdf2bd1f0b4.png", "lexion-760--3-67cdf2bd1f6c1.png"]', 0, 'responsable_inscripto', NULL),
	(3, 'R4060', 'John Deere', NULL, 6000.00, 'ExactApply, AutoTrac', 'hidrostatica', NULL, NULL, 390000.00, 'La Pampa Argentina', '["R4060--1-67cdf41d4e290.png", "R4060--2-67cdf41d4e74f.png", "R4060--3-67cdf41d4eb5f.png", "R4060--4-67cdf41d4ef40.png", "R4060--5-67cdf41d4f22d.png"]', 1, 'responsable_inscripto', NULL),
	(3, 'Patriot 4440', 'Case Ih', NULL, 6600.00, 'AIM Command FLEX, AccuGuide', 'hidrostatica', NULL, NULL, 375000.00, 'Santiago Del Estero Argentina', '["patriot-4440--1-67cdf506c6de8.png", "patriot-4440--2-67cdf506c7275.png", "patriot-4440--3-67cdf506c7834.png", "patriot-4440--4-67cdf506c7c80.png", "patriot-4440--5-67cdf506c8032.png"]', 1, 'responsable_inscripto', NULL),
	(3, 'R4045', 'John Deere', '2018-06-16 00:00:00', 4500.00, 'GreenStar 3, AutoTrac', 'hidrostatica', '2025-01-18 00:00:00', 2300, 220000.00, 'Buenos Aires Argentina', '["R4045--1-67cdf5d715b76.jpg", "R4045--2-67cdf5d71606a.jpg", "R4045--3-67cdf5d7164fd.jpg", "R4045--4-67cdf5d716a43.jpg", "R4045--5-67cdf5d716fdc.jpg"]', 0, 'responsable_inscripto', NULL),
	(3, 'Patriot 3340', 'Case Ih', '2017-07-05 00:00:00', 3800.00, 'AIM Command PRO', 'hidrostatica', '2024-11-25 00:00:00', 3100, 195000.00, 'La Rioja Argentina', '["Patriot-3340--1-67cdf69c9fea7.jpg", "Patriot-3340--2-67cdf69ca048c.jpg", "Patriot-3340--3-67cdf69ca091d.jpg", "Patriot-3340--4-67cdf69ca0e32.jpg", "Patriot-3340--5-67cdf69ca14d2.jpg"]', 0, 'responsable_inscripto', NULL),
	(3, 'Guardian SP.275F', 'New Holland', '2017-04-19 00:00:00', 3800.00, 'IntelliView III', 'hidrostatica', '2025-02-05 00:00:00', 2900, 180000.00, 'Entre Ríos Argentina', '["Guardian-SP-275F--1-67cdf716a250f.jpg"]', 0, 'exento', NULL),
	(8, 'DB90', 'John Deere', NULL, 3500.00, 'ExactEmerge, SeedStar 4HP', 'automatica', NULL, NULL, 320000.00, 'Santa Fe Argentina', '["DB90--1-67cdf7e05cd13.png", "DB90--2-67cdf7e05d209.png"]', 1, 'responsable_inscripto', NULL),
	(8, 'Early Riser 2160', 'Case Ih', NULL, 3300.00, 'Advanced Seed Delivery, AFS Pro 700', 'hidraulica', NULL, NULL, 310000.00, 'Córdoba Argentina', '["early-riser-2160--1-67cdf95f35282.png", "early-riser-2160--2-67cdf95f35987.png", "early-riser-2160--3-67cdf95f35f7b.png", "early-riser-2160--4-67cdf95f36568.png", "early-riser-2160--5-67cdf95f36c91.png"]', 1, 'responsable_inscripto', NULL),
	(8, 'SSM 27', 'Semeato', '2015-12-05 00:00:00', 1800.00, 'Double Disc básico', 'mecanica', '2024-12-10 00:00:00', 2500, 85000.00, 'Entre Ríos Argentina', '["SSM-27--1-67cdfa13d1198.jpg", "SSM-27--2-67cdfa13d15d5.jpg", "SSM-27--3-67cdfa13d1970.jpg", "SSM-27--4-67cdfa13d1ca9.jpg", "SSM-27--5-67cdfa13d2117.jpg"]', 0, 'responsable_inscripto', NULL),
	(8, 'YP-2425A', 'Great Plains', '2016-06-05 00:00:00', 1700.00, 'Air-Pro Metering básico', 'mecanicaHD', '2025-02-05 00:00:00', 2100, 95000.00, 'Chaco Argentina', '["YP-2425A--1-67ce0d1c6f4cd.jpg", "YP-2425A--2-67ce0d1c6f9b9.jpg", "YP-2425A--3-67ce0d1c6fde5.jpg", "YP-2425A--4-67ce0d1c70178.jpg", "YP-2425A--5-67ce0d1c704de.jpg"]', 0, 'exento', NULL),
	(8, 'Planters 3400', 'Agco', '2020-03-02 00:00:00', 1500.00, 'Control de siembra White', 'hidraulica', '2025-01-18 00:00:00', 2800, 80000.00, 'Corrientes Argentina', '["Planters-3400--1-67ce0e107eb87.jpg", "Planters-3400--2-67ce0e107f0f0.jpg", "Planters-3400--3-67ce0e107fb4c.jpg", "Planters-3400--4-67ce0e10802de.jpg", "Planters-3400--5-67ce0e1080901.jpg"]', 0, 'monotributista', NULL),
	(8, 'Momentum G8', 'Massey Ferguson', NULL, 3420.00, 'vSet, vDrive, DeltaForce', 'hidraulica', NULL, NULL, 280000.00, 'Tucumán Argentina', '["Momentum-G8--1-67ce0ee78349a.jpg", "Momentum-G8--2-67ce0ee783900.jpg", "Momentum-G8--3-67ce0ee783e2d.jpg", "Momentum-G8--4-67ce0ee78436e.jpg", "Momentum-G8--5-67ce0ee78487f.jpg"]', 1, 'exento', NULL);

SET FOREIGN_KEY_CHECKS = 1;

-- Registro de finalización
SELECT CONCAT('[', DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:%s'), '] Database import completed successfully - User: SantiAragon') AS log;

-- Volcando datos para la tabla canavesio2.user_favorite_product: ~0 rows (aproximadamente)

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
