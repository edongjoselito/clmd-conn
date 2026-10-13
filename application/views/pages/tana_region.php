<?php
$priority_rows = array();
$analysis_rows = array();
$used_sequences = array();

foreach ($data as $row) {
    $text = isset($row->tana) ? trim((string) $row->tana) : '';

    if ($text === '') {
        continue;
    }

    $priority_rows[] = $text;
}

foreach ($ivy as $analysis) {
    $used_sequences[(int) $analysis->sequence] = true;

    $analysis_text = isset($analysis->tana) ? trim((string) $analysis->tana) : '';

    if ($analysis_text === '') {
        continue;
    }

    $analysis_rows[] = $analysis;
}

$concern_count = count($priority_rows);
$analysis_count = count($analysis_rows);
$dashboard_url = base_url();
?>

<style>
    .tana-region-page {
        --tana-primary: #8b1e3f;
        --tana-primary-dark: #64142d;
        --tana-border: #e8ecf4;
        --tana-muted: #6b7280;
    }

    .tana-region-page .alert {
        border: 0;
        border-radius: 12px;
        box-shadow: 0 6px 18px rgba(31, 45, 75, .07);
    }

    .tana-hero {
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

    .tana-hero h2 {
        margin: 0 0 7px;
        color: #fff;
        font-size: 25px;
        font-weight: 700;
    }

    .tana-hero p {
        max-width: 690px;
        margin: 0;
        color: rgba(255, 255, 255, .82);
    }

    .tana-hero-actions {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }

    .tana-count,
    .tana-hero-link,
    .tana-add-button {
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

    .tana-add-button {
        border: 0;
        color: var(--tana-primary-dark);
        background: #fff;
        box-shadow: 0 7px 18px rgba(22, 36, 88, .18);
    }

    .tana-hero-link:hover {
        color: var(--tana-primary-dark);
        background: #fff;
        text-decoration: none;
    }

    .tana-add-button:hover {
        color: var(--tana-primary);
        transform: translateY(-1px);
    }

    .tana-panel {
        margin-bottom: 22px;
        border: 1px solid var(--tana-border);
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 8px 28px rgba(31, 45, 75, .07);
        overflow: hidden;
    }

    .tana-panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 20px 24px;
        border-bottom: 1px solid var(--tana-border);
    }

    .tana-panel-header h4 {
        margin: 0 0 3px;
        color: #27324a;
        font-size: 17px;
        font-weight: 700;
    }

    .tana-panel-header p {
        margin: 0;
        color: var(--tana-muted);
        font-size: 12px;
    }

    .tana-panel-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 11px;
        border-radius: 999px;
        color: var(--tana-primary-dark);
        background: #f9e9ee;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
    }

    .tana-table-wrap {
        padding: 10px 24px 24px;
    }

    .tana-table {
        margin: 0;
        border-collapse: separate;
        border-spacing: 0 8px;
    }

    .tana-table thead th {
        padding: 11px 14px;
        border: 0;
        color: #687086;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
    }

    .tana-table tbody td {
        padding: 15px 14px;
        border-top: 1px solid var(--tana-border);
        border-bottom: 1px solid var(--tana-border);
        vertical-align: top;
        background: #fff;
    }

    .tana-table tbody td:first-child {
        border-left: 1px solid var(--tana-border);
        border-radius: 11px 0 0 11px;
    }

    .tana-table tbody td:last-child {
        border-right: 1px solid var(--tana-border);
        border-radius: 0 11px 11px 0;
    }

    .tana-table tbody tr:hover td {
        background: #fff7f9;
    }

    .tana-sequence {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 36px;
        height: 36px;
        padding: 0 9px;
        border-radius: 10px;
        color: var(--tana-primary-dark);
        background: #f9e9ee;
        font-size: 11px;
        font-weight: 700;
    }

    .tana-concern {
        color: #39445b;
        line-height: 1.65;
        white-space: normal;
    }

    .tana-delete-button {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 7px 10px;
        border-radius: 8px;
        font-size: 11px;
        font-weight: 700;
    }

    .tana-empty {
        padding: 48px 24px;
        color: var(--tana-muted);
        text-align: center;
    }

    .tana-empty i {
        display: block;
        margin-bottom: 10px;
        color: #aab2c3;
        font-size: 38px;
    }

    .tana-modal .modal-content {
        border: 0;
        border-radius: 16px;
        box-shadow: 0 18px 45px rgba(31, 45, 75, .2);
        overflow: hidden;
    }

    .tana-modal .modal-header {
        padding: 18px 20px;
        color: #fff;
        background: linear-gradient(135deg, #64142d, #a83255);
    }

    .tana-modal .modal-body {
        padding: 22px;
    }

    .tana-modal .form-control {
        border: 1px solid #dce2ee;
        border-radius: 9px;
        box-shadow: none;
    }

    .tana-modal textarea.form-control {
        min-height: 150px;
        resize: vertical;
    }

    .tana-modal select option:disabled {
        color: #9aa1b1;
        background: #f0f2f6;
    }

    @media (max-width: 767.98px) {
        .tana-hero {
            align-items: flex-start;
            flex-direction: column;
            padding: 22px;
            border-radius: 14px;
        }

        .tana-hero-actions {
            width: 100%;
        }

        .tana-count,
        .tana-hero-link,
        .tana-add-button {
            justify-content: center;
            flex: 1 1 auto;
        }

        .tana-panel-header {
            align-items: flex-start;
            flex-direction: column;
            padding: 18px;
        }

        .tana-table-wrap {
            padding: 8px 14px 18px;
        }

        .tana-table thead {
            display: none;
        }

        .tana-table,
        .tana-table tbody,
        .tana-table tr,
        .tana-table td {
            display: block;
            width: 100%;
        }

        .tana-table {
            border-spacing: 0;
        }

        .tana-table tbody tr {
            margin-bottom: 13px;
            padding: 8px 14px;
            border: 1px solid var(--tana-border);
            border-radius: 12px;
            background: #fff;
        }

        .tana-table tbody td,
        .tana-table tbody td:first-child,
        .tana-table tbody td:last-child {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            padding: 11px 0;
            border: 0;
            border-bottom: 1px solid #f0f2f7;
            border-radius: 0;
        }

        .tana-table tbody td:last-child {
            border-bottom: 0;
        }

        .tana-table tbody td::before {
            content: attr(data-label);
            flex: 0 0 70px;
            color: var(--tana-muted);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .tana-table tbody td.tana-text-cell {
            align-items: flex-start;
            flex-direction: column;
        }
    }
</style>

<div class="tana-region-page">
    <div class="row">
        <div class="col-12">
            <div class="tana-hero">
                <div>
                    <h2><i class="mdi mdi-earth mr-2"></i>Regional TANA Summary</h2>
                    <p>Review division-submitted concerns and consolidate them into regional thematic analyses for prioritization and planning.</p>
                </div>
                <div class="tana-hero-actions">
                    <span class="tana-count">
                        <i class="mdi mdi-format-list-bulleted"></i>
                        <?= $concern_count; ?> <?= $concern_count === 1 ? 'concern' : 'concerns'; ?>
                    </span>
                    <a href="<?= $dashboard_url; ?>" class="tana-hero-link">
                        <i class="mdi mdi-arrow-left"></i> Back to Dashboard
                    </a>
                    <button type="button" class="tana-add-button" data-toggle="modal" data-target="#tanaRegionModal">
                        <i class="mdi mdi-plus-circle-outline"></i> Add Analysis
                    </button>
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

    <section class="tana-panel">
        <div class="tana-panel-header">
            <div>
                <h4>Division Priority Concerns</h4>
                <p>Division-level concerns, issues, gaps, problems, and bottlenecks consolidated for regional review.</p>
            </div>
            <span class="tana-panel-badge">
                <i class="mdi mdi-file-document-multiple-outline"></i> Regional Inputs
            </span>
        </div>

        <?php if (!empty($priority_rows)) { ?>
            <div class="tana-table-wrap table-responsive">
                <table class="table tana-table">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Concern, Issue, Gap, Problem or Bottleneck</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($priority_rows as $index => $text) : ?>
                            <tr>
                                <td data-label="No."><span class="tana-sequence"><?= $index + 1; ?></span></td>
                                <td class="tana-text-cell" data-label="Concern">
                                    <div class="tana-concern"><?= html_escape($text); ?></div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php } else { ?>
            <div class="tana-empty">
                <i class="mdi mdi-clipboard-alert-outline"></i>
                No division priority concerns are available for the selected fiscal year.
            </div>
        <?php } ?>
    </section>

    <section class="tana-panel">
        <div class="tana-panel-header">
            <div>
                <h4>Regional Thematic Analysis</h4>
                <p>Regional themes arranged according to their priority sequence for regional consolidation.</p>
            </div>
            <span class="tana-panel-badge">
                <i class="mdi mdi-format-list-numbered"></i>
                <?= $analysis_count; ?> <?= $analysis_count === 1 ? 'analysis' : 'analyses'; ?>
            </span>
        </div>

        <?php if (!empty($analysis_rows)) { ?>
            <div class="tana-table-wrap table-responsive">
                <table class="table tana-table">
                    <thead>
                        <tr>
                            <th>Priority</th>
                            <th>Thematic Analysis</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($analysis_rows as $analysis) { ?>
                            <tr>
                                <td data-label="Priority"><span class="tana-sequence"><?= (int) $analysis->sequence; ?></span></td>
                                <td class="tana-text-cell" data-label="Analysis">
                                    <div class="tana-concern"><?= html_escape($analysis->tana); ?></div>
                                </td>
                                <td data-label="Action">
                                    <?= form_open('Pages/tana_region_delete', array('style' => 'display:inline;', 'onsubmit' => "return confirm('Delete this thematic analysis?');")); ?>
                                        <input type="hidden" name="id" value="<?= (int) $analysis->id; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm tana-delete-button"><i class="mdi mdi-trash-can-outline"></i> Delete</button>
                                    <?= form_close(); ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } else { ?>
            <div class="tana-empty">
                <i class="mdi mdi-file-search-outline"></i>
                No regional thematic analysis has been added yet.
            </div>
        <?php } ?>
    </section>
</div>

<div id="tanaRegionModal" class="modal fade tana-modal" tabindex="-1" role="dialog" aria-labelledby="tanaRegionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-white" id="tanaRegionModalLabel">Add Regional Thematic Analysis</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-hidden="true">&times;</button>
            </div>

            <?= form_open('Pages/tana_region', array('class' => 'parsley-examples')); ?>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="tanaRegionText">Thematic Analysis</label>
                        <textarea
                            class="form-control"
                            rows="5"
                            name="tana"
                            id="tanaRegionText"
                            required
                        ></textarea>
                    </div>

                    <div class="form-group mb-0">
                        <label for="tanaRegionSequence">Priority Sequence</label>
                        <select class="form-control" name="sequence" id="tanaRegionSequence" required>
                            <option value="">Select priority sequence</option>
                            <?php for ($sequence = 1; $sequence <= 20; $sequence++) :
                                $is_used = !empty($used_sequences[$sequence]);
                            ?>
                                <option value="<?= $sequence; ?>" <?= $is_used ? 'disabled' : ''; ?>>
                                    <?= $sequence; ?><?= $is_used ? ' - already assigned' : ''; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                        <small class="form-text text-muted">Previously assigned sequence numbers are unavailable.</small>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="mdi mdi-content-save-outline mr-1"></i> Save Analysis
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
