<?php
$format_title = static function ($value) {
    $value = trim((string) $value);
    if ($value === '') {
        return '';
    }

    return function_exists('mb_convert_case')
        ? mb_convert_case($value, MB_CASE_TITLE, 'UTF-8')
        : ucwords(strtolower($value));
};

$escape = static function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};

$district_id = !empty($district->id) ? (int) $district->id : (int) $this->session->district;
$district_name = !empty($district->description)
    ? $format_title($district->description)
    : $format_title($this->session->user);
$division_name = !empty($division->description)
    ? $format_title($division->description)
    : 'Division';
$school_total = isset($school_total) ? (int) $school_total : 0;
$checklist_submission_total = isset($checklist_submission_count) ? (int) $checklist_submission_count : 0;
$completed_checklist_total = isset($completed_checklist_count) ? (int) $completed_checklist_count : 0;
$ta_submission_total = isset($ta_submission_count) ? (int) $ta_submission_count : 0;
$action_plan_submission_total = isset($action_plan_submission_count) ? (int) $action_plan_submission_count : 0;
$tech_entry_total = isset($tech_entry_count) ? (int) $tech_entry_count : 0;

$sgc_not_organized = isset($sgc_counts[1]) ? (int) $sgc_counts[1] : 0;
$sgc_not_functional = isset($sgc_counts[2]) ? (int) $sgc_counts[2] : 0;
$sgc_functional = isset($sgc_counts[3]) ? (int) $sgc_counts[3] : 0;
$sgc_total = $sgc_not_organized + $sgc_not_functional + $sgc_functional;

$percentage = static function ($value, $total) {
    return $total > 0 ? ($value / $total) * 100 : 0;
};

$checklist_submission_rate = $percentage($checklist_submission_total, $school_total);
$completed_checklist_rate = $percentage($completed_checklist_total, $school_total);
$ta_submission_rate = $percentage($ta_submission_total, $school_total);
$action_plan_submission_rate = $percentage($action_plan_submission_total, $school_total);

$sgc_percentages = array(
    1 => $percentage($sgc_not_organized, $sgc_total),
    2 => $percentage($sgc_not_functional, $sgc_total),
    3 => $percentage($sgc_functional, $sgc_total),
);

$schools_url = base_url() . 'pages/schools_district/' . rawurlencode((string) $district_id);
$checklist_url = base_url() . 'Pages/school_list_division/' . rawurlencode((string) $district_id) . '/sbm';
$ta_forms_url = base_url() . 'Pages/school_list_division/' . rawurlencode((string) $district_id) . '/sbm_ta';
$action_plan_url = base_url() . 'Pages/school_list_division/' . rawurlencode((string) $district_id) . '/sgod_action_plan';
$tech_workspace_url = base_url() . 'Pages/sbm_district_tech';
$tech_new_url = base_url() . 'Pages/sbm_district_tech_new';

$rate_details = array(
    1 => array('label' => 'Not Yet Manifested', 'class' => 'rate-one'),
    2 => array('label' => 'Rarely Manifested', 'class' => 'rate-two'),
    3 => array('label' => 'Frequently Manifested', 'class' => 'rate-three'),
    4 => array('label' => 'Always Manifested', 'class' => 'rate-four'),
);

$submission_cards = array(
    array(
        'title' => 'Total Schools',
        'count' => $school_total,
        'summary' => 'Active schools in this district.',
        'url' => $schools_url,
        'icon' => 'mdi-school-outline',
        'meta' => 'Open school directory',
    ),
    array(
        'title' => 'Self-Assessment Schools',
        'count' => $checklist_submission_total,
        'summary' => number_format($checklist_submission_rate, 1) . '% with an SBM checklist record.',
        'url' => $checklist_url,
        'icon' => 'mdi-format-list-checks',
        'meta' => 'Review checklist submissions',
    ),
    array(
        'title' => 'Finalized Checklists',
        'count' => $completed_checklist_total,
        'summary' => number_format($completed_checklist_rate, 1) . '% have finalized their checklist.',
        'url' => $checklist_url,
        'icon' => 'mdi-clipboard-check-outline',
        'meta' => 'Open checklist school list',
    ),
    array(
        'title' => 'TA Form Schools',
        'count' => $ta_submission_total,
        'summary' => number_format($ta_submission_rate, 1) . '% have TA form entries.',
        'url' => $ta_forms_url,
        'icon' => 'mdi-lifebuoy',
        'meta' => 'Review TA form submissions',
    ),
);

$coverage_cards = array(
    array(
        'title' => 'Self-Assessment Coverage',
        'count' => $checklist_submission_total,
        'rate' => $checklist_submission_rate,
        'summary' => $school_total > 0
            ? $checklist_submission_total . ' of ' . $school_total . ' schools have checklist data.'
            : 'No schools are assigned to this district yet.',
        'url' => $checklist_url,
    ),
    array(
        'title' => 'Checklist Finalization',
        'count' => $completed_checklist_total,
        'rate' => $completed_checklist_rate,
        'summary' => $school_total > 0
            ? $completed_checklist_total . ' of ' . $school_total . ' schools have finalized the checklist.'
            : 'No schools are assigned to this district yet.',
        'url' => $checklist_url,
    ),
    array(
        'title' => 'TA Form Coverage',
        'count' => $ta_submission_total,
        'rate' => $ta_submission_rate,
        'summary' => $school_total > 0
            ? $ta_submission_total . ' of ' . $school_total . ' schools have TA form records.'
            : 'No schools are assigned to this district yet.',
        'url' => $ta_forms_url,
    ),
    array(
        'title' => 'Action Plan Coverage',
        'count' => $action_plan_submission_total,
        'rate' => $action_plan_submission_rate,
        'summary' => $school_total > 0
            ? $action_plan_submission_total . ' of ' . $school_total . ' schools have action plan entries.'
            : 'No schools are assigned to this district yet.',
        'url' => $action_plan_url,
    ),
);

$work_queue_cards = array(
    array(
        'title' => 'Open School Directory',
        'description' => 'Review the full district school list and school account details.',
        'url' => $schools_url,
        'icon' => 'mdi-domain',
    ),
    array(
        'title' => 'Review Self-Assessment',
        'description' => 'Open the school queue for checklist viewing and follow-up.',
        'url' => $checklist_url,
        'icon' => 'mdi-format-list-checks',
    ),
    array(
        'title' => 'Review TA Forms',
        'description' => 'Check district TA form submissions and district reviewer pages.',
        'url' => $ta_forms_url,
        'icon' => 'mdi-lifebuoy',
    ),
    array(
        'title' => 'District TA Workspace',
        'description' => 'Manage district technical assistance plans and interventions.',
        'url' => $tech_workspace_url,
        'icon' => 'mdi-wrench-outline',
    ),
);
?>

<style>
    .district-dashboard {
        --dd-navy: var(--llcm-navy, #3b0718);
        --dd-blue: var(--llcm-blue, #922c43);
        --dd-sky: var(--llcm-sky, #fbf4f5);
        --dd-line: var(--llcm-border, #ead7dc);
        --dd-ink: var(--llcm-ink, #233342);
        --dd-muted: #6b7f92;
        --dd-faint: #9db2c4;
        --dd-amber: #9a6b1f;
        --dd-green: #1d6b3c;
        --dd-radius: 10px;
        --dd-shadow: 0 4px 16px rgba(20, 62, 94, .05);
        margin-bottom: 24px;
        color: var(--dd-ink);
    }

    .district-dashboard .alert {
        border: 0;
        border-radius: var(--dd-radius);
        box-shadow: var(--dd-shadow);
    }

    /* ---------- Page header ---------- */
    .dd-header {
        display: flex;
        flex-wrap: wrap;
        gap: 14px 18px;
        align-items: center;
        justify-content: space-between;
        margin: 16px 0 18px;
        padding: 18px 22px;
        border-radius: var(--dd-radius);
        color: #fff;
        background: linear-gradient(118deg, var(--dd-navy), var(--dd-blue));
    }

    .dd-eyebrow {
        display: block;
        margin-bottom: 4px;
        color: rgba(255, 255, 255, .75);
        font-size: .72rem;
        font-weight: 600;
        letter-spacing: .09em;
        text-transform: uppercase;
    }

    .dd-header h1 {
        margin: 0;
        color: #fff;
        font-size: 1.45rem;
        font-weight: 600;
        line-height: 1.25;
    }

    .dd-header-meta {
        margin: 6px 0 0;
        color: rgba(255, 255, 255, .85);
        font-size: .85rem;
    }

    .dd-header-meta span + span::before {
        content: "\00b7";
        margin: 0 .5rem;
        color: rgba(255, 255, 255, .5);
    }

    .dd-header-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .dd-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 13px;
        border: 1px solid transparent;
        border-radius: 8px;
        font-size: .83rem;
        font-weight: 600;
        text-decoration: none;
        white-space: nowrap;
        transition: background .15s ease, border-color .15s ease, color .15s ease;
    }

    .dd-btn-ghost {
        color: #fff;
        border-color: rgba(255, 255, 255, .45);
        background: transparent;
    }

    .dd-btn-ghost:hover {
        color: var(--dd-navy);
        background: #fff;
        border-color: #fff;
        text-decoration: none;
    }

    .dd-btn-solid {
        color: var(--dd-navy);
        background: #fff;
        border-color: #fff;
    }

    .dd-btn-solid:hover {
        color: var(--dd-navy);
        background: var(--dd-sky);
        border-color: var(--dd-sky);
        text-decoration: none;
    }

    /* ---------- Metric tiles ---------- */
    .dd-metrics {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 18px;
    }

    .dd-metric {
        display: block;
        padding: 15px 17px;
        border: 1px solid var(--dd-line);
        border-radius: var(--dd-radius);
        background: #fff;
        box-shadow: var(--dd-shadow);
        color: inherit;
        text-decoration: none;
        transition: border-color .15s ease, box-shadow .15s ease, transform .15s ease;
    }

    .dd-metric:hover,
    .dd-metric:focus {
        border-color: #bcd7e9;
        box-shadow: 0 8px 22px rgba(20, 62, 94, .09);
        color: inherit;
        text-decoration: none;
        transform: translateY(-1px);
    }

    .dd-metric-top {
        display: flex;
        gap: 12px;
        align-items: center;
        justify-content: space-between;
    }

    .dd-metric-label {
        color: var(--dd-muted);
        font-size: .74rem;
        font-weight: 600;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .dd-metric-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 32px;
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: var(--dd-sky);
        color: var(--dd-blue);
        font-size: 1rem;
    }

    .dd-metric-value {
        display: block;
        margin: 10px 0 0;
        color: var(--dd-navy);
        font-size: 1.6rem;
        font-weight: 600;
        line-height: 1.1;
        font-variant-numeric: tabular-nums;
    }

    .dd-metric-note {
        display: block;
        margin-top: 4px;
        color: var(--dd-muted);
        font-size: .8rem;
        line-height: 1.45;
    }

    /* ---------- Panels ---------- */
    .dd-layout {
        display: grid;
        grid-template-columns: minmax(0, 1.15fr) minmax(0, .85fr);
        gap: 16px;
        margin-bottom: 18px;
    }

    .dd-panel {
        margin-bottom: 18px;
        border: 1px solid var(--dd-line);
        border-radius: var(--dd-radius);
        background: #fff;
        box-shadow: var(--dd-shadow);
        overflow: hidden;
    }

    .dd-layout .dd-panel { margin-bottom: 0; }

    .dd-panel-head {
        display: flex;
        flex-wrap: wrap;
        gap: 6px 14px;
        align-items: baseline;
        justify-content: space-between;
        padding: 15px 20px;
        border-bottom: 1px solid var(--dd-line);
    }

    .dd-panel-head h4 {
        margin: 0;
        color: var(--dd-navy);
        font-size: 1.02rem;
        font-weight: 600;
    }

    .dd-panel-head p {
        flex: 1 0 100%;
        margin: 0;
        color: var(--dd-muted);
        font-size: .84rem;
        line-height: 1.5;
    }

    .dd-tag {
        display: inline-flex;
        gap: 5px;
        align-items: center;
        padding: 3px 11px;
        border-radius: 999px;
        background: var(--dd-sky);
        color: var(--dd-navy);
        font-size: .74rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .dd-panel-body { padding: 6px 20px 16px; }
    .dd-panel-body-padded { padding: 18px 20px; }

    /* ---------- Coverage rows ---------- */
    .dd-coverage-row {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 150px;
        gap: 12px 18px;
        align-items: center;
        padding: 13px 0;
        border-top: 1px solid #edf3f8;
        color: inherit;
        text-decoration: none;
    }

    .dd-coverage-row:first-of-type { border-top: 0; }

    a.dd-coverage-row:hover,
    a.dd-coverage-row:focus {
        color: inherit;
        text-decoration: none;
    }

    a.dd-coverage-row:hover .dd-coverage-title { color: var(--dd-blue); }

    .dd-coverage-title {
        margin: 0 0 2px;
        color: var(--dd-navy);
        font-size: .9rem;
        font-weight: 600;
    }

    .dd-coverage-sub {
        display: block;
        color: var(--dd-muted);
        font-size: .78rem;
        line-height: 1.45;
    }

    .dd-coverage-meter {
        display: flex;
        gap: 10px;
        align-items: center;
        justify-content: flex-end;
    }

    .dd-coverage-bar {
        flex: 1 1 auto;
        height: 6px;
        border-radius: 999px;
        background: #e7eff5;
        overflow: hidden;
    }

    .dd-coverage-bar span {
        display: block;
        height: 100%;
        border-radius: inherit;
        background: var(--dd-blue);
    }

    .dd-coverage-pct {
        flex: 0 0 auto;
        min-width: 46px;
        color: var(--dd-navy);
        font-size: .88rem;
        font-weight: 600;
        text-align: right;
        font-variant-numeric: tabular-nums;
    }

    /* ---------- Work queue ---------- */
    .dd-queue { margin: 0; padding: 0; list-style: none; }

    .dd-queue a {
        display: grid;
        grid-template-columns: 32px minmax(0, 1fr) auto;
        gap: 12px;
        align-items: center;
        padding: 12px 0;
        border-top: 1px solid #edf3f8;
        color: inherit;
        text-decoration: none;
    }

    .dd-queue li:first-child a { border-top: 0; }

    .dd-queue a:hover,
    .dd-queue a:focus { color: inherit; text-decoration: none; }
    .dd-queue a:hover .dd-queue-title { color: var(--dd-blue); }

    .dd-queue-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: var(--dd-sky);
        color: var(--dd-blue);
        font-size: 1rem;
    }

    .dd-queue-title {
        margin: 0;
        color: var(--dd-navy);
        font-size: .88rem;
        font-weight: 600;
    }

    .dd-queue-sub {
        display: block;
        color: var(--dd-muted);
        font-size: .77rem;
        line-height: 1.45;
    }

    .dd-queue-arrow { color: var(--dd-faint); font-size: 1.05rem; }

    .dd-note {
        margin: 4px 0 16px;
        padding: 12px 14px;
        border-left: 3px solid var(--dd-blue);
        border-radius: 0 8px 8px 0;
        background: #f4fafd;
        color: #3a5568;
        font-size: .85rem;
        line-height: 1.6;
    }

    .dd-note strong { color: var(--dd-navy); font-weight: 600; }

    .dd-note-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 10px;
    }

    .dd-btn-primary {
        color: #fff;
        background: var(--dd-blue);
        border-color: var(--dd-blue);
    }

    .dd-btn-primary:hover {
        color: #fff;
        background: var(--dd-navy);
        border-color: var(--dd-navy);
        text-decoration: none;
    }

    .dd-btn-outline {
        color: var(--dd-blue);
        background: #fff;
        border-color: var(--dd-blue);
    }

    .dd-btn-outline:hover {
        color: #fff;
        background: var(--dd-blue);
        text-decoration: none;
    }

    /* ---------- SGC status ---------- */
    .dd-sgc-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
    }

    .dd-sgc-card {
        padding: 15px 17px;
        border: 1px solid var(--dd-line);
        border-radius: var(--dd-radius);
        background: #fbfdff;
    }

    .dd-sgc-head {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 10px;
    }

    .dd-sgc-card h5 {
        margin: 0 0 2px;
        color: var(--dd-navy);
        font-size: .88rem;
        font-weight: 600;
    }

    .dd-sgc-card small { color: var(--dd-muted); font-size: .76rem; }

    .dd-sgc-rate {
        color: var(--dd-navy);
        font-size: 1.05rem;
        font-weight: 600;
        font-variant-numeric: tabular-nums;
    }

    .dd-sgc-bar {
        height: 6px;
        border-radius: 999px;
        background: #e7eff5;
        overflow: hidden;
    }

    .dd-sgc-bar span {
        display: block;
        height: 100%;
        border-radius: inherit;
    }

    .dd-sgc-card p {
        margin: 9px 0 0;
        color: var(--dd-muted);
        font-size: .79rem;
        line-height: 1.55;
    }

    .sgc-one .dd-sgc-bar span { background: #7a8a97; }
    .sgc-two .dd-sgc-bar span { background: #d79a35; }
    .sgc-three .dd-sgc-bar span { background: #2f8f5b; }

    /* ---------- Assessment accordion ---------- */
    .dd-principle {
        margin-bottom: 10px;
        border: 1px solid var(--dd-line);
        border-radius: var(--dd-radius);
        background: #fff;
        overflow: hidden;
    }

    .dd-principle:last-child { margin-bottom: 0; }

    .dd-principle-toggle {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        width: 100%;
        padding: 13px 18px;
        color: var(--dd-navy);
        font-weight: 600;
        text-align: left;
        background: #fff;
    }

    .dd-principle-toggle:hover {
        color: var(--dd-blue);
        background: #f7fafc;
        text-decoration: none;
    }

    .dd-principle-title { display: grid; gap: 2px; min-width: 0; }

    .dd-principle-title small {
        color: var(--dd-muted);
        font-size: .66rem;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
    }

    .dd-principle-title strong {
        color: inherit;
        font-size: .92rem;
        font-weight: 600;
        line-height: 1.45;
    }

    .dd-principle-count {
        flex: 0 0 auto;
        padding: 3px 11px;
        border-radius: 999px;
        background: #f1f5f8;
        color: var(--dd-muted);
        font-size: .74rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .dd-principle-description {
        margin: 0;
        padding: 12px 18px;
        border-top: 1px solid var(--dd-line);
        color: #4f5d75;
        background: #fbfcfe;
        font-size: .82rem;
        line-height: 1.65;
    }

    .dd-assessment-wrap { padding: 6px 14px 14px; }

    .dd-assessment-table {
        min-width: 860px;
        margin: 0;
    }

    .dd-assessment-table thead th {
        padding: 9px 8px;
        border-top: 0;
        color: var(--dd-muted);
        font-size: .68rem;
        font-weight: 700;
        letter-spacing: .05em;
        text-transform: uppercase;
        vertical-align: middle;
    }

    .dd-assessment-table tbody td {
        padding: 9px 8px;
        vertical-align: middle;
    }

    .dd-indicator-number {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 28px;
        height: 28px;
        padding: 0 8px;
        border-radius: 7px;
        color: var(--dd-navy);
        background: var(--dd-sky);
        font-size: .74rem;
        font-weight: 700;
    }

    .dd-indicator-description {
        min-width: 300px;
        color: #384860;
        font-size: .8rem;
        line-height: 1.55;
    }

    .dd-rate-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 38px;
        padding: 4px 9px;
        border-radius: 999px;
        font-size: .74rem;
        font-weight: 700;
        font-variant-numeric: tabular-nums;
        transition: transform .15s ease;
    }

    .dd-rate-count:hover {
        transform: translateY(-1px);
        text-decoration: none;
    }

    .rate-one { color: #5b6b7a; background: #eef2f5; }
    .rate-two { color: #9a6b1f; background: #fdf6e7; }
    .rate-three { color: #922c43; background: #fbf4f5; }
    .rate-four { color: #1d6b3c; background: #edf9f1; }

    /* ---------- Modal ---------- */
    .district-modal .modal-content {
        border: 0;
        border-radius: var(--dd-radius);
        box-shadow: 0 24px 50px rgba(15, 23, 42, .2);
        overflow: hidden;
    }

    .district-modal .modal-header {
        color: #fff;
        background: var(--dd-navy);
    }

    /* ---------- Responsive ---------- */
    @media (max-width: 1199.98px) {
        .dd-metrics { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }

    @media (max-width: 991.98px) {
        .dd-layout { grid-template-columns: 1fr; }
        .dd-layout .dd-panel { margin-bottom: 18px; }
        .dd-layout .dd-panel:last-child { margin-bottom: 0; }
        .dd-sgc-grid { grid-template-columns: 1fr; }
    }

    @media (max-width: 767.98px) {
        .dd-header { padding: 16px 18px; }
        .dd-header h1 { font-size: 1.25rem; }
        .dd-header-actions { width: 100%; }
        .dd-header-actions .dd-btn { flex: 1 1 auto; justify-content: center; }
        .dd-metrics { grid-template-columns: 1fr; gap: 10px; }
        .dd-coverage-row { grid-template-columns: 1fr; }
        .dd-coverage-meter { justify-content: flex-start; }
        .dd-panel-head, .dd-panel-body-padded { padding-left: 16px; padding-right: 16px; }
        .dd-panel-body { padding: 4px 16px 14px; }
        .dd-principle-toggle { align-items: flex-start; flex-direction: column; }
        .dd-assessment-wrap { padding: 6px 8px 12px; }
    }
</style>

<div class="district-dashboard">
    <header class="dd-header">
        <div>
            <span class="dd-eyebrow"><i class="mdi mdi-map-marker-radius-outline"></i> District Dashboard</span>
            <h1><?= $escape($district_name); ?></h1>
            <p class="dd-header-meta">
                <span><?= $escape($division_name); ?></span>
                <span><?= (int) $school_total; ?> schools</span>
                <span>Fiscal Year <?= $escape($this->session->fy); ?></span>
            </p>
        </div>
        <div class="dd-header-actions">
            <a href="<?= $schools_url; ?>" class="dd-btn dd-btn-ghost">
                <i class="mdi mdi-format-list-bulleted-square"></i> School Directory
            </a>
            <a href="<?= $tech_workspace_url; ?>" class="dd-btn dd-btn-solid">
                <i class="mdi mdi-wrench-clock"></i> TA Workspace
            </a>
        </div>
    </header>

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

    <div class="dd-metrics">
        <?php foreach ($submission_cards as $card) : ?>
            <a href="<?= $card['url']; ?>" class="dd-metric" title="<?= $escape($card['meta']); ?>">
                <div class="dd-metric-top">
                    <span class="dd-metric-label"><?= $escape($card['title']); ?></span>
                    <span class="dd-metric-icon"><i class="mdi <?= $escape($card['icon']); ?>"></i></span>
                </div>
                <span class="dd-metric-value"><?= (int) $card['count']; ?></span>
                <span class="dd-metric-note"><?= $escape($card['summary']); ?></span>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="dd-layout">
        <section class="dd-panel">
            <div class="dd-panel-head">
                <h4>Coverage Snapshot</h4>
                <span class="dd-tag"><i class="mdi mdi-chart-donut"></i> Based on <?= (int) $school_total; ?> schools</span>
                <p>School participation against the district total. Select a row to open the corresponding school list.</p>
            </div>
            <div class="dd-panel-body">
                <?php foreach ($coverage_cards as $card) :
                    $progress_width = min(100, max(0, (float) $card['rate']));
                ?>
                    <a href="<?= $card['url']; ?>" class="dd-coverage-row">
                        <div>
                            <h5 class="dd-coverage-title"><?= $escape($card['title']); ?></h5>
                            <small class="dd-coverage-sub"><?= $escape($card['summary']); ?></small>
                        </div>
                        <div class="dd-coverage-meter">
                            <div class="dd-coverage-bar"><span style="width: <?= $progress_width; ?>%;"></span></div>
                            <span class="dd-coverage-pct"><?= number_format((float) $card['rate'], 1); ?>%</span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="dd-panel">
            <div class="dd-panel-head">
                <h4>District Work Queue</h4>
                <span class="dd-tag"><i class="mdi mdi-clipboard-outline"></i> <?= (int) $tech_entry_total; ?> TA entries</span>
                <p>Frequently used pages for checking submissions and planning support.</p>
            </div>
            <div class="dd-panel-body">
                <ul class="dd-queue">
                    <?php foreach ($work_queue_cards as $card) : ?>
                        <li>
                            <a href="<?= $card['url']; ?>">
                                <span class="dd-queue-icon"><i class="mdi <?= $escape($card['icon']); ?>"></i></span>
                                <span>
                                    <h5 class="dd-queue-title"><?= $escape($card['title']); ?></h5>
                                    <small class="dd-queue-sub"><?= $escape($card['description']); ?></small>
                                </span>
                                <i class="mdi mdi-chevron-right dd-queue-arrow"></i>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <div class="dd-note">
                    <strong>District technical assistance planning.</strong>
                    Record technical assistance strategies, schedules, and management teams that support the concerns surfaced in TA forms and action plans.
                    <div class="dd-note-actions">
                        <a href="<?= $tech_workspace_url; ?>" class="dd-btn dd-btn-primary">
                            <i class="mdi mdi-wrench-outline"></i> Open TA Workspace
                        </a>
                        <a href="<?= $tech_new_url; ?>" class="dd-btn dd-btn-outline">
                            <i class="mdi mdi-plus-circle-outline"></i> Add TA Entry
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <section class="dd-panel">
        <div class="dd-panel-head">
            <h4>School Governance Council</h4>
            <span class="dd-tag"><i class="mdi mdi-account-group-outline"></i> <?= (int) $sgc_total; ?> schools tracked</span>
            <p>Current SGC organization and functionality status for the schools handled by this district.</p>
        </div>
        <div class="dd-panel-body-padded">
            <div class="dd-sgc-grid">
                <article class="dd-sgc-card sgc-one">
                    <div class="dd-sgc-head">
                        <div>
                            <h5>Not Yet Organized</h5>
                            <small><?= (int) $sgc_not_organized; ?> schools</small>
                        </div>
                        <span class="dd-sgc-rate"><?= number_format($sgc_percentages[1], 1); ?>%</span>
                    </div>
                    <div class="dd-sgc-bar"><span style="width: <?= min(100, $sgc_percentages[1]); ?>%;"></span></div>
                    <p>Schools that still need SGC organization support and initial setup follow-through.</p>
                </article>

                <article class="dd-sgc-card sgc-two">
                    <div class="dd-sgc-head">
                        <div>
                            <h5>Organized, Not Functional</h5>
                            <small><?= (int) $sgc_not_functional; ?> schools</small>
                        </div>
                        <span class="dd-sgc-rate"><?= number_format($sgc_percentages[2], 1); ?>%</span>
                    </div>
                    <div class="dd-sgc-bar"><span style="width: <?= min(100, $sgc_percentages[2]); ?>%;"></span></div>
                    <p>Schools that may need coaching on meetings, documentation, and practical council operations.</p>
                </article>

                <article class="dd-sgc-card sgc-three">
                    <div class="dd-sgc-head">
                        <div>
                            <h5>Functional</h5>
                            <small><?= (int) $sgc_functional; ?> schools</small>
                        </div>
                        <span class="dd-sgc-rate"><?= number_format($sgc_percentages[3], 1); ?>%</span>
                    </div>
                    <div class="dd-sgc-bar"><span style="width: <?= min(100, $sgc_percentages[3]); ?>%;"></span></div>
                    <p>Schools with a working SGC structure that can be sustained and used as peer reference points.</p>
                </article>
            </div>
        </div>
    </section>

    <section class="dd-panel">
        <div class="dd-panel-head">
            <h4>Self-Assessment Manifestation Counts</h4>
            <span class="dd-tag"><i class="mdi mdi-filter-outline"></i> Click counts to open school lists</span>
            <p>How many district schools reported each manifestation level for every SBM indicator during the active fiscal year.</p>
        </div>
        <div class="dd-panel-body-padded">
            <div id="districtDashboardAccordion">
                <?php foreach ($sbm as $principle_index => $principle) :
                    $principle_id = (string) $principle->id;
                    $principle_questions = isset($sbm_sub_by_principle[$principle_id]) ? $sbm_sub_by_principle[$principle_id] : array();
                    $collapse_id = 'districtPrinciple' . $principle_id;
                    $is_open = $principle_index === 0;
                ?>
                    <section class="dd-principle">
                        <a
                            href="#<?= $escape($collapse_id); ?>"
                            class="dd-principle-toggle"
                            data-toggle="collapse"
                            aria-expanded="<?= $is_open ? 'true' : 'false'; ?>"
                            aria-controls="<?= $escape($collapse_id); ?>"
                        >
                            <div class="dd-principle-title">
                                <small>SBM Principle</small>
                                <strong><?= $escape((string) $principle->indicator); ?></strong>
                            </div>
                            <span class="dd-principle-count">
                                <i class="mdi mdi-format-list-numbered"></i>
                                <?= count($principle_questions); ?> indicators
                            </span>
                        </a>

                        <div id="<?= $escape($collapse_id); ?>" class="collapse <?= $is_open ? 'show' : ''; ?>" data-parent="#districtDashboardAccordion">
                            <p class="dd-principle-description"><?= $escape((string) $principle->description); ?></p>

                            <div class="dd-assessment-wrap table-responsive">
                                <table class="table dd-assessment-table">
                                    <thead>
                                        <tr>
                                            <th rowspan="2" colspan="2">SBM Indicator</th>
                                            <th colspan="4" class="text-center">Degree of Manifestation</th>
                                        </tr>
                                        <tr>
                                            <?php foreach ($rate_details as $rate) : ?>
                                                <th class="text-center"><?= $escape($rate['label']); ?></th>
                                            <?php endforeach; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($principle_questions as $indicator) :
                                            $indicator_number = (int) $indicator->i_no;
                                        ?>
                                            <tr>
                                                <td><span class="dd-indicator-number"><?= $indicator_number; ?></span></td>
                                                <td class="dd-indicator-description"><?= $escape((string) $indicator->description); ?></td>
                                                <?php foreach ($rate_details as $rate_value => $rate_meta) :
                                                    $count = isset($sbm_rate_counts[$indicator_number][$rate_value])
                                                        ? (int) $sbm_rate_counts[$indicator_number][$rate_value]
                                                        : 0;
                                                    $href = base_url() . 'Pages/sbm_rate_list/q' . $indicator_number . '/' . $rate_value;
                                                ?>
                                                    <td class="text-center">
                                                        <a href="<?= $href; ?>" class="dd-rate-count <?= $escape($rate_meta['class']); ?>">
                                                            <?= $count; ?>
                                                        </a>
                                                    </td>
                                                <?php endforeach; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

</div>
