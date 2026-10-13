# AP-LEAD production security requirements

The application now defaults to the `production` environment. A production
deployment must use HTTPS and provide secrets through the web-server or service
environment; do not put them in the repository or a web-accessible `.env` file.

Required environment variables:

- `APP_BASE_URL` — canonical HTTPS URL ending in `/`
- `APP_ENCRYPTION_KEY` — at least 32 cryptographically random bytes
- `DB_HOST`, `DB_USERNAME`, `DB_PASSWORD`, `DB_DATABASE`
- `MAIL_HOST`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_PORT`, `MAIL_CRYPTO`

For local HTTP-only development, set `CI_ENV=development`. Never use that value
on an internet-facing server.

School signup uses Google reCAPTCHA v3. Configure the site
key for both localhost and the production domain in Google's reCAPTCHA console.
PHP cURL and outbound HTTPS access to Google are required. Tokens are generated
on submission and checked server-side for action `school_signup` and score >= 0.5.

reCAPTCHA settings are stored in `recaptcha_settings`, row `id = 1`:
`site_key`, `secret_key`, and optional `expected_hostname`. Only the site key is
rendered in HTML. Keep the secret out of public exports and restrict database access.
The signup library creates this table if absent and imports the previous server
configuration once if the row is missing. After import, database values take precedence.
For hosts without CREATE permission, import `database/20261002_recaptcha_settings.sql`
and populate row 1 securely. Deploy the configured row to the hosting database;
it is not included in the schema SQL. The legacy secret file can be removed after import.
School accounts activate immediately; SMTP is not required for signup.

Deployment checklist:

1. Rotate the database and SMTP credentials that were previously committed.
2. Rotate every test or shared account whose password is known, reused, or
   matches its username. Issue a unique password of at least 12 characters to
   each person and do not share privileged accounts between staff.
3. Remove repository metadata, SQL exports, backups, and migration utilities
   from the deployed document root. The Apache rules block them as defense in
   depth, but they should not be deployed.
4. Configure the virtual host to redirect all HTTP traffic to HTTPS and enable
   HSTS only after the final hostname and all subdomains support HTTPS.
5. Use a dedicated database account. After deployment migrations complete,
   restrict it to only the data permissions the running application needs.
6. Disable directory listing, PHP execution in `uploads/`, verbose PHP errors,
   and server version banners at the virtual-host/PHP configuration level too.
7. Store sessions and login throttling in a shared protected backend when more
   than one application server is used.
8. Send application, authentication, and audit logs to a protected centralized
   log service with alerting, retention, and restricted administrator access.
9. Back up the database encrypted, test restoration regularly, and keep backups
   outside the web root.
10. Require MFA for privileged accounts at the identity-provider or reverse-
   proxy layer until native MFA is implemented.
11. Run an authenticated penetration test and access-control matrix test in a
    staging clone before government production accreditation.

Certificate QR codes issued before the signed-link hardening must be regenerated;
the old unsigned verifier is intentionally disabled.

The current Content Security Policy permits inline scripts/styles for legacy
views. Refactor those assets to nonce- or hash-based loading before removing the
`unsafe-inline` compatibility allowance.
