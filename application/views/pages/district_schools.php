<?php
$district_name = !empty($district) ? $district->description : 'All Schools';
$is_admin_view = isset($is_admin_view) ? $is_admin_view : false;
$submission_labels = array(
    'sgod_action_plan' => 'Action Plan',
    'sbm' => 'Self-Assessment',
    'sbm_ta' => 'TA Form'
);
$selected_label = isset($submission_labels[$selected_submission])
    ? $submission_labels[$selected_submission]
    : 'Submission';
$hero_title = (!$is_admin_view && empty($division_school_scope))
    ? 'SBM Submissions'
    : mb_convert_case($district_name, MB_CASE_TITLE, 'UTF-8');
?>

<style>
    .division-schools-page {
        --ds-navy: var(--llcm-navy, #3b0718);
        --ds-blue: var(--llcm-blue, #922c43);
        --ds-sky: var(--llcm-sky, #fbf4f5);
        --ds-line: var(--llcm-border, #ead7dc);
        --ds-ink: var(--llcm-ink, #233342);
        --ds-muted: #6b7f92;
        --ds-radius: 10px;
        --ds-shadow: 0 4px 16px rgba(20, 62, 94, .05);
        margin-bottom: 24px;
        color: var(--ds-ink);
    }

    .division-schools-page .alert {
        border: 0;
        border-radius: var(--ds-radius);
        box-shadow: var(--ds-shadow);
    }

    /* ---------- Page header ---------- */
    .ds-header {
        display: flex;
        flex-wrap: wrap;
        gap: 14px 18px;
        align-items: center;
        justify-content: space-between;
        margin: 16px 0 18px;
        padding: 18px 22px;
        border-radius: var(--ds-radius);
        color: #fff;
        background: linear-gradient(118deg, var(--ds-navy), var(--ds-blue));
    }

    .ds-eyebrow {
        display: block;
        margin-bottom: 3px;
        color: rgba(255, 255, 255, .75);
        font-size: .72rem;
        font-weight: 600;
        letter-spacing: .09em;
        text-transform: uppercase;
    }

    .ds-header h1 {
        margin: 0;
        color: #fff;
        font-size: 1.45rem;
        font-weight: 600;
        line-height: 1.25;
    }

    .ds-header-meta {
        margin: 6px 0 0;
        color: rgba(255, 255, 255, .85);
        font-size: .85rem;
    }

    .ds-header-meta span + span::before {
        content: "\00b7";
        margin: 0 .5rem;
        color: rgba(255, 255, 255, .5);
    }

    .ds-header-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .ds-btn {
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

    .ds-btn-ghost {
        color: #fff;
        border-color: rgba(255, 255, 255, .45);
        background: transparent;
    }

    .ds-btn-ghost:hover {
        color: var(--ds-navy);
        background: #fff;
        border-color: #fff;
        text-decoration: none;
    }

    /* ---------- Table panel ---------- */
    .ds-panel {
        border: 1px solid var(--ds-line);
        border-radius: var(--ds-radius);
        background: #fff;
        box-shadow: var(--ds-shadow);
        overflow: hidden;
    }

    .ds-panel-head {
        display: flex;
        flex-wrap: wrap;
        gap: 6px 14px;
        align-items: center;
        justify-content: space-between;
        padding: 15px 20px;
        border-bottom: 1px solid var(--ds-line);
    }

    .ds-panel-head h4 {
        margin: 0;
        color: var(--ds-navy);
        font-size: 1.02rem;
        font-weight: 600;
    }

    .ds-panel-head .ds-head-text { flex: 1 1 auto; min-width: 200px; }

    .ds-panel-head p {
        margin: 2px 0 0;
        color: var(--ds-muted);
        font-size: .84rem;
    }

    .ds-tag {
        display: inline-flex;
        gap: 5px;
        align-items: center;
        padding: 3px 11px;
        border-radius: 999px;
        background: var(--ds-sky);
        color: var(--ds-navy);
        font-size: .74rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .ds-table-wrap { padding: 4px 20px 18px; }

    .division-schools-page .dataTables_wrapper .row:first-child {
        align-items: center;
        padding: 10px 0 4px;
    }

    .division-schools-page .dataTables_filter input,
    .division-schools-page .dataTables_length select {
        min-height: 34px;
        border: 1px solid var(--ds-line);
        border-radius: 8px;
        box-shadow: none;
    }

    .division-schools-page table.dataTable {
        margin-top: 8px !important;
        border-collapse: collapse !important;
        border-spacing: 0 !important;
    }

    .division-schools-page table.dataTable thead th {
        padding: 9px 12px;
        border: 0;
        border-bottom: 1px solid var(--ds-line);
        color: var(--ds-muted);
        background: #f7fbfe;
        font-size: .7rem;
        font-weight: 700;
        letter-spacing: .05em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .division-schools-page table.dataTable tbody td {
        padding: 10px 12px;
        border: 0;
        border-bottom: 1px solid #edf3f8;
        vertical-align: middle;
        background: #fff;
        font-size: .86rem;
    }

    .division-schools-page table.dataTable tbody tr:last-child td { border-bottom: 0; }

    .division-schools-page table.dataTable tbody tr:hover td { background: #f7fafc; }

    .ds-school-cell {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 220px;
    }

    .ds-school-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        flex: 0 0 30px;
        border-radius: 8px;
        background: var(--ds-sky);
        color: var(--ds-blue);
        font-size: .95rem;
    }

    .ds-school-name {
        color: var(--ds-navy);
        font-size: .88rem;
        font-weight: 600;
    }

    .ds-school-id {
        color: var(--ds-muted);
        font-family: Consolas, Monaco, monospace;
        font-size: .8rem;
        white-space: nowrap;
    }

    .ds-submission {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        min-width: 72px;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: .76rem;
        font-weight: 600;
    }

    .ds-submission.available {
        color: #1d6b3c;
        background: #edf9f1;
    }

    .ds-submission.available:hover {
        color: #fff;
        background: #1d6b3c;
        text-decoration: none;
    }

    .ds-submission.missing {
        color: #7a8a97;
        background: #f1f4f7;
    }

    .division-schools-page .btn-sm {
        padding: 5px 10px;
        border-radius: 7px;
        font-size: .76rem;
    }

    @media (max-width: 767.98px) {
        .ds-header { padding: 16px 18px; }
        .ds-header h1 { font-size: 1.25rem; }
        .ds-header-actions { width: 100%; }
        .ds-header-actions .ds-btn { flex: 1 1 auto; justify-content: center; }
        .ds-panel-head { padding: 14px 16px; }
        .ds-table-wrap { padding: 4px 14px 14px; }

        .division-schools-page .dataTables_wrapper .row:first-child > div {
            width: 100%;
            max-width: 100%;
            flex: 0 0 100%;
        }

        .division-schools-page .dataTables_filter,
        .division-schools-page .dataTables_length { text-align: left; }

        .division-schools-page .dataTables_filter input {
            width: calc(100% - 58px);
            margin-left: 6px;
        }

        .division-schools-page .dataTables_info,
        .division-schools-page .dataTables_paginate {
            text-align: center !important;
            white-space: normal;
        }
    }
</style>

<div class="division-schools-page">
    <header class="ds-header">
        <div>
            <span class="ds-eyebrow"><i class="mdi mdi-school-outline"></i> <?= $is_admin_view ? 'School Management' : 'District Submissions'; ?></span>
            <h1><?= html_escape($hero_title); ?></h1>
            <p class="ds-header-meta">
                <span><?= count($data); ?> <?= count($data) === 1 ? 'school' : 'schools'; ?></span>
                <span><?= $is_admin_view ? 'Manage all schools in the system' : 'Available SBM documents for this district'; ?></span>
            </p>
        </div>
        <div class="ds-header-actions">
            <?php if (!empty($division_school_scope) && strtolower((string) $this->session->position) !== 'district') { ?>
            <a class="ds-btn ds-btn-ghost" href="<?= base_url(); ?>Pages/district_list">
                <i class="mdi mdi-arrow-left"></i> Districts
            </a>
            <?php } ?>
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

    <section class="ds-panel">
        <div class="ds-panel-head">
            <div class="ds-head-text">
                <h4><?= $is_admin_view ? 'School Management' : 'School Submissions'; ?></h4>
                <p><?= $is_admin_view ? 'Edit or delete school records.' : 'Open any available document in a new tab.'; ?></p>
            </div>
            <?php if (!$is_admin_view) : ?>
            <span class="ds-tag">
                <i class="mdi mdi-filter-outline"></i>
                <?= html_escape($selected_label); ?>
            </span>
            <?php endif; ?>
        </div>

        <div class="ds-table-wrap table-responsive">
            <table id="datatable" class="table dt-responsive" style="width: 100%;">
                <thead>
                    <tr>
                        <th>School</th>
                        <th>School ID</th>
                        <?php if ($is_admin_view) : ?>
                        <th>Division</th>
                        <th>District</th>
                        <th class="text-center">Actions</th>
                        <?php else : ?>
                        <th class="text-center">Action Plan</th>
                        <th class="text-center">Self-Assessment</th>
                        <th class="text-center">TA Form</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data as $row) :
                        $school_id = trim((string) $row->schoolID);
                        $school_name = !empty($row->schoolName) ? mb_convert_case($row->schoolName, MB_CASE_TITLE, 'UTF-8') : '';

                        if ($is_admin_view) {
                            // Get division and district names for admin view
                            $division_name_display = !empty($row->division_name) ? mb_convert_case($row->division_name, MB_CASE_TITLE, 'UTF-8') : '';
                            $district_name_display = !empty($row->district_name) ? mb_convert_case($row->district_name, MB_CASE_TITLE, 'UTF-8') : '';

                            // Check if school has completed Self-Assessment and Action Plan
                            $has_action_plan = !empty($submission_status['sgod_action_plan'][$school_id]);
                            $has_self_assessment = !empty($submission_status['sbm'][$school_id]);
                            $can_delete = !($has_action_plan && $has_self_assessment);
                        } else {
                            $links = array(
                                'sgod_action_plan' => base_url() . 'Pages/sbm_action_plan_pview_district/' . rawurlencode($school_id),
                                'sbm' => base_url() . 'Pages/checklist_district/' . rawurlencode($school_id),
                                'sbm_ta' => base_url() . 'Pages/tapr_form_district/' . rawurlencode($school_id)
                            );
                        }
                    ?>
                        <tr>
                            <td>
                                <div class="ds-school-cell">
                                    <span class="ds-school-icon"><i class="mdi mdi-school-outline"></i></span>
                                    <span class="ds-school-name"><?= html_escape($school_name); ?></span>
                                </div>
                            </td>
                            <td><span class="ds-school-id"><?= html_escape($school_id); ?></span></td>
                            <?php if ($is_admin_view) : ?>
                            <td><?= html_escape($division_name_display); ?></td>
                            <td><?= html_escape($district_name_display); ?></td>
                            <td class="text-center">
                                <a href="<?= base_url(); ?>pages/school_update/<?= rawurlencode($school_id); ?>" class="btn btn-sm btn-outline-secondary">
                                    <i class="mdi mdi-pencil-outline"></i> Edit
                                </a>
                                <?php if ($can_delete) : ?>
                                <?= form_open('pages/school_delete', array('style' => 'display:inline;', 'onsubmit' => "return confirm('Are you sure you want to delete this school?');")); ?>
                                <input type="hidden" name="id" value="<?= (int) $row->recID; ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="mdi mdi-trash-can-outline"></i> Delete</button>
                                <?= form_close(); ?>
                                <?php else : ?>
                                <button class="btn btn-sm btn-outline-danger" disabled title="Cannot delete: School has protected existing records">
                                    <i class="mdi mdi-trash-can-outline"></i> Delete
                                </button>
                                <?php endif; ?>
                            </td>
                            <?php else : ?>
                            <?php foreach (array('sgod_action_plan', 'sbm', 'sbm_ta') as $submission) :
                                $available = !empty($submission_status[$submission][$school_id]);
                            ?>
                                <td class="text-center">
                                    <?php if ($available) { ?>
                                        <a
                                            target="_blank"
                                            rel="noopener"
                                            href="<?= $links[$submission]; ?>"
                                            class="ds-submission available"
                                        >
                                            <i class="mdi mdi-open-in-new"></i> View
                                        </a>
                                    <?php } else { ?>
                                        <span class="ds-submission missing">
                                            <i class="mdi mdi-minus-circle-outline"></i> None
                                        </span>
                                    <?php } ?>
                                </td>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>
