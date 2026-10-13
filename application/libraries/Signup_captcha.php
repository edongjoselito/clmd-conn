<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Google reCAPTCHA v3: the secret is used only in server-to-server requests. */
class Signup_captcha
{
    private $settings = array();

    public function __construct()
    {
        $ci =& get_instance();
        $ci->load->database();
        if (!$ci->db->table_exists('recaptcha_settings')) {
            $ci->db->query("CREATE TABLE IF NOT EXISTS recaptcha_settings (
                id TINYINT UNSIGNED NOT NULL PRIMARY KEY,
                site_key VARCHAR(255) NOT NULL,
                secret_key VARCHAR(255) NOT NULL,
                expected_hostname VARCHAR(255) NOT NULL DEFAULT ''
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        }
        $row = $ci->db->where('id', 1)->get('recaptcha_settings')->row_array();
        if (!$row) {
            // One-time import of the existing server configuration. Afterwards,
            // the database row is authoritative and edits take effect next request.
            $secret = getenv('RECAPTCHA_SECRET_KEY');
            $legacy_file = APPPATH . 'config/recaptcha_secret.php';
            if (!$secret && is_file($legacy_file)) {
                $secret = require $legacy_file;
            }
            if ($secret) {
                $ci->db->query('INSERT IGNORE INTO recaptcha_settings (id, site_key, secret_key, expected_hostname) VALUES (?, ?, ?, ?)', array(
                    1,
                    getenv('RECAPTCHA_SITE_KEY') ?: '6LeyxNotAAAAAJ7xV5VvLv5QgPigHzdpK5bndfeQ',
                    $secret,
                    getenv('RECAPTCHA_EXPECTED_HOSTNAME') ?: '',
                ));
                $row = $ci->db->where('id', 1)->get('recaptcha_settings')->row_array();
            }
        }
        $this->settings = $row ?: array();
    }

    public function site_key()
    {
        return isset($this->settings['site_key']) ? $this->settings['site_key'] : '';
    }

    public function verify($token)
    {
        if (!is_string($token) || trim($token) === '' || strlen($token) > 4096) {
            return 'Security verification is missing. Please submit the form again.';
        }
        $secret = isset($this->settings['secret_key']) ? $this->settings['secret_key'] : '';
        if (!$secret || !function_exists('curl_init')) {
            log_message('error', 'Signup reCAPTCHA requires a secret key and PHP cURL.');
            return 'Security verification is unavailable. Please contact the administrator.';
        }
        $request = curl_init('https://www.google.com/recaptcha/api/siteverify');
        curl_setopt_array($request, array(
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query(array('secret' => $secret, 'response' => trim($token))),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ));
        $body = curl_exec($request);
        $status = curl_getinfo($request, CURLINFO_HTTP_CODE);
        curl_close($request);
        $result = is_string($body) ? json_decode($body, true) : null;
        if ($status !== 200 || !is_array($result)) {
            return 'Unable to reach Google reCAPTCHA. Please try again; your details have been kept.';
        }
        if (empty($result['success'])) {
            $codes = isset($result['error-codes']) ? $result['error-codes'] : array();
            if (array_intersect(array('missing-input-secret', 'invalid-input-secret'), $codes)) {
                log_message('error', 'Google rejected the configured signup reCAPTCHA secret.');
                return 'Security verification is not configured correctly. Please contact the administrator.';
            }
            return 'Please complete reCAPTCHA again. The previous response may have expired.';
        }
        if (!isset($result['action'], $result['score']) || $result['action'] !== 'school_signup'
            || !is_numeric($result['score']) || (float) $result['score'] < 0.5 || (float) $result['score'] > 1) {
            return 'Security verification was unsuccessful. Please try again; your details have been kept.';
        }
        $expected = isset($this->settings['expected_hostname']) ? trim($this->settings['expected_hostname']) : '';
        if ($expected !== '' && (!isset($result['hostname']) || !hash_equals($expected, $result['hostname']))) {
            return 'Security verification failed for this website. Please contact the administrator.';
        }
        return true;
    }
}
