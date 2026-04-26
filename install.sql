CREATE DATABASE IF NOT EXISTS `smartsupplysolutions` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `smartsupplysolutions`;

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(190) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `is_admin` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_users_username` (`username`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `daily_visits` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `visit_date` DATE NOT NULL,
  `follow_up_date` DATE NULL,
  `follow_up_status` VARCHAR(50) NULL,
  `follow_up_done_at` DATETIME NULL,
  `follow_up_action_note` TEXT NULL,
  `area` VARCHAR(190) NOT NULL,
  `address` VARCHAR(255) NULL,
  `clinic_name` VARCHAR(255) NOT NULL,
  `visit_number` VARCHAR(50) NOT NULL,
  `person_name` VARCHAR(255) NOT NULL,
  `job_title` VARCHAR(190) NULL,
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

CREATE TABLE IF NOT EXISTS `daily_visit_contacts` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `daily_visit_id` BIGINT UNSIGNED NOT NULL,
  `person_name` VARCHAR(255) NOT NULL,
  `job_title` VARCHAR(190) NULL,
  `mobile` VARCHAR(50) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_daily_visit_contacts_visit_id` (`daily_visit_id`),
  CONSTRAINT `fk_daily_visit_contacts_visit_id` FOREIGN KEY (`daily_visit_id`) REFERENCES `daily_visits` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `reminders` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `daily_visit_id` BIGINT UNSIGNED NOT NULL,
  `follow_up_date` DATE NOT NULL,
  `status` VARCHAR(50) NOT NULL DEFAULT 'next',
  `done_at` DATETIME NULL,
  `action_note` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_reminders_follow_up_date` (`follow_up_date`),
  KEY `idx_reminders_status` (`status`),
  KEY `idx_reminders_daily_visit_id` (`daily_visit_id`),
  CONSTRAINT `fk_reminders_daily_visit_id` FOREIGN KEY (`daily_visit_id`) REFERENCES `daily_visits` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

INSERT INTO `users` (`username`, `password_hash`, `is_admin`)
VALUES ('admin', '$2y$10$pvkw62KQArrjPKMfqeUxueZpq3vVfY1Hs6bmZxRIaQxxj0n5MR.a6', 1)
ON DUPLICATE KEY UPDATE `username` = `username`, `is_admin` = 1;
