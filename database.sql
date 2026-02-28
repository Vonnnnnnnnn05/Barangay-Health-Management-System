-- ================================================================
-- BARANGAY HEALTH MANAGEMENT SYSTEM - DATABASE SCHEMA
-- Database Name: bs
-- ================================================================

CREATE DATABASE IF NOT EXISTS `bs` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `bs`;

-- ----------------------------------------------------------------
-- 1. USERS TABLE (Admin, BHW, Patient)
-- ----------------------------------------------------------------
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `first_name` VARCHAR(100) NOT NULL,
  `last_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin','health_worker','patient') NOT NULL DEFAULT 'patient',
  `phone` VARCHAR(20) DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `gender` ENUM('Male','Female','Other') DEFAULT NULL,
  `date_of_birth` DATE DEFAULT NULL,
  `profile_picture` VARCHAR(255) DEFAULT 'default.png',
  `status` ENUM('active','inactive') DEFAULT 'active',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ----------------------------------------------------------------
-- 2. PATIENT MEDICAL HISTORY
-- ----------------------------------------------------------------
CREATE TABLE `medical_history` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `patient_id` INT NOT NULL,
  `condition_name` VARCHAR(255) NOT NULL,
  `diagnosis_date` DATE DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  `recorded_by` INT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`patient_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`recorded_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ----------------------------------------------------------------
-- 3. TREATMENTS
-- ----------------------------------------------------------------
CREATE TABLE `treatments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `patient_id` INT NOT NULL,
  `treatment_name` VARCHAR(255) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `treatment_date` DATE NOT NULL,
  `recorded_by` INT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`patient_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`recorded_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ----------------------------------------------------------------
-- 4. APPOINTMENTS
-- ----------------------------------------------------------------
CREATE TABLE `appointments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `patient_id` INT NOT NULL,
  `health_worker_id` INT DEFAULT NULL,
  `appointment_date` DATE NOT NULL,
  `appointment_time` TIME NOT NULL,
  `purpose` VARCHAR(255) NOT NULL,
  `status` ENUM('pending','confirmed','completed','cancelled') DEFAULT 'pending',
  `notes` TEXT DEFAULT NULL,
  `email_sent` TINYINT(1) DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`patient_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`health_worker_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ----------------------------------------------------------------
-- 5. MEDICINES INVENTORY
-- ----------------------------------------------------------------
CREATE TABLE `medicines` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `quantity` INT NOT NULL DEFAULT 0,
  `unit` VARCHAR(50) DEFAULT 'pcs',
  `expiry_date` DATE DEFAULT NULL,
  `added_by` INT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`added_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ----------------------------------------------------------------
-- 6. DISPENSED MEDICINES
-- ----------------------------------------------------------------
CREATE TABLE `dispensed_medicines` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `patient_id` INT NOT NULL,
  `medicine_id` INT NOT NULL,
  `dosage` VARCHAR(100) NOT NULL,
  `frequency` VARCHAR(100) NOT NULL,
  `quantity_given` INT NOT NULL DEFAULT 1,
  `dispensed_date` DATE NOT NULL,
  `dispensed_by` INT DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`patient_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`medicine_id`) REFERENCES `medicines`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`dispensed_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ----------------------------------------------------------------
-- 7. IMMUNIZATION SCHEDULES
-- ----------------------------------------------------------------
CREATE TABLE `immunizations` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `patient_id` INT NOT NULL,
  `vaccine_name` VARCHAR(255) NOT NULL,
  `dose_number` INT DEFAULT 1,
  `scheduled_date` DATE NOT NULL,
  `administered_date` DATE DEFAULT NULL,
  `status` ENUM('scheduled','completed','missed') DEFAULT 'scheduled',
  `administered_by` INT DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`patient_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`administered_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ----------------------------------------------------------------
-- 8. CHAT MESSAGES
-- ----------------------------------------------------------------
CREATE TABLE `chat_messages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `sender_id` INT NOT NULL,
  `receiver_id` INT NOT NULL,
  `message` TEXT NOT NULL,
  `is_read` TINYINT(1) DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`sender_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`receiver_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ----------------------------------------------------------------
-- 9. FEEDBACK
-- ----------------------------------------------------------------
CREATE TABLE `feedback` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `subject` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `rating` INT DEFAULT NULL CHECK (`rating` BETWEEN 1 AND 5),
  `status` ENUM('pending','reviewed') DEFAULT 'pending',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ----------------------------------------------------------------
-- 10. SYSTEM SETTINGS (optional)
-- ----------------------------------------------------------------
CREATE TABLE `settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(100) NOT NULL UNIQUE,
  `setting_value` TEXT DEFAULT NULL,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ----------------------------------------------------------------
-- DEFAULT DATA
-- ----------------------------------------------------------------

-- Default Admin Account (password: admin123)
INSERT INTO `users` (`first_name`, `last_name`, `email`, `password`, `role`, `phone`, `address`, `gender`, `date_of_birth`)
VALUES ('System', 'Administrator', 'admin@barangay.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '09171234567', 'Barangay Hall', 'Male', '1990-01-01');

-- Default Settings
INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('system_name', 'Barangay Health Management System'),
('barangay_name', 'Barangay Health Center'),
('contact_email', 'admin@barangay.com'),
('contact_phone', '09171234567');
