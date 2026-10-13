-- AP-LEAD Region XI division accounts
-- Run this file once against the production database after taking a backup.
-- It is safe to re-run: an existing username or email is not inserted again.

START TRANSACTION;

SET @has_must_change_password := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'users'
      AND COLUMN_NAME = 'must_change_password'
);
SET @add_must_change_password := IF(
    @has_must_change_password = 0,
    'ALTER TABLE users ADD must_change_password TINYINT(1) NOT NULL DEFAULT 0 AFTER password',
    'SELECT 1'
);
PREPARE password_schema_statement FROM @add_must_change_password;
EXECUTE password_schema_statement;
DEALLOCATE PREPARE password_schema_statement;

CREATE TABLE IF NOT EXISTS app_schema_migrations (
    migration VARCHAR(190) NOT NULL,
    applied_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (migration)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO app_schema_migrations (migration)
SELECT '20260922_users_must_change_password'
WHERE NOT EXISTS (
    SELECT 1
    FROM app_schema_migrations
    WHERE migration = '20260922_users_must_change_password'
);

-- Temporary password: rodel.pagayon
INSERT INTO users (username, password, must_change_password, position, fname, mname, lname, gender, image, stat, sec, r_id, p_id, d_id, virified, email)
SELECT 'rodel.pagayon@deped.gov.ph', '$2y$12$ac2IBgikUkhHH4U5WyD/teEauSyjQAPXFglsrlFqzr5/eVVGQRelG', 1, 'division', 'Rodel', 'L.', 'Pagayon', 0, 'r11-logo.jpg', 0, 0, 12, 105, NULL, 0, 'rodel.pagayon@deped.gov.ph'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE LOWER(username) = 'rodel.pagayon@deped.gov.ph' OR LOWER(email) = 'rodel.pagayon@deped.gov.ph');

-- Temporary password: gracesanta.daclan
INSERT INTO users (username, password, must_change_password, position, fname, mname, lname, gender, image, stat, sec, r_id, p_id, d_id, virified, email)
SELECT 'gracesanta.daclan@deped.gov.ph', '$2y$12$.jqdqEYqbV6eG68wJKza0u0dOdBVJjEV6aAgmdaVlleDEU58MpruK', 1, 'division', 'Grace Santa', 'T.', 'Daclan', 0, 'r11-logo.jpg', 0, 0, 12, 58, NULL, 0, 'gracesanta.daclan@deped.gov.ph'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE LOWER(username) = 'gracesanta.daclan@deped.gov.ph' OR LOWER(email) = 'gracesanta.daclan@deped.gov.ph');

-- Temporary password: jonathan.araneta002
INSERT INTO users (username, password, must_change_password, position, fname, mname, lname, gender, image, stat, sec, r_id, p_id, d_id, virified, email)
SELECT 'jonathan.araneta002@deped.gov.ph', '$2y$12$mDSV5X.l2n9j/l0X4bO3v.E46QHi4.2fcQ8Lj4TwK3yHg1SY665BK', 1, 'division', 'Jonathan', 'C.', 'Araneta', 0, 'r11-logo.jpg', 0, 0, 12, 60, NULL, 0, 'jonathan.araneta002@deped.gov.ph'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE LOWER(username) = 'jonathan.araneta002@deped.gov.ph' OR LOWER(email) = 'jonathan.araneta002@deped.gov.ph');

-- Temporary password: grace.pontillas
INSERT INTO users (username, password, must_change_password, position, fname, mname, lname, gender, image, stat, sec, r_id, p_id, d_id, virified, email)
SELECT 'grace.pontillas@deped.gov.ph', '$2y$12$bSGb4bnneESJMTGxxoTAneQlQoJTqGfboz76g79I0WciEBv7wKJAG', 1, 'division', 'Grace', 'D.', 'Pontillas', 0, 'r11-logo.jpg', 0, 0, 12, 57, NULL, 0, 'grace.pontillas@deped.gov.ph'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE LOWER(username) = 'grace.pontillas@deped.gov.ph' OR LOWER(email) = 'grace.pontillas@deped.gov.ph');

-- Temporary password: leonoraliza.dacillo
INSERT INTO users (username, password, must_change_password, position, fname, mname, lname, gender, image, stat, sec, r_id, p_id, d_id, virified, email)
SELECT 'leonoraliza.dacillo@deped.gov.ph', '$2y$12$KmbKJUYPdw2b7/GnKlgAP.H6vKzSBKHBhfuhRLPRgv75cl7yU2ZaS', 1, 'division', 'Leonora Liza', 'D.', 'Dacillo', 0, 'r11-logo.jpg', 0, 0, 12, 59, NULL, 0, 'leonoraliza.dacillo@deped.gov.ph'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE LOWER(username) = 'leonoraliza.dacillo@deped.gov.ph' OR LOWER(email) = 'leonoraliza.dacillo@deped.gov.ph');

-- Temporary password: alan.limbadan001
INSERT INTO users (username, password, must_change_password, position, fname, mname, lname, gender, image, stat, sec, r_id, p_id, d_id, virified, email)
SELECT 'alan.limbadan001@deped.gov.ph', '$2y$12$9Yzq.CSpi.qKvxKIgaX8Y.TjFJxS81F9ZSlZSfJQqu/lGU9Ah7gU6', 1, 'division', 'Alan', 'D.', 'Limbadan', 0, 'r11-logo.jpg', 0, 0, 12, 61, NULL, 0, 'alan.limbadan001@deped.gov.ph'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE LOWER(username) = 'alan.limbadan001@deped.gov.ph' OR LOWER(email) = 'alan.limbadan001@deped.gov.ph');

-- Temporary password: john.visillas
INSERT INTO users (username, password, must_change_password, position, fname, mname, lname, gender, image, stat, sec, r_id, p_id, d_id, virified, email)
SELECT 'john.visillas@deped.gov.ph', '$2y$12$VW9o7RYTteeJ3gouDHMX1OHiDovAfY4et7WJOha7FY/apGzR6vYYC', 1, 'division', 'John', '', 'Visillas', 0, 'r11-logo.jpg', 0, 0, 12, 108, NULL, 0, 'john.visillas@deped.gov.ph'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE LOWER(username) = 'john.visillas@deped.gov.ph' OR LOWER(email) = 'john.visillas@deped.gov.ph');

-- Temporary password: marilyn.pajaro
INSERT INTO users (username, password, must_change_password, position, fname, mname, lname, gender, image, stat, sec, r_id, p_id, d_id, virified, email)
SELECT 'marilyn.pajaro@deped.gov.ph', '$2y$12$sblGgd2PA3Mfj4sD1SBRjeMa2Xiq29xqE6MOIksGfnceTFsUh26QC', 1, 'division', 'Marilyn', 'G.', 'Pajaro', 0, 'r11-logo.jpg', 0, 0, 12, 103, NULL, 0, 'marilyn.pajaro@deped.gov.ph'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE LOWER(username) = 'marilyn.pajaro@deped.gov.ph' OR LOWER(email) = 'marilyn.pajaro@deped.gov.ph');

-- Temporary password: leila.ibita001
INSERT INTO users (username, password, must_change_password, position, fname, mname, lname, gender, image, stat, sec, r_id, p_id, d_id, virified, email)
SELECT 'leila.ibita001@deped.gov.ph', '$2y$12$x5aRlJm2SwzegWLfw.YF.etew06gGPfRqtjO.OBwXK/iiEQwU0w/O', 1, 'division', 'Leila', 'L.', 'Ibita', 0, 'r11-logo.jpg', 0, 0, 12, 104, NULL, 0, 'leila.ibita001@deped.gov.ph'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE LOWER(username) = 'leila.ibita001@deped.gov.ph' OR LOWER(email) = 'leila.ibita001@deped.gov.ph');

-- Temporary password: marichu.celestial
INSERT INTO users (username, password, must_change_password, position, fname, mname, lname, gender, image, stat, sec, r_id, p_id, d_id, virified, email)
SELECT 'marichu.celestial@deped.gov.ph', '$2y$12$XpCEj4rzj34yrnAo17uLIOupgvFiW9SxB7ADt2NEAA69AVB6KYfiW', 1, 'division', 'Marichu', 'M.', 'Celestial', 0, 'r11-logo.jpg', 0, 0, 12, 106, NULL, 0, 'marichu.celestial@deped.gov.ph'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE LOWER(username) = 'marichu.celestial@deped.gov.ph' OR LOWER(email) = 'marichu.celestial@deped.gov.ph');

COMMIT;

SELECT username, position, p_id, must_change_password
FROM users
WHERE username IN (
    'rodel.pagayon@deped.gov.ph',
    'gracesanta.daclan@deped.gov.ph',
    'jonathan.araneta002@deped.gov.ph',
    'grace.pontillas@deped.gov.ph',
    'leonoraliza.dacillo@deped.gov.ph',
    'alan.limbadan001@deped.gov.ph',
    'john.visillas@deped.gov.ph',
    'marilyn.pajaro@deped.gov.ph',
    'leila.ibita001@deped.gov.ph',
    'marichu.celestial@deped.gov.ph'
)
ORDER BY p_id;
