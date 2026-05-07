-- =====================================================
-- 狐蒂云维权系统 - 数据库结构
-- 数据库名: root
-- =====================================================

USE `root`;

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `nickname` VARCHAR(50) NOT NULL UNIQUE COMMENT '昵称(支持中文)',
    `password_hash` VARCHAR(255) NOT NULL COMMENT 'bcrypt加密密码',
    `server_ip` VARCHAR(45) DEFAULT NULL,
    `order_number` VARCHAR(100) DEFAULT NULL,
    `purchase_duration` VARCHAR(50) DEFAULT NULL,
    `remaining_time` VARCHAR(50) DEFAULT NULL,
    `refund_amount` DECIMAL(10,2) DEFAULT 0.00,
    `image_path` VARCHAR(255) DEFAULT NULL,
    `remark` TEXT DEFAULT NULL,
    `status` TINYINT NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_status` (`status`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `user_images`;
DROP TABLE IF EXISTS `admin_users`;
