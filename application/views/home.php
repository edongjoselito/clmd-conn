<?php
$division_list = isset($divisions) && is_array($divisions) ? $divisions : array();
$division_count = count($division_list);
$region_name = !empty($region->description) ? $region->description : 'Region XI - Davao Region';
$login_failed = $this->session->flashdata('failed');
$page_success = $this->session->flashdata('success');
$login_validation_errors = validation_errors();
$open_login_modal = !empty($login_failed) || !empty($login_validation_errors);

// Filenames contain spaces and commas, so every path segment is encoded before it becomes a URL.
$person_photo_url = function ($relative_path) {
    $relative_path = trim((string) $relative_path);
    if ($relative_path === '' || !is_file(FCPATH . $relative_path)) {
        return '';
    }
    return base_url(implode('/', array_map('rawurlencode', explode('/', $relative_path))));
};

/*
 * The supplied portraits are full resolution - several over 1.5MB at ~1250px wide - but they display
 * at roughly 190px, so the browser was decoding a multi-megabyte bitmap per card only to shrink it.
 * This caches a display-sized copy under assets/images/sdo/thumbs/ and serves that instead.
 *
 * The cache key includes the source file's modification time, so replacing a photo regenerates its
 * thumbnail on the next request - dropping in a new file still just works. Anything that cannot be
 * resized (GD missing, unreadable file, already small enough) falls back to the original URL.
 */
$person_display_url = function ($relative_path, $target_width = 440) use ($person_photo_url) {
    $original_url = $person_photo_url($relative_path);
    if ($original_url === '') {
        return '';
    }

    $source = FCPATH . trim((string) $relative_path);
    $size = @getimagesize($source);
    if (!$size || !function_exists('imagecreatetruecolor')) {
        return $original_url;
    }

    list($source_width, $source_height) = $size;
    if ($source_width <= $target_width * 1.15) {
        return $original_url;
    }

    $cache_relative = 'assets/images/sdo/thumbs/';
    $cache_dir = FCPATH . $cache_relative;
    if (!is_dir($cache_dir) && !@mkdir($cache_dir, 0755, true) && !is_dir($cache_dir)) {
        return $original_url;
    }

    // Keyed as <path+width>-<mtime> so a replaced photo misses the cache, and so the superseded
    // thumbnails for that same photo can be found and cleared instead of accumulating.
    $cache_stem = md5($relative_path . '|' . $target_width);
    $cache_name = $cache_stem . '-' . filemtime($source) . '.jpg';
    $cache_path = $cache_dir . $cache_name;

    if (!is_file($cache_path)) {
        foreach ((array) glob($cache_dir . $cache_stem . '-*.jpg') as $stale) {
            @unlink($stale);
        }

        switch ($size[2]) {
            case IMAGETYPE_JPEG:
                $source_image = @imagecreatefromjpeg($source);
                break;
            case IMAGETYPE_PNG:
                $source_image = @imagecreatefrompng($source);
                break;
            default:
                return $original_url;
        }
        if (!$source_image) {
            return $original_url;
        }

        $target_height = (int) round($source_height * ($target_width / $source_width));
        $thumb = imagecreatetruecolor($target_width, $target_height);
        // These are portraits, so flattening any PNG transparency onto white matches the card.
        imagefilledrectangle($thumb, 0, 0, $target_width, $target_height, imagecolorallocate($thumb, 255, 255, 255));
        imagecopyresampled($thumb, $source_image, 0, 0, 0, 0, $target_width, $target_height, $source_width, $source_height);
        $written = @imagejpeg($thumb, $cache_path, 82);
        imagedestroy($thumb);
        imagedestroy($source_image);
        if (!$written) {
            return $original_url;
        }
    }

    return base_url($cache_relative . $cache_name);
};

// The regional seal is a 495px source shown at 62px or less, so every placement shares one small copy.
$seal_url = $person_display_url('assets/r11-logo.jpg', 160);
if ($seal_url === '') {
    $seal_url = base_url('assets/r11-logo.jpg');
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#5b1026">
    <meta name="description" content="The Curriculum Learning Management Division (CLMD) portal for DepEd Region XI supports learner progress monitoring and targeted instructional support across all learning areas.">
    <title>CLMD Learning Management Portal | DepEd Region XI</title>
    <link rel="icon" href="<?= base_url('assets/images/favicon.ico'); ?>">
    <style>
        :root {
            --blue-950: #3b0718;
            --blue-900: #5b1026;
            --blue-800: #741c34;
            --blue-700: #922c43;
            --blue-100: #f4e6e9;
            --blue-50: #fbf4f5;
            --gold-500: #f0aa20;
            --green-700: #247249;
            --ink: #172535;
            --muted: #5d6d7d;
            --line: #d9e3ec;
            --surface: #fff;
            --shadow: 0 18px 50px rgba(59, 7, 24, .12);
            --gold-300: #f8cf6a;
            --red-600: #ce1126;
            --flag-blue: #0038a8;
            --ease-out: cubic-bezier(.16, 1, .3, 1);
        }

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        main section[id] {
            scroll-margin-top: 96px;
        }

        .to-top {
            position: fixed;
            right: 20px;
            bottom: 20px;
            z-index: 150;
            width: 46px;
            height: 46px;
            display: grid;
            place-items: center;
            padding: 0;
            color: #fff;
            border: 0;
            border-radius: 50%;
            background: var(--blue-900);
            box-shadow: 0 10px 26px rgba(59, 7, 24, .3);
            cursor: pointer;
            opacity: 0;
            visibility: hidden;
            transform: translateY(12px);
            transition: opacity .25s ease, transform .25s var(--ease-out), visibility .25s, background .2s ease;
        }

        .to-top.is-shown {
            opacity: 1;
            visibility: visible;
            transform: none;
        }

        .to-top:hover {
            background: var(--blue-700);
        }

        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            margin: -1px;
            padding: 0;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }

        body {
            margin: 0;
            color: var(--ink);
            background: #fff;
            font-family: Inter, "Segoe UI", Arial, sans-serif;
            font-size: 16px;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        body.menu-open,
        body.modal-open {
            overflow: hidden;
        }

        img {
            max-width: 100%;
        }

        a {
            color: inherit;
        }

        button,
        input {
            font: inherit;
        }

        .container {
            width: min(1180px, calc(100% - 40px));
            margin-inline: auto;
        }

        .skip-link {
            position: fixed;
            top: 8px;
            left: 8px;
            z-index: 1000;
            padding: 10px 15px;
            color: #fff;
            background: var(--blue-950);
            border-radius: 4px;
            transform: translateY(-150%);
        }

        .skip-link:focus {
            transform: none;
        }

        .government-bar {
            color: rgba(255, 255, 255, .88);
            background: var(--blue-950);
            font-size: 12px;
            letter-spacing: .02em;
        }

        .government-bar .container {
            min-height: 34px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
        }

        .government-bar p {
            margin: 0;
        }

        .government-bar span {
            color: var(--gold-500);
            font-weight: 700;
        }

        .site-header {
            position: sticky;
            top: 0;
            z-index: 100;
            border-bottom: 1px solid var(--line);
            background: rgba(255, 255, 255, .97);
            backdrop-filter: blur(12px);
            transition: box-shadow .3s ease, border-color .3s ease;
        }

        .site-header.is-scrolled {
            border-bottom-color: transparent;
            box-shadow: 0 10px 30px rgba(59, 7, 24, .1);
        }

        .masthead {
            min-height: 88px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 28px;
        }

        .brand {
            min-width: 0;
            display: flex;
            align-items: center;
            gap: 13px;
            text-decoration: none;
        }

        .brand img {
            width: 62px;
            height: 62px;
            object-fit: contain;
            border-radius: 50%;
        }

        .brand-copy {
            min-width: 0;
            line-height: 1.18;
        }

        .brand-copy small {
            display: block;
            margin-bottom: 3px;
            color: var(--muted);
            font-family: Georgia, "Times New Roman", serif;
            font-size: 12px;
        }

        .brand-copy strong {
            display: block;
            color: var(--blue-950);
            font-size: 17px;
        }

        .brand-copy span {
            display: block;
            margin-top: 4px;
            color: var(--blue-700);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .site-nav {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .site-nav a {
            padding: 10px 12px;
            color: #614550;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
        }

        .site-nav a:hover,
        .site-nav a:focus-visible,
        .site-nav a.is-active {
            color: var(--blue-800);
            background: var(--blue-50);
        }

        .site-nav a:not(.nav-login) {
            position: relative;
        }

        .site-nav a:not(.nav-login)::after {
            content: "";
            position: absolute;
            left: 12px;
            right: 12px;
            bottom: 5px;
            height: 2px;
            border-radius: 2px;
            background: var(--gold-500);
            transform: scaleX(0);
            transition: transform .25s var(--ease-out);
        }

        .site-nav a.is-active::after {
            transform: scaleX(1);
        }

        .site-nav .nav-login {
            margin-left: 7px;
            padding-inline: 18px;
            color: #fff;
            background: var(--blue-900);
        }

        .site-nav .nav-login:hover,
        .site-nav .nav-login:focus-visible {
            color: #fff;
            background: var(--blue-700);
        }

        .menu-toggle {
            width: 44px;
            height: 44px;
            display: none;
            place-items: center;
            border: 1px solid var(--line);
            border-radius: 7px;
            color: var(--blue-900);
            background: #fff;
            cursor: pointer;
        }

        .hero {
            position: relative;
            overflow: hidden;
            isolation: isolate;
            color: #fff;
            background: radial-gradient(900px 540px at 88% 0%, rgba(240, 170, 32, .22), transparent 62%), radial-gradient(800px 560px at 0% 100%, rgba(146, 44, 67, .6), transparent 62%), linear-gradient(135deg, #270510 0%, #541126 52%, #7a2039 100%);
        }

        .hero::before {
            content: "";
            position: absolute;
            inset: 0;
            z-index: -1;
            background-image: radial-gradient(rgba(255, 255, 255, .16) 1px, transparent 1.5px);
            background-size: 26px 26px;
            -webkit-mask-image: radial-gradient(ellipse 70% 80% at 72% 40%, #000 10%, transparent 72%);
            mask-image: radial-gradient(ellipse 70% 80% at 72% 40%, #000 10%, transparent 72%);
        }

        .hero-orb {
            position: absolute;
            z-index: -1;
            border-radius: 50%;
            pointer-events: none;
            will-change: transform;
        }

        .hero-orb-a {
            width: 460px;
            height: 460px;
            top: -140px;
            left: 38%;
            background: radial-gradient(circle, rgba(240, 170, 32, .2), transparent 68%);
            animation: orbDrift 18s ease-in-out infinite alternate;
        }

        .hero-orb-b {
            width: 520px;
            height: 520px;
            bottom: -220px;
            right: -120px;
            background: radial-gradient(circle, rgba(94, 170, 235, .24), transparent 68%);
            animation: orbDrift 22s ease-in-out infinite alternate-reverse;
        }

        @keyframes orbDrift {
            from {
                transform: translate3d(0, 0, 0);
            }

            to {
                transform: translate3d(-60px, 40px, 0);
            }
        }

        .hero-grid {
            position: relative;
            min-height: 640px;
            display: grid;
            grid-template-columns: minmax(0, 1.12fr) minmax(340px, .78fr);
            align-items: center;
            gap: 64px;
            padding-block: 84px 140px;
        }

        .hero-grid>* {
            min-width: 0;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 22px;
            padding: 7px 15px 7px 11px;
            color: var(--gold-300);
            border: 1px solid rgba(255, 255, 255, .16);
            border-radius: 999px;
            background: rgba(255, 255, 255, .06);
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .12em;
            text-transform: uppercase;
        }

        .eyebrow::before {
            content: "";
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--gold-500);
            animation: pulseDot 2.4s ease-out infinite;
        }

        @keyframes pulseDot {
            0% {
                box-shadow: 0 0 0 0 rgba(240, 170, 32, .55);
            }

            80%,
            100% {
                box-shadow: 0 0 0 10px rgba(240, 170, 32, 0);
            }
        }

        .hero h1 {
            max-width: 760px;
            margin: 0;
            color: #fff;
            font-family: Georgia, "Times New Roman", serif;
            font-size: clamp(44px, 5.2vw, 72px);
            line-height: 1.02;
            letter-spacing: -.035em;
        }

        .hero-accent {
            white-space: nowrap;
            color: transparent;
            background: linear-gradient(100deg, #ffe39a 0%, var(--gold-500) 45%, #ffd06b 100%);
            -webkit-background-clip: text;
            background-clip: text;
        }

        .hero-tagline {
            max-width: 640px;
            margin: 18px 0 0;
            color: #f2b6c2;
            font-size: 15px;
            font-weight: 800;
            line-height: 1.45;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .hero-lead {
            max-width: 640px;
            margin: 20px 0 0;
            color: rgba(255, 255, 255, .76);
            font-size: 18px;
            line-height: 1.75;
        }

        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 34px;
        }

        .button {
            position: relative;
            overflow: hidden;
            min-height: 50px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 0 22px;
            border: 1px solid transparent;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 800;
            text-decoration: none;
            cursor: pointer;
            transition: transform .2s var(--ease-out), background .2s ease, border-color .2s ease, box-shadow .2s ease;
        }

        .button:hover {
            transform: translateY(-2px);
        }

        .button-arrow {
            display: inline-block;
            transition: transform .25s var(--ease-out);
        }

        .button:hover .button-arrow {
            transform: translateX(4px);
        }

        .button-primary {
            color: #fff;
            background: var(--blue-900);
        }

        .button-primary:hover {
            background: var(--blue-700);
        }

        .button-gold {
            color: var(--blue-950);
            background: linear-gradient(135deg, var(--gold-300), var(--gold-500));
            box-shadow: 0 10px 28px rgba(240, 170, 32, .3);
        }

        .button-gold:hover {
            box-shadow: 0 14px 34px rgba(240, 170, 32, .45);
        }

        .button-gold::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(110deg, transparent 30%, rgba(255, 255, 255, .55) 50%, transparent 70%);
            transform: translateX(-120%);
            transition: transform .7s ease;
        }

        .button-gold:hover::after {
            transform: translateX(120%);
        }

        .button-ghost {
            color: #fff;
            border-color: rgba(255, 255, 255, .32);
            background: rgba(255, 255, 255, .06);
        }

        .button-ghost:hover {
            border-color: rgba(255, 255, 255, .6);
            background: rgba(255, 255, 255, .12);
        }

        .public-note {
            display: flex;
            align-items: center;
            gap: 9px;
            margin: 26px 0 0;
            color: rgba(255, 255, 255, .62);
            font-size: 12px;
        }

        .public-note svg {
            flex: 0 0 auto;
            color: #6fdca0;
        }

        .hero-copy>* {
            animation: heroRise .9s var(--ease-out) both;
        }

        .hero-copy> :nth-child(2) {
            animation-delay: .08s;
        }

        .hero-copy> :nth-child(3) {
            animation-delay: .16s;
        }

        .hero-copy> :nth-child(4) {
            animation-delay: .24s;
        }

        .hero-copy> :nth-child(5) {
            animation-delay: .32s;
        }

        .hero-copy> :nth-child(6) {
            animation-delay: .4s;
        }

        .hero-visual {
            position: relative;
            animation: heroRise 1s var(--ease-out) .25s both;
        }

        @keyframes heroRise {
            from {
                opacity: 0;
                transform: translate3d(0, 22px, 0);
            }

            to {
                opacity: 1;
                transform: none;
            }
        }

        .impact-panel {
            position: relative;
            padding: 30px;
            overflow: hidden;
            color: #fff;
            border: 1px solid rgba(255, 255, 255, .18);
            border-radius: 20px;
            background: linear-gradient(160deg, rgba(255, 255, 255, .13), rgba(255, 255, 255, .04));
            box-shadow: 0 40px 90px rgba(2, 16, 31, .45), inset 0 1px 0 rgba(255, 255, 255, .18);
            -webkit-backdrop-filter: blur(14px);
            backdrop-filter: blur(14px);
        }

        .impact-panel::before {
            content: "";
            position: absolute;
            width: 280px;
            height: 280px;
            top: -120px;
            right: -110px;
            border: 56px solid rgba(240, 170, 32, .1);
            border-radius: 50%;
        }

        .impact-top,
        .impact-flow,
        .impact-footer {
            position: relative;
            z-index: 1;
        }

        .impact-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }

        .impact-top span {
            color: var(--gold-300);
            font-size: 10px;
            font-weight: 900;
            letter-spacing: .14em;
            text-transform: uppercase;
        }

        .impact-seal {
            width: 58px;
            height: 58px;
            padding: 3px;
            border-radius: 50%;
            background: #fff;
            box-shadow: 0 0 0 5px rgba(255, 255, 255, .08);
        }

        .impact-panel h2 {
            position: relative;
            z-index: 1;
            max-width: 320px;
            margin: 24px 0 8px;
            color: #fff;
            font-family: Georgia, serif;
            font-size: 30px;
            line-height: 1.18;
        }

        .impact-panel>p {
            position: relative;
            z-index: 1;
            margin: 0;
            color: rgba(255, 255, 255, .68);
            font-size: 13px;
        }

        .impact-flow {
            display: grid;
            grid-template-columns: 1fr auto 1fr auto 1fr;
            align-items: center;
            gap: 8px;
            margin-top: 28px;
        }

        .impact-stage {
            min-height: 66px;
            display: grid;
            place-items: center;
            padding: 10px 6px;
            border: 1px solid rgba(255, 255, 255, .16);
            border-radius: 10px;
            background: rgba(255, 255, 255, .07);
            text-align: center;
            animation: stageGlow 6s ease-in-out infinite;
        }

        .impact-flow> :nth-child(3) {
            animation-delay: 2s;
        }

        .impact-flow> :nth-child(5) {
            animation-delay: 4s;
        }

        @keyframes stageGlow {

            0%,
            40%,
            100% {
                border-color: rgba(255, 255, 255, .16);
                background: rgba(255, 255, 255, .07);
                box-shadow: none;
            }

            12%,
            28% {
                border-color: rgba(240, 170, 32, .75);
                background: rgba(240, 170, 32, .14);
                box-shadow: 0 0 22px rgba(240, 170, 32, .25);
            }
        }

        .impact-stage strong {
            display: block;
            color: var(--gold-300);
            font-size: 10px;
            letter-spacing: .08em;
        }

        .impact-stage span {
            display: block;
            margin-top: 3px;
            color: #fff;
            font-size: 11px;
            font-weight: 800;
        }

        .impact-arrow {
            color: rgba(255, 255, 255, .4);
            font-size: 15px;
        }

        .impact-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            margin-top: 20px;
            padding-top: 18px;
            border-top: 1px solid rgba(255, 255, 255, .13);
            color: rgba(255, 255, 255, .68);
            font-size: 11px;
        }

        .impact-footer span {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .impact-footer span::before {
            content: "";
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #6fdca0;
            animation: pulseLive 2s ease-out infinite;
        }

        @keyframes pulseLive {
            0% {
                box-shadow: 0 0 0 0 rgba(111, 220, 160, .6);
            }

            80%,
            100% {
                box-shadow: 0 0 0 8px rgba(111, 220, 160, 0);
            }
        }

        .impact-footer strong {
            color: #fff;
            font-size: 14px;
        }

        .hero-chip {
            position: absolute;
            z-index: 2;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px 10px 10px;
            color: var(--blue-950);
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 18px 40px rgba(2, 16, 31, .3);
            font-size: 12px;
            font-weight: 800;
            line-height: 1.25;
            animation: chipFloat 6s ease-in-out infinite;
        }

        .hero-chip small {
            display: block;
            color: var(--muted);
            font-size: 10px;
            font-weight: 700;
        }

        .hero-chip-icon {
            width: 32px;
            height: 32px;
            flex: 0 0 auto;
            display: grid;
            place-items: center;
            color: #fff;
            border-radius: 9px;
            background: var(--blue-800);
        }

        .hero-chip-a {
            top: -24px;
            left: -36px;
        }

        .hero-chip-b {
            right: 32px;
            bottom: -26px;
            animation-delay: -3s;
        }

        .hero-chip-b .hero-chip-icon {
            color: var(--blue-950);
            background: var(--gold-500);
        }

        @keyframes chipFloat {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-8px);
            }
        }

        .login-card {
            position: relative;
            padding: 30px;
            border: 1px solid rgba(185, 205, 221, .95);
            border-top: 5px solid var(--gold-500);
            border-radius: 10px;
            background: rgba(255, 255, 255, .97);
            box-shadow: var(--shadow);
        }

        .login-card::after {
            content: "OFFICIAL PORTAL";
            position: absolute;
            top: 21px;
            right: 25px;
            color: #8293a3;
            font-size: 9px;
            font-weight: 800;
            letter-spacing: .12em;
        }

        .login-icon {
            width: 44px;
            height: 44px;
            display: grid;
            place-items: center;
            color: #fff;
            border-radius: 50%;
            background: var(--blue-900);
        }

        .login-card h2 {
            margin: 17px 0 4px;
            color: var(--blue-950);
            font-size: 23px;
            line-height: 1.25;
        }

        .login-intro {
            margin: 0 0 23px;
            color: var(--muted);
            font-size: 13px;
        }

        .alert {
            margin-bottom: 18px;
            padding: 11px 13px;
            border: 1px solid;
            border-radius: 5px;
            font-size: 12px;
            line-height: 1.5;
        }

        .alert-danger,
        .error {
            color: #8a2525;
            border-color: #efc2c2;
            background: #fff3f3;
        }

        .alert-success {
            color: #1f6842;
            border-color: #b9dfc8;
            background: #effaf3;
        }

        .error {
            margin-bottom: 12px;
            padding: 9px 11px;
        }

        .field {
            margin-bottom: 16px;
        }

        .field label {
            display: block;
            margin: 0 0 7px;
            color: #563642;
            font-size: 12px;
            font-weight: 800;
        }

        .input-wrap {
            position: relative;
        }

        .field input {
            width: 100%;
            height: 46px;
            padding: 0 43px 0 13px;
            color: var(--ink);
            border: 1px solid #bdccd8;
            border-radius: 5px;
            background: #fff;
            outline: none;
            font-size: 14px;
        }

        .field input:focus {
            border-color: var(--blue-700);
            box-shadow: 0 0 0 3px rgba(30, 103, 159, .12);
        }

        .input-icon {
            position: absolute;
            top: 50%;
            right: 13px;
            color: #7b8c9c;
            transform: translateY(-50%);
            pointer-events: none;
        }

        .password-toggle {
            position: absolute;
            top: 50%;
            right: 8px;
            padding: 6px;
            border: 0;
            color: var(--blue-700);
            background: transparent;
            transform: translateY(-50%);
            cursor: pointer;
            font-size: 11px;
            font-weight: 800;
        }

        .login-submit {
            width: 100%;
            margin-top: 4px;
        }

        .login-help {
            margin: 18px 0 0;
            padding-top: 16px;
            border-top: 1px solid var(--line);
            color: var(--muted);
            text-align: center;
            font-size: 11px;
        }

        .login-help a {
            color: var(--blue-700);
            font-weight: 800;
            text-decoration: none;
        }

        .page-message {
            padding: 11px 0;
            color: #1f6842;
            border-bottom: 1px solid #b9dfc8;
            background: #effaf3;
            font-size: 12px;
            text-align: center;
        }

        .portal-overlay {
            position: fixed;
            inset: 0;
            z-index: 500;
            display: grid;
            place-items: center;
            padding: 20px;
            overflow-y: auto;
            background: rgba(4, 25, 44, .72);
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: opacity .2s ease, visibility .2s ease;
            backdrop-filter: blur(5px);
        }

        .portal-overlay[aria-hidden="false"] {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
        }

        .portal-dialog {
            width: min(100%, 440px);
            position: relative;
            transform: translateY(14px) scale(.98);
            transition: transform .2s ease;
        }

        .portal-overlay[aria-hidden="false"] .portal-dialog {
            transform: none;
        }

        .portal-dialog .login-card {
            max-height: calc(100vh - 40px);
            overflow-y: auto;
        }

        .portal-close {
            position: absolute;
            top: 17px;
            right: 17px;
            z-index: 3;
            width: 35px;
            height: 35px;
            display: grid;
            place-items: center;
            padding: 0;
            color: #5d7082;
            border: 1px solid var(--line);
            border-radius: 50%;
            background: #fff;
            cursor: pointer;
        }

        .portal-dialog .login-card::after {
            right: 68px;
        }

        /* The modal holds both the sign-in and the reset-password forms; only one is ever shown. */
        .portal-panel[hidden] {
            display: none;
        }

        .portal-panel {
            animation: portalPanelIn .2s var(--ease-out) both;
        }

        @keyframes portalPanelIn {
            from {
                opacity: 0;
                transform: translateY(6px);
            }

            to {
                opacity: 1;
                transform: none;
            }
        }

        .login-submit {
            position: relative;
        }

        .button-spinner {
            display: none;
            width: 19px;
            height: 19px;
            border: 2px solid rgba(255, 255, 255, .4);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin .7s linear infinite;
        }

        .login-submit.is-loading .button-spinner {
            display: block;
        }

        .login-submit.is-loading .button-label {
            display: none;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .stat-band {
            position: relative;
            z-index: 3;
            margin-top: -76px;
        }

        .stat-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            overflow: hidden;
            border: 1px solid var(--line);
            border-radius: 18px;
            background: #fff;
            box-shadow: 0 24px 60px rgba(59, 7, 24, .14);
        }

        .stat {
            position: relative;
            min-height: 124px;
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 26px 30px;
            border-right: 1px solid var(--line);
            transition: background .25s ease;
        }

        .stat:last-child {
            border-right: 0;
        }

        .stat::after {
            content: "";
            position: absolute;
            inset: auto 30px 0;
            height: 3px;
            border-radius: 3px 3px 0 0;
            background: var(--gold-500);
            transform: scaleX(0);
            transition: transform .35s var(--ease-out);
        }

        .stat:hover {
            background: var(--blue-50);
        }

        .stat:hover::after {
            transform: scaleX(1);
        }

        .stat-icon {
            width: 54px;
            height: 54px;
            flex: 0 0 auto;
            display: grid;
            place-items: center;
            color: var(--blue-800);
            border-radius: 14px;
            background: linear-gradient(145deg, var(--blue-100), #fff);
            box-shadow: inset 0 0 0 1px #d3e3f0;
            transition: transform .35s var(--ease-out), color .25s ease, background .25s ease;
        }

        .stat:hover .stat-icon {
            color: #fff;
            background: linear-gradient(145deg, var(--blue-700), var(--blue-950));
            transform: rotate(-6deg) scale(1.05);
        }

        .stat strong {
            display: block;
            color: var(--blue-950);
            font-family: Georgia, "Times New Roman", serif;
            font-size: 34px;
            line-height: 1;
            font-variant-numeric: tabular-nums;
        }

        .stat strong+span {
            display: block;
            margin-top: 7px;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.4;
        }

        .section {
            padding-block: 92px;
        }

        .section-soft {
            background: var(--blue-50);
        }

        .section-heading {
            max-width: 700px;
            margin-bottom: 42px;
        }

        .section-heading.center {
            margin-inline: auto;
            text-align: center;
        }

        .kicker {
            margin: 0 0 10px;
            color: var(--blue-700);
            font-size: 12px;
            font-weight: 800;
            letter-spacing: .11em;
            text-transform: uppercase;
        }

        .section h2 {
            margin: 0;
            color: var(--blue-950);
            font-family: Georgia, "Times New Roman", serif;
            font-size: clamp(32px, 4vw, 46px);
            line-height: 1.14;
            letter-spacing: -.025em;
        }

        .section-heading>p:last-child {
            margin: 17px 0 0;
            color: var(--muted);
            font-size: 16px;
        }

        .mission-grid {
            display: grid;
            grid-template-columns: .82fr 1.18fr;
            gap: 72px;
            align-items: start;
        }

        .mission-quote {
            position: sticky;
            top: 120px;
            padding: 40px 36px 34px;
            color: #fff;
            border-radius: 20px;
            background: radial-gradient(420px 260px at 100% 0%, rgba(240, 170, 32, .2), transparent 60%), linear-gradient(150deg, var(--blue-950), var(--blue-800));
            box-shadow: 0 30px 60px rgba(59, 7, 24, .22);
            overflow: hidden;
        }

        .mission-quote::before {
            content: "\201C";
            position: absolute;
            top: -14px;
            left: 24px;
            color: var(--gold-500);
            font-family: Georgia, serif;
            font-size: 140px;
            line-height: 1;
            opacity: .4;
        }

        .mission-quote::after {
            content: "CLMD";
            position: absolute;
            right: -8px;
            bottom: -35px;
            color: rgba(255, 255, 255, .05);
            font-family: Georgia, serif;
            font-size: 150px;
            font-weight: 800;
            line-height: 1;
        }

        .mission-quote blockquote {
            position: relative;
            z-index: 1;
            margin: 34px 0 0;
            font-family: Georgia, serif;
            font-size: 26px;
            line-height: 1.5;
        }

        .mission-quote p {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 24px 0 0;
            color: var(--gold-300);
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .1em;
            text-transform: uppercase;
        }

        .mission-quote p::before {
            content: "";
            width: 26px;
            height: 2px;
            background: var(--gold-500);
        }

        .principles {
            display: grid;
            gap: 14px;
        }

        .principle {
            display: grid;
            grid-template-columns: 52px 1fr;
            gap: 18px;
            padding: 20px 22px;
            border: 1px solid var(--line);
            border-radius: 16px;
            background: #fff;
            transition: transform .3s var(--ease-out), box-shadow .3s ease, border-color .3s ease;
        }

        .principle:hover {
            transform: translateY(-3px);
            border-color: #b9d0e2;
            box-shadow: 0 16px 36px rgba(59, 7, 24, .1);
        }

        .principle-icon {
            width: 52px;
            height: 52px;
            display: grid;
            place-items: center;
            color: var(--blue-800);
            border-radius: 14px;
            background: var(--blue-100);
            transition: color .25s ease, background .25s ease, transform .35s var(--ease-out);
        }

        .principle:hover .principle-icon {
            color: var(--blue-950);
            background: var(--gold-500);
            transform: rotate(-6deg);
        }

        .principle h3 {
            margin: 2px 0 5px;
            color: var(--blue-950);
            font-size: 17px;
        }

        .principle p {
            margin: 0;
            color: var(--muted);
            font-size: 14px;
        }

        .steps {
            position: relative;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
        }

        .steps::before,
        .steps::after {
            content: "";
            position: absolute;
            top: 31px;
            left: calc(100% / 6);
            right: calc(100% / 6);
            height: 2px;
        }

        .steps::before {
            background: #d3e1ec;
        }

        .steps::after {
            background: linear-gradient(90deg, var(--blue-700), var(--gold-500));
            transform: scaleX(0);
            transform-origin: 0 50%;
            transition: transform 1.4s var(--ease-out) .25s;
        }

        .steps.is-visible::after,
        html:not(.has-js) .steps::after {
            transform: scaleX(1);
        }

        .step {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .step-marker {
            width: 64px;
            height: 64px;
            flex: 0 0 auto;
            display: grid;
            place-items: center;
            margin-bottom: 22px;
            border-radius: 50%;
            background: #fff;
            box-shadow: 0 0 0 6px var(--blue-50), 0 12px 26px rgba(59, 7, 24, .14);
        }

        .step-icon {
            width: 48px;
            height: 48px;
            display: grid;
            place-items: center;
            color: #fff;
            border-radius: 50%;
            background: linear-gradient(145deg, var(--blue-700), var(--blue-950));
            transition: transform .35s var(--ease-out), color .25s ease, background .25s ease;
        }

        .step:hover .step-icon {
            color: var(--blue-950);
            background: linear-gradient(145deg, var(--gold-300), var(--gold-500));
            transform: scale(1.08) rotate(-6deg);
        }

        .step-card {
            width: 100%;
            flex: 1 1 auto;
            padding: 26px 26px 28px;
            border: 1px solid var(--line);
            border-radius: 18px;
            background: #fff;
            transition: transform .3s var(--ease-out), box-shadow .3s ease, border-color .3s ease;
        }

        .step:hover .step-card {
            transform: translateY(-4px);
            border-color: #b9d0e2;
            box-shadow: 0 18px 40px rgba(59, 7, 24, .1);
        }

        .step-label {
            margin: 0 0 8px;
            color: #a8740a;
            font-size: 11px;
            font-weight: 900;
            letter-spacing: .12em;
            text-transform: uppercase;
        }

        .step h3 {
            margin: 0 0 8px;
            color: var(--blue-950);
            font-size: 19px;
        }

        .step-card p:last-child {
            margin: 0;
            color: var(--muted);
            font-size: 14px;
        }

        .has-js .steps.reveal .step {
            transition: opacity .7s var(--ease-out), transform .7s var(--ease-out);
        }

        .has-js .steps.reveal:not(.is-visible) .step {
            opacity: 0;
            transform: translateY(22px);
        }

        .has-js .steps.reveal .step:nth-child(2) {
            transition-delay: .15s;
        }

        .has-js .steps.reveal .step:nth-child(3) {
            transition-delay: .3s;
        }


        .people-directory {
            margin-top: 64px;
        }

        .people-block+.people-block {
            margin-top: 54px;
        }

        .people-block-head {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 18px;
            margin-bottom: 26px;
        }

        .people-title {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .people-title-icon {
            flex: 0 0 auto;
            width: 38px;
            height: 38px;
            display: grid;
            place-items: center;
            color: #fff;
            border-radius: 9px;
            background: linear-gradient(145deg, var(--blue-800), var(--blue-950));
            box-shadow: 0 6px 16px rgba(59, 7, 24, .22);
        }

        .developers-block .people-title-icon {
            background: linear-gradient(145deg, #d9930f, var(--gold-500));
            box-shadow: 0 6px 16px rgba(240, 170, 32, .32);
        }

        .people-title h3 {
            margin: 0;
            color: var(--blue-950);
            font-family: Georgia, "Times New Roman", serif;
            font-size: 24px;
            line-height: 1.2;
            letter-spacing: -.015em;
        }

        .people-title h3::after {
            content: "";
            display: block;
            width: 42px;
            height: 3px;
            margin-top: 8px;
            border-radius: 3px;
            background: var(--gold-500);
        }

        .people-rule {
            flex: 1 1 auto;
            height: 1px;
            margin-bottom: 9px;
            background: linear-gradient(90deg, #c7dae8, rgba(199, 218, 232, 0));
        }

        .people-grid {
            display: grid;
            grid-template-columns: repeat(6, minmax(0, 1fr));
            gap: 16px;
        }

        .people-grid.developers-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
            width: min(100%, 830px);
            margin-inline: auto;
        }

        /* The card is the reveal target; the frame inside carries hover, so the two never fight
           over `transform` and the entrance can safely replay every time the card re-enters view. */
        .person-card {
            position: relative;
        }

        .person-frame {
            position: relative;
            height: 100%;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            border: 1px solid var(--line);
            border-radius: 12px;
            background: #fff;
            text-align: center;
            box-shadow: 0 2px 8px rgba(59, 7, 24, .05);
            transition: transform .3s cubic-bezier(.22, .61, .36, 1), box-shadow .3s ease, border-color .3s ease;
        }

        .person-card:hover .person-frame {
            transform: translateY(-6px);
            border-color: #a9c4d9;
            box-shadow: 0 18px 38px rgba(59, 7, 24, .16);
        }

        .person-photo {
            position: relative;
            aspect-ratio: 3 / 4;
            overflow: hidden;
            background: linear-gradient(165deg, var(--blue-100), #f7fbfd);
        }

        .person-photo img {
            width: 100%;
            height: 100%;
            display: block;
            object-fit: cover;
            filter: saturate(.86) contrast(1.05);
            transition: transform .55s cubic-bezier(.22, .61, .36, 1), filter .45s ease;
        }

        .person-card:hover .person-photo img {
            transform: scale(1.06);
            filter: saturate(1) contrast(1);
        }

        /* Portraits arrive with very different backdrops, so a shared scrim grounds every card the same way. */
        .person-photo::before {
            content: "";
            position: absolute;
            inset: 0;
            z-index: 1;
            background: linear-gradient(175deg, rgba(59, 7, 24, .05) 0 42%, rgba(59, 7, 24, .34) 100%);
            pointer-events: none;
            transition: opacity .35s ease;
        }

        .person-card:hover .person-photo::before {
            opacity: .55;
        }

        .person-badge {
            position: absolute;
            z-index: 2;
            left: 8px;
            bottom: 8px;
            max-width: calc(100% - 16px);
            height: 22px;
            display: grid;
            place-items: center;
            padding: 0 8px;
            overflow: hidden;
            white-space: nowrap;
            color: var(--blue-900);
            border-radius: 6px;
            background: rgba(255, 255, 255, .93);
            box-shadow: 0 3px 10px rgba(59, 7, 24, .26);
            font-size: 9px;
            font-weight: 800;
            letter-spacing: .06em;
        }

        .developers-grid .person-badge {
            width: 26px;
            padding: 0;
            color: #b07c07;
        }

        .person-badge img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .person-placeholder {
            position: relative;
            z-index: 0;
            width: 100%;
            height: 100%;
            display: grid;
            place-items: center;
            color: #adc0cf;
            background: repeating-linear-gradient(135deg, #f5f9fc 0 9px, #eef4f9 9px 18px);
        }

        .person-info {
            position: relative;
            flex: 1 1 auto;
            padding: 14px 10px 16px;
        }

        .person-info::before {
            content: "";
            position: absolute;
            top: 0;
            left: 50%;
            width: 0;
            height: 3px;
            background: var(--gold-500);
            transform: translateX(-50%);
            transition: width .35s cubic-bezier(.22, .61, .36, 1);
        }

        .person-card:hover .person-info::before {
            width: 100%;
        }

        .person-info strong {
            display: flex;
            min-height: 2.7em;
            align-items: center;
            justify-content: center;
            color: var(--blue-950);
            font-size: 13px;
            line-height: 1.35;
        }

        .developers-grid .person-info strong a {
            text-decoration: none;
        }

        .person-info span {
            display: block;
            margin-top: 6px;
            color: #8392a1;
            font-size: 9px;
            font-weight: 800;
            letter-spacing: .07em;
            text-transform: uppercase;
        }

        .person-card.is-vacant .person-frame {
            border-style: dashed;
            box-shadow: none;
            background: #fcfdfe;
        }

        .person-card.is-vacant:hover .person-frame {
            transform: translateY(-3px);
            box-shadow: 0 10px 24px rgba(59, 7, 24, .08);
        }

        .person-card.is-vacant .person-photo::before {
            display: none;
        }

        .person-card.is-vacant .person-badge {
            color: #94a5b3;
            background: rgba(255, 255, 255, .9);
            box-shadow: 0 2px 6px rgba(59, 7, 24, .12);
        }

        .person-card.is-vacant .person-info strong {
            color: #93a3b1;
            font-weight: 700;
            font-style: italic;
        }

        .developers-grid .person-frame::after {
            content: "";
            position: absolute;
            z-index: 3;
            inset: 0 0 auto;
            height: 3px;
            background: linear-gradient(90deg, var(--gold-500), #f6c65f);
        }

        /* Entrance in three beats: the card frame fades up, the portrait develops in behind it, then
           the name plate follows. Every beat is opacity-only apart from a 16px lift on the card itself:
           no zoom, blur, or clip wipe. Scaling a grid of faces was what caused motion discomfort, and
           a hard clip edge slices the portrait mid-reveal. Delays come from JS (--reveal-delay). */
        .has-js .reveal {
            transition: opacity .8s cubic-bezier(.16, 1, .3, 1) var(--reveal-delay, 0ms), transform .8s cubic-bezier(.16, 1, .3, 1) var(--reveal-delay, 0ms);
        }

        .has-js .reveal:not(.is-visible) {
            opacity: 0;
            transform: translateY(16px);
        }

        .has-js .reveal .person-photo img,
        .has-js .reveal .person-placeholder,
        .has-js .reveal .person-badge {
            transition: opacity .9s ease calc(var(--reveal-delay, 0ms) + 120ms), transform .55s cubic-bezier(.22, .61, .36, 1) 0s, filter .45s ease 0s;
        }

        .has-js .reveal:not(.is-visible) .person-photo img,
        .has-js .reveal:not(.is-visible) .person-placeholder,
        .has-js .reveal:not(.is-visible) .person-badge {
            opacity: 0;
        }

        .has-js .reveal .person-info {
            transition: opacity .6s ease calc(var(--reveal-delay, 0ms) + 230ms), transform .6s cubic-bezier(.16, 1, .3, 1) calc(var(--reveal-delay, 0ms) + 230ms);
        }

        .has-js .reveal:not(.is-visible) .person-info {
            opacity: 0;
            transform: translateY(8px);
        }

        .cta {
            position: relative;
            padding-block: 0 92px;
            background: var(--blue-50);
        }

        .cta-card {
            position: relative;
            overflow: hidden;
            isolation: isolate;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 35px;
            padding: 50px 54px;
            color: #fff;
            border-radius: 24px;
            background: radial-gradient(520px 300px at 100% 0%, rgba(240, 170, 32, .28), transparent 60%), radial-gradient(420px 300px at 0% 100%, rgba(94, 170, 235, .3), transparent 60%), linear-gradient(135deg, var(--blue-950), var(--blue-800));
            box-shadow: 0 30px 70px rgba(59, 7, 24, .25);
        }

        .cta-card::before {
            content: "";
            position: absolute;
            inset: 0;
            z-index: -1;
            background-image: radial-gradient(rgba(255, 255, 255, .14) 1px, transparent 1.5px);
            background-size: 22px 22px;
            -webkit-mask-image: linear-gradient(90deg, transparent, #000);
            mask-image: linear-gradient(90deg, transparent, #000);
        }

        .cta h2 {
            margin: 0;
            color: #fff;
            font-family: Georgia, serif;
            font-size: clamp(26px, 3vw, 34px);
            line-height: 1.2;
        }

        .cta p {
            max-width: 560px;
            margin: 10px 0 0;
            color: rgba(255, 255, 255, .74);
            font-size: 14px;
        }

        .cta .button {
            flex: 0 0 auto;
        }

        .site-footer {
            position: relative;
            padding-block: 56px 25px;
            color: rgba(255, 255, 255, .74);
            background: #3b0718;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 1.35fr .65fr;
            gap: 50px;
            padding-bottom: 34px;
        }

        .footer-brand {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .footer-brand img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            box-shadow: 0 0 0 4px rgba(255, 255, 255, .08);
        }

        .footer-brand strong {
            display: block;
            color: #fff;
            font-size: 16px;
        }

        .footer-brand span {
            display: block;
            margin-top: 4px;
            font-size: 11px;
        }

        .footer-copy {
            max-width: 560px;
            margin: 18px 0 0;
            font-size: 13px;
        }

        .footer-links strong {
            display: block;
            margin-bottom: 12px;
            color: #fff;
            font-size: 12px;
            letter-spacing: .06em;
            text-transform: uppercase;
        }

        .footer-links a {
            display: block;
            width: max-content;
            margin: 9px 0;
            color: rgba(255, 255, 255, .72);
            font-size: 13px;
            text-decoration: none;
            transition: color .2s ease, transform .2s var(--ease-out);
        }

        .footer-links a:hover {
            color: var(--gold-500);
            transform: translateX(4px);
        }

        .copyright {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            padding-top: 22px;
            border-top: 1px solid rgba(255, 255, 255, .12);
            font-size: 11px;
        }

        :focus-visible {
            outline: 3px solid rgba(240, 170, 32, .6);
            outline-offset: 3px;
        }

        /* Leadership gets larger cards for hierarchy; consultants wrap as a centred flex row so the
           11-card grid never leaves a lopsided last row. */
        .top-management-block .people-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 20px;
            width: min(100%, 1000px);
            margin-inline: auto;
        }

        .top-management-block .person-info strong {
            font-size: 15px;
        }

        .top-management-block .person-info span {
            font-size: 10px;
        }

        .consultants-block .people-grid {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
        }

        .consultants-block .person-card {
            flex: 0 0 calc((100% - 80px) / 6);
        }

        .person-info span {
            color: #66788a;
            font-size: 9.5px;
        }

        /* Portrait viewer: card photos open a larger copy with name, role, and previous/next controls. */
        .person-photo[role="button"] {
            cursor: zoom-in;
        }

        .person-photo[role="button"]:focus-visible {
            outline: 3px solid var(--gold-500);
            outline-offset: -3px;
        }

        .person-zoom {
            position: absolute;
            z-index: 2;
            top: 8px;
            right: 8px;
            width: 30px;
            height: 30px;
            display: grid;
            place-items: center;
            color: var(--blue-900);
            border-radius: 50%;
            background: rgba(255, 255, 255, .93);
            box-shadow: 0 3px 10px rgba(59, 7, 24, .26);
            opacity: 0;
            transform: scale(.8);
            transition: opacity .25s ease, transform .25s var(--ease-out);
            pointer-events: none;
        }

        .person-card:hover .person-zoom,
        .person-photo:focus-visible .person-zoom {
            opacity: 1;
            transform: none;
        }

        @media (hover: none) {
            .person-zoom {
                opacity: .95;
                transform: none;
            }
        }

        .photo-viewer {
            position: fixed;
            inset: 0;
            z-index: 600;
            display: grid;
            place-items: center;
            padding: 72px 84px;
            background: rgba(4, 20, 36, .88);
            -webkit-backdrop-filter: blur(6px);
            backdrop-filter: blur(6px);
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: opacity .25s ease, visibility .25s;
        }

        .photo-viewer[aria-hidden="false"] {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
        }

        .photo-viewer-dialog {
            width: 100%;
            display: grid;
            place-items: center;
        }

        .photo-viewer-figure {
            width: min(460px, 100%);
            margin: 0;
            overflow: hidden;
            border-radius: 18px;
            background: #fff;
            box-shadow: 0 40px 90px rgba(0, 0, 0, .45);
            transform: translateY(16px) scale(.97);
            transition: transform .3s var(--ease-out);
        }

        .photo-viewer[aria-hidden="false"] .photo-viewer-figure {
            transform: none;
        }

        .photo-viewer-figure img {
            width: 100%;
            height: auto;
            max-height: calc(100vh - 250px);
            display: block;
            object-fit: contain;
            background: var(--blue-100);
        }

        .photo-viewer-figure figcaption {
            padding: 16px 20px 18px;
            text-align: center;
        }

        .photo-viewer-figure strong {
            display: block;
            color: var(--blue-950);
            font-size: 18px;
            line-height: 1.3;
        }

        .photo-viewer-figure span {
            display: block;
            margin-top: 5px;
            color: #66788a;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .photo-viewer-btn {
            position: fixed;
            z-index: 1;
            width: 46px;
            height: 46px;
            display: grid;
            place-items: center;
            padding: 0;
            color: #fff;
            border: 1px solid rgba(255, 255, 255, .28);
            border-radius: 50%;
            background: rgba(255, 255, 255, .1);
            cursor: pointer;
            transition: background .2s ease, transform .2s var(--ease-out);
        }

        .photo-viewer-btn:hover {
            background: rgba(255, 255, 255, .22);
        }

        .photo-viewer-close {
            top: 18px;
            right: 18px;
        }

        .photo-viewer-prev,
        .photo-viewer-next {
            top: 50%;
            margin-top: -23px;
        }

        .photo-viewer-prev {
            left: 20px;
        }

        .photo-viewer-next {
            right: 20px;
        }

        .photo-viewer-prev:hover {
            transform: translateX(-3px);
        }

        .photo-viewer-next:hover {
            transform: translateX(3px);
        }

        .photo-viewer-count {
            position: fixed;
            top: 30px;
            left: 50%;
            margin: 0;
            color: rgba(255, 255, 255, .75);
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .08em;
            transform: translateX(-50%);
        }

        @media (max-width: 540px) {
            .photo-viewer {
                padding: 70px 16px 92px;
            }

            .photo-viewer-figure img {
                max-height: calc(100vh - 262px);
            }

            .photo-viewer-prev,
            .photo-viewer-next {
                top: auto;
                bottom: 24px;
                margin-top: 0;
            }

            .photo-viewer-prev {
                left: calc(50% - 58px);
            }

            .photo-viewer-next {
                right: calc(50% - 58px);
            }
        }

        @media (max-width: 1040px) {
            .brand-copy small {
                display: none;
            }

            .site-nav a {
                padding-inline: 9px;
            }

            .site-nav a:not(.nav-login)::after {
                left: 9px;
                right: 9px;
            }

            .hero-grid {
                grid-template-columns: 1fr .84fr;
                gap: 40px;
            }

            .hero-chip-a {
                left: -10px;
            }

            .hero-chip-b {
                right: -8px;
            }

            .stat {
                padding-inline: 22px;
            }

            .people-grid {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }

            .consultants-block .person-card {
                flex-basis: calc((100% - 48px) / 4);
            }
        }

        @media (max-width: 820px) {
            .container {
                width: min(100% - 30px, 680px);
            }

            main section[id] {
                scroll-margin-top: 84px;
            }

            .menu-toggle {
                display: grid;
            }

            .site-nav {
                position: fixed;
                inset: 122px 0 auto;
                display: none;
                flex-direction: column;
                align-items: stretch;
                padding: 18px 20px 24px;
                border-bottom: 1px solid var(--line);
                background: #fff;
                box-shadow: 0 20px 30px rgba(59, 7, 24, .12);
            }

            .site-nav.open {
                display: flex;
            }

            .site-nav a {
                padding: 12px;
            }

            .site-nav .nav-login {
                margin: 5px 0 0;
                text-align: center;
            }

            .site-nav a:not(.nav-login)::after {
                display: none;
            }

            .hero-grid {
                grid-template-columns: minmax(0, 1fr);
                gap: 56px;
                padding-block: 64px 128px;
            }

            .hero-copy {
                text-align: center;
            }

            .hero-actions,
            .public-note {
                justify-content: center;
            }

            .hero-tagline,
            .hero-lead {
                margin-inline: auto;
            }

            .hero-visual {
                width: min(100%, 480px);
                margin-inline: auto;
            }

            .stat-grid {
                grid-template-columns: 1fr;
            }

            .stat {
                min-height: 96px;
                border-right: 0;
                border-bottom: 1px solid var(--line);
            }

            .stat:last-child {
                border-bottom: 0;
            }

            .mission-grid {
                grid-template-columns: 1fr;
                gap: 38px;
            }

            .mission-quote {
                position: relative;
                top: auto;
            }

            .steps {
                grid-template-columns: 1fr;
                gap: 18px;
            }

            .steps::before,
            .steps::after {
                top: 32px;
                bottom: 32px;
                left: 31px;
                right: auto;
                width: 2px;
                height: auto;
            }

            .steps::after {
                transform: scaleY(0);
                transform-origin: 50% 0;
            }

            .steps.is-visible::after,
            html:not(.has-js) .steps::after {
                transform: scaleY(1);
            }

            .step {
                flex-direction: row;
                align-items: flex-start;
                gap: 16px;
                text-align: left;
            }

            .step-marker {
                margin-bottom: 0;
            }

            .people-grid,
            .people-grid.developers-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .top-management-block .people-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                width: min(100%, 520px);
            }

            .consultants-block .person-card {
                flex-basis: calc((100% - 32px) / 3);
            }

            .people-block-head {
                align-items: flex-start;
                flex-direction: column;
                gap: 7px;
            }

            .cta-card {
                flex-direction: column;
                align-items: flex-start;
            }

            .footer-grid {
                grid-template-columns: 1fr;
                gap: 30px;
            }
        }

        @media (max-width: 540px) {
            .government-bar .container {
                justify-content: center;
            }

            .government-bar {
                font-size: 11px;
            }

            .masthead {
                min-height: 78px;
                gap: 10px;
            }

            .brand {
                gap: 8px;
            }

            .brand img {
                width: 52px;
                height: 52px;
            }

            .brand-copy strong {
                max-width: 185px;
                font-size: 14px;
            }

            .brand-copy span {
                max-width: 185px;
                font-size: 9px;
            }

            .site-nav {
                top: 112px;
            }

            .hero-grid {
                gap: 44px;
                padding-block: 48px 124px;
            }

            .hero h1 {
                font-size: clamp(38px, 11vw, 46px);
            }

            .hero-tagline {
                font-size: 13px;
            }

            .hero-lead {
                font-size: 16px;
            }

            .hero-actions .button {
                width: 100%;
            }

            .impact-panel {
                padding: 24px 18px;
            }

            .impact-panel h2 {
                font-size: 25px;
            }

            .impact-flow {
                gap: 4px;
            }

            .impact-stage {
                padding-inline: 4px;
            }

            .hero-chip {
                display: none;
            }

            .login-card {
                padding: 25px 20px;
            }

            .login-card::after {
                display: none;
            }

            .portal-close {
                top: 13px;
                right: 13px;
            }

            .section {
                padding-block: 70px;
            }

            .stat-band {
                margin-top: -88px;
            }

            .stat {
                padding: 20px;
            }

            .stat strong {
                font-size: 28px;
            }

            .mission-quote {
                padding: 34px 24px 26px;
            }

            .mission-quote blockquote {
                font-size: 21px;
            }

            .principle {
                grid-template-columns: 44px 1fr;
                gap: 14px;
                padding: 18px;
            }

            .principle-icon {
                width: 44px;
                height: 44px;
            }

            .step-card {
                padding: 20px;
            }

            .people-directory {
                margin-top: 46px;
            }

            .people-block+.people-block {
                margin-top: 40px;
            }

            .people-grid,
            .people-grid.developers-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 11px;
            }

            .consultants-block .person-card {
                flex-basis: calc((100% - 11px) / 2);
            }

            .cta {
                padding-bottom: 70px;
            }

            .cta-card {
                padding: 36px 22px 26px;
            }

            .cta .button {
                width: 100%;
            }

            .copyright {
                flex-direction: column;
            }

            .to-top {
                right: 14px;
                bottom: 14px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            html {
                scroll-behavior: auto;
            }

            *,
            *::before,
            *::after {
                transition-duration: .01ms !important;
                animation-duration: .01ms !important;
                animation-iteration-count: 1 !important;
                animation-delay: 0s !important;
            }

            .has-js .reveal,
            .has-js .reveal:not(.is-visible),
            .has-js .reveal:not(.is-visible) .person-photo img,
            .has-js .reveal:not(.is-visible) .person-placeholder,
            .has-js .reveal:not(.is-visible) .person-badge,
            .has-js .reveal:not(.is-visible) .person-info,
            .has-js .steps.reveal:not(.is-visible) .step {
                opacity: 1;
                transform: none;
                transition: none;
            }

            .steps::after {
                transform: none !important;
            }

            .person-card:hover .person-photo img {
                transform: none;
            }
        }
        #downloads { scroll-margin-top: 110px; background: #f5f7fb; }
        #downloads h2 { margin: 12px 0; }
        #downloads .button { margin-top: 12px; }
        #downloads .button:disabled { opacity: .65; cursor: default; }
        .download-options { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 20px; margin: 24px 0; }
        .download-options article { padding: 24px; border: 1px solid #dce4ec; border-radius: 12px; background: #fff; }
        .download-options h3 { margin: 0 0 12px; }
        @media (max-width: 760px) { .download-options { grid-template-columns: 1fr; } }
    </style>
    <script>
        document.documentElement.className += ' has-js';
    </script>
<?php $this->load->view('templates/pwa'); ?>
</head>

<body>
    <a class="skip-link" href="#main-content">Skip to main content</a>
    <div class="government-bar">
        <div class="container">
            <p class="utility-text">Department of Education · Regional Office XI</p>
        </div>
    </div>
    <header class="site-header">
        <div class="container masthead">
            <a class="brand" href="<?= base_url('homepage'); ?>" aria-label="CLMD Learning Management Portal home">
                <img src="<?= html_escape($seal_url); ?>" alt="Department of Education Region XI seal" width="62" height="62" decoding="async">
                <span class="brand-copy"><small>Republic of the Philippines</small><strong>Department of Education</strong><span>CLMD · Regional Office XI</span></span>
            </a>
            <button class="menu-toggle" id="menuToggle" type="button" aria-controls="siteNav" aria-expanded="false" aria-label="Open navigation menu"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M4 7h16M4 12h16M4 17h16" />
                </svg></button>
            <nav class="site-nav" id="siteNav" aria-label="Main navigation"><a href="#about">About</a><a href="#process">Data-to-action</a><a href="#downloads">Downloads</a><a href="<?= base_url('signup'); ?>">School Account Signup</a><a class="nav-login" href="#portal" data-open-login>Sign in</a></nav>
        </div>
    </header>
    <?php if (!empty($page_success)) : ?><div class="page-message" role="status"><?= html_escape($page_success); ?></div><?php endif; ?>

    <main id="main-content">
        <section class="hero" aria-labelledby="hero-title">
            <span class="hero-orb hero-orb-a" aria-hidden="true"></span>
            <span class="hero-orb hero-orb-b" aria-hidden="true"></span>
            <div class="container hero-grid">
                <div class="hero-copy">
                    <div class="eyebrow">All Learning Areas · Region XI</div>
                    <h1 id="hero-title">CLMD <span class="hero-accent">REGION XI</span></h1>
                    <p class="hero-tagline">Connecting curriculum, learning evidence, and support across every learning area.</p>
                    <p class="hero-lead">A regional platform for schools and education leaders to monitor learner progress, identify least learned competencies across all learning areas, and coordinate timely, targeted instructional support.</p>
                    <div class="hero-actions">
                        <a class="button button-gold" href="#about">Explore the CLMD portal <span class="button-arrow" aria-hidden="true">→</span></a>
                        <a class="button button-ghost" href="#portal" data-open-login>Sign in to the portal</a>
                    </div>
                    <p class="public-note"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z" />
                            <path d="m9 12 2 2 4-4" />
                        </svg>An official learning monitoring initiative of DepEd Regional Office XI</p>
                </div>
                <div class="hero-visual">
                    <div class="hero-chip hero-chip-a" aria-hidden="true">
                        <span class="hero-chip-icon"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                <circle cx="11" cy="11" r="7" />
                                <path d="m20 20-4-4" />
                            </svg></span>
                        <span>Least learned competencies<small>Flagged for focused support</small></span>
                    </div>
                    <aside class="impact-panel" aria-label="CLMD data-to-action overview">
                        <div class="impact-top"><span>Regional learning intelligence</span><img class="impact-seal" src="<?= html_escape($seal_url); ?>" alt="" width="58" height="58" decoding="async"></div>
                        <h2>Learning evidence in motion</h2>
                        <p>A shared system for timely, focused, and accountable instructional support.</p>
                        <div class="impact-flow" aria-hidden="true">
                            <div class="impact-stage">
                                <div><strong>01</strong><span>Identify</span></div>
                            </div>
                            <div class="impact-arrow">→</div>
                            <div class="impact-stage">
                                <div><strong>02</strong><span>Prioritize</span></div>
                            </div>
                            <div class="impact-arrow">→</div>
                            <div class="impact-stage">
                                <div><strong>03</strong><span>Respond</span></div>
                            </div>
                        </div>
                        <div class="impact-footer"><span>One regional network</span><strong><?= (int) $division_count; ?> SDOs connected</strong></div>
                    </aside>
                    <div class="hero-chip hero-chip-b" aria-hidden="true">
                        <span class="hero-chip-icon"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                <circle cx="12" cy="12" r="8" />
                                <circle cx="12" cy="12" r="3.5" />
                            </svg></span>
                        <span>Targeted assistance<small>Guided by the evidence</small></span>
                    </div>
                </div>
            </div>
        </section>

        <section class="stat-band" aria-label="CLMD portal at a glance">
            <div class="container">
                <div class="stat-grid reveal reveal-once">
                    <div class="stat">
                        <span class="stat-icon" aria-hidden="true"><svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M3 21h18M6 18V9m4 9V9m4 9V9m4 9V9M4 6l8-4 8 4v3H4V6Z" />
                            </svg></span>
                        <div><strong><span class="sr-only"><?= (int) $division_count; ?></span><span data-count="<?= (int) $division_count; ?>" aria-hidden="true"><?= (int) $division_count; ?></span></strong><span>Schools Division Offices connected across Region XI</span></div>
                    </div>
                    <div class="stat">
                        <span class="stat-icon" aria-hidden="true"><svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M4 19V9m6 10V5m6 14v-7m4 7H2" />
                            </svg></span>
                        <div><strong>All</strong><span>Learning areas represented in a shared regional view</span></div>
                    </div>
                    <div class="stat">
                        <span class="stat-icon" aria-hidden="true"><svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <circle cx="12" cy="12" r="9" />
                                <path d="m12 7 1.6 3.4L17 12l-3.4 1.6L12 17l-1.6-3.4L7 12l3.4-1.6L12 7Z" />
                            </svg></span>
                        <div><strong>Action</strong><span>Targeted support guided by the needs shown in the data</span></div>
                    </div>
                </div>
            </div>
        </section>

        <section class="section" id="about">
            <div class="container mission-grid">
                <div class="mission-quote reveal reveal-once">
                    <blockquote>Every learning data point should lead to a clearer decision and a better response for learners.</blockquote>
                    <p>The CLMD commitment</p>
                </div>
                <div>
                    <div class="section-heading reveal reveal-once">
                        <p class="kicker">What the CLMD portal does</p>
                        <h2>From classroom evidence to focused regional support</h2>
                        <p>The Curriculum Learning Management Division (CLMD) portal supports learner progress monitoring and identifies least learned competencies across all learning areas. Schools select the learning areas configured for their grade level and division, while shared evidence helps Schools Division Offices and the Regional Office provide targeted technical assistance, interventions, and recommendations.</p>
                    </div>
                    <div class="principles reveal reveal-once">
                        <article class="principle">
                            <div class="principle-icon"><svg width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path d="M4 19V9m6 10V5m6 14v-7m4 7H2" />
                                </svg></div>
                            <div>
                                <h3>Timely identification of learning gaps</h3>
                                <p>Structured monitoring surfaces competency gaps and areas that require immediate instructional support.</p>
                            </div>
                        </article>
                        <article class="principle">
                            <div class="principle-icon"><svg width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <circle cx="11" cy="11" r="7" />
                                    <path d="m20 20-4-4m-5-8v6m-3-3h6" />
                                </svg></div>
                            <div>
                                <h3>Targeted, data-driven assistance</h3>
                                <p>SDOs and the Regional Office can focus technical assistance and interventions where they are most needed.</p>
                            </div>
                        </article>
                        <article class="principle">
                            <div class="principle-icon"><svg width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path d="m4 14 6-6 4 4 6-7" />
                                    <path d="M14 5h6v6" />
                                    <path d="M4 20h16" />
                                </svg></div>
                            <div>
                                <h3>More informed education decisions</h3>
                                <p>Schools and education leaders use shared evidence to improve teaching and learner outcomes across all learning areas.</p>
                            </div>
                        </article>
                    </div>
                </div>
            </div>
        </section>

        <section class="section section-soft" id="process">
            <div class="container">
                <div class="section-heading center reveal reveal-once">
                    <p class="kicker">The data-to-action cycle</p>
                    <h2>A simple path from learning gaps to better outcomes</h2>
                    <p>The platform supports a repeatable cycle of evidence gathering, collaborative analysis, and targeted response.</p>
                </div>
                <div class="steps reveal reveal-once">
                    <article class="step">
                        <div class="step-marker" aria-hidden="true"><span class="step-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="5" y="4" width="14" height="17" rx="2" />
                                    <path d="M9 4h6v3H9zM9 12h6M9 16h4" />
                                </svg></span></div>
                        <div class="step-card">
                            <p class="step-label">Step 01</p>
                            <h3>Collect learning evidence</h3>
                            <p>Schools record least learned competencies through a common and structured monitoring process.</p>
                        </div>
                    </article>
                    <article class="step">
                        <div class="step-marker" aria-hidden="true"><span class="step-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M4 19V9m6 10V5m6 14v-7m4 7H2" />
                                </svg></span></div>
                        <div class="step-card">
                            <p class="step-label">Step 02</p>
                            <h3>Understand the pattern</h3>
                            <p>Division and regional views help leaders identify shared needs, local differences, and areas of priority.</p>
                        </div>
                    </article>
                    <article class="step">
                        <div class="step-marker" aria-hidden="true"><span class="step-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="m4 14 6-6 4 4 6-7" />
                                    <path d="M14 5h6v6" />
                                </svg></span></div>
                        <div class="step-card">
                            <p class="step-label">Step 03</p>
                            <h3>Act and improve</h3>
                            <p>Findings inform responsive interventions, technical assistance, and follow-through across learning areas.</p>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        <section class="cta">
            <div class="container">
                <div class="cta-card reveal reveal-once">
                    <div>
                        <h2>Ready to turn learning evidence into action?</h2>
                        <p>Authorized school, district, division, and regional personnel may access the CLMD Learning Management Portal.</p>
                    </div>
                    <a class="button button-gold" href="#portal" data-open-login>Proceed to sign in <span class="button-arrow" aria-hidden="true">→</span></a>
                </div>
            </div>
        </section>
        <section class="section" id="downloads" aria-labelledby="downloads-title">
            <div class="container">
                <div class="eyebrow">CLMD Portal on your device</div>
                <h2 id="downloads-title">Download &amp; install</h2>
                <p>Install CLMD Portal for quick access from your home screen or desktop. Installation happens through your browser; no separate installer file is needed.</p>
                <button type="button" class="button button-gold" data-pwa-install aria-describedby="pwa-install-status" aria-controls="pwa-install-help">How to install</button>
                <p id="pwa-install-status" role="status" data-pwa-status>Use the instructions below if your browser does not show an installation prompt.</p>
                <div id="pwa-install-help" data-pwa-help tabindex="-1" hidden>
                    <h3>Install on your device</h3>
                    <p><strong>Firefox on Mac:</strong> Web app installation is not supported. Use the portal in Firefox, bookmark it with <strong>Command+D</strong>, or open this address in Chrome or Safari to install.</p>
                    <p><strong>Mac with Safari:</strong> On macOS Sonoma 14 or later, choose <strong>File &gt; Add to Dock</strong>, then click <strong>Add</strong>. This step must be done from Safari’s menu.</p>
                    <p><strong>Chrome on Mac or Windows:</strong> Open the <strong>three-dot menu &gt; Cast, save, and share &gt; Install page as app</strong>. If already installed, enter <strong>chrome://apps</strong> in the address bar to open it. In Edge, use <strong>Menu &gt; Apps &gt; Install this site as an app</strong>.</p>
                    <p><strong>iPhone or iPad:</strong> In Safari, use <strong>Share &gt; Add to Home Screen</strong>.</p>
                </div>
                <noscript><p>Use your browser menu to install. In Safari on macOS Sonoma 14 or later, choose File &gt; Add to Dock.</p></noscript>
                <div class="download-options">
                    <article><h3>Android</h3><p>Open this page in Chrome, then use <strong>Install CLMD Portal</strong> when available. You can also look for <strong>Install app</strong> or <strong>Add to Home screen</strong> in the browser menu.</p></article>
                    <article><h3>iPhone &amp; iPad</h3><p>Open this page in Safari. Tap <strong>Share</strong>, choose <strong>Add to Home Screen</strong>, then tap <strong>Add</strong>.</p></article>
                    <article><h3>Mac &amp; Windows</h3><p><strong>Safari on Mac:</strong> With macOS Sonoma 14 or later, choose <strong>File &gt; Add to Dock</strong>, then <strong>Add</strong>.</p><p>Open this page in Chrome or Edge. Click <strong>Install CLMD Portal</strong> when available, or use the install option in the address bar or browser menu.</p></article>
                </div>
                <p>Internet access is required for signing in, viewing reports, and submitting forms.</p>
            </div>
        </section>

    </main>

    <footer class="site-footer">
        <div class="container">
            <div class="footer-grid">
                <div>
                    <div class="footer-brand"><img src="<?= html_escape($seal_url); ?>" alt="Department of Education Region XI seal" width="60" height="60" loading="lazy" decoding="async">
                        <div><strong>CLMD Learning Management Portal</strong><span>Department of Education · Regional Office XI</span></div>
                    </div>
                    <p class="footer-copy">The CLMD portal supports the responsible use of learning data across all learning areas for informed decisions, focused assistance, and improved learner outcomes across the Davao Region.</p>
                </div>
                <nav class="footer-links" aria-label="Footer navigation"><strong>Quick links</strong><a href="#about">About the CLMD portal</a><a href="#process">Data-to-action cycle</a><a href="#downloads">Download / install app</a><a href="#portal" data-open-login>Portal access</a></nav>
            </div>
            <div class="copyright"><span>© <?= date('Y'); ?> Department of Education Regional Office XI. All rights reserved.</span><span><?= html_escape($region_name); ?></span></div>
        </div>
    </footer>

    <button class="to-top" id="toTop" type="button" aria-label="Back to top"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
            <path d="m6 15 6-6 6 6" />
        </svg></button>

    <div class="photo-viewer" id="photoViewer" aria-hidden="true">
        <div class="photo-viewer-dialog" role="dialog" aria-modal="true" aria-labelledby="photoViewerName">
            <p class="photo-viewer-count" id="photoViewerCount" aria-live="polite"></p>
            <button class="photo-viewer-btn photo-viewer-close" id="photoViewerClose" type="button" aria-label="Close photo"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                    <path d="m6 6 12 12M18 6 6 18" />
                </svg></button>
            <button class="photo-viewer-btn photo-viewer-prev" id="photoViewerPrev" type="button" aria-label="Previous photo"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                    <path d="m15 18-6-6 6-6" />
                </svg></button>
            <figure class="photo-viewer-figure">
                <img id="photoViewerImg" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" alt="">
                <figcaption><strong id="photoViewerName"></strong><span id="photoViewerRole"></span></figcaption>
            </figure>
            <button class="photo-viewer-btn photo-viewer-next" id="photoViewerNext" type="button" aria-label="Next photo"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                    <path d="m9 18 6-6-6-6" />
                </svg></button>
        </div>
    </div>

    <div class="portal-overlay" id="portalModal" aria-hidden="<?= $open_login_modal ? 'false' : 'true'; ?>">
        <div class="portal-dialog" role="dialog" aria-modal="true" aria-labelledby="login-title">
            <section class="login-card">
                <button class="portal-close" id="portalClose" type="button" aria-label="Close portal sign in"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="m6 6 12 12M18 6 6 18" />
                    </svg></button>
                <div class="portal-panel" id="portalSignin">
                    <div class="login-icon" aria-hidden="true"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21a8 8 0 0 0-16 0" />
                            <circle cx="12" cy="7" r="4" />
                        </svg></div>
                    <h2 id="login-title">Portal access</h2>
                    <p class="login-intro">Sign in using your authorized CLMD portal account.</p>
                    <?php if (!empty($login_failed)) : ?><div class="alert alert-danger" role="alert"><?= html_escape($login_failed); ?></div><?php endif; ?>
                    <?= $login_validation_errors; ?>
                    <?= form_open('log_in', array('id' => 'portalLoginForm')); ?>
                    <div class="field"><label for="username">School ID, username or email address</label>
                        <div class="input-wrap"><input id="username" name="username" type="text" value="<?= html_escape(set_value('username')); ?>" autocomplete="username" required><span class="input-icon" aria-hidden="true"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M20 21a8 8 0 0 0-16 0" />
                                    <circle cx="12" cy="7" r="4" />
                                </svg></span></div>
                    </div>
                    <div class="field"><label for="password">Password</label>
                        <div class="input-wrap"><input id="password" name="password" type="password" autocomplete="current-password" required><button class="password-toggle" type="button" id="togglePassword" aria-controls="password" aria-pressed="false">SHOW</button></div>
                    </div>
                    <button class="button button-primary login-submit" id="loginSubmit" type="submit"><span class="button-label">Sign in securely</span><span class="button-spinner" aria-hidden="true"></span></button>
                    <?= form_close(); ?>
                    <p class="login-help">New school? <a href="<?= base_url('signup'); ?>">Create a School Account</a></p>
                    <p class="login-help"><a href="<?= base_url('Pages/forgot_password'); ?>" data-portal-view="reset">Forgot your password?</a><br>For account concerns, contact your division system administrator.</p>
                </div>

                <div class="portal-panel" id="portalReset" hidden>
                    <div class="login-icon" aria-hidden="true"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="11" width="18" height="10" rx="2" />
                            <path d="M8 11V7a4 4 0 0 1 8 0v4" />
                        </svg></div>
                    <h2 id="reset-title">Reset password</h2>
                    <p class="login-intro">Enter the email address registered to your CLMD portal account. A new password will be sent to it.</p>
                    <div class="alert" id="resetFeedback" role="status" aria-live="polite" hidden></div>
                    <form id="portalResetForm" action="<?= base_url('Pages/forgot_password'); ?>" method="post">
                        <input type="hidden" name="<?= html_escape($this->security->get_csrf_token_name()); ?>" value="<?= html_escape($this->security->get_csrf_hash()); ?>">
                        <div class="field"><label for="reset-email">Email address</label>
                            <div class="input-wrap"><input id="reset-email" name="email" type="email" autocomplete="email" placeholder="name@deped.gov.ph" required><span class="input-icon" aria-hidden="true"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="3" y="5" width="18" height="14" rx="2" />
                                        <path d="m3 7 9 6 9-6" />
                                    </svg></span></div>
                        </div>
                        <button class="button button-primary login-submit" id="resetSubmit" type="submit"><span class="button-label">Send new password</span><span class="button-spinner" aria-hidden="true"></span></button>
                    </form>
                    <p class="login-help"><a href="#portal" data-portal-view="signin">&larr; Back to sign in</a><br>For account concerns, contact your division system administrator.</p>
                </div>
            </section>
        </div>
    </div>

    <script>
        (function() {
            var menuButton = document.getElementById('menuToggle'),
                navigation = document.getElementById('siteNav');
            var passwordButton = document.getElementById('togglePassword'),
                passwordField = document.getElementById('password');
            var modal = document.getElementById('portalModal'),
                closeButton = document.getElementById('portalClose');
            var loginForm = document.getElementById('portalLoginForm'),
                loginSubmit = document.getElementById('loginSubmit');
            var signinPanel = document.getElementById('portalSignin'),
                resetPanel = document.getElementById('portalReset'),
                portalDialog = modal ? modal.querySelector('.portal-dialog') : null;
            var resetForm = document.getElementById('portalResetForm'),
                resetSubmit = document.getElementById('resetSubmit'),
                resetFeedback = document.getElementById('resetFeedback');
            var lastModalTrigger = null;
            if (menuButton && navigation) {
                menuButton.addEventListener('click', function() {
                    var isOpen = navigation.classList.toggle('open');
                    menuButton.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                    menuButton.setAttribute('aria-label', isOpen ? 'Close navigation menu' : 'Open navigation menu');
                    document.body.classList.toggle('menu-open', isOpen);
                });
                navigation.querySelectorAll('a').forEach(function(link) {
                    link.addEventListener('click', function() {
                        navigation.classList.remove('open');
                        menuButton.setAttribute('aria-expanded', 'false');
                        document.body.classList.remove('menu-open');
                    });
                });
            }
            if (passwordButton && passwordField) passwordButton.addEventListener('click', function() {
                var show = passwordField.type === 'password';
                passwordField.type = show ? 'text' : 'password';
                passwordButton.textContent = show ? 'HIDE' : 'SHOW';
                passwordButton.setAttribute('aria-pressed', show ? 'true' : 'false');
            });

            // The portal card carries two views - sign in and reset password - so neither one
            // sends the visitor off the homepage.
            function showPortalView(view) {
                var isReset = view === 'reset';
                if (signinPanel && resetPanel) {
                    signinPanel.hidden = isReset;
                    resetPanel.hidden = !isReset;
                    if (portalDialog) portalDialog.setAttribute('aria-labelledby', isReset ? 'reset-title' : 'login-title');
                }
                var field = document.getElementById(isReset ? 'reset-email' : 'username');
                if (field) window.setTimeout(function() {
                    field.focus();
                }, 100);
            }

            function openPortal(trigger, view) {
                if (!modal) return;
                lastModalTrigger = trigger || null;
                modal.setAttribute('aria-hidden', 'false');
                document.body.classList.add('modal-open');
                showPortalView(view || 'signin');
            }

            function closePortal() {
                if (!modal) return;
                modal.setAttribute('aria-hidden', 'true');
                document.body.classList.remove('modal-open');
                if (signinPanel && resetPanel) {
                    signinPanel.hidden = false;
                    resetPanel.hidden = true;
                    if (portalDialog) portalDialog.setAttribute('aria-labelledby', 'login-title');
                }
                if (resetFeedback) resetFeedback.hidden = true;
                if (lastModalTrigger) lastModalTrigger.focus();
            }
            document.querySelectorAll('[data-open-login]').forEach(function(trigger) {
                trigger.addEventListener('click', function(event) {
                    event.preventDefault();
                    openPortal(trigger);
                });
            });
            // Anchors keep their real href, so without JS they still land on the standalone page.
            document.querySelectorAll('[data-portal-view]').forEach(function(link) {
                link.addEventListener('click', function(event) {
                    event.preventDefault();
                    if (resetFeedback) resetFeedback.hidden = true;
                    showPortalView(link.getAttribute('data-portal-view'));
                });
            });

            function setResetFeedback(ok, message) {
                if (!resetFeedback) return;
                resetFeedback.className = 'alert ' + (ok ? 'alert-success' : 'alert-danger');
                resetFeedback.textContent = message;
                resetFeedback.hidden = false;
            }

            function setResetLoading(busy) {
                if (!resetSubmit) return;
                resetSubmit.classList.toggle('is-loading', busy);
                resetSubmit.disabled = busy;
                if (busy) resetSubmit.setAttribute('aria-busy', 'true');
                else resetSubmit.removeAttribute('aria-busy');
            }

            // Same controller the standalone page posts to; it answers JSON for XHR, so a wrong
            // address is reported in place instead of costing a full round trip.
            if (resetForm && window.fetch) resetForm.addEventListener('submit', function(event) {
                var email = document.getElementById('reset-email');
                if (email && !email.value.trim()) return; // let the browser raise its own prompt
                event.preventDefault();
                setResetLoading(true);
                fetch(resetForm.action, {
                    method: 'POST',
                    body: new FormData(resetForm),
                    credentials: 'same-origin',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                }).then(function(response) {
                    return response.text();
                }).then(function(text) {
                    var payload = null;
                    try {
                        payload = JSON.parse(text);
                    } catch (error) {
                        payload = null;
                    }
                    setResetLoading(false);
                    if (!payload) {
                        setResetFeedback(false, 'Something went wrong on our end. Please try again.');
                        return;
                    }
                    setResetFeedback(!!payload.success, payload.message || '');
                    if (payload.success) resetForm.reset();
                }).catch(function() {
                    setResetLoading(false);
                    setResetFeedback(false, 'We could not reach the server. Check your connection and try again.');
                });
            });

            if (closeButton) closeButton.addEventListener('click', closePortal);
            if (modal) modal.addEventListener('click', function(event) {
                if (event.target === modal) closePortal();
            });
            document.addEventListener('keydown', function(event) {
                if (!modal || modal.getAttribute('aria-hidden') !== 'false') return;
                if (event.key === 'Escape') closePortal();
                if (event.key === 'Tab') {
                    var focusable = Array.prototype.filter.call(
                        modal.querySelectorAll('button:not([disabled]), input:not([disabled]), a[href]'),
                        function(node) {
                            return node.offsetParent !== null; // skips whichever panel is hidden
                        }
                    );
                    if (!focusable.length) return;
                    var first = focusable[0],
                        last = focusable[focusable.length - 1];
                    if (event.shiftKey && document.activeElement === first) {
                        event.preventDefault();
                        last.focus();
                    } else if (!event.shiftKey && document.activeElement === last) {
                        event.preventDefault();
                        first.focus();
                    }
                }
            });
            if (loginForm && loginSubmit) loginForm.addEventListener('submit', function() {
                loginSubmit.classList.add('is-loading');
                loginSubmit.disabled = true;
                loginSubmit.setAttribute('aria-busy', 'true');
            });
            if (modal && modal.getAttribute('aria-hidden') === 'false') {
                document.body.classList.add('modal-open');
                window.setTimeout(function() {
                    document.getElementById('username').focus();
                }, 100);
            }
            if (window.location.hash === '#portal') openPortal(null);
            else if (window.location.hash === '#forgot-password') openPortal(null, 'reset');

            var motionQuery = window.matchMedia ? window.matchMedia('(prefers-reduced-motion: reduce)') : null;
            var reduceMotion = !!(motionQuery && motionQuery.matches);

            // Scroll-linked chrome (header shadow, back-to-top) shares one rAF-throttled listener.
            var siteHeader = document.querySelector('.site-header'),
                toTop = document.getElementById('toTop'),
                scrollTicking = false;

            function updateScrollChrome() {
                var y = window.pageYOffset || document.documentElement.scrollTop;
                if (siteHeader) siteHeader.classList.toggle('is-scrolled', y > 8);
                if (toTop) toTop.classList.toggle('is-shown', y > 700);
                scrollTicking = false;
            }
            window.addEventListener('scroll', function() {
                if (!scrollTicking) {
                    scrollTicking = true;
                    window.requestAnimationFrame(updateScrollChrome);
                }
            }, {
                passive: true
            });
            window.addEventListener('resize', updateScrollChrome);
            updateScrollChrome();
            if (toTop) toTop.addEventListener('click', function() {
                window.scrollTo({
                    top: 0,
                    behavior: reduceMotion ? 'auto' : 'smooth'
                });
            });

            // Scroll spy: highlight the nav link for the section crossing the middle of the viewport.
            // The hero is observed too, so returning to the top clears the highlight.
            var navLinks = navigation ? [].slice.call(navigation.querySelectorAll('a[href^="#"]:not([data-open-login])')) : [];
            if ('IntersectionObserver' in window && navLinks.length) {
                var spyObserver = new IntersectionObserver(function(entries) {
                    entries.forEach(function(entry) {
                        if (!entry.isIntersecting) return;
                        navLinks.forEach(function(link) {
                            var active = entry.target.id !== '' && link.getAttribute('href') === '#' + entry.target.id;
                            link.classList.toggle('is-active', active);
                            if (active) link.setAttribute('aria-current', 'location');
                            else link.removeAttribute('aria-current');
                        });
                    });
                }, {
                    rootMargin: '-45% 0px -50% 0px'
                });
                var heroSection = document.querySelector('.hero');
                if (heroSection) spyObserver.observe(heroSection);
                navLinks.forEach(function(link) {
                    var target = document.querySelector(link.getAttribute('href'));
                    if (target) spyObserver.observe(target);
                });
            }

            // Count-up for numeric stats. The real value is in the markup, so no-JS and reduced-motion
            // visitors simply see the final number.
            var counters = [].slice.call(document.querySelectorAll('[data-count]'));
            if (counters.length && !reduceMotion && 'IntersectionObserver' in window) {
                var countObserver = new IntersectionObserver(function(entries) {
                    entries.forEach(function(entry) {
                        if (!entry.isIntersecting) return;
                        countObserver.unobserve(entry.target);
                        var counter = entry.target,
                            target = parseInt(counter.getAttribute('data-count'), 10) || 0,
                            startTime = null;

                        function tick(now) {
                            if (startTime === null) startTime = now;
                            var progress = Math.min((now - startTime) / 1400, 1);
                            counter.textContent = Math.round(target * (1 - Math.pow(1 - progress, 3)));
                            if (progress < 1) window.requestAnimationFrame(tick);
                        }
                        window.requestAnimationFrame(tick);
                    });
                }, {
                    threshold: .6
                });
                counters.forEach(function(counter) {
                    counter.textContent = '0';
                    countObserver.observe(counter);
                });
            }

            // Portrait viewer: every card with a photo opens a larger copy; arrows, keys, and swipes step through them.
            var viewer = document.getElementById('photoViewer'),
                viewerDialog = viewer ? viewer.querySelector('.photo-viewer-dialog') : null,
                viewerImg = document.getElementById('photoViewerImg'),
                viewerName = document.getElementById('photoViewerName'),
                viewerRole = document.getElementById('photoViewerRole'),
                viewerCount = document.getElementById('photoViewerCount'),
                viewerClose = document.getElementById('photoViewerClose'),
                viewerPrev = document.getElementById('photoViewerPrev'),
                viewerNext = document.getElementById('photoViewerNext'),
                viewerIndex = 0,
                viewerTrigger = null;
            var portraits = [].slice.call(document.querySelectorAll('.person-photo img[data-full]')).map(function(img) {
                var card = img.closest('.person-card'),
                    name = card.querySelector('.person-info strong'),
                    role = card.querySelector('.person-info span');
                return {
                    img: img,
                    frame: img.closest('.person-photo'),
                    full: img.getAttribute('data-full') || img.src,
                    name: name ? name.textContent.trim() : '',
                    role: role ? role.textContent.trim() : ''
                };
            });

            function showPortrait(index) {
                viewerIndex = (index + portraits.length) % portraits.length;
                var person = portraits[viewerIndex];
                viewerImg.src = person.full;
                viewerImg.alt = person.name;
                viewerImg.style.objectPosition = person.img.style.objectPosition || 'center 12%';
                viewerName.textContent = person.name;
                viewerRole.textContent = person.role;
                viewerCount.textContent = (viewerIndex + 1) + ' / ' + portraits.length;
            }

            function openViewer(index, trigger) {
                viewerTrigger = trigger;
                showPortrait(index);
                viewer.setAttribute('aria-hidden', 'false');
                document.body.classList.add('modal-open');
                // Wait for the fade-in to start: a still-hidden dialog cannot take focus.
                window.setTimeout(function() {
                    viewerClose.focus();
                }, 60);
            }

            function closeViewer() {
                viewer.setAttribute('aria-hidden', 'true');
                if (!modal || modal.getAttribute('aria-hidden') !== 'false') document.body.classList.remove('modal-open');
                if (viewerTrigger) viewerTrigger.focus();
            }
            if (viewer && portraits.length) {
                portraits.forEach(function(person, index) {
                    var frame = person.frame;
                    frame.setAttribute('role', 'button');
                    frame.setAttribute('tabindex', '0');
                    frame.setAttribute('aria-label', 'View larger photo of ' + person.name);
                    frame.insertAdjacentHTML('beforeend', '<span class="person-zoom" aria-hidden="true"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4M11 8v6M8 11h6"/></svg></span>');
                    frame.addEventListener('click', function() {
                        openViewer(index, frame);
                    });
                    frame.addEventListener('keydown', function(event) {
                        if (event.key === 'Enter' || event.key === ' ') {
                            event.preventDefault();
                            openViewer(index, frame);
                        }
                    });
                });
                viewerClose.addEventListener('click', closeViewer);
                viewerPrev.addEventListener('click', function() {
                    showPortrait(viewerIndex - 1);
                });
                viewerNext.addEventListener('click', function() {
                    showPortrait(viewerIndex + 1);
                });
                viewer.addEventListener('click', function(event) {
                    if (event.target === viewer || event.target === viewerDialog) closeViewer();
                });
                document.addEventListener('keydown', function(event) {
                    if (viewer.getAttribute('aria-hidden') !== 'false') return;
                    if (event.key === 'Escape') closeViewer();
                    else if (event.key === 'ArrowLeft') showPortrait(viewerIndex - 1);
                    else if (event.key === 'ArrowRight') showPortrait(viewerIndex + 1);
                    else if (event.key === 'Tab') {
                        var controls = [viewerClose, viewerPrev, viewerNext],
                            position = controls.indexOf(document.activeElement);
                        event.preventDefault();
                        controls[(position + (event.shiftKey ? -1 : 1) + controls.length) % controls.length].focus();
                    }
                });
                var touchStartX = null;
                viewer.addEventListener('touchstart', function(event) {
                    touchStartX = event.touches[0].clientX;
                }, {
                    passive: true
                });
                viewer.addEventListener('touchend', function(event) {
                    if (touchStartX === null) return;
                    var deltaX = event.changedTouches[0].clientX - touchStartX;
                    if (Math.abs(deltaX) > 50) showPortrait(viewerIndex + (deltaX < 0 ? 1 : -1));
                    touchStartX = null;
                });
            }

            var revealItems = [].slice.call(document.querySelectorAll('.reveal'));
            if (!revealItems.length) return;

            function showAllReveals() {
                revealItems.forEach(function(element) {
                    element.classList.add('is-visible');
                });
            }
            if (!('IntersectionObserver' in window) || reduceMotion) {
                showAllReveals();
            } else {
                // Two thresholds give the toggle hysteresis: an item appears once it is meaningfully on
                // screen and only resets after it has left completely, so edge-of-viewport scrolling
                // cannot make it flicker. People cards stay observed so their reveal repeats in both
                // directions; section content marked .reveal-once animates the first time only.
                var revealObserver = new IntersectionObserver(function(entries) {
                    var entering = [],
                        minTop = Infinity,
                        minLeft = Infinity;
                    entries.forEach(function(entry) {
                        if (entry.intersectionRatio >= .12) {
                            entering.push(entry);
                            if (entry.boundingClientRect.top < minTop) minTop = entry.boundingClientRect.top;
                            if (entry.boundingClientRect.left < minLeft) minLeft = entry.boundingClientRect.left;
                        } else if (!entry.isIntersecting && !entry.target.classList.contains('reveal-once')) {
                            entry.target.classList.remove('is-visible');
                            entry.target.style.removeProperty('--reveal-delay');
                        }
                    });
                    // Stagger is measured from live layout rather than a baked-in index, so the cascade
                    // runs diagonally from the top-left of whatever actually came into view. A card that
                    // enters on its own starts at 0ms instead of inheriting a stale grid position.
                    entering.forEach(function(entry) {
                        var box = entry.boundingClientRect;
                        var delay = Math.min((box.top - minTop) * .3 + (box.left - minLeft) * .28, 620);
                        entry.target.style.setProperty('--reveal-delay', Math.round(delay) + 'ms');
                        entry.target.classList.add('is-visible');
                        if (entry.target.classList.contains('reveal-once')) revealObserver.unobserve(entry.target);
                    });
                }, {
                    threshold: [0, .12],
                    rootMargin: '0px 0px -6% 0px'
                });
                revealItems.forEach(function(element) {
                    revealObserver.observe(element);
                });
                if (motionQuery && motionQuery.addEventListener) {
                    motionQuery.addEventListener('change', function(event) {
                        if (event.matches) {
                            revealObserver.disconnect();
                            showAllReveals();
                        }
                    });
                }
            }
        }());
    </script>
</body>

</html>
