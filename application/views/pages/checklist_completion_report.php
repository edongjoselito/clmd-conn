<?php
$result_count = count($records);
$district_group_total = count($district_groups);
$is_region_scope = isset($report_scope) && $report_scope === 'region';
$filter_options = isset($filter_options) ? $filter_options : array();
$selected_filter = isset($selected_filter) ? (string) $selected_filter : '';
$print_mode = !empty($print_mode);
$share_mode = !empty($share_mode);
$printable_url = isset($printable_url) ? (string) $printable_url : '';
$shareable_url = isset($shareable_url) ? (string) $shareable_url : '';
$back_url = isset($back_url) ? (string) $back_url : '';
$filter_reset_url = isset($filter_reset_url) ? (string) $filter_reset_url : '';
$filter_hidden_fields = isset($filter_hidden_fields) && is_array($filter_hidden_fields)
    ? $filter_hidden_fields
    : array();
$back_label = isset($back_label) && trim((string) $back_label) !== ''
    ? (string) $back_label
    : 'Back to Dashboard';
$selected_filter_name = '';

foreach ($filter_options as $option) {
    if ((string) $option['id'] === $selected_filter) {
        $selected_filter_name = $option['name'];
        break;
    }
}
?>

<style>
    .checklist-report-page {
        --report-primary: #8b1e3f;
        --report-primary-dark: #64142d;
        --report-border: #e8ecf4;
        --report-muted: #6b7280;
    }

    .checklist-report-page .alert {
        border: 0;
        border-radius: 12px;
        box-shadow: 0 6px 18px rgba(31, 45, 75, .07);
    }

    .report-hero {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 24px;
        margin: 18px 0 22px;
        padding: 28px;
        border-radius: 18px;
        color: #fff;
        background:
            radial-gradient(circle at 90% 15%, rgba(255, 255, 255, .2), transparent 25%),
            linear-gradient(135deg, #64142d 0%, #a83255 100%);
        box-shadow: 0 14px 34px rgba(139, 30, 63, .22);
    }

    .report-hero h2 {
        margin: 0 0 7px;
        color: #fff;
        font-size: 25px;
        font-weight: 700;
    }

    .report-hero p {
        max-width: 760px;
        margin: 0;
        color: rgba(255, 255, 255, .82);
    }

    .report-actions {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }

    .report-pill,
    .report-action-link,
    .report-action-button {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 10px 14px;
        border: 1px solid rgba(255, 255, 255, .24);
        border-radius: 999px;
        color: #fff;
        background: rgba(255, 255, 255, .14);
        font-size: 12px;
        font-weight: 700;
        backdrop-filter: blur(5px);
    }

    .report-action-link:hover,
    .report-action-button:hover {
        color: var(--report-primary-dark);
        background: #fff;
        text-decoration: none;
    }

    .report-action-button {
        cursor: pointer;
    }

    .report-action-link.printable-link {
        color: #fff;
    }

    .report-summary-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 22px;
    }

    .report-summary-card {
        padding: 20px;
        border: 1px solid var(--report-border);
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 8px 24px rgba(31, 45, 75, .06);
    }

    .report-summary-card span {
        display: block;
        color: var(--report-muted);
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .05em;
        text-transform: uppercase;
    }

    .report-summary-card strong {
        display: block;
        margin-top: 6px;
        color: #27324a;
        font-size: 28px;
        font-weight: 700;
    }

    .report-filter-card {
        margin-bottom: 22px;
        padding: 20px 22px;
        border: 1px solid var(--report-border);
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 8px 24px rgba(31, 45, 75, .06);
    }

    .report-filter-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 16px;
    }

    .report-filter-head h4 {
        margin: 0 0 4px;
        color: #27324a;
        font-size: 17px;
        font-weight: 700;
    }

    .report-filter-head p {
        margin: 0;
        color: var(--report-muted);
        font-size: 12px;
    }

    .report-filter-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 12px;
        border-radius: 999px;
        color: var(--report-primary-dark);
        background: #f9e9ee;
        font-size: 11px;
        font-weight: 700;
    }

    .report-filter-form {
        display: flex;
        align-items: end;
        flex-wrap: wrap;
        gap: 12px;
    }

    .report-filter-field {
        min-width: 240px;
        flex: 1 1 320px;
    }

    .report-filter-field label {
        display: block;
        margin-bottom: 8px;
        color: #596277;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .03em;
        text-transform: uppercase;
    }

    .report-filter-select {
        width: 100%;
        height: 44px;
        padding: 0 14px;
        border: 1px solid #d8deea;
        border-radius: 12px;
        color: #27324a;
        background: #fbfcff;
        font-size: 13px;
        font-weight: 600;
    }

    .report-filter-select:focus {
        border-color: #c65a77;
        outline: 0;
        box-shadow: 0 0 0 3px rgba(198, 90, 119, .12);
    }

    .report-filter-actions {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }

    .report-filter-button,
    .report-filter-reset {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        height: 44px;
        padding: 0 16px;
        border: 1px solid #d8deea;
        border-radius: 12px;
        background: #fff;
        color: #27324a;
        font-size: 13px;
        font-weight: 700;
    }

    .report-filter-button {
        border-color: transparent;
        color: #fff;
        background: linear-gradient(135deg, #8b1e3f, #c65a77);
    }

    .report-filter-reset:hover,
    .report-filter-button:hover {
        text-decoration: none;
    }

    .report-filter-reset:hover {
        color: var(--report-primary-dark);
        border-color: #c65a77;
    }

    .district-report-card {
        margin-bottom: 18px;
        border: 1px solid var(--report-border);
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 8px 28px rgba(31, 45, 75, .07);
        overflow: hidden;
        page-break-inside: avoid;
    }

    .district-report-card:last-child {
        margin-bottom: 0;
    }

    .district-report-trigger {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        width: 100%;
        padding: 20px 24px;
        border: 0;
        border-bottom: 1px solid var(--report-border);
        text-align: left;
        background: linear-gradient(180deg, #fff 0%, #fcfbfd 100%);
    }

    .district-report-trigger:hover {
        background: #fff7f9;
    }

    .district-report-header h4 {
        margin: 0 0 4px;
        color: #27324a;
        font-size: 18px;
        font-weight: 700;
    }

    .district-report-header p {
        margin: 0;
        color: var(--report-muted);
        font-size: 12px;
    }

    .district-report-badges {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: 8px;
    }

    .district-report-header-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 12px;
    }

    .district-report-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 12px;
        border-radius: 999px;
        color: var(--report-primary-dark);
        background: #f9e9ee;
        font-size: 11px;
        font-weight: 700;
    }

    .district-report-chevron {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border-radius: 999px;
        color: var(--report-primary-dark);
        background: #f9e9ee;
        font-size: 18px;
        transition: transform .2s ease;
    }

    .district-report-trigger[aria-expanded="true"] .district-report-chevron {
        transform: rotate(180deg);
    }

    .district-report-table-wrap {
        padding: 10px 24px 22px;
    }

    .district-report-table {
        margin: 0;
        border-collapse: separate;
        border-spacing: 0 8px;
    }

    .district-report-table thead th {
        padding: 11px 14px;
        border: 0;
        color: #687086;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .district-report-table tbody td {
        padding: 14px;
        border-top: 1px solid var(--report-border);
        border-bottom: 1px solid var(--report-border);
        vertical-align: middle;
        background: #fff;
    }

    .district-report-table tbody td:first-child {
        border-left: 1px solid var(--report-border);
        border-radius: 11px 0 0 11px;
    }

    .district-report-table tbody td:last-child {
        border-right: 1px solid var(--report-border);
        border-radius: 0 11px 11px 0;
    }

    .district-report-table tbody tr:hover td {
        background: #fff7f9;
    }

    .school-cell {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 250px;
    }

    .school-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        flex: 0 0 42px;
        border-radius: 12px;
        color: #fff;
        background: linear-gradient(135deg, #8b1e3f, #c65a77);
        font-size: 19px;
    }

    .school-name {
        display: block;
        color: #27324a;
        font-weight: 600;
        text-transform: uppercase;
    }

    .school-id {
        color: #596277;
        font-family: Consolas, Monaco, monospace;
        font-size: 12px;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 10px;
        border-radius: 999px;
        color: #147a50;
        background: #e6f7ef;
        font-size: 11px;
        font-weight: 700;
    }

    .report-actions-cell {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-wrap: wrap;
        gap: 8px;
        min-width: 150px;
    }

    .checklist-column {
        white-space: nowrap;
    }

    .report-empty {
        padding: 52px 24px;
        color: var(--report-muted);
        text-align: center;
    }

    .report-empty i {
        display: block;
        margin-bottom: 10px;
        color: #aab2c3;
        font-size: 38px;
    }

    @media (max-width: 991.98px) {
        .report-summary-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 767.98px) {
        .report-hero,
        .district-report-trigger {
            align-items: flex-start;
            flex-direction: column;
        }

        .report-hero {
            padding: 22px;
            border-radius: 14px;
        }

        .report-actions,
        .district-report-badges,
        .report-filter-actions,
        .district-report-header-actions {
            width: 100%;
        }

        .report-action-link,
        .report-action-button,
        .report-pill,
        .report-filter-button,
        .report-filter-reset {
            justify-content: center;
            flex: 1 1 auto;
        }

        .report-filter-head,
        .report-filter-form {
            align-items: stretch;
            flex-direction: column;
        }

        .district-report-header-actions {
            align-items: flex-start;
            flex-direction: column;
        }

        .district-report-table-wrap {
            padding: 8px 14px 18px;
        }
    }

    @media print {
        body {
            background: #fff !important;
        }

        .navbar-custom,
        .left-side-menu,
        .page-title-box,
        .footer,
        .report-actions,
        .report-filter-card,
        .btn,
        .report-actions-cell,
        .checklist-column {
            display: none !important;
        }

        .content-page,
        .content,
        .container-fluid {
            margin: 0 !important;
            padding: 0 !important;
        }

        .public-report-shell {
            max-width: none !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .district-report-collapse {
            display: block !important;
            height: auto !important;
        }

        .report-hero,
        .report-summary-card,
        .district-report-card {
            box-shadow: none !important;
        }
    }

    <?php if ($print_mode) : ?>
    .navbar-custom,
    .left-side-menu,
    .page-title-box,
    .footer,
    .report-filter-card,
    .report-actions-cell,
    .checklist-column {
        display: none !important;
    }

    .content-page,
    .content,
    .container-fluid {
        margin: 0 !important;
        padding: 0 !important;
    }

    .public-report-shell {
        max-width: none !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    .district-report-collapse {
        display: block !important;
        height: auto !important;
    }

    .report-hero,
    .report-summary-card,
    .district-report-card {
        box-shadow: none !important;
    }
    <?php endif; ?>
</style>

<div class="checklist-report-page <?= $is_region_scope ? 'region-scope' : 'division-scope'; ?>">
    <div class="row">
        <div class="col-12">
            <div class="report-hero">
                <div>
                    <h2><i class="mdi mdi-file-chart-outline mr-2"></i><?= html_escape($hero_title); ?></h2>
                    <?php if (trim((string) $hero_description) !== '') : ?>
                        <p><?= html_escape($hero_description); ?></p>
                    <?php endif; ?>
                </div>
                <div class="report-actions">
                    <span class="report-pill">
                        <i class="mdi mdi-check-decagram-outline"></i>
                        <?= $result_count; ?> finalized <?= $result_count === 1 ? 'school' : 'schools'; ?>
                    </span>
                    <?php if (!$print_mode && $printable_url !== '') : ?>
                        <a href="<?= html_escape($printable_url); ?>" class="report-action-link printable-link" target="_blank" rel="noopener">
                            <i class="mdi mdi-file-document-outline"></i>
                            Printable Version
                        </a>
                    <?php endif; ?>
                    <?php if (!$print_mode && $shareable_url !== '') : ?>
                        <a href="<?= html_escape($shareable_url); ?>" class="report-action-link" target="_blank" rel="noopener">
                            <i class="mdi mdi-share-variant"></i>
                            Shared Link
                        </a>
                    <?php endif; ?>
                    <?php if ($back_url !== '') : ?>
                        <a href="<?= html_escape($back_url); ?>" class="report-action-link">
                            <i class="mdi mdi-arrow-left"></i>
                            <?= html_escape($back_label); ?>
                        </a>
                    <?php endif; ?>
                    <?php if (!$share_mode) : ?>
                        <button type="button" class="report-action-button" onclick="window.print();">
                            <i class="mdi mdi-printer-outline"></i>
                            Print Report
                        </button>
                    <?php endif; ?>
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

    <?php if (!empty($filter_options)) : ?>
        <section class="report-filter-card">
            <div class="report-filter-head">
                <div>
                    <h4><?= html_escape($filter_title); ?></h4>
                    <p><?= html_escape($filter_description); ?></p>
                </div>
                <span class="report-filter-badge">
                    <i class="mdi <?= html_escape(isset($filter_icon) ? $filter_icon : 'mdi-filter-variant'); ?>"></i>
                    <?= html_escape($selected_filter_name !== '' ? $selected_filter_name : $filter_placeholder); ?>
                </span>
            </div>

            <form action="<?= html_escape($filter_action_url); ?>" method="get" class="report-filter-form">
                <?php foreach ($filter_hidden_fields as $hidden_name => $hidden_value) : ?>
                    <input
                        type="hidden"
                        name="<?= html_escape((string) $hidden_name); ?>"
                        value="<?= html_escape((string) $hidden_value); ?>"
                    >
                <?php endforeach; ?>
                <div class="report-filter-field">
                    <label for="checklistReportFilter"><?= html_escape($filter_title); ?></label>
                    <select id="checklistReportFilter" name="<?= html_escape($filter_param); ?>" class="report-filter-select" onchange="this.form.submit()">
                        <option value=""><?= html_escape($filter_placeholder); ?></option>
                        <?php foreach ($filter_options as $option) : ?>
                            <option value="<?= html_escape($option['id']); ?>" <?= (string) $option['id'] === $selected_filter ? 'selected' : ''; ?>>
                                <?= html_escape($option['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="report-filter-actions">
                    <button type="submit" class="report-filter-button">
                        <i class="mdi mdi-filter-outline"></i>
                        Apply Filter
                    </button>
                    <a href="<?= html_escape($filter_reset_url !== '' ? $filter_reset_url : $filter_action_url); ?>" class="report-filter-reset">
                        <i class="mdi mdi-refresh"></i>
                        Reset
                    </a>
                </div>
            </form>
        </section>
    <?php endif; ?>

    <div class="report-summary-grid">
        <div class="report-summary-card">
            <span>District Groups</span>
            <strong><?= $district_group_total; ?></strong>
        </div>
        <div class="report-summary-card">
            <span>Finalized Schools</span>
            <strong><?= $result_count; ?></strong>
        </div>
        <div class="report-summary-card">
            <span>Report Scope</span>
            <strong><?= $is_region_scope ? 'Regional' : 'Division'; ?></strong>
        </div>
    </div>

    <?php if (!empty($district_groups)) : ?>
        <?php foreach ($district_groups as $group_index => $group) :
            $collapse_id = 'districtReportCollapse' . $group_index;
        ?>
            <section class="district-report-card">
                <button
                    type="button"
                    class="district-report-trigger"
                    data-toggle="collapse"
                    data-target="#<?= $collapse_id; ?>"
                    aria-expanded="true"
                    aria-controls="<?= $collapse_id; ?>"
                >
                    <div class="district-report-header">
                        <h4><?= html_escape($group['district_name']); ?></h4>
                        <p>Schools are arranged alphabetically within this district report group.</p>
                    </div>
                    <div class="district-report-header-actions">
                        <div class="district-report-badges">
                            <?php if ($is_region_scope) : ?>
                                <span class="district-report-badge">
                                    <i class="mdi mdi-domain"></i>
                                    <?= html_escape($group['division_name']); ?>
                                </span>
                            <?php endif; ?>
                            <span class="district-report-badge">
                                <i class="mdi mdi-school-outline"></i>
                                <?= count($group['records']); ?> <?= count($group['records']) === 1 ? 'school' : 'schools'; ?>
                            </span>
                            <span class="district-report-badge">
                                <i class="mdi mdi-calendar-range"></i>
                                <?= html_escape($report_badge); ?>
                            </span>
                        </div>
                        <span class="district-report-chevron">
                            <i class="mdi mdi-chevron-down"></i>
                        </span>
                    </div>
                </button>

                <div id="<?= $collapse_id; ?>" class="collapse show district-report-collapse">
                    <div class="district-report-table-wrap table-responsive">
                        <table class="table district-report-table">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>School</th>
                                    <th>School ID</th>
                                    <th>Status</th>
                                    <?php if (!$share_mode) : ?>
                                        <th class="text-center checklist-column">Checklist</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($group['records'] as $index => $record) : ?>
                                    <tr>
                                        <td><?= $index + 1; ?></td>
                                        <td>
                                            <div class="school-cell">
                                                <span class="school-icon"><i class="mdi mdi-school-outline"></i></span>
                                                <span class="school-name"><?= html_escape($record->schoolName !== '' ? $record->schoolName : 'Unnamed School'); ?></span>
                                            </div>
                                        </td>
                                        <td><span class="school-id"><?= html_escape($record->schoolID); ?></span></td>
                                        <td>
                                            <span class="status-badge">
                                                <i class="mdi mdi-check-decagram-outline"></i>
                                                <?= html_escape($record->detail_status); ?>
                                            </span>
                                        </td>
                                        <?php if (!$share_mode) : ?>
                                            <td class="checklist-column">
                                                <div class="report-actions-cell">
                                                    <a href="<?= base_url(); ?>Pages/checklist_district/<?= rawurlencode($record->school_id); ?>" class="btn btn-success btn-sm" target="_blank" rel="noopener">
                                                        <i class="mdi mdi-clipboard-text-outline"></i> Open
                                                    </a>
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
        <?php endforeach; ?>
    <?php else : ?>
        <div class="card district-report-card">
            <div class="report-empty">
                <i class="mdi mdi-database-search-outline"></i>
                No finalized Self-Assessment Checklist records were found for the active fiscal year.
            </div>
        </div>
    <?php endif; ?>
</div>
