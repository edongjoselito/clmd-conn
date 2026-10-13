<?php
$division_id = !empty($division->id) ? (int) $division->id : (int) $this->uri->segment(3);
$division_name = !empty($division->description) ? trim((string) $division->description) : 'Division';
$selected_submission = isset($selected_submission) ? (string) $selected_submission : 'sgod_action_plan';
$school_total = !empty($data) ? count($data) : 0;
$district_lookup = array();
$participating_district_ids = array();
$all_completed_total = 0;

$format_title = function ($value) {
    $value = trim((string) $value);

    if ($value === '') {
        return 'Not Available';
    }

    return mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');
};

$submission_meta = array(
    'sgod_action_plan' => array(
        'label' => 'Action Plan',
        'short_label' => 'Action Plan',
        'description' => 'Schools with encoded action plans for the active fiscal year.',
        'icon' => 'mdi-clipboard-check-outline',
        'class' => 'action-plan',
        'url' => 'Pages/sbm_action_plan_pview_district/'
    ),
    'sbm' => array(
        'label' => 'Self-Assessment Checklist',
        'short_label' => 'Self-Assessment',
        'description' => 'Schools that completed the self-assessment checklist.',
        'icon' => 'mdi-format-list-checks',
        'class' => 'assessment',
        'url' => 'Pages/checklist_district/'
    ),
    'sbm_ta' => array(
        'label' => 'TA Form',
        'short_label' => 'TA Form',
        'description' => 'Schools with submitted thematic analysis and technical assistance forms.',
        'icon' => 'mdi-lifebuoy',
        'class' => 'ta-form',
        'url' => 'Pages/tapr_form_district/'
    )
);

if (!isset($submission_meta[$selected_submission])) {
    $selected_submission = 'sgod_action_plan';
}

foreach ($districts as $district_row) {
    $district_lookup[(string) $district_row->id] = trim((string) $district_row->description);
}

foreach ($data as $row) {
    $school_id = isset($row->schoolID) ? (string) $row->schoolID : '';
    $district_id = !empty($row->district_id) ? (string) $row->district_id : (string) $row->district;

    if ($district_id !== '') {
        $participating_district_ids[$district_id] = true;
    }

    $has_action_plan = !empty($submission_status['sgod_action_plan'][$school_id]);
    $has_assessment = !empty($submission_status['sbm'][$school_id]);
    $has_ta_form = !empty($submission_status['sbm_ta'][$school_id]);

    if ($has_action_plan && $has_assessment && $has_ta_form) {
        $all_completed_total++;
    }
}

$current_submission = $submission_meta[$selected_submission];
$participating_district_total = count($participating_district_ids);
?>

<style>
    .division-submission-page {
        --division-primary: #8b1e3f;
        --division-primary-dark: #64142d;
        --division-primary-soft: #f9e9ee;
        --division-border: #e8ecf4;
        --division-text: #27324a;
        --division-muted: #6b7280;
        --division-surface: #ffffff;
        --division-surface-alt: #fbfcff;
        --division-shadow: 0 14px 34px rgba(139, 30, 63, .18);
    }

    .division-submission-hero {
        position: relative;
        margin: 18px 0 22px;
        padding: 28px;
        border-radius: 20px;
        overflow: hidden;
        color: #fff;
        background:
            radial-gradient(circle at top right, rgba(255, 255, 255, .18), transparent 28%),
            linear-gradient(135deg, #64142d 0%, #a83255 55%, #d46d86 100%);
        box-shadow: var(--division-shadow);
    }

    .division-submission-hero::after {
        content: "";
        position: absolute;
        inset: auto -60px -90px auto;
        width: 220px;
        height: 220px;
        border-radius: 50%;
        background: rgba(255, 255, 255, .08);
    }

    .division-submission-hero > * {
        position: relative;
        z-index: 1;
    }

    .division-submission-hero-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 22px;
    }

    .division-submission-hero h2 {
        margin: 0 0 8px;
        color: #fff;
        font-size: 27px;
        font-weight: 700;
        line-height: 1.2;
    }

    .division-submission-hero p {
        margin: 0;
        max-width: 760px;
        color: rgba(255, 255, 255, .84);
        font-size: 14px;
    }

    .submission-focus-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 11px 16px;
        border: 1px solid rgba(255, 255, 255, .26);
        border-radius: 999px;
        color: #fff;
        background: rgba(255, 255, 255, .14);
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
        backdrop-filter: blur(5px);
    }

    .division-submission-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
    }

    .division-stat-card {
        padding: 16px 18px;
        border: 1px solid rgba(255, 255, 255, .16);
        border-radius: 16px;
        background: rgba(255, 255, 255, .12);
        backdrop-filter: blur(4px);
    }

    .division-stat-card span {
        display: block;
        color: rgba(255, 255, 255, .76);
        font-size: 11px;
        font-weight: 600;
        letter-spacing: .06em;
        text-transform: uppercase;
    }

    .division-stat-card strong {
        display: block;
        margin-top: 5px;
        color: #fff;
        font-size: 26px;
        font-weight: 700;
        line-height: 1.1;
    }

    .division-submission-card {
        border: 1px solid var(--division-border);
        border-radius: 18px;
        background: var(--division-surface);
        box-shadow: 0 10px 30px rgba(31, 45, 75, .08);
        overflow: hidden;
    }

    .division-submission-card .card-body {
        padding: 0;
    }

    .division-submission-toolbar {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        padding: 22px 24px 16px;
        border-bottom: 1px solid var(--division-border);
        background: linear-gradient(180deg, #fff 0%, #fdfcff 100%);
    }

    .division-submission-toolbar h4 {
        margin: 0 0 5px;
        color: var(--division-text);
        font-size: 18px;
        font-weight: 700;
    }

    .division-submission-toolbar p {
        margin: 0;
        color: var(--division-muted);
        font-size: 13px;
    }

    .submission-switcher {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
        padding: 0 24px 18px;
        border-bottom: 1px solid var(--division-border);
        background: #fff;
    }

    .submission-switcher-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 9px 13px;
        border: 1px solid var(--division-border);
        border-radius: 999px;
        color: var(--division-muted);
        background: #fff;
        font-size: 12px;
        font-weight: 700;
        transition: all .18s ease;
    }

    .submission-switcher-link:hover {
        color: var(--division-primary);
        border-color: rgba(139, 30, 63, .18);
        background: #fff7f9;
    }

    .submission-switcher-link.active {
        color: #fff;
        border-color: transparent;
        background: linear-gradient(135deg, #8b1e3f, #bf4f6d);
        box-shadow: 0 10px 22px rgba(139, 30, 63, .18);
    }

    .division-submission-table-wrap {
        padding: 12px 24px 24px;
    }

    .division-submission-table {
        margin: 0;
        border-collapse: separate;
        border-spacing: 0 9px;
    }

    .division-submission-table thead th {
        padding: 10px 14px;
        border: 0;
        color: #687086;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .05em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .division-submission-table tbody td {
        padding: 14px;
        border-top: 1px solid var(--division-border);
        border-bottom: 1px solid var(--division-border);
        vertical-align: middle;
        background: #fff;
    }

    .division-submission-table tbody td:first-child {
        border-left: 1px solid var(--division-border);
        border-radius: 12px 0 0 12px;
    }

    .division-submission-table tbody td:last-child {
        border-right: 1px solid var(--division-border);
        border-radius: 0 12px 12px 0;
    }

    .division-submission-table tbody tr:hover td {
        background: #fff8fa;
    }

    .school-sequence {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: 11px;
        color: var(--division-primary-dark);
        background: var(--division-primary-soft);
        font-size: 12px;
        font-weight: 700;
    }

    .school-profile {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 230px;
    }

    .school-profile-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        flex: 0 0 42px;
        border-radius: 13px;
        color: #fff;
        background: linear-gradient(135deg, #8b1e3f, #c65a77);
        font-size: 20px;
    }

    .school-profile strong {
        display: block;
        color: var(--division-text);
        font-size: 14px;
        font-weight: 700;
        line-height: 1.35;
    }

    .school-profile small {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        margin-top: 4px;
        color: var(--division-muted);
        font-size: 12px;
    }

    .district-pill {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 8px 12px;
        border-radius: 999px;
        color: #7a3952;
        background: #fdf1f5;
        font-size: 12px;
        font-weight: 700;
    }

    .submission-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        min-width: 118px;
        padding: 8px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        transition: transform .16s ease, box-shadow .16s ease;
    }

    .submission-action.available:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 18px rgba(31, 45, 75, .12);
    }

    .submission-action.action-plan.available {
        color: #147a50;
        background: #e6f7ef;
    }

    .submission-action.assessment.available {
        color: #176f8c;
        background: #e6f6fb;
    }

    .submission-action.ta-form.available {
        color: #8b1e3f;
        background: #f9e9ee;
    }

    .submission-action.missing {
        color: #8b93a7;
        background: #f3f5fa;
        cursor: default;
    }

    .division-empty-state {
        padding: 48px 24px;
        text-align: center;
    }

    .division-empty-state i {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 70px;
        height: 70px;
        margin-bottom: 14px;
        border-radius: 20px;
        color: var(--division-primary);
        background: var(--division-primary-soft);
        font-size: 32px;
    }

    .division-empty-state h5 {
        margin: 0 0 6px;
        color: var(--division-text);
        font-size: 18px;
        font-weight: 700;
    }

    .division-empty-state p {
        margin: 0;
        color: var(--division-muted);
    }

    .division-submission-page .alert {
        border: 0;
        border-radius: 13px;
        box-shadow: 0 8px 20px rgba(31, 45, 75, .08);
    }

    @media (max-width: 991.98px) {
        .division-submission-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 767.98px) {
        .division-submission-hero {
            padding: 22px;
            border-radius: 16px;
        }

        .division-submission-hero-top,
        .division-submission-toolbar {
            flex-direction: column;
        }

        .division-submission-stats {
            grid-template-columns: 1fr;
        }

        .submission-switcher,
        .division-submission-table-wrap {
            padding-left: 14px;
            padding-right: 14px;
        }

        .division-submission-table thead {
            display: none;
        }

        .division-submission-table,
        .division-submission-table tbody,
        .division-submission-table tr,
        .division-submission-table td {
            display: block;
            width: 100%;
        }

        .division-submission-table {
            border-spacing: 0;
        }

        .division-submission-table tbody tr {
            margin-bottom: 14px;
            padding: 8px 14px;
            border: 1px solid var(--division-border);
            border-radius: 14px;
            background: #fff;
        }

        .division-submission-table tbody td,
        .division-submission-table tbody td:first-child,
        .division-submission-table tbody td:last-child {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 10px 0;
            border: 0;
            border-bottom: 1px solid #eef1f6;
            border-radius: 0;
        }

        .division-submission-table tbody td:last-child {
            border-bottom: 0;
        }

        .division-submission-table tbody td::before {
            content: attr(data-label);
            color: var(--division-muted);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .05em;
            text-transform: uppercase;
        }

        .division-submission-table tbody td.school-cell {
            align-items: flex-start;
            flex-direction: column;
        }

        .school-profile {
            min-width: 0;
        }

        .submission-action {
            min-width: 0;
        }
    }
</style>

<div class="division-submission-page">
    <div class="row">
        <div class="col-12">
            <div class="division-submission-hero">
                <div class="division-submission-hero-top">
                    <div>
                        <h2><i class="mdi <?= html_escape($current_submission['icon']); ?> mr-2"></i><?= html_escape($current_submission['short_label']); ?> Overview</h2>
                        <p>
                            <?= html_escape($current_submission['description']); ?>
                            This list covers <?= html_escape($format_title($division_name)); ?> for fiscal year <?= html_escape($this->session->fy); ?>.
                        </p>
                    </div>
                    <span class="submission-focus-badge">
                        <i class="mdi mdi-domain"></i>
                        <?= html_escape($format_title($division_name)); ?>
                    </span>
                </div>

                <div class="division-submission-stats">
                    <div class="division-stat-card">
                        <span>Schools In View</span>
                        <strong><?= $school_total; ?></strong>
                    </div>
                    <div class="division-stat-card">
                        <span>District Coverage</span>
                        <strong><?= $participating_district_total; ?></strong>
                    </div>
                    <div class="division-stat-card">
                        <span>All 3 Completed</span>
                        <strong><?= $all_completed_total; ?></strong>
                    </div>
                    <div class="division-stat-card">
                        <span>Active Fiscal Year</span>
                        <strong><?= html_escape($this->session->fy); ?></strong>
                    </div>
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
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card division-submission-card">
                <div class="card-body">
                    <div class="division-submission-toolbar">
                        <div>
                            <h4>School Submission Details</h4>
                            <p>Switch between submission types or open the available school records directly from this list.</p>
                        </div>
                        <small class="text-muted">Showing <?= $school_total; ?> <?= $school_total === 1 ? 'school' : 'schools'; ?></small>
                    </div>

                    <div class="submission-switcher">
                        <?php foreach ($submission_meta as $submission_key => $submission) : ?>
                            <a
                                class="submission-switcher-link <?= $submission_key === $selected_submission ? 'active' : ''; ?>"
                                href="<?= base_url(); ?>Pages/district_list_division/<?= $division_id; ?>/<?= $submission_key; ?>"
                            >
                                <i class="mdi <?= html_escape($submission['icon']); ?>"></i>
                                <?= html_escape($submission['short_label']); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>

                    <?php if (!empty($data)) : ?>
                        <div class="division-submission-table-wrap table-responsive">
                            <table id="datatable-buttons" class="table division-submission-table">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>School</th>
                                        <th>District</th>
                                        <th class="text-center">Action Plan</th>
                                        <th class="text-center">Self-Assessment</th>
                                        <th class="text-center">TA Form</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $counter = 1; ?>
                                    <?php foreach ($data as $row) : ?>
                                        <?php
                                        $school_id = isset($row->schoolID) ? (string) $row->schoolID : '';
                                        $school_name = isset($row->schoolName) ? $format_title($row->schoolName) : 'Not Available';
                                        $district_id = !empty($row->district_id) ? (string) $row->district_id : (string) $row->district;
                                        $district_name = isset($district_lookup[$district_id])
                                            ? $format_title($district_lookup[$district_id])
                                            : ($district_id !== '' ? $format_title($district_id) : 'Unknown District');
                                        ?>
                                        <tr>
                                            <td data-label="No.">
                                                <span class="school-sequence"><?= $counter++; ?></span>
                                            </td>
                                            <td class="school-cell" data-label="School">
                                                <div class="school-profile">
                                                    <span class="school-profile-icon"><i class="mdi mdi-school-outline"></i></span>
                                                    <div>
                                                        <strong><?= html_escape($school_name); ?></strong>
                                                        <small>
                                                            <i class="mdi mdi-pound"></i>
                                                            School ID: <?= html_escape($school_id); ?>
                                                        </small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td data-label="District">
                                                <span class="district-pill">
                                                    <i class="mdi mdi-map-marker-outline"></i>
                                                    <?= html_escape($district_name); ?>
                                                </span>
                                            </td>

                                            <?php foreach ($submission_meta as $submission_key => $submission) : ?>
                                                <?php $has_submission = !empty($submission_status[$submission_key][$school_id]); ?>
                                                <td class="text-center" data-label="<?= html_escape($submission['short_label']); ?>">
                                                    <?php if ($has_submission) : ?>
                                                        <a
                                                            target="_blank"
                                                            href="<?= base_url() . $submission['url'] . rawurlencode($school_id); ?>"
                                                            class="submission-action available <?= html_escape($submission['class']); ?>"
                                                        >
                                                            <i class="mdi mdi-open-in-new"></i>
                                                            View
                                                        </a>
                                                    <?php else : ?>
                                                        <span class="submission-action missing">
                                                            <i class="mdi mdi-clock-outline"></i>
                                                            Pending
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else : ?>
                        <div class="division-empty-state">
                            <i class="mdi mdi-file-search-outline"></i>
                            <h5>No submission records found</h5>
                            <p>There are no schools to display for <?= html_escape($current_submission['short_label']); ?> in fiscal year <?= html_escape($this->session->fy); ?>.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
