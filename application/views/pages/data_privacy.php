<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>CLMD Learning Management Portal</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />

  <style>
    :root {
      --maroon: #800000;
      --maroon-dark: #5c0000;
      --maroon-light: #a52a2a;
      --slate: #334155;
      --muted: #64748b;
      --bg: #f1f5f9;
      --card-bg: #ffffff;
      --border: #e2e8f0;
      --accent-purple: #7c3aed;
      --accent-blue: #922c43;
      --accent-green: #15803d;
      --accent-amber: #b45309;
      --accent-rose: #be123c;
    }

    * {
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    }

    body {
      background-color: var(--bg);
      color: var(--slate);
      line-height: 1.65;
      font-size: 15px;
    }

    .topbar {
      background: #fff;
      border-bottom: 1px solid var(--border);
      padding: 14px 0;
    }

    .topbar-brand {
      display: flex;
      align-items: center;
      gap: 12px;
      text-decoration: none;
      color: var(--maroon);
      font-weight: 700;
      font-size: 18px;
    }

    .topbar-brand img {
      height: 40px;
    }

    .topbar-nav a {
      color: var(--muted);
      text-decoration: none;
      font-weight: 500;
      font-size: 14px;
      padding: 6px 14px;
      border-radius: 8px;
      transition: all .15s ease;
    }

    .topbar-nav a:hover {
      color: var(--maroon);
      background: #f8f5f5;
    }

    .topbar-nav a.active {
      color: var(--maroon);
      background: #f8f5f5;
      font-weight: 600;
    }

    .privacy-header {
      background: linear-gradient(160deg, var(--maroon) 0%, var(--maroon-light) 60%, #c24157 100%);
      color: #fff;
      padding: 64px 0 72px;
      position: relative;
      overflow: hidden;
    }

    .privacy-header::before {
      content: '';
      position: absolute;
      top: -80px;
      right: -60px;
      width: 300px;
      height: 300px;
      border-radius: 50%;
      background: rgba(255, 255, 255, .06);
    }

    .privacy-header::after {
      content: '';
      position: absolute;
      bottom: -40px;
      left: -40px;
      width: 200px;
      height: 200px;
      border-radius: 50%;
      background: rgba(255, 255, 255, .05);
    }

    .privacy-header h1 {
      font-weight: 800;
      font-size: 2.4rem;
      margin-bottom: 12px;
      letter-spacing: -0.5px;
    }

    .privacy-header p {
      opacity: .88;
      font-size: 1.05rem;
      max-width: 560px;
      line-height: 1.6;
    }

    .privacy-header .badge-pill {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: rgba(255, 255, 255, .15);
      border: 1px solid rgba(255, 255, 255, .2);
      padding: 6px 14px;
      border-radius: 999px;
      font-size: 13px;
      font-weight: 500;
      margin-bottom: 20px;
      backdrop-filter: blur(4px);
    }

    .privacy-body {
      padding: 48px 0 64px;
    }

    .policy-card {
      background: var(--card-bg);
      border-radius: 16px;
      border: 1px solid var(--border);
      padding: 0;
      margin-bottom: 20px;
      overflow: hidden;
      transition: box-shadow .2s ease;
    }

    .policy-card:hover {
      box-shadow: 0 12px 32px rgba(0, 0, 0, .06);
    }

    .policy-card-header {
      display: flex;
      align-items: center;
      gap: 14px;
      padding: 22px 28px;
      border-bottom: 1px solid var(--border);
    }

    .policy-card-header .icon-wrap {
      width: 42px;
      height: 42px;
      border-radius: 12px;
      display: grid;
      place-items: center;
      font-size: 20px;
      color: #fff;
      flex-shrink: 0;
    }

    .policy-card-header h2 {
      font-size: 16px;
      font-weight: 700;
      color: var(--slate);
      margin: 0;
    }

    .policy-card-body {
      padding: 22px 28px 26px;
      color: var(--slate);
    }

    .policy-card-body p {
      margin-bottom: 14px;
      color: var(--slate);
    }

    .policy-card-body p:last-child {
      margin-bottom: 0;
    }

    .policy-card-body ul {
      padding-left: 0;
      list-style: none;
      margin-bottom: 0;
    }

    .policy-card-body ul li {
      position: relative;
      padding-left: 22px;
      margin-bottom: 10px;
      font-size: 14.5px;
    }

    .policy-card-body ul li::before {
      content: '';
      position: absolute;
      left: 0;
      top: 9px;
      width: 7px;
      height: 7px;
      border-radius: 50%;
      background: var(--muted);
      opacity: .5;
    }

    .policy-card-body ul li strong {
      color: var(--slate);
      font-weight: 600;
    }

    .highlight-box {
      background: #f8fafc;
      border-left: 3px solid var(--maroon);
      border-radius: 0 10px 10px 0;
      padding: 16px 20px;
      margin-top: 18px;
      font-size: 14px;
      color: var(--slate);
    }

    .footer-bar {
      background: #fff;
      border-top: 1px solid var(--border);
      padding: 28px 0;
      text-align: center;
      font-size: 13.5px;
      color: var(--muted);
    }

    .footer-bar strong {
      color: var(--slate);
      font-weight: 600;
    }

    @media (max-width: 768px) {
      .privacy-header {
        padding: 44px 0 52px;
      }

      .privacy-header h1 {
        font-size: 1.7rem;
      }

      .policy-card-header {
        padding: 18px 20px;
      }

      .policy-card-body {
        padding: 18px 20px 22px;
      }
    }
  </style>
</head>

<body>

  <!-- Topbar -->
  <div class="topbar">
    <div class="container">
      <div class="d-flex align-items-center justify-content-between">
        <a href="<?= base_url(); ?>" class="topbar-brand">
          <img src="<?= base_url(); ?>assets/images/ftad-logo.png" alt="FTAD">
          <span>OneView</span>
        </a>
        <div class="topbar-nav d-none d-md-flex gap-1">
          <a href="<?= base_url(); ?>">Home</a>
          <a href="<?= base_url(); ?>Pages/about">About</a>
          <a href="<?= base_url(); ?>Pages/data_privacy" class="active">Privacy</a>
          <a href="<?= base_url(); ?>Pages/authors">Authors</a>
        </div>
      </div>
    </div>
  </div>

  <!-- Header -->
  <header class="privacy-header">
    <div class="container" style="position:relative; z-index:1;">
      <div class="badge-pill">
        <i class="bi bi-shield-check"></i> Republic Act No. 10173 — Data Privacy Act of 2012
      </div>
      <h1>Privacy Notice</h1>
      <p>Before you use FTAD OneView, here's what happens to the information you enter in the system. We keep this plain and straightforward.</p>
    </div>
  </header>

  <!-- Body -->
  <section class="privacy-body">
    <div class="container" style="max-width: 800px;">

      <div class="policy-card">
        <div class="policy-card-header">
          <div class="icon-wrap" style="background: linear-gradient(135deg, #7c3aed, #a78bfa);">
            <i class="bi bi-folder"></i>
          </div>
          <h2>What we collect from you</h2>
        </div>
        <div class="policy-card-body">
          <p>When you register or use this system, we ask for and store the following:</p>
          <ul>
            <li><strong>School profile</strong> — School ID, complete school name, address, division, district, school category, and email. These are needed to match your records to the correct office.</li>
            <li><strong>Account details</strong> — Your full name, username, password (hashed), position or role, and the school or office you belong to. This controls what screens you can open.</li>
            <li><strong>SBM forms you fill out</strong> — Checklist answers, TA form narratives, TANA scores, priority rankings, and action plan items. These are the core outputs of the system.</li>
            <li><strong>SGC status</strong> — Whether your School Governance Council is organized, functional, or not yet formed.</li>
            <li><strong>Login records</strong> — Timestamps of when you signed in and which fiscal year you selected. We use this only for session management and basic audit trails.</li>
          </ul>
          <div class="highlight-box">
            <i class="bi bi-info-circle me-2" style="color: var(--maroon);"></i>
            We do <strong>not</strong> collect browsing history outside this site, biometric data, or financial information.
          </div>
        </div>
      </div>

      <div class="policy-card">
        <div class="policy-card-header">
          <div class="icon-wrap" style="background: linear-gradient(135deg, #5b1026, #c66b7c);">
            <i class="bi bi-diagram-3"></i>
          </div>
          <h2>How your data is used</h2>
        </div>
        <div class="policy-card-body">
          <p>The system uses your data only for SBM monitoring and technical assistance. Nothing else:</p>
          <ul>
            <li>Your login credentials are checked on every page load so you only see what your role allows.</li>
            <li>Checklist and TANA results are rolled up into division and region reports so supervisors can see where help is needed.</li>
            <li>TA forms and action plans are shared upward (school → district → division → region) so coaches and supervisors can follow up on flagged priorities.</li>
            <li>Login timestamps help us troubleshoot account issues and detect unusual access patterns.</li>
            <li>We do <strong>not</strong> use your data for advertising, marketing, or any commercial activity.</li>
          </ul>
        </div>
      </div>

      <div class="policy-card">
        <div class="policy-card-header">
          <div class="icon-wrap" style="background: linear-gradient(135deg, #15803d, #4ade80);">
            <i class="bi bi-people"></i>
          </div>
          <h2>Who can see your data</h2>
        </div>
        <div class="policy-card-body">
          <p>Access is locked to your level in the DepEd hierarchy. Nobody outside your chain can browse your records:</p>
          <ul>
            <li><strong>School</strong> — You see only your own school's checklist, TA, TANA, and action plan.</li>
            <li><strong>District</strong> — You see summary counts and school lists for your district only. You cannot open another district's data.</li>
            <li><strong>Division</strong> — You see district summaries and the full school directory under your division.</li>
            <li><strong>Region / Admin</strong> — You see division-level dashboards and region-wide completion reports.</li>
          </ul>
          <div class="highlight-box">
            <i class="bi bi-lock me-2" style="color: var(--maroon);"></i>
            Your data is <strong>not sold, rented, or shared</strong> with third-party companies, researchers, or advertisers.
          </div>
        </div>
      </div>

      <div class="policy-card">
        <div class="policy-card-header">
          <div class="icon-wrap" style="background: linear-gradient(135deg, #b45309, #fbbf24);">
            <i class="bi bi-shield-lock"></i>
          </div>
          <h2>How we protect it</h2>
        </div>
        <div class="policy-card-body">
          <p>We apply the same safeguards used in DepEd ICT systems:</p>
          <ul>
            <li>Passwords are hashed before storage. Plain-text passwords are never saved.</li>
            <li>Session tokens on the mobile app are encrypted and expire automatically.</li>
            <li>Every data request is checked against your role before any record is returned.</li>
            <li>The database is backed up regularly. If hardware fails, we can restore up to the last backup point.</li>
            <li>Server-side validation prevents users from editing records that do not belong to them.</li>
            <li>Access logs are kept so we can trace who changed or finalized a record.</li>
          </ul>
        </div>
      </div>

      <div class="policy-card">
        <div class="policy-card-header">
          <div class="icon-wrap" style="background: linear-gradient(135deg, #be123c, #fb7185);">
            <i class="bi bi-hand-thumbs-up"></i>
          </div>
          <h2>Your rights as a user</h2>
        </div>
        <div class="policy-card-body">
          <p>Under the Data Privacy Act of 2012, you may:</p>
          <ul>
            <li>Update your school profile at any time through the system.</li>
            <li>Request correction of incorrect entries by contacting your district or division coordinator.</li>
            <li>Ask for a copy of your own records. The system can export most SBM forms to PDF.</li>
            <li>Request deactivation of your account if you are no longer assigned to the school or office.</li>
          </ul>
          <p style="margin-top: 12px; color: var(--muted); font-size: 14px;">
            <i class="bi bi-clock-history me-1"></i> We keep finalized SBM records for the current and previous fiscal years for compliance tracking. Older records may be archived.
          </p>
        </div>
      </div>

      <div class="policy-card">
        <div class="policy-card-header">
          <div class="icon-wrap" style="background: linear-gradient(135deg, #475569, #94a3b8);">
            <i class="bi bi-envelope"></i>
          </div>
          <h2>Questions or concerns?</h2>
        </div>
        <div class="policy-card-body">
          <p>If something about your data doesn't look right, or you want your account deactivated, reach out to:</p>
          <p style="margin-bottom: 4px;"><strong>Field Technical Assistance Division (FTAD)</strong></p>
          <p style="color: var(--muted); font-size: 14px; margin-bottom: 0;">You may also contact your Schools Division Office ICT coordinator or the regional ICT unit for faster response on account issues.</p>
        </div>
      </div>

    </div>
  </section>

  <footer class="footer-bar">
    <div class="container">
      <strong>FTAD OneView</strong> — School-Based Management Monitoring System &nbsp;|&nbsp; 2022 – <?= date('Y'); ?> &nbsp;|&nbsp; DepEd Region XI
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
