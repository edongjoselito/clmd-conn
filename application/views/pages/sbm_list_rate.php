<?php
$rate_labels = array(
    1 => 'Not Yet Manifested',
    2 => 'Rarely Manifested',
    3 => 'Frequently Manifested',
    4 => 'Always Manifested'
);
$rate_label = isset($rate_labels[$rate_value]) ? $rate_labels[$rate_value] : 'Rating';
$indicator_number = (int) substr($rate_question, 1);
$result_count = count($data);
$indicator_description = isset($rate_indicator_description) ? trim((string) $rate_indicator_description) : '';
$indicator_principle = isset($rate_indicator_principle) ? trim((string) $rate_indicator_principle) : '';
$results_description = in_array($rate_scope, array('division', 'region'), true)
    ? 'Schools with finalized checklist submissions reporting'
    : 'Schools reporting';
$back_url = $rate_scope === 'division'
    ? base_url()
    : 'javascript:history.back()';
?>

<style>
    .rate-results-page {
        --rate-primary: #8b1e3f;
        --rate-primary-dark: #64142d;
        --rate-border: #e8ecf4;
        --rate-muted: #6b7280;
    }

    .rate-results-hero {
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

    .rate-results-hero h2 {
        margin: 0 0 7px;
        color: #fff;
        font-size: 25px;
        font-weight: 700;
    }

    .rate-results-hero p {
        margin: 0;
        color: rgba(255, 255, 255, .82);
    }

    .rate-hero-actions {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }

    .rate-result-count,
    .rate-back-link {
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

    .rate-back-link:hover {
        color: var(--rate-primary-dark);
        background: #fff;
    }

    .rate-results-card {
        border: 1px solid var(--rate-border);
        border-radius: 16px;
        box-shadow: 0 8px 28px rgba(31, 45, 75, .07);
        overflow: hidden;
    }

    .rate-results-card .card-body {
        padding: 0;
    }

    .rate-results-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 20px 24px;
        border-bottom: 1px solid var(--rate-border);
    }

    .rate-results-toolbar h4 {
        margin: 0 0 3px;
        color: #27324a;
        font-size: 17px;
        font-weight: 700;
    }

    .rate-results-toolbar p {
        margin: 0;
        color: var(--rate-muted);
        font-size: 12px;
    }

    .rate-filter-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 11px;
        border-radius: 999px;
        color: var(--rate-primary-dark);
        background: #f9e9ee;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
    }

    .rate-table-wrap {
        padding: 8px 24px 22px;
    }

    .rate-results-page .dataTables_wrapper .row:first-child {
        align-items: center;
        padding: 12px 0 6px;
    }

    .rate-results-page .dataTables_filter input,
    .rate-results-page .dataTables_length select {
        min-height: 38px;
        border: 1px solid #dce2ee;
        border-radius: 9px;
        box-shadow: none;
    }

    .rate-results-page table.dataTable {
        margin-top: 12px !important;
        border-collapse: separate !important;
        border-spacing: 0 8px !important;
    }

    .rate-results-page table.dataTable thead th {
        padding: 11px 14px;
        border: 0;
        color: #687086;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .rate-results-page table.dataTable tbody td {
        padding: 14px;
        border-top: 1px solid var(--rate-border);
        border-bottom: 1px solid var(--rate-border);
        vertical-align: middle;
        background: #fff;
    }

    .rate-results-page table.dataTable tbody td:first-child {
        border-left: 1px solid var(--rate-border);
        border-radius: 11px 0 0 11px;
    }

    .rate-results-page table.dataTable tbody td:last-child {
        border-right: 1px solid var(--rate-border);
        border-radius: 0 11px 11px 0;
    }

    .rate-results-page table.dataTable tbody tr:hover td {
        background: #fff7f9;
    }

    .rate-school-cell {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 230px;
    }

    .rate-school-icon {
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

    .rate-school-name {
        color: #27324a;
        font-weight: 600;
    }

    .rate-school-id {
        color: #596277;
        font-family: Consolas, Monaco, monospace;
        font-size: 12px;
    }

    .division-label {
        display: inline-flex;
        padding: 6px 10px;
        border-radius: 999px;
        color: var(--rate-primary-dark);
        background: #f9e9ee;
        font-size: 11px;
        font-weight: 700;
    }

    .document-actions {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-wrap: wrap;
        gap: 6px;
        min-width: 270px;
    }

    .document-actions .btn {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 7px 9px;
        border-radius: 8px;
        font-size: 11px;
        font-weight: 600;
    }

    .rate-results-page .alert {
        border: 0;
        border-radius: 12px;
        box-shadow: 0 6px 18px rgba(31, 45, 75, .07);
    }

    .rate-indicator-detail {
        margin-top: 16px;
        padding: 16px 18px;
        border: 1px solid rgba(255, 255, 255, .2);
        border-radius: 14px;
        background: rgba(255, 255, 255, .12);
        backdrop-filter: blur(6px);
        max-width: 780px;
    }

    .rate-indicator-detail strong {
        display: block;
        margin-bottom: 6px;
        color: #fff;
        font-size: 13px;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .rate-indicator-detail h5 {
        margin: 0 0 6px;
        color: #fff;
        font-size: 15px;
        font-weight: 700;
    }

    .rate-indicator-detail p {
        margin: 0;
        color: rgba(255, 255, 255, .9);
        line-height: 1.65;
    }

    @media (max-width: 767.98px) {
        .rate-results-hero {
            align-items: flex-start;
            flex-direction: column;
            padding: 22px;
            border-radius: 14px;
        }

        .rate-hero-actions {
            width: 100%;
        }

        .rate-result-count,
        .rate-back-link {
            justify-content: center;
            flex: 1 1 auto;
        }

        .rate-results-toolbar {
            align-items: flex-start;
            flex-direction: column;
            padding: 18px;
        }

        .rate-table-wrap {
            padding: 6px 14px 18px;
        }

        .rate-results-page .dataTables_wrapper .row:first-child > div {
            width: 100%;
            max-width: 100%;
            flex: 0 0 100%;
        }

        .rate-results-page .dataTables_filter,
        .rate-results-page .dataTables_length {
            text-align: left;
        }

        .rate-results-page .dataTables_filter input {
            width: calc(100% - 58px);
            margin-left: 6px;
        }

        .rate-results-page .dataTables_info,
        .rate-results-page .dataTables_paginate {
            text-align: center !important;
            white-space: normal;
        }
    }
</style>

<div class="rate-results-page">
    <div class="row">
        <div class="col-12">
            <div class="rate-results-hero">
                <div>
                    <h2><i class="mdi mdi-chart-bar mr-2"></i>SBM Rating Results</h2>
                    <p><?= html_escape($results_description); ?> “<?= html_escape($rate_label); ?>” for SBM Indicator <?= $indicator_number; ?>.</p>
                    <?php if ($indicator_description !== '' || $indicator_principle !== '') : ?>
                        <div class="rate-indicator-detail">
                            <strong>Indicator Details</strong>
                            <?php if ($indicator_principle !== '') : ?>
                                <h5><?= html_escape($indicator_principle); ?></h5>
                            <?php endif; ?>
                            <?php if ($indicator_description !== '') : ?>
                                <p><?= html_escape($indicator_description); ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="rate-hero-actions">
                    <span class="rate-result-count">
                        <i class="mdi mdi-school-outline"></i>
                        <?= $result_count; ?> <?= $result_count === 1 ? 'school' : 'schools'; ?>
                    </span>
                    <a href="<?= $back_url; ?>" class="rate-back-link">
                        <i class="mdi mdi-arrow-left"></i> Back
                    </a>
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
            <div class="card rate-results-card">
                <div class="card-body">
                    <div class="rate-results-toolbar">
                        <div>
                            <h4>Matching Schools</h4>
                            <p>Open available SBM documents for each school in a new tab.</p>
                        </div>
                        <span class="rate-filter-badge">
                            <i class="mdi mdi-filter-outline"></i>
                            <?= html_escape($rate_question); ?> · <?= html_escape($rate_label); ?>
                        </span>
                    </div>

                    <div class="rate-table-wrap table-responsive">
                        <table id="datatable" class="table dt-responsive" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th>School</th>
                                    <th>School ID</th>
                                    <th>Division</th>
                                    <th class="text-center">Documents</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data as $row) :
                                    $school_id = (string) $row->school_id;
                                    $school_name = isset($row->schoolName) && trim((string) $row->schoolName) !== ''
                                        ? (string) $row->schoolName
                                        : 'Unnamed School';
                                    $division_name = isset($division_names[(string) $row->division_id])
                                        ? $division_names[(string) $row->division_id]
                                        : 'Division';
                                    $division_label = trim((string) $division_name) !== ''
                                        ? (string) $division_name
                                        : 'Division';
                                ?>
                                    <tr>
                                        <td>
                                            <div class="rate-school-cell">
                                                <span class="rate-school-icon"><i class="mdi mdi-school-outline"></i></span>
                                                <span class="rate-school-name"><?= html_escape(mb_convert_case($school_name, MB_CASE_TITLE, 'UTF-8')); ?></span>
                                            </div>
                                        </td>
                                        <td><span class="rate-school-id"><?= html_escape($school_id); ?></span></td>
                                        <td>
                                            <span class="division-label"><?= html_escape(mb_convert_case($division_label, MB_CASE_TITLE, 'UTF-8')); ?></span>
                                        </td>
                                        <td>
                                            <div class="document-actions">
                                                <a
                                                    target="_blank"
                                                    rel="noopener"
                                                    href="<?= base_url(); ?>Pages/sbm_action_plan_pview_district/<?= rawurlencode($school_id); ?>"
                                                    class="btn btn-success btn-sm"
                                                >
                                                    <i class="mdi mdi-clipboard-check-outline"></i> Action Plan
                                                </a>
                                                <a
                                                    target="_blank"
                                                    rel="noopener"
                                                    href="<?= base_url(); ?>Pages/checklist_district/<?= rawurlencode($school_id); ?>"
                                                    class="btn btn-info btn-sm"
                                                >
                                                    <i class="mdi mdi-format-list-checks"></i> Assessment
                                                </a>
                                                <a
                                                    target="_blank"
                                                    rel="noopener"
                                                    href="<?= base_url(); ?>Pages/tapr_form_district/<?= rawurlencode($school_id); ?>"
                                                    class="btn btn-warning btn-sm"
                                                >
                                                    <i class="mdi mdi-lifebuoy"></i> TA Form
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
