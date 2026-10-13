-- The site key is public. Keep the secret key out of public database exports.
CREATE TABLE IF NOT EXISTS recaptcha_settings (
    id TINYINT UNSIGNED NOT NULL PRIMARY KEY,
    site_key VARCHAR(255) NOT NULL,
    secret_key VARCHAR(255) NOT NULL,
    expected_hostname VARCHAR(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- Configure row id=1 via your database administration tool, or let signup
-- import the existing server-side configuration once when no row exists.
