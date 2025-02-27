-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Versión del servidor:         8.0.30 - MySQL Community Server - GPL
-- SO del servidor:              Win64
-- HeidiSQL Versión:             12.1.0.6537
-- --------------------------------------------------------

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

-- Volcando estructura para tabla canavesio2.cart
CREATE TABLE IF NOT EXISTS `cart` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_BA388B7A76ED395` (`user_id`),
  CONSTRAINT `FK_BA388B7A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla canavesio2.cart: ~3 rows (aproximadamente)
INSERT INTO `cart` (`id`, `user_id`) VALUES
	(1, 1),
	(3, 7),
	(4, 10),
	(5, 11),
	(6, 12);

-- Volcando estructura para tabla canavesio2.cart_product_order
CREATE TABLE IF NOT EXISTS `cart_product_order` (
  `id` int NOT NULL AUTO_INCREMENT,
  `cart_id` int NOT NULL,
  `orders_id` int DEFAULT NULL,
  `product_id` int NOT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla canavesio2.cart_product_order: ~6 rows (aproximadamente)

-- Volcando estructura para tabla canavesio2.favorite
CREATE TABLE IF NOT EXISTS `favorite` (
  `id` int NOT NULL AUTO_INCREMENT,
  `flag` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla canavesio2.favorite: ~1 rows (aproximadamente)
INSERT INTO `favorite` (`id`, `flag`) VALUES
	(1, 1),
	(2, 1),
	(3, 1),
	(4, 1),
	(5, 1);

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
  `quantity` int DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `brand` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `min_stock` int NOT NULL,
  `is_enabled` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla canavesio2.product: ~1 rows (aproximadamente)

-- Volcando estructura para tabla canavesio2.product_parts
CREATE TABLE IF NOT EXISTS `product_parts` (
  `product_id` int NOT NULL,
  `parts_id` int NOT NULL,
  PRIMARY KEY (`product_id`,`parts_id`),
  KEY `IDX_C81CDCA24584665A` (`product_id`),
  KEY `IDX_C81CDCA24E81F03D` (`parts_id`),
  CONSTRAINT `FK_C81CDCA24584665A` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_C81CDCA24E81F03D` FOREIGN KEY (`parts_id`) REFERENCES `parts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla canavesio2.product_parts: ~0 rows (aproximadamente)

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

-- Volcando estructura para tabla canavesio2.used_machinery
CREATE TABLE IF NOT EXISTS `used_machinery` (
  `id` int NOT NULL AUTO_INCREMENT,
  `machinery_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `brand` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `years_old` int NOT NULL,
  `months` int NOT NULL,
  `hours_of_use` int NOT NULL,
  `last_service` date NOT NULL,
  `price` double DEFAULT NULL,
  `image_filename` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_new` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla canavesio2.used_machinery: ~6 rows (aproximadamente)
INSERT INTO `used_machinery` (`id`, `machinery_name`, `brand`, `years_old`, `months`, `hours_of_use`, `last_service`, `price`, `image_filename`, `category`, `is_new`) VALUES
	(1, 'machinaria1', 'xdddd', 13, 11, 13, '2025-02-24', 5555, NULL, 'Tractor', 1),
	(2, 'asdasdasd', 'asdasdasd', 0, 0, 0, '2025-02-25', 11231, 'Embutidora-de-forraje-Canavesio-R-2800-Dual-67bd0d2b5c191.jpg', 'embutidora', 1),
	(3, 'usada', 'usada', 4, 4, 13, '2025-01-29', 11231, 'tractor6E-67bd0dd265c0a.jpg', 'tractor', 0),
	(4, 'nueva', 'nueva', 0, 0, 0, '2025-02-25', 11231, 'tractor6E-67bd0de84d9e1.jpg', 'sembradora', 1),
	(5, 'dadasd', 'dadad', 0, 0, 0, '2025-02-25', 12312, 'canavesiofoto3-67bd0ef55e7c0.jpg', 'tractor', 1),
	(6, 'dadasd', 'dadad', 0, 0, 0, '2025-02-25', 12312, 'canavesiofoto-67bd0f58960e5.jpg', 'tractor', 1),
	(7, 'Maquinaria1', 'asddasffff', 7, 11, 120, '2025-02-19', 1231243, 'Canavesio-acoplado-volcador-hidr--ulico-M40-e1512992608305-67bfc46cae7c8.jpg', 'equipo de forraje', 0);

-- Volcando estructura para tabla canavesio2.user
CREATE TABLE IF NOT EXISTS `user` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `roles` json NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reset_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `security_question` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `security_answer` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_IDENTIFIER_EMAIL` (`email`),
  UNIQUE KEY `UNIQ_8D93D649F85E0677` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla canavesio2.user: ~7 rows (aproximadamente)
INSERT INTO `user` (`id`, `email`, `roles`, `password`, `reset_token`, `username`, `phone`, `security_question`, `security_answer`) VALUES
	(1, 'jojojuju@xd.es', '["ROLE_USER"]', '$2y$13$ZFJGg4wtW2kNK7/LtO8mOu3X07DeY.ownx2Dif.b1BO8BtH.KuxZC', NULL, 'jota', '342555', 'comida_favorita', 'la chota'),
	(2, 'santi123@gmail.com', '["ROLE_USER"]', '$2y$13$Sm6Uxv44b0G4B40EkLbxbu4TDz79JxNwjrthkcipcS7zYHS8wKn.e', NULL, 'Sani', '13234', 'comida_favorita', 'la chota'),
	(7, 'admin@prueba.com', '["ROLE_ADMIN"]', '$2y$13$HIy.928anPNbrFEYVEWTfuWhfDU3cbbTey7jQfMfPIGLEega/mmdu', NULL, 'ADMIN', '12345', 'nombre_mascota', 'ernesto'),
	(8, 'santiago@gmail.com', '["ROLE_USER"]', '$2y$13$NqcC6UYwOCTg3ZcPpzmmsOcnKSvZ1ZVswAJFtxAUxeIUc0tmGtn.2', NULL, 'santiago', '12345', 'primer_trabajo', 'verdulero'),
	(9, 'stock@prueba.com', '["ROLE_GESTORSTOCK"]', '$2y$13$o0TTCEu4XF9J89V8AmpNA.vcGRjNuhpWLKgzF7HyfCncb3mFc18Z.', NULL, 'JEFESTOCK', '342555', 'comida_favorita', 'jeje'),
	(10, 'ventas@prueba.com', '["ROLE_VENDEDOR"]', '$2y$13$N0/1S8hjNzv.uoZvnU07iehR6rh5LnaS/EcmKUWmVex0HNNDWtx1u', NULL, 'VENDEDOR', '12345', 'nombre_mascota', 'pelusa'),
	(11, 'asdada@gmail.com', '["ROLE_USER"]', '$2y$13$zwQp76956OQvoR4t6M7ezeY6BQH6nFdDGxIMalBSjex/y.AfKdL8m', NULL, 'dasdasd', '4523', 'primer_trabajo', 'hola'),
	(12, 'usuario@gmail.com', '["ROLE_USER"]', '$2y$13$qquAJHtc9lDqQQSKm9HyROGMzjo.CF4/JnlxVOB.1kMUxv.VmxFCG', NULL, 'USUARIO', '12345', 'nombre_mascota', 'pelusa'),
	(13, 'santi13@gmail.com', '["ROLE_USUARIO"]', '$2y$13$CMg0oT13Hpa6rOfCT9VzaOv5s9XDYeubIpzrcCobHw8GFtGYFmWQ.', NULL, 'santiii', '3255234', 'comida_favorita', 'la milanesa'),
	(14, 'santi1@gmail.com', '["ROLE_USUARIO"]', '$2y$13$FdFoZEBxDJD6bELQOHpac.vvqCmF1dr38VLZWT9llWCabTz9jp/f2', NULL, 'sdadsad', '21313213', 'comida_favorita', 'la milanesa');

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
  CONSTRAINT `FK_F30BE81E4584665A` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`),
  CONSTRAINT `FK_F30BE81EA76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
  CONSTRAINT `FK_F30BE81EAA17481D` FOREIGN KEY (`favorite_id`) REFERENCES `favorite` (`id`),
  CONSTRAINT `FK_F30BE81EF6B75B26` FOREIGN KEY (`machine_id`) REFERENCES `machine` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla canavesio2.user_favorite_product: ~2 rows (aproximadamente)

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
