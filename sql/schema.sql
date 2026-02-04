
CREATE TABLE IF NOT EXISTS `suppliers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `cpf_cnpj` varchar(20) DEFAULT NULL,
  `pf_pj` enum('PF','PJ') NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `company_name` varchar(255) DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_suppliers_cpf_cnpj` (`cpf_cnpj`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
CREATE TABLE IF NOT EXISTS `tags` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
CREATE TABLE IF NOT EXISTS `expenses` (
  `id` int NOT NULL AUTO_INCREMENT,
  `supplier_id` int DEFAULT NULL,
  `date` date NOT NULL,
  `description` varchar(255) NOT NULL,
  `amount_nf` decimal(10,2) DEFAULT NULL,
  `amount_paid` decimal(10,2) NOT NULL,
  `additional_discount` decimal(10,2) DEFAULT NULL,
  `calendar_year` int NOT NULL,
  `is_confirmed` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_exp_supplier` (`supplier_id`),
  KEY `idx_expenses_is_confirmed` (`is_confirmed`),
  KEY `idx_expenses_calendar_year` (`calendar_year`),
  KEY `idx_expenses_date` (`date`),
  CONSTRAINT `fk_exp_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
CREATE TABLE IF NOT EXISTS `expense_tags` (
  `expense_id` int NOT NULL,
  `tag_id` int NOT NULL,
  PRIMARY KEY (`expense_id`,`tag_id`),
  KEY `fk_expense_tags_tag` (`tag_id`),
  CONSTRAINT `fk_expense_tags_expense` FOREIGN KEY (`expense_id`) REFERENCES `expenses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_expense_tags_tag` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


