/*
MySQL Data Transfer
Source Host: localhost
Source Database: db_iiiaaaacgbfafbfdadradehaeiceqf
Target Host: localhost
Target Database: db_iiiaaaacgbfafbfdadradehaeiceqf
Date: 4/24/2017 5:52:10 AM
*/

SET FOREIGN_KEY_CHECKS=0;
-- ----------------------------
-- Table structure for account
-- ----------------------------
DROP TABLE IF EXISTS `account`;
CREATE TABLE `account` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `account_category_type_id` int(10) unsigned NOT NULL,
  `account_detail_type_id` int(10) unsigned NOT NULL,
  `account_number` int(10) unsigned NOT NULL,
  `balance` double DEFAULT NULL,
  `is_trash` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `account_account_category_type_id_foreign` (`account_category_type_id`),
  KEY `account_account_detail_type_id_foreign` (`account_detail_type_id`),
  CONSTRAINT `account_account_category_type_id_foreign` FOREIGN KEY (`account_category_type_id`) REFERENCES `account_category_type` (`id`),
  CONSTRAINT `account_account_detail_type_id_foreign` FOREIGN KEY (`account_detail_type_id`) REFERENCES `account_detail_type` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for account_category_type
-- ----------------------------
DROP TABLE IF EXISTS `account_category_type`;
CREATE TABLE `account_category_type` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_trash` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for account_detail_type
-- ----------------------------
DROP TABLE IF EXISTS `account_detail_type`;
CREATE TABLE `account_detail_type` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `account_category_type_id` int(10) unsigned NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_trash` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `account_detail_type_account_category_type_id_foreign` (`account_category_type_id`),
  CONSTRAINT `account_detail_type_account_category_type_id_foreign` FOREIGN KEY (`account_category_type_id`) REFERENCES `account_category_type` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for attachment
-- ----------------------------
DROP TABLE IF EXISTS `attachment`;
CREATE TABLE `attachment` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_trash` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for bill
-- ----------------------------
DROP TABLE IF EXISTS `bill`;
CREATE TABLE `bill` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `expenses_id` int(10) unsigned NOT NULL,
  `statement_memo` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bill_expenses_id_foreign` (`expenses_id`),
  CONSTRAINT `bill_expenses_id_foreign` FOREIGN KEY (`expenses_id`) REFERENCES `expenses` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for bill_account
-- ----------------------------
DROP TABLE IF EXISTS `bill_account`;
CREATE TABLE `bill_account` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `bill_id` int(10) unsigned DEFAULT NULL,
  `rank` smallint(5) unsigned DEFAULT NULL,
  `account_id` int(10) unsigned DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `amount` double DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bill_account_account_id_foreign` (`account_id`),
  KEY `bill_account_bill_id_foreign` (`bill_id`),
  CONSTRAINT `bill_account_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `account` (`id`),
  CONSTRAINT `bill_account_bill_id_foreign` FOREIGN KEY (`bill_id`) REFERENCES `bill` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for bill_item
-- ----------------------------
DROP TABLE IF EXISTS `bill_item`;
CREATE TABLE `bill_item` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `bill_id` int(10) unsigned NOT NULL,
  `rank` smallint(5) unsigned NOT NULL,
  `product_service_id` int(10) unsigned NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `qty` int(11) NOT NULL,
  `rate` double NOT NULL,
  `amount` double NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bill_item_bill_id_foreign` (`bill_id`),
  KEY `bill_item_product_service_id_foreign` (`product_service_id`),
  CONSTRAINT `bill_item_bill_id_foreign` FOREIGN KEY (`bill_id`) REFERENCES `bill` (`id`),
  CONSTRAINT `bill_item_product_service_id_foreign` FOREIGN KEY (`product_service_id`) REFERENCES `product_service` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for bill_payment
-- ----------------------------
DROP TABLE IF EXISTS `bill_payment`;
CREATE TABLE `bill_payment` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `expenses_id` int(10) unsigned NOT NULL,
  `account_id` int(10) unsigned NOT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bill_payment_account_id_foreign` (`account_id`),
  KEY `bill_payment_expenses_id_foreign` (`expenses_id`),
  CONSTRAINT `bill_payment_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `account` (`id`),
  CONSTRAINT `bill_payment_expenses_id_foreign` FOREIGN KEY (`expenses_id`) REFERENCES `expenses` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for company_profile
-- ----------------------------
DROP TABLE IF EXISTS `company_profile`;
CREATE TABLE `company_profile` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_name` text COLLATE utf8mb4_unicode_ci,
  `business_id_no` int(10) unsigned DEFAULT NULL,
  `industry` text COLLATE utf8mb4_unicode_ci,
  `company_email` text COLLATE utf8mb4_unicode_ci,
  `company_phone` text COLLATE utf8mb4_unicode_ci,
  `company_website` text COLLATE utf8mb4_unicode_ci,
  `address` text COLLATE utf8mb4_unicode_ci,
  `city` text COLLATE utf8mb4_unicode_ci,
  `country` text COLLATE utf8mb4_unicode_ci,
  `company_logo` text COLLATE utf8mb4_unicode_ci,
  `is_trash` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for customer
-- ----------------------------
DROP TABLE IF EXISTS `customer`;
CREATE TABLE `customer` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `company` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address1` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `balance` double DEFAULT NULL,
  `active` tinyint(1) NOT NULL,
  `is_trash` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for expense
-- ----------------------------
DROP TABLE IF EXISTS `expense`;
CREATE TABLE `expense` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `expenses_id` int(10) unsigned NOT NULL,
  `statement_memo` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `expense_expenses_id_foreign` (`expenses_id`),
  CONSTRAINT `expense_expenses_id_foreign` FOREIGN KEY (`expenses_id`) REFERENCES `expenses` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for expense_account
-- ----------------------------
DROP TABLE IF EXISTS `expense_account`;
CREATE TABLE `expense_account` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `expense_id` int(10) unsigned DEFAULT NULL,
  `rank` smallint(5) unsigned DEFAULT NULL,
  `account_id` int(10) unsigned DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `amount` double DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `expense_account_account_id_foreign` (`account_id`),
  KEY `expense_account_expense_id_foreign` (`expense_id`),
  CONSTRAINT `expense_account_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `account` (`id`),
  CONSTRAINT `expense_account_expense_id_foreign` FOREIGN KEY (`expense_id`) REFERENCES `expense` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for expense_item
-- ----------------------------
DROP TABLE IF EXISTS `expense_item`;
CREATE TABLE `expense_item` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `expense_id` int(10) unsigned NOT NULL,
  `rank` smallint(5) unsigned NOT NULL,
  `product_service_id` int(10) unsigned NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `qty` int(10) unsigned NOT NULL,
  `rate` double NOT NULL,
  `amount` double NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `expense_item_product_service_id_foreign` (`product_service_id`),
  KEY `expense_item_expense_id_foreign` (`expense_id`),
  CONSTRAINT `expense_item_expense_id_foreign` FOREIGN KEY (`expense_id`) REFERENCES `expense` (`id`) ON DELETE CASCADE,
  CONSTRAINT `expense_item_product_service_id_foreign` FOREIGN KEY (`product_service_id`) REFERENCES `product_service` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for expenses
-- ----------------------------
DROP TABLE IF EXISTS `expenses`;
CREATE TABLE `expenses` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `transaction_type` tinyint(3) unsigned NOT NULL,
  `payee_id` int(10) unsigned NOT NULL,
  `payee_type` tinyint(3) unsigned NOT NULL,
  `account_id` int(10) unsigned NOT NULL,
  `due_date` date DEFAULT NULL,
  `total` double NOT NULL,
  `balance` double NOT NULL,
  `status` tinyint(3) unsigned NOT NULL,
  `is_trash` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for invoice
-- ----------------------------
DROP TABLE IF EXISTS `invoice`;
CREATE TABLE `invoice` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sales_id` int(10) unsigned NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `statement_memo` text COLLATE utf8mb4_unicode_ci,
  `discount_type_id` tinyint(3) unsigned NOT NULL,
  `discount_amount` double DEFAULT NULL,
  `sub_total` double NOT NULL,
  `shipping` double DEFAULT NULL,
  `deposit` double DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `invoice_sales_id_foreign` (`sales_id`),
  CONSTRAINT `invoice_sales_id_foreign` FOREIGN KEY (`sales_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for invoice_item
-- ----------------------------
DROP TABLE IF EXISTS `invoice_item`;
CREATE TABLE `invoice_item` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `invoice_id` int(10) unsigned DEFAULT NULL,
  `rank` smallint(5) unsigned DEFAULT NULL,
  `item_type` tinyint(3) unsigned DEFAULT NULL,
  `product_service_id` int(10) unsigned DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `qty` int(10) unsigned DEFAULT NULL,
  `rate` double DEFAULT NULL,
  `amount` double DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `invoice_item_product_service_id_foreign` (`product_service_id`),
  KEY `invoice_item_invoice_id_foreign` (`invoice_id`),
  CONSTRAINT `invoice_item_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoice` (`id`) ON DELETE CASCADE,
  CONSTRAINT `invoice_item_product_service_id_foreign` FOREIGN KEY (`product_service_id`) REFERENCES `product_service` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for map_bill_bill_payment
-- ----------------------------
DROP TABLE IF EXISTS `map_bill_bill_payment`;
CREATE TABLE `map_bill_bill_payment` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `bill_id` int(10) unsigned NOT NULL,
  `bill_payment_id` int(10) unsigned NOT NULL,
  `payment` double NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `map_bill_bill_payment_bill_id_foreign` (`bill_id`),
  KEY `map_bill_bill_payment_bill_payment_id_foreign` (`bill_payment_id`),
  CONSTRAINT `map_bill_bill_payment_bill_id_foreign` FOREIGN KEY (`bill_id`) REFERENCES `bill` (`id`),
  CONSTRAINT `map_bill_bill_payment_bill_payment_id_foreign` FOREIGN KEY (`bill_payment_id`) REFERENCES `bill_payment` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for map_expense_attachment
-- ----------------------------
DROP TABLE IF EXISTS `map_expense_attachment`;
CREATE TABLE `map_expense_attachment` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `expense_id` int(10) unsigned NOT NULL,
  `attachment_id` int(10) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `map_expense_attachment_expense_id_foreign` (`expense_id`),
  KEY `map_expense_attachment_attachment_id_foreign` (`attachment_id`),
  CONSTRAINT `map_expense_attachment_attachment_id_foreign` FOREIGN KEY (`attachment_id`) REFERENCES `attachment` (`id`),
  CONSTRAINT `map_expense_attachment_expense_id_foreign` FOREIGN KEY (`expense_id`) REFERENCES `expense` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for map_invoice_payment
-- ----------------------------
DROP TABLE IF EXISTS `map_invoice_payment`;
CREATE TABLE `map_invoice_payment` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `invoice_id` int(10) unsigned NOT NULL,
  `payment_id` int(10) unsigned NOT NULL,
  `payment` double NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `map_invoice_payment_invoice_id_foreign` (`invoice_id`),
  KEY `map_invoice_payment_payment_id_foreign` (`payment_id`),
  CONSTRAINT `map_invoice_payment_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoice` (`id`) ON DELETE CASCADE,
  CONSTRAINT `map_invoice_payment_payment_id_foreign` FOREIGN KEY (`payment_id`) REFERENCES `payment` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for map_sales_attachment
-- ----------------------------
DROP TABLE IF EXISTS `map_sales_attachment`;
CREATE TABLE `map_sales_attachment` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sales_id` int(10) unsigned NOT NULL,
  `attachment_id` int(10) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `map_sales_attachment_sales_id_foreign` (`sales_id`),
  KEY `map_sales_attachment_attachment_id_foreign` (`attachment_id`),
  CONSTRAINT `map_sales_attachment_attachment_id_foreign` FOREIGN KEY (`attachment_id`) REFERENCES `attachment` (`id`),
  CONSTRAINT `map_sales_attachment_sales_id_foreign` FOREIGN KEY (`sales_id`) REFERENCES `sales` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for migrations
-- ----------------------------
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for payment
-- ----------------------------
DROP TABLE IF EXISTS `payment`;
CREATE TABLE `payment` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sales_id` int(10) unsigned NOT NULL,
  `account_id` int(10) unsigned NOT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payment_account_id_foreign` (`account_id`),
  KEY `payment_sales_id_foreign` (`sales_id`),
  CONSTRAINT `payment_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `account` (`id`),
  CONSTRAINT `payment_sales_id_foreign` FOREIGN KEY (`sales_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for product_category
-- ----------------------------
DROP TABLE IF EXISTS `product_category`;
CREATE TABLE `product_category` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_trash` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for product_service
-- ----------------------------
DROP TABLE IF EXISTS `product_service`;
CREATE TABLE `product_service` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sku` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `selling_price` double NOT NULL,
  `product_category_id` int(10) unsigned NOT NULL,
  `purchase_price` double NOT NULL,
  `item_type` tinyint(3) unsigned NOT NULL,
  `is_inventoriable` tinyint(1) NOT NULL,
  `active` tinyint(1) NOT NULL,
  `is_trash` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_service_product_category_id_foreign` (`product_category_id`),
  CONSTRAINT `product_service_product_category_id_foreign` FOREIGN KEY (`product_category_id`) REFERENCES `product_category` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for sales
-- ----------------------------
DROP TABLE IF EXISTS `sales`;
CREATE TABLE `sales` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `transaction_type` tinyint(3) unsigned NOT NULL,
  `invoice_receipt_no` int(10) unsigned DEFAULT NULL,
  `customer_id` int(10) unsigned NOT NULL,
  `due_date` date DEFAULT NULL,
  `total` double NOT NULL,
  `balance` double NOT NULL,
  `status` tinyint(3) unsigned NOT NULL,
  `is_trash` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sales_customer_id_foreign` (`customer_id`),
  CONSTRAINT `sales_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for sales_receipt
-- ----------------------------
DROP TABLE IF EXISTS `sales_receipt`;
CREATE TABLE `sales_receipt` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sales_id` int(10) unsigned NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `statement_memo` text COLLATE utf8mb4_unicode_ci,
  `discount_type_id` tinyint(3) unsigned NOT NULL,
  `discount_amount` double NOT NULL,
  `sub_total` double NOT NULL,
  `shipping` double NOT NULL,
  `deposit` double NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sales_receipt_sales_id_foreign` (`sales_id`),
  CONSTRAINT `sales_receipt_sales_id_foreign` FOREIGN KEY (`sales_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for sales_receipt_item
-- ----------------------------
DROP TABLE IF EXISTS `sales_receipt_item`;
CREATE TABLE `sales_receipt_item` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sales_receipt_id` int(10) unsigned DEFAULT NULL,
  `rank` smallint(5) unsigned DEFAULT NULL,
  `item_type` tinyint(3) unsigned DEFAULT NULL,
  `product_service_id` int(10) unsigned DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `qty` int(10) unsigned DEFAULT NULL,
  `rate` double DEFAULT NULL,
  `amount` double DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sales_receipt_item_product_service_id_foreign` (`product_service_id`),
  KEY `sales_receipt_item_sales_receipt_id_foreign` (`sales_receipt_id`),
  CONSTRAINT `sales_receipt_item_product_service_id_foreign` FOREIGN KEY (`product_service_id`) REFERENCES `product_service` (`id`),
  CONSTRAINT `sales_receipt_item_sales_receipt_id_foreign` FOREIGN KEY (`sales_receipt_id`) REFERENCES `sales_receipt` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for supplier
-- ----------------------------
DROP TABLE IF EXISTS `supplier`;
CREATE TABLE `supplier` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `company` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address1` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `balance` double DEFAULT NULL,
  `active` tinyint(1) NOT NULL,
  `is_trash` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for user_profile
-- ----------------------------
DROP TABLE IF EXISTS `user_profile`;
CREATE TABLE `user_profile` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` text COLLATE utf8mb4_unicode_ci,
  `email_verified` tinyint(1) DEFAULT NULL,
  `mobile` text COLLATE utf8mb4_unicode_ci,
  `mobile_verified` tinyint(1) DEFAULT NULL,
  `password` text COLLATE utf8mb4_unicode_ci,
  `address` text COLLATE utf8mb4_unicode_ci,
  `city` text COLLATE utf8mb4_unicode_ci,
  `country` text COLLATE utf8mb4_unicode_ci,
  `is_trash` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Records 
-- ----------------------------
INSERT INTO `account` VALUES ('1', 'Cash', '', '1', '1', '101', '0', '0', null, null);
INSERT INTO `account` VALUES ('2', 'Accounts Receivable', '', '1', '2', '120', '0', '0', null, null);
INSERT INTO `account` VALUES ('3', 'Allowance for Bad Debts', null, '1', '3', '130', '0', '0', null, null);
INSERT INTO `account` VALUES ('4', 'Merchandise Inventory', '', '1', '4', '140', '0', '0', null, null);
INSERT INTO `account` VALUES ('5', 'Supplies', '', '1', '5', '150', '0', '0', null, null);
INSERT INTO `account` VALUES ('6', 'Prepaid Insurance', '', '1', '6', '160', '0', '0', null, null);
INSERT INTO `account` VALUES ('7', 'Land', '', '2', '7', '170', '0', '0', null, null);
INSERT INTO `account` VALUES ('8', 'Buildings', '', '2', '8', '175', '0', '0', null, null);
INSERT INTO `account` VALUES ('9', 'Accumulated Depreciation - Buildings', '', '2', '9', '178', '0', '0', null, null);
INSERT INTO `account` VALUES ('10', 'Equipment', '', '2', '10', '180', '0', '0', null, null);
INSERT INTO `account` VALUES ('11', 'Accumulated Depreciation - Equipment', '', '2', '11', '188', '0', '0', null, null);
INSERT INTO `account` VALUES ('12', 'Notes Payable', '', '3', '12', '210', '0', '0', null, null);
INSERT INTO `account` VALUES ('13', 'Accounts Payable', '', '3', '13', '215', '0', '0', null, null);
INSERT INTO `account` VALUES ('14', 'Wages Payable', '', '3', '14', '220', '0', '0', null, null);
INSERT INTO `account` VALUES ('15', 'Interest Payable', '', '3', '15', '230', '0', '0', null, null);
INSERT INTO `account` VALUES ('16', 'Unearned Revenues', '', '3', '16', '240', '0', '0', null, null);
INSERT INTO `account` VALUES ('17', 'Mortgage Loan Payable', '', '4', '17', '250', '0', '0', null, null);
INSERT INTO `account` VALUES ('18', 'Owners Capital', '', '5', '18', '290', '0', '0', null, null);
INSERT INTO `account` VALUES ('19', 'Owners Drawing', '', '5', '19', '295', '0', '0', null, null);
INSERT INTO `account` VALUES ('20', 'Service Revenues', '', '6', '20', '310', '0', '0', null, null);
INSERT INTO `account` VALUES ('21', 'Sales Revenues', '', '6', '21', '320', '0', '0', null, null);
INSERT INTO `account` VALUES ('22', 'Salaries Expense', '', '7', '22', '500', '0', '0', null, null);
INSERT INTO `account` VALUES ('23', 'Wages Expense', '', '7', '23', '510', '0', '0', null, null);
INSERT INTO `account` VALUES ('24', 'Supplies Expense', '', '7', '24', '520', '0', '0', null, null);
INSERT INTO `account` VALUES ('25', 'Rent Expense', '', '7', '25', '530', '0', '0', null, null);
INSERT INTO `account` VALUES ('26', 'Utilities Expense', '', '7', '26', '540', '0', '0', null, null);
INSERT INTO `account` VALUES ('27', 'Postage and Communications', '', '7', '27', '550', '0', '0', null, null);
INSERT INTO `account` VALUES ('28', 'Advertising Expense', '', '7', '28', '560', '0', '0', null, null);
INSERT INTO `account` VALUES ('29', 'Depreciation Expense', '', '7', '29', '570', '0', '0', null, null);
INSERT INTO `account` VALUES ('30', 'Bad Debts Expense', '', '7', '30', '580', '0', '0', null, null);
INSERT INTO `account` VALUES ('31', 'Purchases', '', '7', '31', '590', '0', '0', null, null);
INSERT INTO `account` VALUES ('32', 'Cost of Sales/Services', '', '8', '32', '610', '0', '0', null, null);
INSERT INTO `account` VALUES ('33', 'Interest Revenues', '', '9', '33', '710', '0', '0', null, null);
INSERT INTO `account` VALUES ('34', 'Gain on Sale of Assets', '', '9', '34', '720', '0', '0', null, null);
INSERT INTO `account` VALUES ('35', 'Loss on Sale of Assets', '', '10', '35', '810', '0', '0', null, null);
INSERT INTO `account_category_type` VALUES ('1', 'Current Asset', '0', null, null);
INSERT INTO `account_category_type` VALUES ('2', 'Non-Current Asset', '0', null, null);
INSERT INTO `account_category_type` VALUES ('3', 'Current Liability', '0', null, null);
INSERT INTO `account_category_type` VALUES ('4', 'Non-Current Liability', '0', null, null);
INSERT INTO `account_category_type` VALUES ('5', 'Owner\'s Equity', '0', null, null);
INSERT INTO `account_category_type` VALUES ('6', 'Operating Revenue', '0', null, null);
INSERT INTO `account_category_type` VALUES ('7', 'Operating Expense', '0', null, null);
INSERT INTO `account_category_type` VALUES ('8', 'Cost of Sales/Services', '0', null, null);
INSERT INTO `account_category_type` VALUES ('9', 'Non-Operating Revenues and Gains', '0', null, null);
INSERT INTO `account_category_type` VALUES ('10', 'Non-Operating Expenses and Loss', '0', null, null);
INSERT INTO `account_detail_type` VALUES ('1', '1', 'Cash', null, '0', null, null);
INSERT INTO `account_detail_type` VALUES ('2', '1', 'Accounts Receivable', null, '0', null, null);
INSERT INTO `account_detail_type` VALUES ('3', '1', 'Allowance for Bad Debts', null, '0', null, null);
INSERT INTO `account_detail_type` VALUES ('4', '1', 'Merchandise Inventory', null, '0', null, null);
INSERT INTO `account_detail_type` VALUES ('5', '1', 'Supplies', null, '0', null, null);
INSERT INTO `account_detail_type` VALUES ('6', '1', 'Prepaid Insurance', null, '0', null, null);
INSERT INTO `account_detail_type` VALUES ('7', '2', 'Land', null, '0', null, null);
INSERT INTO `account_detail_type` VALUES ('8', '2', 'Buildings', null, '0', null, null);
INSERT INTO `account_detail_type` VALUES ('9', '2', 'Accumulated Depreciation - Buildings', null, '0', null, null);
INSERT INTO `account_detail_type` VALUES ('10', '2', 'Equipment', null, '0', null, null);
INSERT INTO `account_detail_type` VALUES ('11', '2', 'Accumulated Depreciation - Equipment', null, '0', null, null);
INSERT INTO `account_detail_type` VALUES ('12', '3', 'Notes Payable', null, '0', null, null);
INSERT INTO `account_detail_type` VALUES ('13', '3', 'Accounts Payable', null, '0', null, null);
INSERT INTO `account_detail_type` VALUES ('14', '3', 'Wages Payable', null, '0', null, null);
INSERT INTO `account_detail_type` VALUES ('15', '3', 'Interest Payable', null, '0', null, null);
INSERT INTO `account_detail_type` VALUES ('16', '3', 'Unearned Revenues', null, '0', null, null);
INSERT INTO `account_detail_type` VALUES ('17', '4', 'Mortgage Loan Payable', null, '0', null, null);
INSERT INTO `account_detail_type` VALUES ('18', '5', 'Owners Capital', null, '0', null, null);
INSERT INTO `account_detail_type` VALUES ('19', '5', 'Owners Drawing', null, '0', null, null);
INSERT INTO `account_detail_type` VALUES ('20', '6', 'Service Revenues', null, '0', null, null);
INSERT INTO `account_detail_type` VALUES ('21', '6', 'Sales Revenues', null, '0', null, null);
INSERT INTO `account_detail_type` VALUES ('22', '7', 'Salaries Expense', null, '0', null, null);
INSERT INTO `account_detail_type` VALUES ('23', '7', 'Wages Expense', null, '0', null, null);
INSERT INTO `account_detail_type` VALUES ('24', '7', 'Supplies Expense', null, '0', null, null);
INSERT INTO `account_detail_type` VALUES ('25', '7', 'Rent Expense', null, '0', null, null);
INSERT INTO `account_detail_type` VALUES ('26', '7', 'Utilities Expense', null, '0', null, null);
INSERT INTO `account_detail_type` VALUES ('27', '7', 'Postage and Communications', null, '0', null, null);
INSERT INTO `account_detail_type` VALUES ('28', '7', 'Advertising Expense', null, '0', null, null);
INSERT INTO `account_detail_type` VALUES ('29', '7', 'Depreciation Expense', null, '0', null, null);
INSERT INTO `account_detail_type` VALUES ('30', '7', 'Bad Debts Expense', null, '0', null, null);
INSERT INTO `account_detail_type` VALUES ('31', '7', 'Purchases', null, '0', null, null);
INSERT INTO `account_detail_type` VALUES ('32', '8', 'Cost of Sales/Services', null, '0', null, null);
INSERT INTO `account_detail_type` VALUES ('33', '9', 'Interest Revenues', null, '0', null, null);
INSERT INTO `account_detail_type` VALUES ('34', '9', 'Gain on Sale of Assets', null, '0', null, null);
INSERT INTO `account_detail_type` VALUES ('35', '10', 'Loss on Sale of Assets', null, '0', null, null);
INSERT INTO `migrations` VALUES ('1', '2017_04_08_122015_create_customer_table', '1');
INSERT INTO `migrations` VALUES ('2', '2017_04_08_122111_create_product_category_table', '1');
INSERT INTO `migrations` VALUES ('3', '2017_04_08_122155_create_account_category_type_table', '1');
INSERT INTO `migrations` VALUES ('4', '2017_04_08_122207_create_expenses_table', '1');
INSERT INTO `migrations` VALUES ('5', '2017_04_08_122218_create_expens_table', '1');
INSERT INTO `migrations` VALUES ('6', '2017_04_08_122226_create_sale_table', '1');
INSERT INTO `migrations` VALUES ('7', '2017_04_08_122247_create_product_service_table', '1');
INSERT INTO `migrations` VALUES ('8', '2017_04_08_122319_create_account_detail_types_table', '1');
INSERT INTO `migrations` VALUES ('9', '2017_04_08_122524_create_sales_receipt_table', '1');
INSERT INTO `migrations` VALUES ('10', '2017_04_08_122541_create_invoice_table', '1');
INSERT INTO `migrations` VALUES ('11', '2017_04_08_122600_create_expense_item_table', '1');
INSERT INTO `migrations` VALUES ('12', '2017_04_08_122620_create_account_table', '1');
INSERT INTO `migrations` VALUES ('13', '2017_04_08_122638_create_sales_receipt_item_table', '1');
INSERT INTO `migrations` VALUES ('14', '2017_04_08_122701_create_invoice_item_table', '1');
INSERT INTO `migrations` VALUES ('15', '2017_04_08_122713_create_payment_table', '1');
INSERT INTO `migrations` VALUES ('16', '2017_04_08_122723_create_expense_account_table', '1');
INSERT INTO `migrations` VALUES ('17', '2017_04_08_122747_create_map_invoice_payments_table', '1');
INSERT INTO `migrations` VALUES ('18', '2017_04_08_122803_create_supplier_table', '1');
INSERT INTO `migrations` VALUES ('19', '2017_04_08_122821_create_attachments_table', '1');
INSERT INTO `migrations` VALUES ('20', '2017_04_08_122846_create_company_profile_table', '1');
INSERT INTO `migrations` VALUES ('21', '2017_04_08_122934_create_user_profiles_table', '1');
INSERT INTO `migrations` VALUES ('22', '2017_04_09_190340_create_map_sales_attachment_table', '1');
INSERT INTO `migrations` VALUES ('23', '2017_04_09_190358_create_map_expense_attachment_table', '1');
INSERT INTO `migrations` VALUES ('24', '2017_04_11_194405_create_bills_table', '1');
INSERT INTO `migrations` VALUES ('25', '2017_04_11_194423_create_bill_accounts_table', '1');
INSERT INTO `migrations` VALUES ('26', '2017_04_11_194436_create_bill_items_table', '1');
INSERT INTO `migrations` VALUES ('27', '2017_04_11_194452_create_bill_payments_table', '1');
INSERT INTO `migrations` VALUES ('28', '2017_04_14_183608_create_map_bill_bill_payment', '1');
INSERT INTO `user_profile` VALUES ('1', 'Test User', 'testuser@email.com', null, null, null, '$2y$10$h4IQxyxvkpn8olQourEj4.mfm9SijciXmDZ94gI6fojirlRJ8A97.', null, null, null, '0', '2017-04-23 21:48:39', '2017-04-23 21:48:39');
