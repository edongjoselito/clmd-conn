<?php
$division_total = count($data);
$dashboard_url = base_url();
?>

<style>
    .report-page {
        --report-primary: #8b1e3f;
        --report-primary-dark: #64142d;
        --report-border: #e8ecf4;
        --report-muted: #6b7280;
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
        max-width: 720px;
        margin: 0;
        color: rgba(255, 255, 255, .82);
    }

    .report-actions {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }

    .report-pill {
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

    .report-card {
        border: 1px solid var(--report-border);
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 8px 28px rgba(31, 45, 75, .07);
        overflow: hidden;
    }

    .report-card .card-body {
        padding: 0;
    }

    .report-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 20px 24px;
        border-bottom: 1px solid var(--report-border);
    }

    .report-card-header h4 {
        margin: 0 0 3px;
        color: #27324a;
        font-size: 17px;
        font-weight: 700;
    }

    .report-card-header p {
        margin: 0;
        color: var(--report-muted);
        font-size: 12px;
    }

    .report-table-wrap {
        padding: 8px 24px 24px;
    }

    .report-page table.dataTable {
        margin-top: 12px !important;
        border-collapse: separate !important;
        border-spacing: 0 8px !important;
    }

    .report-page table.dataTable thead th {
        padding: 11px 14px;
        border: 0;
        color: #687086;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .report-page table.dataTable tbody td {
        padding: 14px;
        border-top: 1px solid var(--report-border);
        border-bottom: 1px solid var(--report-border);
        vertical-align: middle;
        background: #fff;
    }

    .report-page table.dataTable tbody td:first-child {
        border-left: 1px solid var(--report-border);
        border-radius: 11px 0 0 11px;
    }

    .report-page table.dataTable tbody td:last-child {
        border-right: 1px solid var(--report-border);
        border-radius: 0 11px 11px 0;
    }

    .report-page table.dataTable tbody tr:hover td {
        background: #fff7f9;
    }

    .stat-cell {
        min-width: 100px;
    }

    .stat-value {
        display: block;
        font-size: 13px;
        font-weight: 700;
        color: #27324a;
    }

    .stat-label {
        display: block;
        font-size: 11px;
        color: var(--report-muted);
    }

    .progress-wrapper {
        margin-top: 6px;
    }

    .progress {
        height: 8px;
        background: #e8ecf4;
        border-radius: 4px;
        overflow: hidden;
    }

    .progress-bar {
        height: 100%;
        border-radius: 4px;
        transition: width 0.3s ease;
    }

    .progress-bar-danger {
        background: linear-gradient(90deg, #dc3545, #c82333);
    }

    .progress-bar-warning {
        background: linear-gradient(90deg, #ffc107, #fd7e14);
    }

    .progress-bar-success {
        background: linear-gradient(90deg, #28a745, #20c997);
    }

    .percentage-text {
        display: block;
        margin-top: 4px;
        font-size: 11px;
        font-weight: 600;
        color: var(--report-muted);
    }

    .report-empty {
        padding: 48px 24px;
        color: var(--report-muted);
        text-align: center;
    }

    .report-empty i {
        display: block;
        margin-bottom: 10px;
        color: #aab2c3;
        font-size: 38px;
    }
</style>

<div class="report-page">
    <div class="row">
        <div class="col-12">
            <div class="report-hero">
                <div>
                    <h2><i class="mdi mdi-account-group mr-2"></i>School Governance Council Report</h2>
                    <p>View the SGC status across divisions, including schools that have not yet submitted an SGC response.</p>
                </div>
                <div class="report-actions">
                    <span class="report-pill">
                        <i class="mdi mdi-office-building"></i>
                        <?= $division_total; ?> <?= $division_total === 1 ? 'division' : 'divisions'; ?>
                    </span>
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
            <div class="card report-card">
                <div class="card-body">
                    <div class="report-card-header">
                        <div>
                            <h4><?= html_escape($title); ?></h4>
                            <p>SGC status breakdown per division, with schools that have not yet responded based on total encoded schools.</p>
                        </div>
                    </div>

                    <?php if (!empty($data)) { ?>
                        <div class="report-table-wrap table-responsive">
                            <table id="datatable" class="table dt-responsive" style="width: 100%;">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>Division</th>
                                        <th>Total Schools</th>
                                        <th>Not Yet Organized</th>
                                        <th>Organized (Not Functional)</th>
                                        <th>Functional</th>
                                        <th>Total SGC</th>
                                        <th>Not Yet Responded</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data as $index => $row) { ?>
                                        <tr>
                                            <td><?= $index + 1; ?></td>
                                            <td>
                                                <span class="stat-value"><?= html_escape($row->description); ?></span>
                                            </td>
                                            <td class="stat-cell">
                                                <span class="stat-value"><?= html_escape($row->total_schools); ?></span>
                                                <span class="stat-label">Total Schools</span>
                                            </td>
                                            <td class="stat-cell">
                                                <span class="stat-value"><?= html_escape($row->not_yet_organized); ?></span>
                                                <span class="stat-label">Not Yet Organized</span>
                                                <?php if ($row->total_sgc > 0) { ?>
                                                <div class="progress-wrapper">
                                                    <div class="progress">
                                                        <div class="progress-bar progress-bar-danger" style="width: <?= round(($row->not_yet_organized / $row->total_sgc) * 100, 1); ?>%;"></div>
                                                    </div>
                                                    <span class="percentage-text"><?= round(($row->not_yet_organized / $row->total_sgc) * 100, 1); ?>%</span>
                                                </div>
                                                <?php } ?>
                                            </td>
                                            <td class="stat-cell">
                                                <span class="stat-value"><?= html_escape($row->organized_not_functional); ?></span>
                                                <span class="stat-label">Organized (Not Functional)</span>
                                                <?php if ($row->total_sgc > 0) { ?>
                                                <div class="progress-wrapper">
                                                    <div class="progress">
                                                        <div class="progress-bar progress-bar-warning" style="width: <?= round(($row->organized_not_functional / $row->total_sgc) * 100, 1); ?>%;"></div>
                                                    </div>
                                                    <span class="percentage-text"><?= round(($row->organized_not_functional / $row->total_sgc) * 100, 1); ?>%</span>
                                                </div>
                                                <?php } ?>
                                            </td>
                                            <td class="stat-cell">
                                                <span class="stat-value"><?= html_escape($row->functional); ?></span>
                                                <span class="stat-label">Functional</span>
                                                <?php if ($row->total_sgc > 0) { ?>
                                                <div class="progress-wrapper">
                                                    <div class="progress">
                                                        <div class="progress-bar progress-bar-success" style="width: <?= round(($row->functional / $row->total_sgc) * 100, 1); ?>%;"></div>
                                                    </div>
                                                    <span class="percentage-text"><?= round(($row->functional / $row->total_sgc) * 100, 1); ?>%</span>
                                                </div>
                                                <?php } ?>
                                            </td>
                                            <td class="stat-cell">
                                                <span class="stat-value"><?= html_escape($row->total_sgc); ?></span>
                                                <span class="stat-label">Total SGC</span>
                                            </td>
                                            <td class="stat-cell">
                                                <span class="stat-value"><?= html_escape($row->not_yet_responded); ?></span>
                                                <span class="stat-label">Total Schools - Total SGC</span>
                                                <?php if ($row->total_schools > 0) { ?>
                                                <div class="progress-wrapper">
                                                    <div class="progress">
                                                        <div class="progress-bar progress-bar-danger" style="width: <?= round(($row->not_yet_responded / $row->total_schools) * 100, 1); ?>%;"></div>
                                                    </div>
                                                    <span class="percentage-text"><?= round(($row->not_yet_responded / $row->total_schools) * 100, 1); ?>%</span>
                                                </div>
                                                <?php } ?>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    <?php } else { ?>
                        <div class="report-empty">
                            <i class="mdi mdi-office-building-remove-outline"></i>
                            No divisions are available for the active region.
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#datatable').DataTable({
        pageLength: 20,
        lengthMenu: [[10, 20, 50, 100], [10, 20, 50, 100]],
        order: [[0, 'asc']],
        responsive: true,
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search divisions..."
        }
    });
});
</script>
