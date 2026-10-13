<?php
$action_plan_rows = isset($data) ? $data : array();
if ($action_plan_rows instanceof Traversable) {
    $action_plan_rows = iterator_to_array($action_plan_rows, false);
}
$action_plan_rows = is_array($action_plan_rows) ? array_values($action_plan_rows) : array();

$escape = static function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};

$excerpt = static function ($value, $limit = 170) {
    $text = trim(preg_replace('/\s+/', ' ', strip_tags((string) $value)));
    if ($text === '') {
        return '';
    }

    if (function_exists('mb_strlen') && function_exists('mb_substr')) {
        if (mb_strlen($text, 'UTF-8') <= $limit) {
            return $text;
        }

        return rtrim(mb_substr($text, 0, $limit - 1, 'UTF-8')) . '...';
    }

    if (strlen($text) <= $limit) {
        return $text;
    }

    return rtrim(substr($text, 0, $limit - 1)) . '...';
};

$parse_budget = static function ($value) {
    $raw = trim((string) $value);
    if ($raw === '') {
        return null;
    }

    $normalized = preg_replace('/[^0-9.\-]/', '', str_replace(',', '', $raw));
    if ($normalized === '' || !is_numeric($normalized)) {
        return null;
    }

    return (float) $normalized;
};

$format_budget = static function ($value) use ($parse_budget, $escape) {
    $raw = trim((string) $value);
    if ($raw === '') {
        return 'Not set';
    }

    $numeric = $parse_budget($raw);
    if ($numeric === null) {
        return $escape($raw);
    }

    return 'PHP ' . number_format($numeric, 2);
};

$total_entries = count($action_plan_rows);
$budget_total = 0.0;
$budgeted_entries = 0;
$remarks_entries = 0;
$outputs_entries = 0;
$owners_map = array();
$timeframe_map = array();

foreach ($action_plan_rows as $row) {
    $budget_value = $parse_budget(isset($row->bud_req) ? $row->bud_req : '');
    if ($budget_value !== null && $budget_value > 0) {
        $budget_total += $budget_value;
        $budgeted_entries++;
    }

    $owner = trim((string) (isset($row->person_involved) ? $row->person_involved : ''));
    if ($owner !== '') {
        $owners_map[strtolower($owner)] = $owner;
    }

    $time_frame = trim((string) (isset($row->time_frame) ? $row->time_frame : ''));
    if ($time_frame !== '') {
        $timeframe_map[strtolower($time_frame)] = $time_frame;
    }

    $remarks = trim((string) (isset($row->remarks) ? $row->remarks : ''));
    if ($remarks !== '') {
        $remarks_entries++;
    }

    $has_output = trim((string) (isset($row->ex_output) ? $row->ex_output : '')) !== ''
        || trim((string) (isset($row->metho_strategy) ? $row->metho_strategy : '')) !== '';
    if ($has_output) {
        $outputs_entries++;
    }
}

$unique_owners = count($owners_map);
$unique_timeframes = count($timeframe_map);
$budget_coverage_rate = $total_entries > 0 ? ($budgeted_entries / $total_entries) * 100 : 0;
$remarks_coverage_rate = $total_entries > 0 ? ($remarks_entries / $total_entries) * 100 : 0;
$outputs_coverage_rate = $total_entries > 0 ? ($outputs_entries / $total_entries) * 100 : 0;

$total_budget_label = $budget_total > 0 ? 'PHP ' . number_format($budget_total, 2) : 'No budget yet';
$dashboard_url = base_url();
$new_action_plan_url = base_url() . 'Pages/action_plan_new';
$print_view_url = base_url() . 'Pages/sbm_action_plan_pview';
$checklist_url = base_url() . 'Pages/sbm_checklist';
$ta_url = base_url() . 'Pages/tapr_form';
$tana_url = base_url() . 'Pages/tana_summary';

$next_step = array(
    'eyebrow' => 'Recommended next step',
    'title' => 'Start your first action plan item',
    'description' => 'Turn the school assessment findings into a concrete activity, expected output, owner, schedule, and budget line.',
    'url' => $new_action_plan_url,
    'cta' => 'Add action plan item',
    'secondary_url' => $checklist_url,
    'secondary_cta' => 'Review checklist',
);

if ($total_entries > 0 && $budgeted_entries < $total_entries) {
    $next_step = array(
        'eyebrow' => 'Improve coverage',
        'title' => 'Complete the missing budget lines',
        'description' => 'Only ' . $budgeted_entries . ' of ' . $total_entries . ' items have budget requirements. Review the remaining activities so planning stays realistic.',
        'url' => $new_action_plan_url,
        'cta' => 'Add another item',
        'secondary_url' => $print_view_url,
        'secondary_cta' => 'Print current plan',
    );
} elseif ($total_entries > 0 && $remarks_entries < $total_entries) {
    $next_step = array(
        'eyebrow' => 'Tighten the plan',
        'title' => 'Fill in monitoring remarks',
        'description' => 'Some entries still do not have remarks. Add notes or monitoring context so the plan is easier to review and track later on.',
        'url' => $print_view_url,
        'cta' => 'Review printable plan',
        'secondary_url' => $new_action_plan_url,
        'secondary_cta' => 'Add more items',
    );
} elseif ($total_entries > 0) {
    $next_step = array(
        'eyebrow' => 'Plan is active',
        'title' => 'Review, print, and keep the plan current',
        'description' => 'Your action plan already has active entries. Use the printable view for review meetings and continue refining items as the school priorities evolve.',
        'url' => $print_view_url,
        'cta' => 'Open print view',
        'secondary_url' => $new_action_plan_url,
        'secondary_cta' => 'Add another item',
    );
}
?>

<style>
    .action-plan-page {
        --plan-primary: #7f1d1d;
        --plan-primary-light: #b83a4b;
        --plan-accent: #d6a84b;
        --plan-ink: #172033;
        --plan-muted: #687386;
        --plan-border: #e4e9f0;
        --plan-surface: #f6f8fb;
        --plan-success: #15803d;
        --plan-warning: #c97a11;
        --plan-info: #1d4ed8;
    }

    .action-plan-page .alert {
        border-radius: 14px;
    }

    .action-plan-page .plan-hero {
        position: relative;
        display: flex;
        align-items: stretch;
        justify-content: space-between;
        gap: 24px;
        margin: 18px 0 22px;
        padding: 32px;
        border-radius: 24px;
        color: #fff;
        background:
            radial-gradient(circle at 100% 0%, rgba(255, 255, 255, .15), transparent 30%),
            radial-gradient(circle at 0% 100%, rgba(214, 168, 75, .18), transparent 24%),
            linear-gradient(135deg, #541117 0%, #7f1d1d 46%, #b83a4b 100%);
        box-shadow: 0 22px 44px rgba(84, 17, 23, .20);
        overflow: hidden;
    }

    .action-plan-page .plan-hero::after {
        content: '';
        position: absolute;
        right: -42px;
        bottom: -56px;
        width: 210px;
        height: 210px;
        border-radius: 50%;
        background: rgba(255, 255, 255, .08);
    }

    .action-plan-page .plan-hero-copy,
    .action-plan-page .plan-hero-side {
        position: relative;
        z-index: 1;
    }

    .action-plan-page .plan-hero-copy {
        max-width: 760px;
    }

    .action-plan-page .plan-hero-side {
        display: flex;
        align-items: flex-start;
        justify-content: flex-end;
        min-width: 0;
    }

    .action-plan-page .plan-hero h1 {
        margin: 0;
        color: #fff;
        font-size: 30px;
        font-weight: 800;
        line-height: 1.1;
        letter-spacing: -.03em;
    }

    .action-plan-page .plan-action-stack {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        justify-content: flex-end;
    }

    .action-plan-page .plan-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 11px 16px;
        border: 0;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 700;
        text-decoration: none !important;
        transition: transform .18s ease, box-shadow .18s ease, background .18s ease, color .18s ease;
    }

    .action-plan-page .plan-button:hover {
        transform: translateY(-2px);
    }

    .action-plan-page .plan-button-primary {
        color: var(--plan-primary);
        background: #fff;
        box-shadow: 0 10px 20px rgba(61, 12, 29, .18);
    }

    .action-plan-page .plan-button-secondary {
        color: #fff;
        background: rgba(255, 255, 255, .10);
        border: 1px solid rgba(255, 255, 255, .18);
    }

    .action-plan-page .plan-stats {
        margin-bottom: 2px;
    }

    .action-plan-page .plan-stat-card {
        height: calc(100% - 20px);
        margin-bottom: 20px;
        padding: 20px;
        border: 1px solid var(--plan-border);
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 8px 24px rgba(31, 45, 75, .06);
    }

    .action-plan-page .plan-stat-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
    }

    .action-plan-page .plan-stat-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 46px;
        height: 46px;
        border-radius: 14px;
        color: #fff;
        background: linear-gradient(135deg, var(--plan-primary), var(--plan-primary-light));
        font-size: 20px;
        box-shadow: 0 10px 18px rgba(127, 29, 29, .15);
    }

    .action-plan-page .plan-stat-card small {
        display: block;
        margin-bottom: 8px;
        color: var(--plan-muted);
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
    }

    .action-plan-page .plan-stat-card h3 {
        margin: 0;
        color: var(--plan-ink);
        font-size: 28px;
        font-weight: 800;
        line-height: 1;
    }

    .action-plan-page .plan-stat-card p {
        margin: 10px 0 0;
        color: var(--plan-muted);
        font-size: 13px;
        line-height: 1.6;
    }

    .action-plan-page .mini-progress {
        height: 8px;
        margin-top: 14px;
        border-radius: 999px;
        background: #edf1f6;
        overflow: hidden;
    }

    .action-plan-page .mini-progress span {
        display: block;
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg, var(--plan-primary), var(--plan-accent));
    }

    .action-plan-page .plan-panel {
        margin-bottom: 22px;
        border: 1px solid var(--plan-border);
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 10px 30px rgba(31, 45, 75, .07);
        overflow: hidden;
    }

    .action-plan-page .plan-panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 20px 24px;
        border-bottom: 1px solid var(--plan-border);
    }

    .action-plan-page .plan-panel-header h4 {
        margin: 0 0 4px;
        color: var(--plan-ink);
        font-size: 18px;
        font-weight: 700;
    }

    .action-plan-page .plan-panel-header p,
    .action-plan-page .plan-panel-header small {
        margin: 0;
        color: var(--plan-muted);
        font-size: 12px;
        line-height: 1.6;
    }

    .action-plan-page .plan-panel-body {
        padding: 22px 24px;
    }

    .action-plan-page .empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 10px;
        padding: 30px 22px;
        border: 1px dashed #d9e0ea;
        border-radius: 16px;
        background: #fbfcff;
        text-align: center;
    }

    .action-plan-page .empty-state i {
        font-size: 28px;
        color: var(--plan-primary-light);
    }

    .action-plan-page .empty-state strong {
        color: var(--plan-ink);
        font-size: 15px;
        font-weight: 700;
    }

    .action-plan-page .empty-state p {
        margin: 0;
        color: var(--plan-muted);
        font-size: 12px;
        line-height: 1.7;
    }

    .action-plan-page .empty-state .plan-button {
        margin-top: 6px;
    }

    .action-plan-page .plan-table-wrap {
        overflow: hidden;
        border-radius: 16px;
        border: 1px solid var(--plan-border);
        background: #fff;
    }

    .action-plan-page .plan-table {
        width: 100% !important;
        margin: 0 !important;
        border-collapse: separate !important;
        border-spacing: 0 !important;
    }

    .action-plan-page .plan-table thead th {
        padding: 14px 16px;
        border-bottom: 1px solid var(--plan-border);
        color: var(--plan-muted);
        background: #f8fafc;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
        vertical-align: middle;
    }

    .action-plan-page .plan-table tbody td {
        padding: 16px;
        border-top: 0;
        border-bottom: 1px solid #edf1f5;
        vertical-align: top;
    }

    .action-plan-page .plan-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .action-plan-page .plan-index {
        color: var(--plan-muted);
        font-size: 12px;
        font-weight: 700;
    }

    .action-plan-page .plan-main-cell strong {
        display: block;
        color: var(--plan-ink);
        font-size: 14px;
        font-weight: 700;
        line-height: 1.6;
    }

    .action-plan-page .plan-main-cell span,
    .action-plan-page .plan-detail-copy,
    .action-plan-page .plan-remarks-copy {
        display: block;
        margin-top: 6px;
        color: var(--plan-muted);
        font-size: 12px;
        line-height: 1.75;
    }

    .action-plan-page .plan-detail-block + .plan-detail-block {
        margin-top: 12px;
        padding-top: 12px;
        border-top: 1px dashed #e8edf4;
    }

    .action-plan-page .plan-detail-label {
        display: block;
        margin-bottom: 6px;
        color: var(--plan-muted);
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
    }

    .action-plan-page .meta-pills {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .action-plan-page .meta-pill {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        align-self: flex-start;
        padding: 8px 12px;
        border-radius: 999px;
        background: #f4f7fb;
        color: var(--plan-ink);
        font-size: 12px;
        font-weight: 600;
        line-height: 1.4;
    }

    .action-plan-page .meta-pill i {
        color: var(--plan-primary);
        font-size: 15px;
    }

    .action-plan-page .plan-budget {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 8px 12px;
        border-radius: 999px;
        background: #eef6f0;
        color: var(--plan-success);
        font-size: 12px;
        font-weight: 700;
        line-height: 1.4;
    }

    .action-plan-page .plan-budget.plan-budget-empty {
        background: #f3f4f6;
        color: #475569;
    }

    .action-plan-page .plan-action-buttons {
        display: flex;
        flex-direction: column;
        gap: 8px;
        min-width: 124px;
    }

    .action-plan-page .plan-action-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 10px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 700;
        text-decoration: none !important;
        transition: transform .18s ease, box-shadow .18s ease;
    }

    .action-plan-page .plan-action-link:hover {
        transform: translateY(-1px);
    }

    .action-plan-page .plan-action-link.edit-link {
        color: #7c2d12;
        background: #fff1d6;
    }

    .action-plan-page .plan-action-link.delete-link {
        color: #b91c1c;
        background: #fee2e2;
    }

    .action-plan-page .plan-sidebar-card {
        padding: 18px;
        border: 1px solid var(--plan-border);
        border-radius: 16px;
        background: #fbfcff;
    }

    .action-plan-page .plan-sidebar-card + .plan-sidebar-card {
        margin-top: 16px;
    }

    .action-plan-page .next-step-card {
        background:
            radial-gradient(circle at top right, rgba(214, 168, 75, .18), transparent 34%),
            linear-gradient(180deg, #fff9ec 0%, #fffdf8 100%);
    }

    .action-plan-page .next-step-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        margin-bottom: 10px;
        padding: 7px 10px;
        border-radius: 999px;
        background: rgba(127, 29, 29, .08);
        color: var(--plan-primary);
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .action-plan-page .next-step-card h5,
    .action-plan-page .plan-sidebar-card h5 {
        margin: 0;
        color: var(--plan-ink);
        font-size: 18px;
        font-weight: 800;
        line-height: 1.4;
    }

    .action-plan-page .next-step-card p,
    .action-plan-page .plan-sidebar-card p {
        margin: 12px 0 0;
        color: var(--plan-muted);
        font-size: 12px;
        line-height: 1.75;
    }

    .action-plan-page .next-step-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 18px;
    }

    .action-plan-page .coverage-list {
        display: flex;
        flex-direction: column;
        gap: 16px;
        margin-top: 14px;
    }

    .action-plan-page .coverage-item strong {
        display: block;
        margin-bottom: 6px;
        color: var(--plan-ink);
        font-size: 13px;
        font-weight: 700;
    }

    .action-plan-page .coverage-item span {
        display: block;
        color: var(--plan-muted);
        font-size: 12px;
        line-height: 1.7;
    }

    .action-plan-page .coverage-item .mini-progress {
        margin-top: 10px;
    }

    .action-plan-page .quick-link-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-top: 14px;
    }

    .action-plan-page .quick-link {
        display: inline-flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 13px 14px;
        border-radius: 14px;
        border: 1px solid var(--plan-border);
        color: var(--plan-ink);
        background: #fff;
        font-size: 13px;
        font-weight: 700;
        text-decoration: none !important;
        transition: transform .18s ease, border-color .18s ease, box-shadow .18s ease;
    }

    .action-plan-page .quick-link:hover {
        transform: translateY(-1px);
        border-color: #d6dde7;
        box-shadow: 0 10px 22px rgba(31, 45, 75, .08);
    }

    .action-plan-page .quick-link span {
        display: inline-flex;
        align-items: center;
        gap: 10px;
    }

    .action-plan-page .quick-link i:first-child {
        color: var(--plan-primary);
        font-size: 16px;
    }

    .action-plan-page .muted-copy {
        color: var(--plan-muted);
        font-style: italic;
    }

    .action-plan-page .dataTables_wrapper .row:first-child,
    .action-plan-page .dataTables_wrapper .row:last-child {
        padding-left: 8px;
        padding-right: 8px;
    }

    .action-plan-page .dataTables_wrapper .dataTables_filter label,
    .action-plan-page .dataTables_wrapper .dataTables_length label,
    .action-plan-page .dataTables_wrapper .dataTables_info,
    .action-plan-page .dataTables_wrapper .dataTables_paginate {
        color: var(--plan-muted);
        font-size: 12px;
        font-weight: 600;
    }

    .action-plan-page .dataTables_wrapper .dataTables_filter input,
    .action-plan-page .dataTables_wrapper .dataTables_length select {
        height: 38px;
        margin-left: 8px;
        border: 1px solid var(--plan-border);
        border-radius: 10px;
        background: #fff;
        box-shadow: none;
    }

    .action-plan-page .pagination .page-link {
        border-radius: 10px !important;
    }

    @media (max-width: 1199.98px) {
        .action-plan-page .plan-action-buttons {
            min-width: 112px;
        }
    }

    @media (max-width: 991.98px) {
        .action-plan-page .plan-hero {
            flex-direction: column;
            align-items: flex-start;
        }

        .action-plan-page .plan-hero-side {
            width: 100%;
            min-width: 0;
            align-items: flex-start;
        }

        .action-plan-page .plan-action-stack {
            justify-content: flex-start;
        }
    }

    @media (max-width: 767.98px) {
        .action-plan-page .plan-hero,
        .action-plan-page .plan-panel-body,
        .action-plan-page .plan-panel-header {
            padding-left: 18px;
            padding-right: 18px;
        }

        .action-plan-page .plan-hero h1 {
            font-size: 24px;
        }

        .action-plan-page .next-step-actions {
            flex-direction: column;
            align-items: stretch;
        }

        .action-plan-page .plan-button,
        .action-plan-page .quick-link {
            width: 100%;
        }

        .action-plan-page .plan-action-buttons {
            min-width: 0;
        }
    }
</style>

<div class="action-plan-page">
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

    <div class="plan-hero">
        <div class="plan-hero-copy">
            <h1><?= $escape($title); ?></h1>
        </div>

        <div class="plan-hero-side">
            <div class="plan-action-stack">
                <a href="<?= $new_action_plan_url; ?>" class="plan-button plan-button-primary">
                    <i class="mdi mdi-plus"></i>
                    Add New
                </a>
                <a href="<?= $print_view_url; ?>" target="_blank" class="plan-button plan-button-secondary">
                    <i class="mdi mdi-printer-outline"></i>
                    Print View
                </a>
            </div>
        </div>
    </div>

    <div class="row plan-stats">
        <div class="col-md-4 col-xl-4">
            <div class="plan-stat-card">
                <div class="plan-stat-top">
                    <div>
                        <small>Total Entries</small>
                        <h3><?= $total_entries; ?></h3>
                        <p><?= $total_entries > 0 ? 'Activities already encoded for the current school action plan.' : 'No action plan items have been encoded yet.'; ?></p>
                    </div>
                    <span class="plan-stat-icon"><i class="mdi mdi-format-list-bulleted-square"></i></span>
                </div>
                <div class="mini-progress"><span style="width: <?= min(100, $total_entries * 12); ?>%;"></span></div>
            </div>
        </div>

        <div class="col-md-4 col-xl-4">
            <div class="plan-stat-card">
                <div class="plan-stat-top">
                    <div>
                        <small>Budget Tracked</small>
                        <h3><?= $escape($total_budget_label); ?></h3>
                        <p><?= $budgeted_entries; ?> of <?= $total_entries; ?> item<?= $total_entries === 1 ? '' : 's'; ?> currently have a budget requirement.</p>
                    </div>
                    <span class="plan-stat-icon"><i class="mdi mdi-cash-multiple"></i></span>
                </div>
                <div class="mini-progress"><span style="width: <?= min(100, max(0, $budget_coverage_rate)); ?>%;"></span></div>
            </div>
        </div>

        <div class="col-md-4 col-xl-4">
            <div class="plan-stat-card">
                <div class="plan-stat-top">
                    <div>
                        <small>Time Frames</small>
                        <h3><?= $unique_timeframes; ?></h3>
                        <p><?= $unique_timeframes > 0 ? 'Distinct schedule labels are already mapped in the plan.' : 'No time frame has been encoded yet.'; ?></p>
                    </div>
                    <span class="plan-stat-icon"><i class="mdi mdi-calendar-clock-outline"></i></span>
                </div>
                <div class="mini-progress"><span style="width: <?= $total_entries > 0 ? min(100, ($unique_timeframes / $total_entries) * 100) : 0; ?>%;"></span></div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-8">
            <section class="plan-panel">
                <div class="plan-panel-header">
                    <div>
                        <h4>Action Plan Register</h4>
                        <p>Review every activity, output, strategy, assignment, schedule, and budget line in one place.</p>
                    </div>
                    <small><?= $total_entries; ?> row<?= $total_entries === 1 ? '' : 's'; ?> available</small>
                </div>
                <div class="plan-panel-body">
                    <?php if ($total_entries === 0) : ?>
                        <div class="empty-state mb-4">
                            <i class="mdi mdi-clipboard-text-search-outline"></i>
                            <strong>No action plan items yet</strong>
                            <p>Start building the school action plan by adding the first activity tied to your SBM findings and implementation priorities.</p>
                            <a href="<?= $new_action_plan_url; ?>" class="plan-button plan-button-primary">
                                <i class="mdi mdi-plus"></i>
                                Add first item
                            </a>
                        </div>
                    <?php endif; ?>

                    <div class="plan-table-wrap">
                        <table id="datatable" class="table table-hover plan-table dt-responsive">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Activity And Objectives</th>
                                    <th>Outputs And Strategy</th>
                                    <th>Schedule And Ownership</th>
                                    <th>Budget And Remarks</th>
                                    <?php if ($this->session->position != 'region') : ?>
                                    <th>Action</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($action_plan_rows as $index => $row) :
                                    $activity = trim((string) (isset($row->activity) ? $row->activity : ''));
                                    $objective = trim((string) (isset($row->objective) ? $row->objective : ''));
                                    $output = trim((string) (isset($row->ex_output) ? $row->ex_output : ''));
                                    $strategy = trim((string) (isset($row->metho_strategy) ? $row->metho_strategy : ''));
                                    $time_frame = trim((string) (isset($row->time_frame) ? $row->time_frame : ''));
                                    $person_involved = trim((string) (isset($row->person_involved) ? $row->person_involved : ''));
                                    $remarks = trim((string) (isset($row->remarks) ? $row->remarks : ''));
                                    $budget_text = isset($row->bud_req) ? (string) $row->bud_req : '';
                                    $budget_has_value = $parse_budget($budget_text) !== null || trim($budget_text) !== '';
                                ?>
                                    <tr>
                                        <td class="plan-index"><?= $index + 1; ?></td>
                                        <td>
                                            <div class="plan-main-cell">
                                                <strong><?= $activity !== '' ? $escape($activity) : 'Untitled activity'; ?></strong>
                                                <span><?= $objective !== '' ? $escape($excerpt($objective, 180)) : '<span class="muted-copy">No objective provided.</span>'; ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="plan-detail-block">
                                                <span class="plan-detail-label">Expected Outputs</span>
                                                <div class="plan-detail-copy">
                                                    <?= $output !== '' ? $escape($excerpt($output, 140)) : '<span class="muted-copy">No outputs provided.</span>'; ?>
                                                </div>
                                            </div>
                                            <div class="plan-detail-block">
                                                <span class="plan-detail-label">Methodology Strategy</span>
                                                <div class="plan-detail-copy">
                                                    <?= $strategy !== '' ? $escape($excerpt($strategy, 140)) : '<span class="muted-copy">No strategy provided.</span>'; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="meta-pills">
                                                <span class="meta-pill">
                                                    <i class="mdi mdi-calendar-range"></i>
                                                    <?= $time_frame !== '' ? $escape($time_frame) : 'No time frame'; ?>
                                                </span>
                                                <span class="meta-pill">
                                                    <i class="mdi mdi-account-outline"></i>
                                                    <?= $person_involved !== '' ? $escape($person_involved) : 'No person involved'; ?>
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="plan-budget<?= $budget_has_value ? '' : ' plan-budget-empty'; ?>">
                                                <i class="mdi mdi-cash"></i>
                                                <?= $escape($format_budget($budget_text)); ?>
                                            </span>
                                            <span class="plan-remarks-copy">
                                                <?= $remarks !== '' ? $escape($excerpt($remarks, 130)) : '<span class="muted-copy">No remarks added.</span>'; ?>
                                            </span>
                                        </td>
                                        <?php if ($this->session->position != 'region') : ?>
                                        <td>
                                            <div class="plan-action-buttons">
                                                <a href="<?= base_url(); ?>Pages/sbm_action_plan_update/<?= $row->id; ?>" class="plan-action-link edit-link">
                                                    <i class="mdi mdi-pencil-outline"></i>
                                                    Update
                                                </a>
                                                <?= form_open('Pages/action_plan_delete', array('style' => 'display:inline;', 'onsubmit' => "return confirm('Are you sure you want to delete this action plan item?');")); ?>
                                                <input type="hidden" name="id" value="<?= (int) $row->id; ?>">
                                                <button type="submit" class="plan-action-link delete-link">
                                                    <i class="mdi mdi-trash-can-outline"></i>
                                                    Delete
                                                </button>
                                                <?= form_close(); ?>
                                            </div>
                                        </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>

        <div class="col-xl-4">
            <section class="plan-panel">
                <div class="plan-panel-body">
                    <div class="plan-sidebar-card next-step-card">
                        <span class="next-step-eyebrow">
                            <i class="mdi mdi-compass-outline"></i>
                            <?= $escape($next_step['eyebrow']); ?>
                        </span>
                        <h5><?= $escape($next_step['title']); ?></h5>
                        <p><?= $escape($next_step['description']); ?></p>
                        <div class="next-step-actions">
                            <a href="<?= $next_step['url']; ?>" class="plan-button plan-button-primary">
                                <i class="mdi mdi-arrow-right"></i>
                                <?= $escape($next_step['cta']); ?>
                            </a>
                            <a href="<?= $next_step['secondary_url']; ?>" class="plan-button plan-button-secondary">
                                <i class="mdi mdi-open-in-new"></i>
                                <?= $escape($next_step['secondary_cta']); ?>
                            </a>
                        </div>
                    </div>

                    <div class="plan-sidebar-card">
                        <h5>Planning Coverage</h5>
                        <p>These quick checks help show whether the action plan is detailed enough for follow-through and review.</p>
                        <div class="coverage-list">
                            <div class="coverage-item">
                                <strong>Budget coverage</strong>
                                <span><?= $budgeted_entries; ?> of <?= $total_entries; ?> entries have budget requirements.</span>
                                <div class="mini-progress"><span style="width: <?= min(100, max(0, $budget_coverage_rate)); ?>%;"></span></div>
                            </div>
                            <div class="coverage-item">
                                <strong>Remarks coverage</strong>
                                <span><?= $remarks_entries; ?> of <?= $total_entries; ?> entries have remarks or monitoring notes.</span>
                                <div class="mini-progress"><span style="width: <?= min(100, max(0, $remarks_coverage_rate)); ?>%;"></span></div>
                            </div>
                            <div class="coverage-item">
                                <strong>Outputs and strategy</strong>
                                <span><?= $outputs_entries; ?> of <?= $total_entries; ?> entries include outputs or methodology details.</span>
                                <div class="mini-progress"><span style="width: <?= min(100, max(0, $outputs_coverage_rate)); ?>%;"></span></div>
                            </div>
                        </div>
                    </div>

                    <div class="plan-sidebar-card">
                        <h5>Quick Links</h5>
                        <p>Move between the school’s planning and assessment steps without going back through the menu.</p>
                        <div class="quick-link-list">
                            <a href="<?= $new_action_plan_url; ?>" class="quick-link">
                                <span><i class="mdi mdi-plus-circle-outline"></i> Add action plan item</span>
                                <i class="mdi mdi-arrow-right"></i>
                            </a>
                            <a href="<?= $print_view_url; ?>" target="_blank" class="quick-link">
                                <span><i class="mdi mdi-printer-outline"></i> Print action plan</span>
                                <i class="mdi mdi-arrow-right"></i>
                            </a>
                            <a href="<?= $checklist_url; ?>" class="quick-link">
                                <span><i class="mdi mdi-format-list-checks"></i> Open self-assessment checklist</span>
                                <i class="mdi mdi-arrow-right"></i>
                            </a>
                            <a href="<?= $ta_url; ?>" class="quick-link">
                                <span><i class="mdi mdi-wrench-outline"></i> Open TA form</span>
                                <i class="mdi mdi-arrow-right"></i>
                            </a>
                            <a href="<?= $tana_url; ?>" class="quick-link">
                                <span><i class="mdi mdi-chart-line"></i> Open TANA priorities</span>
                                <i class="mdi mdi-arrow-right"></i>
                            </a>
                            <a href="<?= $dashboard_url; ?>" class="quick-link">
                                <span><i class="mdi mdi-view-dashboard-outline"></i> Back to dashboard</span>
                                <i class="mdi mdi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
