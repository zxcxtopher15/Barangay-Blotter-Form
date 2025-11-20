-- Add DOB (Date of Birth) fields to complaints table
-- This will allow automatic age calculation based on DOB

ALTER TABLE `complaints`
ADD COLUMN `complainant_dob` DATE DEFAULT NULL AFTER `complainant_last_name`,
ADD COLUMN `victim_dob` DATE DEFAULT NULL AFTER `victim_last_name`,
ADD COLUMN `witness_dob` DATE DEFAULT NULL AFTER `witness_last_name`,
ADD COLUMN `respondent_dob` DATE DEFAULT NULL AFTER `respondent_last_name`;

-- Add computed columns for age (MySQL 5.7.6+) or you can calculate age in PHP
-- Note: Age will be calculated as the difference in years from DOB to current date
-- These are virtual columns that are calculated on the fly

-- For MySQL 5.7.6 and above with generated columns support:
-- ALTER TABLE `complaints`
-- MODIFY COLUMN `complainant_age` INT GENERATED ALWAYS AS (TIMESTAMPDIFF(YEAR, complainant_dob, CURDATE())) VIRTUAL,
-- MODIFY COLUMN `victim_age` INT GENERATED ALWAYS AS (TIMESTAMPDIFF(YEAR, victim_dob, CURDATE())) VIRTUAL,
-- MODIFY COLUMN `witness_age` INT GENERATED ALWAYS AS (TIMESTAMPDIFF(YEAR, witness_dob, CURDATE())) VIRTUAL,
-- MODIFY COLUMN `respondent_age` INT GENERATED ALWAYS AS (TIMESTAMPDIFF(YEAR, respondent_dob, CURDATE())) VIRTUAL;

-- For compatibility with older MySQL versions, we'll keep age as INT and calculate it in PHP
-- This approach is more flexible and works with all MySQL versions
ALTER TABLE `complaints`
MODIFY COLUMN `complainant_age` INT DEFAULT NULL,
MODIFY COLUMN `victim_age` INT DEFAULT NULL,
MODIFY COLUMN `witness_age` INT DEFAULT NULL,
MODIFY COLUMN `respondent_age` INT DEFAULT NULL;
