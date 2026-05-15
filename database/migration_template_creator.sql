USE `cv_builder`;

ALTER TABLE `cv_templates`
    ADD COLUMN `is_custom` TINYINT(1) DEFAULT 0 AFTER `is_active`,
    ADD COLUMN `user_id` INT UNSIGNED DEFAULT NULL AFTER `is_custom`,
    ADD COLUMN `base_template_id` INT UNSIGNED DEFAULT NULL AFTER `user_id`,
    ADD COLUMN `global_styles` JSON AFTER `css_styles`;

CREATE TABLE IF NOT EXISTS `template_blocks` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `template_id` INT UNSIGNED NOT NULL,
    `block_type` ENUM('header','sidebar','section','text','image','divider','spacer','columns','grid','personal-field','contact-line','skill-bar','entry-loop') NOT NULL,
    `parent_id` INT UNSIGNED DEFAULT NULL,
    `block_order` INT UNSIGNED DEFAULT 0,
    `config_json` JSON,
    `css_overrides` JSON,
    `is_visible` TINYINT(1) DEFAULT 1,
    FOREIGN KEY (`template_id`) REFERENCES `cv_templates`(`id`) ON DELETE CASCADE,
    INDEX `idx_template_order` (`template_id`, `block_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `template_graphics` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `template_id` INT UNSIGNED DEFAULT NULL,
    `user_id` INT UNSIGNED DEFAULT NULL,
    `file_name` VARCHAR(255) NOT NULL,
    `file_path` VARCHAR(500) NOT NULL,
    `file_type` ENUM('svg','png','jpg','webp') NOT NULL,
    `file_size` INT UNSIGNED,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`template_id`) REFERENCES `cv_templates`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
