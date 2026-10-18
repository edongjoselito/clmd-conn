<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<link rel="manifest" href="<?= html_escape(base_url('manifest.webmanifest')); ?>">
<meta name="theme-color" content="#163b65">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-title" content="CLMD Portal">
<link rel="apple-touch-icon" href="<?= html_escape(base_url('assets/pwa/apple-touch-icon.png')); ?>">
<script defer src="<?= html_escape(base_url('assets/pwa/pwa.js') . '?v=' . filemtime(FCPATH . 'assets/pwa/pwa.js')); ?>"></script>
