-- College Complaint Management System
-- Database Schema & Initial Data
-- Version: 2.0 (BCA Academic Project)

CREATE DATABASE IF NOT EXISTS `college_grievance` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `college_grievance`;

-- -------------------------------------------------------------
-- Table structure for `students`
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `students` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `student_id` VARCHAR(30) NOT NULL UNIQUE,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `department` VARCHAR(100) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- Table structure for `admins`
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admins` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- Table structure for `complaints`
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `complaints` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `student_id` INT NOT NULL,
    `tracking_id` VARCHAR(30) NOT NULL UNIQUE,
    `title` VARCHAR(200) NOT NULL,
    `category` VARCHAR(100) NOT NULL,
    `description` TEXT NOT NULL,
    `attachment` VARCHAR(255) NULL,
    `status` VARCHAR(30) NOT NULL DEFAULT 'Pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_complaints_student` 
        FOREIGN KEY (`student_id`) 
        REFERENCES `students` (`id`) 
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- Default Admin Account
-- Username: admin
-- Password: admin123 (Bcrypt hash: $2y$10$rL5CsFpKatBXCzBdHZB8eegg/NsW0JkC5LcXCYa/y5dqKoQ9Qv8f.)
-- -------------------------------------------------------------
INSERT INTO `admins` (`username`, `password`)
VALUES ('admin', '$2y$10$rL5CsFpKatBXCzBdHZB8eegg/NsW0JkC5LcXCYa/y5dqKoQ9Qv8f.')
ON DUPLICATE KEY UPDATE `username` = `username`;
