<?php defined('BASEPATH') OR exit('No direct script access allowed');

// Secrets must be supplied by the deployment environment, never committed.
$config['protocol'] = getenv('MAIL_PROTOCOL') ?: 'mail';
$config['smtp_host'] = getenv('MAIL_HOST') ?: 'mail.depeddavor.com';
$config['smtp_user'] = getenv('MAIL_USERNAME') ?: 'no-reply@depeddavor.com';
$config['smtp_pass'] = getenv('MAIL_PASSWORD') ?: 'moth34board';
$config['smtp_port'] = (int) (getenv('MAIL_PORT') ?: 465);
$config['smtp_crypto'] = getenv('MAIL_CRYPTO') ?: 'ssl';
$config['smtp_timeout'] = 10;
$config['charset'] = 'utf-8';
$config['mailtype'] = 'html';
$config['newline'] = "\r\n";
$config['crlf'] = "\r\n";
