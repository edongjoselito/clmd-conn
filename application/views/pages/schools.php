<?php
$school_count = count($data);
$dashboard_url = base_url();
?>

<style>
    .schools-page {
        --sl-navy: var(--llcm-navy, #3b0718);
        --sl-blue: var(--llcm-blue, #922c43);
        --sl-sky: var(--llcm-sky, #fbf4f5);
        --sl-line: var(--llcm-border, #d7e5ef);
        --sl-ink: var(--llcm-ink, #233342);
        --sl-muted: #6b7f92;
        --sl-radius: 10px;
        --sl-shadow: 0 4px 16px rgba(20, 62, 94, .05);
        margin-bottom: 24px;
        color: var(--sl-ink);
    }

    .schools-page .alert {
        border: 0;
        border-radius: var(--sl-radius);
        box-shadow: var(--sl-shadow);
    }

    /* ---------- Page header ---------- */
    .sl-header {
        display: flex;
        flex-wrap: wrap;
        gap: 14px 18px;
        align-items: center;
        justify-content: space-between;
        margin: 16px 0 18px;
        padding: 18px 22px;
        border-radius: var(--sl-radius);
        color: #fff;
        background: linear-gradient(118deg, var(--sl-navy), var(--sl-blue));
    }

    .sl-eyebrow {
        display: block;
        margin-bottom: 3px;
        color: rgba(255, 255, 255, .75);
        font-size: .72rem;
        font-weight: 600;
        letter-spacing: .09em;
        text-transform: uppercase;
    }

    .sl-header h1 {
        margin: 0;
        color: #fff;
        font-size: 1.45rem;
        font-weight: 600;
        line-height: 1.25;
    }

    .sl-header-meta {
        margin: 6px 0 0;
        color: rgba(255, 255, 255, .85);
        font-size: .85rem;
    }

    .sl-header-meta span + span::before {
        content: "\00b7";
        margin: 0 .5rem;
        color: rgba(255, 255, 255, .5);
    }

    .sl-header-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .sl-btn {
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

    .sl-btn-solid {
        color: var(--sl-navy);
        background: #fff;
        border-color: #fff;
    }

    .sl-btn-solid:hover {
        color: var(--sl-navy);
        background: var(--sl-sky);
        border-color: var(--sl-sky);
        text-decoration: none;
    }

    /* ---------- Table panel ---------- */
    .sl-panel {
        border: 1px solid var(--sl-line);
        border-radius: var(--sl-radius);
        background: #fff;
        box-shadow: var(--sl-shadow);
        overflow: hidden;
    }

    .sl-panel-head {
        display: flex;
        flex-wrap: wrap;
        gap: 6px 14px;
        align-items: baseline;
        justify-content: space-between;
        padding: 15px 20px;
        border-bottom: 1px solid var(--sl-line);
    }

    .sl-panel-head h4 {
        margin: 0;
        color: var(--sl-navy);
        font-size: 1.02rem;
        font-weight: 600;
    }

    .sl-panel-head p {
        flex: 1 0 100%;
        margin: 0;
        color: var(--sl-muted);
        font-size: .84rem;
    }

    .sl-table-wrap { padding: 4px 20px 18px; }

    .schools-page .dataTables_wrapper .row:first-child {
        align-items: center;
        padding: 10px 0 4px;
    }

    .schools-page .dataTables_filter input,
    .schools-page .dataTables_length select {
        min-height: 34px;
        border: 1px solid var(--sl-line);
        border-radius: 8px;
        box-shadow: none;
    }

    .schools-page table.dataTable {
        margin-top: 8px !important;
        border-collapse: collapse !important;
        border-spacing: 0 !important;
    }

    .schools-page table.dataTable thead th {
        padding: 9px 12px;
        border: 0;
        border-bottom: 1px solid var(--sl-line);
        color: var(--sl-muted);
        background: #f7fbfe;
        font-size: .7rem;
        font-weight: 700;
        letter-spacing: .05em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .schools-page table.dataTable tbody td {
        padding: 10px 12px;
        border: 0;
        border-bottom: 1px solid #edf3f8;
        vertical-align: middle;
        background: #fff;
        font-size: .86rem;
    }

    .schools-page table.dataTable tbody tr:last-child td { border-bottom: 0; }

    .schools-page table.dataTable tbody tr:hover td { background: #f7fafc; }

    .sl-school-id {
        color: var(--sl-muted);
        font-family: Consolas, Monaco, monospace;
        font-size: .8rem;
        white-space: nowrap;
    }

    .sl-school-name {
        color: var(--sl-navy);
        font-size: .88rem;
        font-weight: 600;
    }

    .sl-district-name {
        color: var(--sl-muted);
        font-size: .82rem;
    }

    .sl-actions {
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
    }

    .sl-action {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 5px 10px;
        border: 1px solid transparent;
        border-radius: 7px;
        font-size: .76rem;
        font-weight: 600;
        text-decoration: none;
        transition: background .15s ease, border-color .15s ease, color .15s ease;
    }

    .sl-action-view {
        color: var(--sl-blue);
        background: var(--sl-sky);
    }

    .sl-action-view:hover {
        color: #fff;
        background: var(--sl-blue);
        text-decoration: none;
    }

    .sl-action-edit {
        color: #5b6b7a;
        background: #f1f4f7;
    }

    .sl-action-edit:hover {
        color: var(--sl-navy);
        background: #e3eaf0;
        text-decoration: none;
    }

    .sl-action-delete {
        color: #a33b32;
        background: #fbeeec;
        cursor: pointer;
    }

    .sl-action-delete:hover {
        color: #fff;
        background: #b04a41;
    }

    .sl-empty {
        padding: 44px 24px;
        color: var(--sl-muted);
        text-align: center;
    }

    .sl-empty i {
        display: block;
        margin-bottom: 10px;
        color: #b9cdde;
        font-size: 2.2rem;
    }

    @media (max-width: 767.98px) {
        .sl-header { padding: 16px 18px; }
        .sl-header h1 { font-size: 1.25rem; }
        .sl-header-actions { width: 100%; }
        .sl-header-actions .sl-btn { flex: 1 1 auto; justify-content: center; }
        .sl-panel-head { padding: 14px 16px; }
        .sl-table-wrap { padding: 4px 14px 14px; }

        .schools-page .dataTables_wrapper .row:first-child > div {
            width: 100%;
            max-width: 100%;
            flex: 0 0 100%;
        }

        .schools-page .dataTables_filter,
        .schools-page .dataTables_length { text-align: left; }

        .schools-page .dataTables_filter input {
            width: calc(100% - 58px);
            margin-left: 6px;
        }
    }
</style>

<div class="schools-page">
    <header class="sl-header">
        <div>
            <span class="sl-eyebrow"><i class="mdi mdi-school-outline"></i> School Directory</span>
            <h1>School List</h1>
            <p class="sl-header-meta">
                <span><?= $school_count; ?> <?= $school_count === 1 ? 'school' : 'schools'; ?></span>
                <span>View and manage school records</span>
            </p>
        </div>
        <div class="sl-header-actions">
            <?php if($this->session->position == 'Admin'){?>
            <a href="<?= base_url(); ?>pages/school_new" class="sl-btn sl-btn-solid">
                <i class="mdi mdi-plus"></i> Add New School
            </a>
            <?php } ?>
        </div>
    </header>

    <?php if($this->session->flashdata('success')) : ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <?= $this->session->flashdata('success'); ?>
        </div>
    <?php endif; ?>

    <?php if($this->session->flashdata('danger')) : ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <?= $this->session->flashdata('danger'); ?>
        </div>
    <?php endif; ?>

    <section class="sl-panel">
        <div class="sl-panel-head">
            <h4><?= html_escape($title); ?></h4>
            <p>View and manage school records.</p>
        </div>

        <?php if (!empty($data)) { ?>
            <div class="sl-table-wrap table-responsive">
                <table id="datatable" class="table dt-responsive" style="width: 100%;">
                    <thead>
                        <tr>
                            <th>School ID</th>
                            <th>School Name</th>
                            <th>District</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($data as $row){?>
                        <tr>
                            <td><span class="sl-school-id"><?= html_escape($row->schoolID); ?></span></td>
                            <td><span class="sl-school-name"><?= html_escape(strtoupper($row->schoolName)); ?></span></td>
                            <td><span class="sl-district-name"><?= html_escape($row->description); ?></span></td>
                            <td>
                                <div class="sl-actions">
                                    <a href="<?=base_url(); ?>school/<?= $row->schoolID; ?>" class="sl-action sl-action-view">
                                        <i class="mdi mdi-file-document-box-check-outline"></i> View
                                    </a>
                                    <a href="<?=base_url(); ?>Pages/school_update/<?= $row->recID; ?>" class="sl-action sl-action-edit">
                                        <i class="mdi mdi-pencil-outline"></i> Edit
                                    </a>
                                    <?php if (strtolower((string) $this->session->position) !== 'district') { ?>
                                        <?= form_open('Pages/school_delete', array('style' => 'display:inline;', 'onsubmit' => "return confirm('Are you sure you want to delete this school?');")); ?>
                                        <input type="hidden" name="id" value="<?= (int) $row->recID; ?>">
                                        <button type="submit" class="sl-action sl-action-delete">
                                            <i class="mdi mdi-trash-can-outline"></i> Delete
                                        </button>
                                        <?= form_close(); ?>
                                    <?php } ?>
                                </div>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } else { ?>
            <div class="sl-empty">
                <i class="mdi mdi-school-outline"></i>
                No schools found for this division.
            </div>
        <?php } ?>
    </section>
</div>

<script>
$(document).ready(function() {
    $('#datatable').DataTable({
        pageLength: 20,
        lengthMenu: [[10, 20, 50, 100], [10, 20, 50, 100]],
        order: [[1, 'asc']],
        responsive: true,
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search schools..."
        }
    });
});
</script>
