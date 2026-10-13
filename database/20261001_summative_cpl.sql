-- Summative CPL feature (per-school toggle + per-term Summative 1/2 encoding)
-- Run this file once against the production database after taking a backup.
-- It is safe to re-run: columns are only added when they do not exist yet.
-- The application also applies this automatically on first use; this file is
-- provided for controlled deployments.

START TRANSACTION;

CREATE TABLE IF NOT EXISTS app_schema_migrations (
    migration VARCHAR(190) NOT NULL,
    applied_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (migration)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Per-school feature flag, default OFF. Only the admin (sa) account toggles it.
SET @has_school_flag := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'schools'
      AND COLUMN_NAME = 'cpl_summative_enabled'
);
SET @add_school_flag := IF(
    @has_school_flag = 0,
    'ALTER TABLE schools ADD cpl_summative_enabled TINYINT(1) NOT NULL DEFAULT 0',
    'SELECT 1'
);
PREPARE school_flag_statement FROM @add_school_flag;
EXECUTE school_flag_statement;
DEALLOCATE PREPARE school_flag_statement;

-- Optional CPL values for Summative 1 and Summative 2 on each term record.
SET @has_cpl_s1 := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'learning_gap_records'
      AND COLUMN_NAME = 'cpl_summative_1'
);
SET @add_cpl_s1 := IF(
    @has_cpl_s1 = 0,
    'ALTER TABLE learning_gap_records ADD cpl_summative_1 DECIMAL(5,2) NULL AFTER class_proficiency_level',
    'SELECT 1'
);
PREPARE cpl_s1_statement FROM @add_cpl_s1;
EXECUTE cpl_s1_statement;
DEALLOCATE PREPARE cpl_s1_statement;

SET @has_cpl_s2 := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'learning_gap_records'
      AND COLUMN_NAME = 'cpl_summative_2'
);
SET @add_cpl_s2 := IF(
    @has_cpl_s2 = 0,
    'ALTER TABLE learning_gap_records ADD cpl_summative_2 DECIMAL(5,2) NULL AFTER cpl_summative_1',
    'SELECT 1'
);
PREPARE cpl_s2_statement FROM @add_cpl_s2;
EXECUTE cpl_s2_statement;
DEALLOCATE PREPARE cpl_s2_statement;

INSERT INTO app_schema_migrations (migration)
SELECT '20261001_summative_cpl'
WHERE NOT EXISTS (
    SELECT 1
    FROM app_schema_migrations
    WHERE migration = '20261001_summative_cpl'
);

COMMIT;

SELECT schoolID, schoolName, division_id, cpl_summative_enabled
FROM schools
ORDER BY cpl_summative_enabled DESC, schoolName
LIMIT 20;
