<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>CLMD Learning Management Portal | School Account Signup</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="CLMD Learning Management Portal school account registration" name="description" />
    <meta content="Coderthemes" name="author" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <link rel="shortcut icon" href="<?= base_url(); ?>assets/images/favicon.ico">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link href="<?= base_url(); ?>assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" id="bootstrap-stylesheet" />
    <link href="<?= base_url(); ?>assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="<?= base_url(); ?>assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-stylesheet" />

    <?php
    $signup_value = function ($field) use ($signup_values) {
        return isset($signup_values[$field]) && is_scalar($signup_values[$field]) ? (string) $signup_values[$field] : '';
    };
    $selected_division_id = $signup_value('division_id');
    $selected_district_id = $signup_value('d_id');
    $district_options = isset($districts) && is_array($districts) ? $districts : array();

    $format_title = function ($value) {
        $value = trim((string) $value);

        if ($value === '') {
            return 'Not Available';
        }

        return mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');
    };
    ?>

    <style>
        :root {
            --signup-primary: #8b1e3f;
            --signup-primary-dark: #5f1129;
            --signup-accent: #e9b949;
            --signup-ink: #223047;
            --signup-muted: #667085;
            --signup-border: #e6eaf2;
            --signup-surface: #ffffff;
            --signup-surface-soft: #fff7f4;
            --signup-shadow: 0 24px 60px rgba(22, 32, 55, .14);
        }

        body.school-signup-page {
            min-height: 100vh;
            color: var(--signup-ink);
            font-family: "Plus Jakarta Sans", "Segoe UI", sans-serif;
            background:
                radial-gradient(circle at top left, rgba(233, 185, 73, .14), transparent 24%),
                radial-gradient(circle at bottom right, rgba(139, 30, 63, .13), transparent 26%),
                linear-gradient(135deg, #f7efe8 0%, #fbfcff 48%, #f5f7fb 100%);
        }

        .signup-shell {
            position: relative;
            min-height: 100vh;
            padding: 32px 0;
        }

        .signup-shell::before,
        .signup-shell::after {
            content: "";
            position: fixed;
            z-index: 0;
            border-radius: 50%;
            pointer-events: none;
            filter: blur(2px);
        }

        .signup-shell::before {
            top: -120px;
            left: -110px;
            width: 280px;
            height: 280px;
            background: rgba(139, 30, 63, .08);
        }

        .signup-shell::after {
            right: -100px;
            bottom: -120px;
            width: 260px;
            height: 260px;
            background: rgba(233, 185, 73, .12);
        }

        .signup-layout {
            position: relative;
            z-index: 1;
            max-width: 1240px;
            margin: 0 auto;
            border: 1px solid rgba(255, 255, 255, .75);
            border-radius: 30px;
            overflow: hidden;
            background: rgba(255, 255, 255, .68);
            box-shadow: var(--signup-shadow);
            backdrop-filter: blur(14px);
        }

        .signup-sidebar {
            position: relative;
            min-height: 100%;
            padding: 42px 38px;
            color: #fff;
            background:
                radial-gradient(circle at top right, rgba(255, 255, 255, .18), transparent 25%),
                linear-gradient(160deg, #5f1129 0%, #8b1e3f 45%, #c76c48 100%);
        }

        .signup-sidebar::after {
            content: "";
            position: absolute;
            right: -60px;
            bottom: -80px;
            width: 240px;
            height: 240px;
            border-radius: 42px;
            background: rgba(255, 255, 255, .07);
            transform: rotate(18deg);
        }

        .signup-sidebar > * {
            position: relative;
            z-index: 1;
        }

        .signup-brand {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 34px;
        }

        .signup-brand img {
            width: 60px;
            height: auto;
        }

        .signup-brand-text span {
            display: block;
            color: rgba(255, 255, 255, .72);
            font-size: 11px;
            font-weight: 600;
            letter-spacing: .12em;
            text-transform: uppercase;
        }

        .signup-brand-text strong {
            display: block;
            margin-top: 4px;
            color: #fff;
            font-size: 17px;
            font-weight: 700;
            line-height: 1.3;
        }

        .signup-sidebar h1 {
            margin: 0 0 14px;
            color: #fff;
            font-family: "Fraunces", Georgia, serif;
            font-size: 42px;
            font-weight: 700;
            line-height: 1.08;
        }

        .signup-sidebar p {
            margin: 0;
            color: rgba(255, 255, 255, .84);
            font-size: 14px;
            line-height: 1.7;
        }

        .signup-side-card {
            margin-top: 24px;
            padding: 20px 22px;
            border: 1px solid rgba(255, 255, 255, .18);
            border-radius: 20px;
            background: rgba(255, 255, 255, .1);
            backdrop-filter: blur(4px);
        }

        .signup-side-card h5 {
            margin: 0 0 12px;
            color: #fff;
            font-size: 15px;
            font-weight: 700;
        }

        .signup-side-list {
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .signup-side-list li {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 12px;
            color: rgba(255, 255, 255, .82);
            font-size: 13px;
            line-height: 1.55;
        }

        .signup-side-list li:last-child {
            margin-bottom: 0;
        }

        .signup-side-list i {
            margin-top: 1px;
            color: #ffd58b;
            font-size: 17px;
        }

        .signup-quick-links {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            margin-top: 26px;
        }

        .signup-quick-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 14px 16px;
            border: 1px solid rgba(255, 255, 255, .18);
            border-radius: 16px;
            color: #fff;
            background: rgba(255, 255, 255, .08);
            font-size: 13px;
            font-weight: 600;
            transition: transform .18s ease, background .18s ease;
        }

        .signup-quick-link:hover {
            color: #fff;
            background: rgba(255, 255, 255, .14);
            transform: translateY(-1px);
        }

        .signup-main {
            padding: 32px 34px;
        }

        .signup-mobile-brand {
            display: none;
            align-items: center;
            gap: 12px;
            margin-bottom: 22px;
        }

        .signup-mobile-brand img {
            width: 52px;
            height: auto;
        }

        .signup-mobile-brand strong {
            display: block;
            color: var(--signup-ink);
            font-size: 16px;
            font-weight: 700;
            line-height: 1.3;
        }

        .signup-mobile-brand span {
            color: var(--signup-muted);
            font-size: 12px;
        }

        .signup-main-topbar {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 12px;
            margin-bottom: 18px;
        }

        .signup-main-topbar a {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--signup-primary);
            font-size: 13px;
            font-weight: 700;
        }

        .signup-form-card {
            border: 1px solid var(--signup-border);
            border-radius: 26px;
            background: var(--signup-surface);
            box-shadow: 0 18px 45px rgba(31, 45, 75, .08);
            overflow: hidden;
        }

        .signup-form-header {
            padding: 28px 30px 22px;
            border-bottom: 1px solid var(--signup-border);
            background:
                radial-gradient(circle at top right, rgba(139, 30, 63, .08), transparent 26%),
                linear-gradient(180deg, #fff9f6 0%, #ffffff 100%);
        }

        .signup-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 12px;
            padding: 8px 12px;
            border-radius: 999px;
            color: var(--signup-primary);
            background: #fdecef;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
        }

        .signup-form-header h2 {
            margin: 0 0 8px;
            color: var(--signup-ink);
            font-size: 28px;
            font-weight: 700;
        }

        .signup-form-header p {
            margin: 0;
            color: var(--signup-muted);
            font-size: 14px;
            line-height: 1.7;
        }

        .signup-form-body {
            padding: 26px 30px 30px;
        }

        .signup-form-body .alert {
            border: 0;
            border-radius: 16px;
            box-shadow: 0 10px 24px rgba(31, 45, 75, .08);
        }

        .form-section {
            margin-bottom: 22px;
            padding: 20px;
            border: 1px solid var(--signup-border);
            border-radius: 20px;
            background: #fff;
        }

        .form-section:last-of-type {
            margin-bottom: 0;
        }

        .form-section-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 14px;
            margin-bottom: 18px;
        }

        .form-section-header h4 {
            margin: 0 0 4px;
            color: var(--signup-ink);
            font-size: 17px;
            font-weight: 700;
        }

        .form-section-header p {
            margin: 0;
            color: var(--signup-muted);
            font-size: 13px;
        }

        .form-section-step {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            flex: 0 0 36px;
            border-radius: 12px;
            color: var(--signup-primary-dark);
            background: #fdecef;
            font-size: 13px;
            font-weight: 700;
        }

        .signup-field label {
            margin-bottom: 8px;
            color: var(--signup-ink);
            font-size: 13px;
            font-weight: 700;
        }

        .signup-field small {
            display: block;
            margin-top: 6px;
            color: var(--signup-muted);
            font-size: 12px;
            line-height: 1.5;
        }

        .signup-field .form-control,
        .signup-field .custom-select {
            min-height: 48px;
            border: 1px solid #d8dfeb;
            border-radius: 14px;
            color: var(--signup-ink);
            background-color: #fcfdff;
            font-size: 14px;
            transition: border-color .18s ease, box-shadow .18s ease, background-color .18s ease;
        }

        .signup-field .form-control:focus,
        .signup-field .custom-select:focus,
        .signup-field .form-control:focus + .signup-password-toggle {
            border-color: rgba(139, 30, 63, .45);
            box-shadow: 0 0 0 .18rem rgba(139, 30, 63, .12);
            background-color: #fff;
        }

        .signup-password-wrap {
            position: relative;
        }

        .signup-password-input {
            padding-right: 50px;
        }

        .signup-password-toggle {
            position: absolute;
            top: 50%;
            right: 14px;
            transform: translateY(-50%);
            border: 0;
            color: #7a8599;
            background: transparent;
            font-size: 20px;
            cursor: pointer;
        }

        .signup-password-toggle:focus {
            outline: none;
            box-shadow: none;
        }

        .signup-consent {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 16px 18px;
            border: 1px solid #f0dfc4;
            border-radius: 18px;
            background: #fffaf0;
        }

        .signup-consent .checkbox {
            margin: 0;
        }

        .signup-consent label {
            margin: 0;
            color: #6c5a2e;
            font-size: 13px;
            line-height: 1.65;
        }

        .signup-consent a {
            color: var(--signup-primary);
            font-weight: 700;
        }

        .captcha-wrap {
            display: inline-block;
            max-width: 100%;
            overflow-x: auto;
            border-radius: 18px;
        }

        .signup-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            margin-top: 26px;
            padding-top: 6px;
        }

        .signup-actions p {
            margin: 0;
            color: var(--signup-muted);
            font-size: 13px;
            line-height: 1.6;
        }

        .signup-actions a {
            color: var(--signup-primary);
            font-weight: 700;
        }

        .btn-signup-submit {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 13px 22px;
            border: 0;
            border-radius: 999px;
            color: #fff;
            background: linear-gradient(135deg, #8b1e3f, #be5c3c);
            box-shadow: 0 14px 28px rgba(139, 30, 63, .2);
            font-size: 14px;
            font-weight: 700;
        }

        .btn-signup-submit:hover {
            color: #fff;
        }

        .signup-modal .modal-content {
            border: 0;
            border-radius: 22px;
            overflow: hidden;
            box-shadow: 0 25px 60px rgba(18, 28, 48, .22);
        }

        .signup-modal .modal-header {
            border-bottom: 0;
            padding: 18px 22px;
            background: linear-gradient(135deg, #8b1e3f, #be5c3c);
        }

        .signup-modal .modal-header .modal-title {
            color: #fff;
            font-size: 16px;
            font-weight: 700;
        }

        .signup-modal .modal-header .close {
            color: #fff;
            opacity: 1;
            text-shadow: none;
        }

        .signup-modal .modal-body {
            padding: 24px;
            color: var(--signup-ink);
            font-size: 14px;
            line-height: 1.8;
        }

        @media (max-width: 1199.98px) {
            .signup-layout {
                max-width: 820px;
            }

            .signup-sidebar {
                display: none;
            }

            .signup-mobile-brand {
                display: flex;
            }

            .signup-main {
                padding: 28px 24px;
            }
        }

        @media (max-width: 767.98px) {
            .signup-shell {
                padding: 16px 0;
            }

            .signup-layout {
                border-radius: 22px;
            }

            .signup-main {
                padding: 18px 14px;
            }

            .signup-main-topbar {
                justify-content: flex-start;
                margin-bottom: 14px;
            }

            .signup-form-header,
            .signup-form-body {
                padding-left: 18px;
                padding-right: 18px;
            }

            .form-section {
                padding: 16px;
                border-radius: 18px;
            }

            .form-section-header {
                flex-direction: column;
            }

            .signup-actions {
                flex-direction: column;
                align-items: stretch;
            }

            .btn-signup-submit {
                justify-content: center;
                width: 100%;
            }
        }
    </style>
<?php $this->load->view('templates/pwa'); ?>
</head>

<body class="school-signup-page">
    <div class="signup-shell">
        <div class="container-fluid px-lg-4">
            <div class="signup-layout">
                <div class="row no-gutters">
                    <div class="col-xl-5">
                        <aside class="signup-sidebar">
                            <a href="<?= base_url(); ?>" class="signup-brand">
                                <div class="signup-brand-text">
                                    <span>CLMD Learning Portal</span>
                                    <strong>CLMD</strong>
                                </div>
                            </a>

                            <h1>Create your school account with confidence.</h1>
                            <p>Register your school profile, connect it to the correct division and district, and access the CLMD Learning Management Portal after registration.</p>

                            <div class="signup-side-card">
                                <h5>Before you begin</h5>
                                <ul class="signup-side-list">
                                    <li><i class="mdi mdi-check-circle-outline"></i><span>Prepare your official school ID. It will also be used as your username.</span></li>
                                    <li><i class="mdi mdi-email-check-outline"></i><span>Use your official school email as an alternative to your School ID when signing in.</span></li>
                                    <li><i class="mdi mdi-map-marker-radius-outline"></i><span>Select the correct division and district to make your records easier to monitor and support.</span></li>
                                    <li><i class="mdi mdi-shield-check-outline"></i><span>Accept the declaration before submitting. Security verification runs automatically.</span></li>
                                </ul>
                            </div>

                            <div class="signup-side-card">
                                <h5>Who should use this page?</h5>
                                <ul class="signup-side-list">
                                    <li><i class="mdi mdi-school-outline"></i><span>School users registering their school account in the CLMD portal.</span></li>
                                    <li><i class="mdi mdi-account-supervisor-outline"></i><span>District users should use the district signup page instead of the school account form.</span></li>
                                </ul>
                            </div>

                            <div class="signup-quick-links">
                                <a href="<?= base_url('log_in'); ?>" class="signup-quick-link">
                                    <i class="mdi mdi-login-variant"></i>
                                    Sign In
                                </a>
                                <a href="<?= base_url('signup_district'); ?>" class="signup-quick-link">
                                    <i class="mdi mdi-account-group-outline"></i>
                                    District Signup
                                </a>
                            </div>
                        </aside>
                    </div>

                    <div class="col-xl-7">
                        <main class="signup-main">
                            <div class="signup-mobile-brand">
                                <div>
                                    <strong>CLMD</strong>
                                    <span>School account registration</span>
                                </div>
                            </div>

                            <div class="signup-main-topbar">
                                <a href="<?= base_url('log_in'); ?>">
                                    <i class="mdi mdi-arrow-left"></i>
                                    Back to Sign In
                                </a>
                            </div>

                            <div class="signup-form-card">
                                <div class="signup-form-header">
                                    <span class="signup-eyebrow">
                                        <i class="mdi mdi-school-outline"></i>
                                        School Signup
                                    </span>
                                    <h2>Register your school profile</h2>
                                    <p>Fill in the required school details below. The portal organizes your school account within the correct division and district.</p>
                                </div>

                                <div class="signup-form-body">
                                    <div id="signup-feedback" tabindex="-1" aria-live="polite">
                                        <?php foreach (array('success' => 'success', 'failed' => 'danger', 'danger' => 'danger') as $message_key => $message_style): ?>
                                            <?php $signup_message = $this->session->flashdata($message_key); ?>
                                            <?php if ($signup_message): ?>
                                                <div class="alert alert-<?= $message_style; ?>" role="<?= $message_style === 'success' ? 'status' : 'alert'; ?>">
                                                    <?= html_escape($signup_message); ?>
                                                    <?php if ($message_style === 'success'): ?>
                                                        <a href="<?= base_url('homepage'); ?>#portal" class="alert-link">Go to sign in</a>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                        <?= !empty($show_validation_errors) ? validation_errors() : ''; ?>
                                        <?php if (!empty($captcha_error)): ?>
                                            <div class="alert alert-danger" role="alert"><?= html_escape($captcha_error); ?></div>
                                        <?php endif; ?>
                                    </div>

                                    <?= form_open('Pages/signup', array('id' => 'schoolSignupForm')); ?>
                                        <div class="form-section">
                                            <div class="form-section-header">
                                                <div>
                                                    <h4>Access Credentials</h4>
                                                    <p>Set up the login details that the school will use to access the system.</p>
                                                </div>
                                                <span class="form-section-step">01</span>
                                            </div>

                                            <div class="form-row">
                                                <div class="form-group col-md-6 signup-field">
                                                    <label for="schoolID">School ID</label>
                                                    <input class="form-control" type="text" id="schoolID" name="schoolID" value="<?= html_escape($signup_value('schoolID')); ?>" autocomplete="username" required>
                                                    <small>Sign in using your School ID or registered school email address.</small>
                                                </div>

                                                <div class="form-group col-md-6 signup-field">
                                                    <label for="password">Password</label>
                                                    <div class="signup-password-wrap">
                                                        <input id="password" class="form-control signup-password-input" type="password" name="password" value="<?= html_escape($signup_value('password')); ?>" autocomplete="new-password" minlength="8" maxlength="128" required>
                                                        <button type="button" class="signup-password-toggle" id="togglePassword" aria-label="Show password">
                                                            <i class="mdi mdi-eye-outline" id="togglePasswordIcon"></i>
                                                        </button>
                                                    </div>
                                                    <small>Use 8–128 characters for your password.</small>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-section">
                                            <div class="form-section-header">
                                                <div>
                                                    <h4>School Identity</h4>
                                                    <p>Provide the official school name, contact email, and location assignment.</p>
                                                </div>
                                                <span class="form-section-step">02</span>
                                            </div>

                                            <div class="form-row">
                                                <div class="form-group col-md-6 signup-field">
                                                    <label for="schoolName">School Name</label>
                                                    <input class="form-control" type="text" id="schoolName" name="schoolName" value="<?= html_escape($signup_value('schoolName')); ?>" required>
                                                </div>

                                                <div class="form-group col-md-6 signup-field">
                                                    <label for="schoolEmail">School Email</label>
                                                    <input class="form-control" type="email" id="schoolEmail" name="schoolEmail" value="<?= html_escape($signup_value('schoolEmail')); ?>" autocomplete="email" aria-describedby="email-availability" required>
                                                    <small id="email-availability" role="status" aria-live="polite"></small>
                                                </div>
                                            </div>

                                            <div class="form-row">
                                                <div class="form-group col-md-6 signup-field">
                                                    <label for="division">Division</label>
                                                    <select name="division_id" id="division" class="custom-select" required>
                                                        <option value="">Select Division</option>
                                                        <?php foreach ($division as $row) : ?>
                                                            <option value="<?= $row->id; ?>" <?= $selected_division_id === (string) $row->id ? 'selected' : ''; ?>>
                                                                <?= html_escape($format_title($row->description)); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>

                                                <div class="form-group col-md-6 signup-field">
                                                    <label for="district">Districts / Cluster</label>
                                                    <select name="d_id" id="district" class="custom-select" required>
                                                        <option value="">Select District / Cluster</option>
                                                        <?php foreach ($district_options as $district_row) : ?>
                                                            <option value="<?= $district_row->id; ?>" <?= $selected_district_id === (string) $district_row->id ? 'selected' : ''; ?>>
                                                                <?= html_escape($format_title($district_row->description)); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <input type="hidden" name="renren" value="">
                                        <input type="hidden" name="ivykate" value="">
                                        <input type="hidden" name="ivankyle" value="">
                                        <input type="hidden" name="ic" value="">

                                        <div class="form-section">
                                            <div class="form-section-header">
                                                <div>
                                                    <h4>Consent and Verification</h4>
                                                    <p>Review the declaration, confirm your consent, and complete the security check.</p>
                                                </div>
                                                <span class="form-section-step">03</span>
                                            </div>

                                            <div class="signup-consent mb-4">
                                                <div class="checkbox checkbox-success mt-1">
                                                    <input id="termsAccepted" name="termsAccepted" type="checkbox" <?= $signup_value('termsAccepted') === 'on' ? 'checked' : ''; ?> required>
                                                    <label for="termsAccepted">
                                                        I accept the
                                                        <a href="#" data-toggle="modal" data-target="#termsModal">Declaration and Attestation</a>
                                                        for registering and processing school information in the CLMD portal.
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="captcha-wrap signup-field">
                                                <input type="hidden" name="g-recaptcha-response" id="recaptchaResponse" value="">
                                                <small>This form is protected by Google reCAPTCHA.</small>
                                                <p id="recaptchaStatus" role="status" aria-live="polite"></p>
                                            </div>
                                        </div>

                                        <div class="signup-actions">
                                            <p>Already registered? <a href="<?= base_url('log_in'); ?>">Go to the sign in page</a>.</p>
                                            <button class="btn btn-signup-submit waves-effect waves-light" type="submit" <?= !empty($account_saved) ? 'disabled' : ''; ?>>
                                                <i class="mdi mdi-account-plus-outline"></i>
                                                Create School Account
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </main>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="termsModal" class="modal fade signup-modal" tabindex="-1" role="dialog" aria-labelledby="termsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="termsModalLabel">Declaration and Attestation</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>
                <div class="modal-body">
                    <p>
                        I declare that I am authorized to register and manage this school account in the CLMD Learning Management Portal for Region XI. I attest that the school identification, official email address, division, and district information I provide is accurate and complete, and I will keep these details up to date.
                    </p>
                    <p>
                        I understand that the CLMD portal supports monitoring learner progress, assessment results, and least learned competencies across all learning areas. School submissions help identify learning gaps and guide instructional interventions, technical assistance, and recommendations at the school, district, division, and regional levels.
                    </p>
                    <p>
                        I acknowledge that the registration details and school data submitted through this account will be collected, stored, and processed for account administration, learning assessment monitoring, reporting, and educational support. I will submit only information necessary for these purposes, protect account credentials, and handle learner and school information responsibly, sharing it only with authorized personnel for official use.
                    </p>
                    <p class="mb-0">
                        By selecting the declaration checkbox and clicking “Create School Account,” I confirm that I have read and understood this declaration, attest to the accuracy of the information provided, and agree to use the CLMD portal responsibly for its stated educational purposes. For account concerns or corrections to submitted information, I will contact the division system administrator.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
    <script src="<?= base_url(); ?>assets/js/app.min.js"></script>
    <script src="https://www.google.com/recaptcha/api.js?render=<?= rawurlencode($captcha_site_key); ?>" async defer></script>

    <script>
        (function() {
            const feedback = document.getElementById('signup-feedback');
            if (feedback && feedback.querySelector('.alert')) {
                feedback.scrollIntoView({ block: 'center' });
                feedback.focus({ preventScroll: true });
            }

            const signupForm = document.getElementById('schoolSignupForm');
            const captchaStatus = document.getElementById('recaptchaStatus');
            const signupButton = signupForm.querySelector('button[type="submit"]');
            const siteKey = <?= json_encode($captcha_site_key, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
            let verifying = false;
            signupForm.addEventListener('submit', function(event) {
                event.preventDefault();
                if (verifying || signupButton.disabled) return;
                if (!siteKey || !window.grecaptcha || !window.grecaptcha.ready) {
                    captchaStatus.textContent = 'Security verification is still loading. Check your internet connection and try again.';
                    return;
                }
                verifying = true;
                signupButton.disabled = true;
                captchaStatus.textContent = 'Verifying…';
                let finished = false;
                function failed() {
                    if (finished) return;
                    finished = true;
                    clearTimeout(timeout);
                    verifying = false;
                    signupButton.disabled = false;
                    captchaStatus.textContent = 'Security verification could not finish. Please try again. Your details have been kept.';
                }
                const timeout = setTimeout(failed, 15000);
                grecaptcha.ready(function() {
                    if (finished) return;
                    try {
                        grecaptcha.execute(siteKey, {action: 'school_signup'}).then(function(token) {
                            if (finished) return;
                            if (!token) { failed(); return; }
                            // Recheck in case the email availability result arrived while verifying.
                            if (!signupForm.reportValidity()) { failed(); return; }
                            finished = true;
                            clearTimeout(timeout);
                            document.getElementById('recaptchaResponse').value = token;
                            HTMLFormElement.prototype.submit.call(signupForm);
                        }).catch(failed);
                    } catch (error) { failed(); }
                });
            });

            const passwordInput = document.getElementById('password');
            const toggleButton = document.getElementById('togglePassword');
            const toggleIcon = document.getElementById('togglePasswordIcon');

            if (toggleButton && passwordInput && toggleIcon) {
                toggleButton.addEventListener('click', function() {
                    const isHidden = passwordInput.type === 'password';
                    passwordInput.type = isHidden ? 'text' : 'password';
                    toggleIcon.className = isHidden ? 'mdi mdi-eye-off-outline' : 'mdi mdi-eye-outline';
                    toggleButton.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
                });
            }

            const emailField = document.getElementById('schoolEmail');
            const emailStatus = document.getElementById('email-availability');
            let emailTimer;
            let emailRequest = 0;
            emailField.addEventListener('input', function() {
                clearTimeout(emailTimer);
                const request = ++emailRequest;
                emailStatus.textContent = '';
                emailField.setCustomValidity('');
                emailStatus.className = '';
                const email = emailField.value.trim();
                if (!email || !emailField.validity.valid) return;
                emailTimer = setTimeout(function() {
                    emailStatus.textContent = 'Checking email availability…';
                    $.ajax({
                        url: <?= json_encode(base_url('Pages/signup_email_available')); ?>,
                        method: 'POST',
                        dataType: 'json',
                        data: {
                            email: email,
                            <?= json_encode($this->security->get_csrf_token_name()); ?>: <?= json_encode($this->security->get_csrf_hash()); ?>
                        },
                        success: function(result) {
                            if (request !== emailRequest) return;
                            emailStatus.textContent = result.message;
                            emailStatus.className = result.available ? 'text-success' : 'text-danger';
                            emailField.setCustomValidity(result.available ? '' : result.message);
                        },
                        error: function() {
                            if (request !== emailRequest) return;
                            emailStatus.textContent = 'Availability will be checked when you submit.';
                        }
                    });
                }, 450);
            });

            const divisionField = $('#division');
            const districtField = $('#district');
            const selectedDistrict = <?= json_encode((string) $selected_district_id); ?>;

            function populateDistrictOptions(response, chosenDistrict) {
                districtField.html('<option value="">Select District / Cluster</option>');

                $.each(response, function(index, item) {
                    const isSelected = chosenDistrict && String(chosenDistrict) === String(item.id);
                    const option = $('<option></option>')
                        .val(item.id)
                        .text(item.description)
                        .prop('selected', isSelected);

                    districtField.append(option);
                });
            }

            function loadDistricts(divisionID, chosenDistrict) {
                if (!divisionID) {
                    districtField.html('<option value="">Select District / Cluster</option>');
                    return;
                }

                districtField.html('<option value="">Loading districts...</option>');

                $.ajax({
                    url: '<?= base_url("Pages/get_district_by_division"); ?>',
                    method: 'POST',
                    data: {
                        division_id: divisionID,
                        <?= json_encode($this->security->get_csrf_token_name()); ?>: <?= json_encode($this->security->get_csrf_hash()); ?>
                    },
                    dataType: 'json',
                    success: function(response) {
                        populateDistrictOptions(response, chosenDistrict);
                    },
                    error: function() {
                        districtField.html('<option value="">Unable to load districts</option>');
                    }
                });
            }

            divisionField.on('change', function() {
                loadDistricts($(this).val(), '');
            });

            if (divisionField.val() && districtField.find('option').length <= 1) {
                loadDistricts(divisionField.val(), selectedDistrict);
            }
        })();
    </script>
</body>

</html>
