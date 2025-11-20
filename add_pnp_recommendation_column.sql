-- Add pnp_recommendation column to complaints table
ALTER TABLE complaints
ADD COLUMN pnp_recommendation VARCHAR(50) DEFAULT 'BARANGAY_ACTION' AFTER complaint_description;

-- Add pnp_recommendation column to reports_archive table
ALTER TABLE reports_archive
ADD COLUMN pnp_recommendation VARCHAR(50) DEFAULT 'BARANGAY_ACTION' AFTER complaint_description;

-- Update existing records to set recommendation based on complaint_description
UPDATE complaints
SET pnp_recommendation = CASE
    WHEN complaint_description IN ('Murder', 'Homicide', 'Rape', 'Robbery', 'Theft', 'Physical Assault', 'Carnapping', 'Arson', 'Kidnapping', 'Hostage Taking', 'Drug-related', 'Illegal Gambling', 'Illegal Possession of Firearms', 'Violation of Special Laws')
    THEN 'PNP_ENDORSEMENT'
    ELSE 'BARANGAY_ACTION'
END
WHERE pnp_recommendation IS NULL OR pnp_recommendation = 'BARANGAY_ACTION';

UPDATE reports_archive
SET pnp_recommendation = CASE
    WHEN complaint_description IN ('Murder', 'Homicide', 'Rape', 'Robbery', 'Theft', 'Physical Assault', 'Carnapping', 'Arson', 'Kidnapping', 'Hostage Taking', 'Drug-related', 'Illegal Gambling', 'Illegal Possession of Firearms', 'Violation of Special Laws')
    THEN 'PNP_ENDORSEMENT'
    ELSE 'BARANGAY_ACTION'
END
WHERE pnp_recommendation IS NULL OR pnp_recommendation = 'BARANGAY_ACTION';
