-- SQL Update Script for Complaints Table
-- Run this to add missing columns needed for the new features

USE u416486854_p1;

-- Add case_no column (for Care # format)
ALTER TABLE `complaints`
ADD COLUMN `case_no` VARCHAR(50) GENERATED ALWAYS AS
  (CONCAT('BRY-', YEAR(incident_datetime), '-', LPAD(complaint_no, 5, '0'))) STORED
AFTER `complaint_no`;

-- Add other_complaint column (for "Others" complaint type)
ALTER TABLE `complaints`
ADD COLUMN `other_complaint` TEXT NULL
AFTER `complaint_description`;

-- Update complaint_statement to TEXT type (from VARCHAR(250))
ALTER TABLE `complaints`
MODIFY COLUMN `complaint_statement` TEXT NOT NULL;

-- Add incident coordinates for map display
ALTER TABLE `complaints`
ADD COLUMN `incident_latitude` DECIMAL(10, 8) NULL
AFTER `incident_location`,
ADD COLUMN `incident_longitude` DECIMAL(11, 8) NULL
AFTER `incident_latitude`;

-- Add index for better performance on case_no lookups
ALTER TABLE `complaints`
ADD INDEX `idx_case_no` (`case_no`);

-- Do the same for reports_archive table if it exists
ALTER TABLE `reports_archive`
ADD COLUMN `case_no` VARCHAR(50) GENERATED ALWAYS AS
  (CONCAT('BRY-', YEAR(incident_datetime), '-', LPAD(complaint_no, 5, '0'))) STORED
AFTER `complaint_no`;

ALTER TABLE `reports_archive`
ADD COLUMN `other_complaint` TEXT NULL
AFTER `complaint_description`;

ALTER TABLE `reports_archive`
MODIFY COLUMN `complaint_statement` TEXT NOT NULL;

ALTER TABLE `reports_archive`
ADD COLUMN `incident_latitude` DECIMAL(10, 8) NULL
AFTER `incident_location`,
ADD COLUMN `incident_longitude` DECIMAL(11, 8) NULL
AFTER `incident_latitude`;

ALTER TABLE `reports_archive`
ADD INDEX `idx_case_no` (`case_no`);
