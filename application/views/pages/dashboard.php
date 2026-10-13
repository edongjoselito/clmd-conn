<?php
$division_total = isset($division_count) ? (int) $division_count : 0;
$district_total = isset($district_count) ? (int) $district_count : 0;
$registered_school_total = isset($registered_school_count) ? (int) $registered_school_count : 0;
$user_total = isset($user_count) ? (int) $user_count : 0;
$encoded_school_total = isset($encoded_total_schools) ? (int) $encoded_total_schools : 0;
$configured_division_total = isset($configured_division_count) ? (int) $configured_division_count : 0;
$signup_rate = isset($signup_percentage) ? (float) $signup_percentage : 0;
$signup_progress_width = min(100, max(0, $signup_rate));
$division_setup_rate = $division_total > 0 ? ($configured_division_total / $division_total) * 100 : 0;
$division_setup_width = min(100, max(0, $division_setup_rate));
$display_name = isset($this->session->user) && trim((string) $this->session->user) !== ''
    ? mb_convert_case((string) $this->session->user, MB_CASE_TITLE, 'UTF-8')
    : 'Administrator';
$division_list_url = base_url() . 'pages/school_by_district';
$school_directory_url = base_url() . 'pages/school_by_district';
$user_list_url = base_url() . 'pages/userlist';
?>

<style>
    .admin-dashboard {
        --dashboard-primary: #8b1e3f;
        --dashboard-primary-dark: #64142d;
        --dashboard-border: #e8ecf4;
        --dashboard-muted: #6b7280;
    }

    .dashboard-hero {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 24px;
        margin: 18px 0 22px;
        padding: 30px;
        border-radius: 20px;
        color: #fff;
        background:
            radial-gradient(circle at 90% 15%, rgba(255, 255, 255, .2), transparent 25%),
            linear-gradient(135deg, #64142d 0%, #a83255 100%);
        box-shadow: 0 14px 34px rgba(139, 30, 63, .22);
        overflow: hidden;
    }

    .dashboard-hero h1 {
        margin: 0 0 7px;
        color: #fff;
        font-size: 27px;
        font-weight: 700;
    }

    .dashboard-hero p {
        max-width: 720px;
        margin: 0;
        color: rgba(255, 255, 255, .84);
    }

    .dashboard-year-button {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        flex: 0 0 auto;
        padding: 11px 16px;
        border: 1px solid rgba(255, 255, 255, .24);
        border-radius: 999px;
        color: #fff;
        background: rgba(255, 255, 255, .14);
        font-size: 13px;
        font-weight: 700;
        backdrop-filter: blur(5px);
    }

    .dashboard-year-button:hover {
        color: var(--dashboard-primary-dark);
        background: #fff;
        text-decoration: none;
    }

    .dashboard-stats {
        margin-bottom: 22px;
    }

    .dashboard-stat-link,
    .dashboard-count-link {
        display: block;
        color: inherit;
        text-decoration: none;
    }

    .dashboard-stat-link:hover,
    .dashboard-count-link:hover {
        color: inherit;
        text-decoration: none;
    }

    .dashboard-stat-card {
        height: calc(100% - 20px);
        margin-bottom: 20px;
        padding: 20px;
        border: 1px solid var(--dashboard-border);
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 8px 24px rgba(31, 45, 75, .06);
    }

    .dashboard-stat-link .dashboard-stat-card,
    .dashboard-count-link .sgc-status {
        transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
    }

    .dashboard-stat-link:hover .dashboard-stat-card,
    .dashboard-count-link:hover .sgc-status {
        transform: translateY(-2px);
        border-color: #d9dfee;
        box-shadow: 0 14px 30px rgba(31, 45, 75, .10);
    }

    .dashboard-stat-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
    }

    .dashboard-stat-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 46px;
        height: 46px;
        border-radius: 14px;
        color: #fff;
        background: linear-gradient(135deg, #8b1e3f, #c65a77);
        font-size: 21px;
    }

    .dashboard-stat-card h3 {
        margin: 0;
        color: #27324a;
        font-size: 27px;
        font-weight: 700;
    }

    .dashboard-stat-card p {
        margin: 10px 0 0;
        color: var(--dashboard-muted);
        font-size: 13px;
    }

    .dashboard-link-hint {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        margin-top: 10px;
        color: var(--dashboard-primary);
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .02em;
        text-transform: uppercase;
    }

    .dashboard-panel {
        margin-bottom: 22px;
        border: 1px solid var(--dashboard-border);
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 8px 28px rgba(31, 45, 75, .07);
        overflow: hidden;
    }

    .dashboard-panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 20px 24px;
        border-bottom: 1px solid var(--dashboard-border);
    }

    .dashboard-panel-header h4 {
        margin: 0 0 3px;
        color: #27324a;
        font-size: 17px;
        font-weight: 700;
    }

    .dashboard-panel-header p {
        margin: 0;
        color: var(--dashboard-muted);
        font-size: 12px;
    }

    .dashboard-panel-body {
        padding: 22px 24px;
    }

    .sgc-status-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
    }

    .sgc-status {
        padding: 18px;
        border: 1px solid var(--dashboard-border);
        border-radius: 14px;
        background: #fbfcff;
    }

    .checklist-summary {
        margin-bottom: 18px;
    }

    .sgc-status-heading {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 15px;
    }

    .sgc-status h5 {
        margin: 0 0 4px;
        color: #27324a;
        font-size: 14px;
        font-weight: 700;
    }

    .sgc-status small {
        color: var(--dashboard-muted);
    }

    .sgc-percentage {
        font-size: 18px;
        font-weight: 700;
    }

    .sgc-one .sgc-percentage { color: #7653c6; }
    .sgc-two .sgc-percentage { color: #d68a18; }
    .sgc-three .sgc-percentage { color: #16835a; }

    .sgc-progress {
        height: 8px;
        border-radius: 999px;
        background: #edf0f6;
        overflow: hidden;
    }

    .sgc-progress span {
        display: block;
        height: 100%;
        border-radius: inherit;
    }

    .sgc-one .sgc-progress span { background: #7653c6; }
    .sgc-two .sgc-progress span { background: #e7a12d; }
    .sgc-three .sgc-progress span { background: #20a875; }

    .principle-card {
        margin-bottom: 12px;
        border: 1px solid var(--dashboard-border);
        border-radius: 13px;
        overflow: hidden;
    }

    .principle-card:last-child {
        margin-bottom: 0;
    }

    .principle-header {
        padding: 0;
        border: 0;
        background: #fff;
    }

    .principle-toggle {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        width: 100%;
        padding: 17px 19px;
        color: #27324a;
        font-weight: 700;
    }

    .principle-toggle:hover {
        color: var(--dashboard-primary);
        background: #fff7f9;
        text-decoration: none;
    }

    .principle-toggle i {
        transition: transform .2s ease;
    }

    .principle-toggle[aria-expanded="true"] i {
        transform: rotate(180deg);
    }

    .principle-description {
        margin: 0;
        padding: 17px 19px;
        border-top: 1px solid var(--dashboard-border);
        color: #596277;
        background: #fafbfe;
        font-size: 13px;
    }

    .assessment-table-wrap {
        padding: 8px 16px 18px;
    }

    .assessment-table {
        min-width: 920px;
        margin: 0;
    }

    .assessment-table thead th {
        padding: 11px 10px;
        border-top: 0;
        color: #687086;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        vertical-align: middle;
    }

    .assessment-table tbody td {
        padding: 12px 10px;
        vertical-align: middle;
    }

    .indicator-number {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 34px;
        height: 34px;
        padding: 0 8px;
        border-radius: 9px;
        color: var(--dashboard-primary-dark);
        background: #f9e9ee;
        font-size: 11px;
        font-weight: 700;
    }

    .indicator-description {
        min-width: 300px;
        color: #39445b;
        font-size: 12px;
        line-height: 1.55;
    }

    .rate-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 42px;
        padding: 6px 9px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        transition: transform .15s ease;
    }

    .rate-count:hover {
        transform: translateY(-1px);
        text-decoration: none;
    }

    .rate-one { color: #8b1e3f; background: #f9e9ee; }
    .rate-two { color: #7653c6; background: #f1ecfb; }
    .rate-three { color: #17718d; background: #e8f6fa; }
    .rate-four { color: #b57310; background: #fff4dc; }

    .admin-dashboard .alert {
        border: 0;
        border-radius: 12px;
        box-shadow: 0 6px 18px rgba(31, 45, 75, .07);
    }

    .dashboard-modal .modal-content {
        border: 0;
        border-radius: 15px;
        box-shadow: 0 18px 45px rgba(31, 45, 75, .2);
        overflow: hidden;
    }

    .dashboard-modal .modal-header {
        color: #fff;
        background: linear-gradient(135deg, #64142d, #a83255);
    }

    @media (max-width: 991.98px) {
        .sgc-status-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 767.98px) {
        .dashboard-hero {
            align-items: flex-start;
            flex-direction: column;
            padding: 22px;
            border-radius: 14px;
        }

        .dashboard-year-button {
            justify-content: center;
            width: 100%;
        }

        .dashboard-panel-header {
            align-items: flex-start;
            flex-direction: column;
            padding: 18px;
        }

        .dashboard-panel-body {
            padding: 16px;
        }

        .principle-toggle {
            padding: 15px;
        }

        .assessment-table-wrap {
            padding: 5px 10px 15px;
        }
    }
</style>

<div class="admin-dashboard">
    <div class="dashboard-hero">
        <div>
            <h1>Welcome, <?= html_escape($display_name); ?></h1>
            <p>Manage division readiness, school signups, and user accounts across the active region.</p>
        </div>
    </div>

    <?php if ($this->session->flashdata('success')) : ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <?= $this->session->flashdata('success'); ?>
        </div>
    <?php endif; ?>

    <?php if ($this->session->flashdata('danger')) : ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <?= $this->session->flashdata('danger'); ?>
        </div>
    <?php endif; ?>

    <div class="row dashboard-stats">
        <div class="col-md-6 col-xl-3">
            <a href="<?= $division_list_url; ?>" class="dashboard-stat-link" title="View divisions">
                <div class="dashboard-stat-card">
                    <div class="dashboard-stat-top">
                        <div>
                            <h3><?= $division_total; ?></h3>
                            <p>Divisions</p>
                            <span class="dashboard-link-hint"><i class="mdi mdi-arrow-right"></i> View details</span>
                        </div>
                        <span class="dashboard-stat-icon"><i class="mdi mdi-office-building-outline"></i></span>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-xl-3">
            <a href="<?= $school_directory_url; ?>" class="dashboard-stat-link" title="Browse schools by division">
                <div class="dashboard-stat-card">
                    <div class="dashboard-stat-top">
                        <div>
                            <h3><?= $district_total; ?></h3>
                            <p>Districts</p>
                            <span class="dashboard-link-hint"><i class="mdi mdi-arrow-right"></i> Browse schools</span>
                        </div>
                        <span class="dashboard-stat-icon"><i class="mdi mdi-map-marker-multiple-outline"></i></span>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-xl-3">
            <a href="<?= $school_directory_url; ?>" class="dashboard-stat-link" title="View school signups">
                <div class="dashboard-stat-card">
                    <div class="dashboard-stat-top">
                        <div>
                            <h3><?= $registered_school_total; ?></h3>
                            <p>School Signup</p>
                            <span class="dashboard-link-hint"><i class="mdi mdi-arrow-right"></i> View details</span>
                        </div>
                        <span class="dashboard-stat-icon"><i class="mdi mdi-school-outline"></i></span>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-xl-3">
            <a href="<?= $user_list_url; ?>" class="dashboard-stat-link" title="Manage users">
                <div class="dashboard-stat-card">
                    <div class="dashboard-stat-top">
                        <div>
                            <h3><?= $user_total; ?></h3>
                            <p>Manage Users</p>
                            <span class="dashboard-link-hint"><i class="mdi mdi-arrow-right"></i> Open list</span>
                        </div>
                        <span class="dashboard-stat-icon"><i class="mdi mdi-account-supervisor-outline"></i></span>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <section class="dashboard-panel">
        <div class="dashboard-panel-header">
            <div>
                <h4>Regional School Signup Coverage</h4>
                <p>Actual school signups compared with the total number of schools encoded by divisions in the region.</p>
            </div>
            <small class="text-muted">
                <?= $registered_school_total; ?> of <?= $encoded_school_total; ?> schools
            </small>
        </div>
        <div class="dashboard-panel-body">
            <div class="sgc-status-grid">
                <a href="<?= $school_directory_url; ?>" class="dashboard-count-link" title="View school signup progress">
                    <div class="sgc-status">
                        <div class="sgc-status-heading">
                            <div>
                                <h5>Signup Progress</h5>
                                <small>
                                    <?= $encoded_school_total > 0 ? 'Based on total schools encoded by divisions' : 'Division school totals are not yet fully encoded'; ?>
                                </small>
                            </div>
                            <span class="sgc-percentage" style="color:#8b1e3f;"><?= number_format($signup_rate, 1); ?>%</span>
                        </div>
                        <div class="sgc-progress"><span style="width: <?= $signup_progress_width; ?>%; background:#8b1e3f;"></span></div>
                    </div>
                </a>

                <a href="<?= $school_directory_url; ?>" class="dashboard-count-link" title="View signed-up schools">
                    <div class="sgc-status">
                        <div class="sgc-status-heading">
                            <div>
                                <h5>Signed-Up Schools</h5>
                                <small>Current schools registered in the system for this region.</small>
                            </div>
                            <span class="sgc-percentage" style="color:#1f4f8f;"><?= $registered_school_total; ?></span>
                        </div>
                        <div class="sgc-progress"><span style="width: <?= $encoded_school_total > 0 ? min(100, ($registered_school_total / $encoded_school_total) * 100) : 0; ?>%; background:#1f4f8f;"></span></div>
                    </div>
                </a>

                <a href="<?= $division_list_url; ?>" class="dashboard-count-link" title="View division setup status">
                    <div class="sgc-status">
                        <div class="sgc-status-heading">
                            <div>
                                <h5>Division Setup</h5>
                                <small><?= $configured_division_total; ?> of <?= $division_total; ?> divisions encoded their total schools.</small>
                            </div>
                            <span class="sgc-percentage" style="color:#16835a;"><?= number_format($division_setup_rate, 1); ?>%</span>
                        </div>
                        <div class="sgc-progress"><span style="width: <?= $division_setup_width; ?>%; background:#16835a;"></span></div>
                    </div>
                </a>
            </div>
        </div>
    </section>

</div>
