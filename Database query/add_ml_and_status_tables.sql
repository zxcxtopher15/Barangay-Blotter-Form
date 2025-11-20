-- Add status field to complaints table for tracking PNP endorsement
ALTER TABLE `complaints`
ADD COLUMN `status` ENUM('Pending', 'Under Investigation', 'Mediation', 'Settled', 'Endorsed to PNP', 'Closed') DEFAULT 'Pending' AFTER `desk_officer_name`,
ADD COLUMN `ml_classification` ENUM('ENDORSE_TO_PNP', 'BARANGAY_ACTION', 'MANUAL') DEFAULT NULL AFTER `status`,
ADD COLUMN `ml_confidence` DECIMAL(3,2) DEFAULT NULL AFTER `ml_classification`,
ADD COLUMN `ml_reasoning` TEXT DEFAULT NULL AFTER `ml_confidence`,
ADD COLUMN `endorsed_date` DATETIME DEFAULT NULL AFTER `ml_reasoning`,
ADD COLUMN `endorsed_by` VARCHAR(100) DEFAULT NULL AFTER `endorsed_date`;

-- Create table for ML classification logs and dataset
CREATE TABLE IF NOT EXISTS `ml_classifications` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `case_no` INT(11) DEFAULT NULL,
  `complaint_description` VARCHAR(100) NOT NULL,
  `complaint_statement` TEXT NOT NULL,
  `classification` ENUM('ENDORSE_TO_PNP', 'BARANGAY_ACTION') NOT NULL,
  `confidence` DECIMAL(3,2) NOT NULL,
  `reasoning` TEXT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_case_no` (`case_no`),
  INDEX `idx_classification` (`classification`),
  INDEX `idx_created_at` (`created_at`),
  FOREIGN KEY (`case_no`) REFERENCES `complaints`(`case_no`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create table for email logs (PNP endorsement emails)
CREATE TABLE IF NOT EXISTS `email_logs` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `case_no` INT(11) NOT NULL,
  `recipient_email` VARCHAR(255) NOT NULL,
  `subject` VARCHAR(255) NOT NULL,
  `status` ENUM('sent', 'failed', 'pending') DEFAULT 'pending',
  `sent_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `sent_by` VARCHAR(100) DEFAULT NULL,
  `error_message` TEXT DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`case_no`) REFERENCES `complaints`(`case_no`) ON DELETE CASCADE,
  INDEX `idx_case_no` (`case_no`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
