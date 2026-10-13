<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Set a new password | CLMD Learning Management Portal</title>
    <link rel="icon" href="<?= base_url('assets/images/favicon.ico'); ?>">
    <link href="<?= base_url('assets/css/bootstrap.min.css'); ?>" rel="stylesheet">
    <style>
        :root { --navy: #5b1026; --navy-dark: #3b0718; --gold: #c7983d; --muted: #647487; }
        * { box-sizing: border-box; }
        body {
            min-height: 100vh;
            margin: 0;
            display: grid;
            place-items: center;
            padding: 32px 18px;
            color: #172535;
            background: linear-gradient(145deg, #f5e9ec 0%, #fbf4f5 55%, #fff8e9 100%);
            font-family: Inter, "Segoe UI", Arial, sans-serif;
        }
        .password-card {
            width: 100%;
            max-width: 500px;
            padding: 34px;
            border: 1px solid #ead7dc;
            border-top: 5px solid var(--gold);
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 20px 55px rgba(8, 43, 76, .14);
        }
        .brand { display: flex; align-items: center; gap: 13px; margin-bottom: 25px; }
        .brand img { width: 58px; height: 58px; object-fit: contain; }
        .brand strong { display: block; color: var(--navy-dark); font-size: 18px; }
        .brand span { color: var(--navy); font-size: 12px; font-weight: 700; }
        h1 { margin: 0 0 8px; color: var(--navy-dark); font-size: 25px; }
        .intro { margin-bottom: 24px; color: var(--muted); font-size: 14px; line-height: 1.6; }
        label { color: #25384b; font-size: 13px; font-weight: 700; }
        .form-control { min-height: 44px; }
        .password-note { margin: -4px 0 20px; color: var(--muted); font-size: 12px; }
        .btn-primary { border-color: var(--navy); background: var(--navy); font-weight: 700; }
        .btn-primary:hover { border-color: var(--navy-dark); background: var(--navy-dark); }
        .logout-form { margin: 16px 0 0; }
        .logout-button { padding: 0; border: 0; color: var(--muted); background: transparent; font-size: 13px; text-decoration: underline; }
        @media (max-width: 520px) { .password-card { padding: 26px 22px; } }
    </style>
</head>
<body>
    <main class="password-card">
        <div class="brand">
            <img src="<?= base_url('assets/r11-logo.jpg'); ?>" alt="DepEd Region XI logo">
            <div><strong>CLMD Learning Management Portal</strong><span>DepEd Regional Office XI</span></div>
        </div>

        <h1>Create your new password</h1>
        <p class="intro">This account was issued a temporary password. For your security, set a private password before continuing to the portal.</p>

        <?php if ($this->session->flashdata('danger')) : ?>
            <div class="alert alert-danger" role="alert"><?= html_escape($this->session->flashdata('danger')); ?></div>
        <?php endif; ?>

        <?= form_open('Pages/change_password_user'); ?>
            <div class="form-group">
                <label for="required-current-password">Temporary password</label>
                <input type="password" class="form-control" name="current_password" id="required-current-password" autocomplete="current-password" required autofocus>
            </div>
            <div class="form-group">
                <label for="required-new-password">New password</label>
                <input type="password" class="form-control" name="password" id="required-new-password" autocomplete="new-password" minlength="8" maxlength="128" required>
            </div>
            <div class="form-group">
                <label for="required-confirm-password">Confirm new password</label>
                <input type="password" class="form-control" name="password_confirm" id="required-confirm-password" autocomplete="new-password" minlength="8" maxlength="128" required>
            </div>
            <p class="password-note">Use at least 8 characters. Your new password must be different from the temporary password.</p>
            <button type="submit" class="btn btn-primary btn-block">Save and continue</button>
        <?= form_close(); ?>
        <?= form_open('logout', array('class' => 'logout-form text-center')); ?>
            <button type="submit" class="logout-button">Sign out</button>
        <?= form_close(); ?>
    </main>
</body>
</html>
