CREATE DATABASE IF NOT EXISTS `smartsupplysolutions` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `smartsupplysolutions`;

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(190) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_users_username` (`username`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `daily_visits` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `visit_date` DATE NOT NULL,
  `follow_up_date` DATE NULL,
  `area` VARCHAR(190) NOT NULL,
  `clinic_name` VARCHAR(255) NOT NULL,
  `visit_number` VARCHAR(20) NOT NULL,
  `person_name` VARCHAR(255) NOT NULL,
  `job_title` VARCHAR(190) NOT NULL,
  `mobile` VARCHAR(50) NULL,
  `interest` VARCHAR(50) NOT NULL,
  `visit_type` VARCHAR(190) NOT NULL,
  `visit_result` VARCHAR(190) NOT NULL,
  `execution_status` VARCHAR(50) NOT NULL,
  `notes` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_daily_visits_visit_date` (`visit_date`),
  KEY `idx_daily_visits_user_id` (`user_id`),
  CONSTRAINT `fk_daily_visits_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

INSERT INTO `users` (`username`, `password_hash`)
VALUES ('admin', '$2y$10$pvkw62KQArrjPKMfqeUxueZpq3vVfY1Hs6bmZxRIaQxxj0n5MR.a6')
ON DUPLICATE KEY UPDATE `username` = `username`;
