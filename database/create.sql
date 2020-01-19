-- CREATE DATABASE minicart CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
DROP DATABASE IF EXISTS minicart;
CREATE DATABASE IF NOT EXISTS minicart DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'minicart'@'localhost' IDENTIFIED BY 'WUXj8dMatMQgZsvsU%v@*JbPBwUZwqM2Dr9Xnxi2GafHwta9X';
GRANT ALL PRIVILEGES ON `minicart` . * TO 'minicart'@'localhost';
FLUSH PRIVILEGES;
USE minicart;

--
-- Table structure for table `product`
--
DROP TABLE IF EXISTS `product`;
CREATE TABLE IF NOT EXISTS `product` (
`prod_id` INT NOT NULL AUTO_INCREMENT,
`sku` VARCHAR(40) NOT NULL,
`prod_name` VARCHAR(255) DEFAULT '',
-- for GAAP compliance, we need to use DECIMAL(13,4)
`price` DECIMAL(13,2) UNSIGNED NOT NULL,
`date_created` DATETIME,

PRIMARY KEY(`prod_id`),
UNIQUE(`sku`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`sku`, `prod_name`, `price`, `date_created`) VALUES
('A', 'this product A', '50', '2020-01-09 22:38:34'),
('B', 'this product B', '30', '2020-01-09 22:38:44'),
('XXXX', 'this product XXXX', '15.45', '2020-01-09 22:38:57'),
('C', 'this product C', '20', '2020-01-09 22:39:06'),
('D', 'this product D', '15', '2020-01-09 22:39:13'),
('E', 'this product Df', '30', '2020-01-09 22:39:22'),
('MAX', 'this product MAX', '30', '2020-01-09 22:39:31');


--
-- Table structure for table `pricing_rules`
--
DROP TABLE IF EXISTS `pricing_rules`;
CREATE TABLE IF NOT EXISTS `pricing_rules` (
`rule_id` INT NOT NULL AUTO_INCREMENT,
`sku` VARCHAR(40) NOT NULL,
`product_occurrence` INT NOT NULL,
`promo_price` DECIMAL(13,2) UNSIGNED NOT NULL,
`date_created` DATETIME,

PRIMARY KEY(`rule_id`),
FOREIGN KEY(`sku`) REFERENCES product(`sku`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `pricing_rules`
--

INSERT INTO `pricing_rules` (`sku`, `product_occurrence`, `promo_price`, `date_created`) VALUES
('A', 3, '130', '2020-01-09 22:47:05'),
('B', 2, '45', '2020-01-09 23:03:37'),
('E', 4, '95', '2020-01-09 23:03:57');


--
-- Table structure for table `cart`
--
DROP TABLE IF EXISTS cart;
CREATE TABLE IF NOT EXISTS cart (
`id` INT NOT NULL AUTO_INCREMENT,
`cart_id` VARCHAR(40) NOT NULL,
`checkout_status` TINYINT DEFAULT '0',
`date_created` DATETIME,
`total_price` DECIMAL(13,2) UNSIGNED DEFAULT NULL,

PRIMARY KEY(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


--
-- Table structure for table `cart_item`
--
DROP TABLE IF EXISTS cart_item;
CREATE TABLE IF NOT EXISTS `cart_item` (
`id` INT NOT NULL AUTO_INCREMENT,
`cart_id` VARCHAR(40) NOT NULL,
`sku` VARCHAR(40) NOT NULL,
`prod_qty` INT NOT NULL,
`is_discounted` TINYINT DEFAULT '0',
`total_price` DECIMAL(13,2) NULL,
`date_created` DATETIME DEFAULT CURRENT_TIMESTAMP,

PRIMARY KEY(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
