-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               5.6.17 - MySQL Community Server (GPL)
-- Server OS:                    Win32
-- HeidiSQL Version:             8.3.0.4694
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Dumping database structure for csgoshop.development
CREATE DATABASE IF NOT EXISTS `csgoshop.development` /*!40100 DEFAULT CHARACTER SET latin1 */;
USE `csgoshop.development`;


-- Dumping structure for table csgoshop.development.bots
CREATE TABLE IF NOT EXISTS `bots` (
  `id` bigint(17) NOT NULL,
  `username` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `status` int(1) DEFAULT NULL,
  `type` int(1) DEFAULT NULL,
  `avatar_url` varchar(255) DEFAULT NULL,
  `steam_status` int(1) DEFAULT NULL,
  `last_sync` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table csgoshop.development.bots: ~0 rows (approximately)
/*!40000 ALTER TABLE `bots` DISABLE KEYS */;
/*!40000 ALTER TABLE `bots` ENABLE KEYS */;


-- Dumping structure for table csgoshop.development.cashout_listings
CREATE TABLE IF NOT EXISTS `cashout_listings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cashout_request_id` int(11) NOT NULL,
  `listing_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table csgoshop.development.cashout_listings: ~0 rows (approximately)
/*!40000 ALTER TABLE `cashout_listings` DISABLE KEYS */;
/*!40000 ALTER TABLE `cashout_listings` ENABLE KEYS */;


-- Dumping structure for table csgoshop.development.cashout_requests
CREATE TABLE IF NOT EXISTS `cashout_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `total` decimal(6,2) NOT NULL DEFAULT '0.00',
  `status` int(11) NOT NULL DEFAULT '0',
  `user_id` bigint(17) NOT NULL,
  `paypal` varchar(254) NOT NULL,
  `token` varchar(254) DEFAULT NULL,
  `provider` varchar(45) NOT NULL,
  `provider_identifier` varchar(254) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table csgoshop.development.cashout_requests: ~0 rows (approximately)
/*!40000 ALTER TABLE `cashout_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `cashout_requests` ENABLE KEYS */;


-- Dumping structure for table csgoshop.development.descriptions
CREATE TABLE IF NOT EXISTS `descriptions` (
  `id` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `icon_url` varchar(300) NOT NULL,
  `icon_url_large` varchar(300) DEFAULT NULL,
  `inspect_url_template` varchar(300) DEFAULT NULL,
  `stackable` tinyint(4) NOT NULL DEFAULT '-1',
  `name_color` varchar(45) DEFAULT NULL,
  `market_name` varchar(100) DEFAULT NULL,
  `price_preset` decimal(6,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table csgoshop.development.descriptions: ~0 rows (approximately)
/*!40000 ALTER TABLE `descriptions` DISABLE KEYS */;
/*!40000 ALTER TABLE `descriptions` ENABLE KEYS */;


-- Dumping structure for table csgoshop.development.descriptiontags
CREATE TABLE IF NOT EXISTS `descriptiontags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `description_id` varchar(50) NOT NULL,
  `tag_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table csgoshop.development.descriptiontags: ~0 rows (approximately)
/*!40000 ALTER TABLE `descriptiontags` DISABLE KEYS */;
/*!40000 ALTER TABLE `descriptiontags` ENABLE KEYS */;


-- Dumping structure for table csgoshop.development.listings
CREATE TABLE IF NOT EXISTS `listings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(17) NOT NULL,
  `item_id` bigint(17) NOT NULL,
  `message` varchar(500) DEFAULT NULL,
  `price` decimal(6,2) NOT NULL DEFAULT '0.00',
  `stage` tinyint(4) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  `description_id` varchar(50) NOT NULL,
  `trade_url` varchar(255) DEFAULT NULL,
  `trade_code` varchar(45) DEFAULT NULL,
  `featured` int(1) DEFAULT NULL,
  `screenshot_playside` varchar(45) DEFAULT NULL,
  `screenshot_backside` varchar(45) DEFAULT NULL,
  `note_playside` varchar(45) DEFAULT NULL,
  `note_backside` varchar(45) DEFAULT NULL,
  `request_takedown` int(1) NOT NULL DEFAULT '0',
  `parent_listing_id` int(11) DEFAULT NULL,
  `inspect_url` varchar(300) DEFAULT NULL,
  `bot_id` bigint(17) DEFAULT NULL,
  `checkout` int(1) NOT NULL DEFAULT '0',
  `checkout_user_id` bigint(17) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table csgoshop.development.listings: ~0 rows (approximately)
/*!40000 ALTER TABLE `listings` DISABLE KEYS */;
/*!40000 ALTER TABLE `listings` ENABLE KEYS */;


-- Dumping structure for table csgoshop.development.notifications
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(17) NOT NULL DEFAULT '0',
  `receiver_id` bigint(17) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted` int(1) NOT NULL DEFAULT '0',
  `seen` int(1) NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table csgoshop.development.notifications: ~0 rows (approximately)
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;


-- Dumping structure for table csgoshop.development.orderitems
CREATE TABLE IF NOT EXISTS `orderitems` (
  `order_id` int(11) NOT NULL,
  `listing_id` int(11) NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table csgoshop.development.orderitems: ~0 rows (approximately)
/*!40000 ALTER TABLE `orderitems` DISABLE KEYS */;
/*!40000 ALTER TABLE `orderitems` ENABLE KEYS */;


-- Dumping structure for table csgoshop.development.orders
CREATE TABLE IF NOT EXISTS `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(17) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  `provider` varchar(45) NOT NULL,
  `total` decimal(6,2) NOT NULL,
  `transaction` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table csgoshop.development.orders: ~0 rows (approximately)
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;


-- Dumping structure for table csgoshop.development.pages
CREATE TABLE IF NOT EXISTS `pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(17) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

-- Dumping data for table csgoshop.development.pages: ~4 rows (approximately)
/*!40000 ALTER TABLE `pages` DISABLE KEYS */;
INSERT INTO `pages` (`id`, `user_id`, `title`, `body`, `updated_at`, `created_at`) VALUES
	(1, 76561198034369542, 'Frequently Asked Questions', '', '2014-08-26 04:10:50', '2014-08-06 19:42:31'),
	(2, 76561198034369542, 'Affiliates', '', '2014-08-07 03:57:17', '2014-08-06 19:42:31'),
	(3, 76561198044189366, 'Terms of Service', '', '2014-08-25 04:10:09', '2014-08-06 19:42:31'),
	(4, 76561198044189366, 'Privacy Policy', '', '2014-08-25 04:10:12', '2014-08-06 19:42:31');
/*!40000 ALTER TABLE `pages` ENABLE KEYS */;


-- Dumping structure for table csgoshop.development.sessions
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hash` varchar(32) DEFAULT NULL,
  `user_id` bigint(17) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `ip` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hash` (`hash`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table csgoshop.development.sessions: ~0 rows (approximately)
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;


-- Dumping structure for table csgoshop.development.support_replies
CREATE TABLE IF NOT EXISTS `support_replies` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `support_id` int(10) NOT NULL DEFAULT '0',
  `user_id` bigint(17) NOT NULL DEFAULT '0',
  `body` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table csgoshop.development.support_replies: ~0 rows (approximately)
/*!40000 ALTER TABLE `support_replies` DISABLE KEYS */;
/*!40000 ALTER TABLE `support_replies` ENABLE KEYS */;


-- Dumping structure for table csgoshop.development.support_tickets
CREATE TABLE IF NOT EXISTS `support_tickets` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(17) NOT NULL DEFAULT '0',
  `subject` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `status` int(1) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_reply` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table csgoshop.development.support_tickets: ~0 rows (approximately)
/*!40000 ALTER TABLE `support_tickets` DISABLE KEYS */;
/*!40000 ALTER TABLE `support_tickets` ENABLE KEYS */;


-- Dumping structure for table csgoshop.development.tags
CREATE TABLE IF NOT EXISTS `tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category` varchar(45) NOT NULL,
  `category_name` varchar(45) NOT NULL,
  `internal_name` varchar(45) NOT NULL,
  `name` varchar(45) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table csgoshop.development.tags: ~0 rows (approximately)
/*!40000 ALTER TABLE `tags` DISABLE KEYS */;
/*!40000 ALTER TABLE `tags` ENABLE KEYS */;


-- Dumping structure for table csgoshop.development.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint(17) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `profile_private` int(1) NOT NULL,
  `inventory_private` int(1) NOT NULL,
  `rank` int(3) NOT NULL DEFAULT '10',
  `avatar_url` varchar(255) NOT NULL,
  `steam_status` int(1) NOT NULL,
  `last_sync` int(10) NOT NULL,
  `trade_url` varchar(80) DEFAULT NULL,
  `ip_register` varchar(45) NOT NULL,
  `ip_last` varchar(45) DEFAULT NULL,
  `name_register` varchar(255) NOT NULL,
  `inventory_cached` mediumtext,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  `last_cashout` timestamp NULL DEFAULT NULL,
  `tos_agree` int(1) NOT NULL DEFAULT '0',
  `paypal` varchar(254) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table csgoshop.development.users: ~0 rows (approximately)
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
