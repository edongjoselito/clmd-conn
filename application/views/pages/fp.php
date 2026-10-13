<?php
/*
 * Standalone password reset page.
 *
 * The homepage (views/home.php) carries this same form inside its portal modal, so most
 * visitors never land here. This page is the fallback for direct links, bookmarks and
 * browsers without JavaScript, and deliberately reuses the homepage's tokens, header and
 * card styling so the two never drift apart visually.
 */
$reset_failed = $this->session->flashdata('failed');
$reset_success = $this->session->flashdata('success');
$reset_errors = validation_errors();
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#3b0718">
    <meta name="robots" content="noindex">
    <meta name="description" content="Reset the password for your CLMD Learning Management Portal account.">
    <title>Reset password | CLMD Learning Management Portal</title>
    <link rel="icon" href="<?= base_url('assets/images/favicon.ico'); ?>">
    <style>
        :root {
            --blue-950: #3b0718;
            --blue-900: #5b1026;
            --blue-700: #922c43;
            --blue-50: #fbf4f5;
            --gold-500: #f0aa20;
            --ink: #172535;
            --muted: #5d6d7d;
            --line: #ead7dc;
            --shadow: 0 18px 50px rgba(59, 7, 24, .12);
            --ease-out: cubic-bezier(.16, 1, .3, 1);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            color: var(--ink);
            background: var(--blue-50);
            font-family: Inter, "Segoe UI", Arial, sans-serif;
            font-size: 16px;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
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
        }

        .government-bar p {
            margin: 0;
        }

        .site-header {
            border-bottom: 1px solid var(--line);
            background: rgba(255, 255, 255, .97);
        }

        .masthead {
            min-height: 88px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
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
            flex: 0 0 auto;
        }

        .brand-copy {
            display: grid;
            line-height: 1.3;
        }

        .brand-copy small {
            color: var(--muted);
            font-size: 11px;
            letter-spacing: .06em;
            text-transform: uppercase;
        }

        .brand-copy strong {
            color: var(--blue-950);
            font-size: 17px;
        }

        .brand-copy span {
            color: var(--blue-700);
            font-size: 12px;
            font-weight: 700;
        }

        .header-link {
            color: var(--blue-900);
            font-size: 13px;
            font-weight: 800;
            text-decoration: none;
            white-space: nowrap;
        }

        .header-link:hover {
            color: var(--blue-700);
        }

        main {
            flex: 1 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 56px 20px;
        }

        .auth-shell {
            width: 100%;
            max-width: 440px;
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

        .login-card h1 {
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

        .field {
            margin-bottom: 16px;
        }

        .field label {
            display: block;
            margin: 0 0 7px;
            color: #344a5e;
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

        .button {
            position: relative;
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
            transition: transform .2s var(--ease-out), background .2s ease;
        }

        .button:hover {
            transform: translateY(-2px);
        }

        .button-primary {
            color: #fff;
            background: var(--blue-900);
        }

        .button-primary:hover {
            background: var(--blue-700);
        }

        .login-submit {
            width: 100%;
            margin-top: 4px;
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

        .site-footer {
            padding-block: 22px;
            color: rgba(255, 255, 255, .74);
            background: #071f36;
            font-size: 12px;
        }

        .site-footer .container {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        @media (max-width: 560px) {
            .masthead {
                min-height: 74px;
                gap: 14px;
            }

            .brand img {
                width: 50px;
                height: 50px;
            }

            .brand-copy strong {
                font-size: 15px;
            }

            main {
                padding: 34px 16px;
            }

            .header-link {
                font-size: 12px;
            }

            .login-card {
                padding: 25px 20px;
            }

            .login-card::after {
                display: none;
            }
        }

        @media (prefers-reduced-motion: reduce) {

            *,
            *::before,
            *::after {
                transition-duration: .01ms !important;
                animation-duration: .01ms !important;
                animation-iteration-count: 1 !important;
            }
        }
    </style>
</head>

<body>
    <div class="government-bar">
        <div class="container">
            <p>Department of Education &middot; Regional Office XI</p>
        </div>
    </div>

    <header class="site-header">
        <div class="container masthead">
            <a class="brand" href="<?= base_url('homepage'); ?>" aria-label="CLMD Learning Management Portal home">
                <img src="<?= base_url('assets/r11-logo.jpg'); ?>" alt="Department of Education Region XI seal" width="62" height="62" decoding="async">
                <span class="brand-copy"><small>Republic of the Philippines</small><strong>Department of Education</strong><span>CLMD Learning Portal &middot; Regional Office XI</span></span>
            </a>
            <a class="header-link" href="<?= base_url('homepage'); ?>">&larr; Back to home</a>
        </div>
    </header>

    <main>
        <div class="auth-shell">
            <section class="login-card">
                <div class="login-icon" aria-hidden="true"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="11" width="18" height="10" rx="2" />
                        <path d="M8 11V7a4 4 0 0 1 8 0v4" />
                    </svg></div>
                <h1>Reset password</h1>
                <p class="login-intro">Enter the email address registered to your CLMD portal account. A new password will be sent to it.</p>

                <?php if (!empty($reset_failed)) : ?>
                    <div class="alert alert-danger" role="alert"><?= html_escape($reset_failed); ?></div>
                <?php endif; ?>
                <?php if (!empty($reset_success)) : ?>
                    <div class="alert alert-success" role="status"><?= html_escape($reset_success); ?></div>
                <?php endif; ?>
                <?= $reset_errors; ?>

                <?= form_open('Pages/forgot_password', array('id' => 'resetForm')); ?>
                <div class="field"><label for="reset-email">Email address</label>
                    <div class="input-wrap"><input id="reset-email" name="email" type="email" value="<?= html_escape(set_value('email')); ?>" placeholder="name@deped.gov.ph" autocomplete="email" required autofocus><span class="input-icon" aria-hidden="true"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="5" width="18" height="14" rx="2" />
                                <path d="m3 7 9 6 9-6" />
                            </svg></span></div>
                </div>
                <button class="button button-primary login-submit" id="resetSubmit" type="submit"><span class="button-label">Send new password</span><span class="button-spinner" aria-hidden="true"></span></button>
                <?= form_close(); ?>

                <p class="login-help"><a href="<?= base_url('homepage'); ?>#portal">&larr; Back to sign in</a><br>For account concerns, contact your division system administrator.</p>
            </section>
        </div>
    </main>

    <footer class="site-footer">
        <div class="container">
            <span>&copy; <?= date('Y'); ?> Department of Education Regional Office XI. All rights reserved.</span>
            <span>Region XI - Davao Region</span>
        </div>
    </footer>

    <script>
        (function() {
            var form = document.getElementById('resetForm'),
                submit = document.getElementById('resetSubmit');
            // Sending the mail takes a moment, so the button shows it is working and blocks
            // the double submit that would otherwise issue a second password.
            if (form && submit) form.addEventListener('submit', function() {
                submit.classList.add('is-loading');
                submit.disabled = true;
                submit.setAttribute('aria-busy', 'true');
                window.setTimeout(function() {
                    submit.disabled = false;
                }, 8000);
            });
        })();
    </script>
</body>

</html>
