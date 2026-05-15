-- CV Builder Database Schema
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

USE `cv_builder`;

-- USERS
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `first_name` VARCHAR(100) DEFAULT NULL,
    `last_name` VARCHAR(100) DEFAULT NULL,
    `avatar` VARCHAR(255) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `last_login` TIMESTAMP NULL DEFAULT NULL,
    INDEX `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- CV TEMPLATES
CREATE TABLE IF NOT EXISTS `cv_templates` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `description` TEXT,
    `thumbnail` VARCHAR(255) DEFAULT NULL,
    `category` ENUM('professional','creative','minimal','academic','executive') DEFAULT 'professional',
    `html_structure` LONGTEXT NOT NULL,
    `css_styles` LONGTEXT NOT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `sort_order` INT UNSIGNED DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_slug` (`slug`),
    INDEX `idx_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- CVS
CREATE TABLE IF NOT EXISTS `cvs` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `template_id` INT UNSIGNED DEFAULT 1,
    `title` VARCHAR(255) DEFAULT 'Untitled CV',
    `is_public` TINYINT(1) DEFAULT 0,
    `public_slug` VARCHAR(50) DEFAULT NULL UNIQUE,
    `custom_css` LONGTEXT,
    `custom_colors` JSON,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`template_id`) REFERENCES `cv_templates`(`id`) ON DELETE SET NULL,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_public_slug` (`public_slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- CV SECTIONS
CREATE TABLE IF NOT EXISTS `cv_sections` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `cv_id` INT UNSIGNED NOT NULL,
    `section_type` ENUM('personal','summary','experience','education','skills','languages','certifications','references','projects','volunteer','awards','custom') NOT NULL,
    `section_order` INT UNSIGNED DEFAULT 0,
    `title` VARCHAR(255) DEFAULT NULL,
    `content_json` JSON,
    `html_content` LONGTEXT,
    `is_visible` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`cv_id`) REFERENCES `cvs`(`id`) ON DELETE CASCADE,
    INDEX `idx_cv_section` (`cv_id`, `section_type`),
    INDEX `idx_section_order` (`cv_id`, `section_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- INDUSTRIES
CREATE TABLE IF NOT EXISTS `industries` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `icon` VARCHAR(50) DEFAULT NULL,
    `sort_order` INT UNSIGNED DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- PHRASE LIBRARY
CREATE TABLE IF NOT EXISTS `phrase_library` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `industry_id` INT UNSIGNED DEFAULT NULL,
    `category` ENUM('summary','experience','skills','achievement','objective','interests') NOT NULL,
    `subcategory` VARCHAR(100) DEFAULT NULL,
    `phrase_text` TEXT NOT NULL,
    `phrase_type` ENUM('bullet','paragraph','keyword','heading') DEFAULT 'bullet',
    `tags` VARCHAR(500) DEFAULT NULL,
    `usage_count` INT UNSIGNED DEFAULT 0,
    `is_featured` TINYINT(1) DEFAULT 0,
    INDEX `idx_industry` (`industry_id`),
    INDEX `idx_category` (`category`),
    INDEX `idx_tags` (`tags`(255)),
    FULLTEXT INDEX `ft_phrase` (`phrase_text`),
    FOREIGN KEY (`industry_id`) REFERENCES `industries`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- USER SAVED PHRASES
CREATE TABLE IF NOT EXISTS `user_saved_phrases` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `phrase_id` INT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_user_phrase` (`user_id`, `phrase_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`phrase_id`) REFERENCES `phrase_library`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TEMPLATE CUSTOMIZATIONS
CREATE TABLE IF NOT EXISTS `template_customizations` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `template_id` INT UNSIGNED NOT NULL,
    `colors` JSON,
    `fonts` JSON,
    `spacing` JSON,
    `section_visibility` JSON,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_user_template` (`user_id`, `template_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`template_id`) REFERENCES `cv_templates`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- IMPORT HISTORY
CREATE TABLE IF NOT EXISTS `import_history` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `cv_id` INT UNSIGNED DEFAULT NULL,
    `file_name` VARCHAR(255) NOT NULL,
    `file_type` ENUM('pdf','docx') NOT NULL,
    `file_size` INT UNSIGNED DEFAULT NULL,
    `status` ENUM('pending','success','failed') DEFAULT 'pending',
    `parsed_data` JSON,
    `error_message` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`cv_id`) REFERENCES `cvs`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
