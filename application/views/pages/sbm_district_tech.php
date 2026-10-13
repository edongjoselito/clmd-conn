<?php
$entries = isset($data) && is_array($data) ? $data : array();
$entry_count = count($entries);

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

$render_text_block = static function ($value, $placeholder = 'Not provided yet.') use ($escape) {
    $value = trim((string) $value);
    $empty_class = $value === '' ? ' is-empty' : '';
    $display_value = $value === '' ? $placeholder : $value;

    return '<div class="ta-text' . $empty_class . '">' . nl2br($escape($display_value)) . '</div>';
};

$district_name = isset($district) && !empty($district->description)
    ? $format_title($district->description)
    : $format_title($this->session->user);
$district_name = $district_name !== '' ? $district_name : 'District';
$fiscal_year = (string) $this->session->fy;
$dashboard_url = base_url();
$new_entry_url = base_url() . 'Pages/sbm_district_tech_new';

$scheduled_count = 0;
$scope_count = 0;
$team_count = 0;

foreach ($entries as $entry) {
    if (trim((string) $entry->schedule) !== '') {
        $scheduled_count++;
    }

    if (trim((string) $entry->cd) !== '') {
        $scope_count++;
    }

    if (trim((string) $entry->ct) !== '' || trim((string) $entry->mtd) !== '') {
        $team_count++;
    }
}

$ta_metrics = array(
    array('count' => $entry_count, 'label' => 'TA Entries', 'note' => 'Saved for the active fiscal year.', 'icon' => 'mdi-clipboard-list-outline'),
    array('count' => $scheduled_count, 'label' => 'With Schedule', 'note' => 'Entries with a set schedule or timeline.', 'icon' => 'mdi-calendar-clock-outline'),
    array('count' => $scope_count, 'label' => 'With Scope', 'note' => 'Entries with concerned districts / SDO coordination.', 'icon' => 'mdi-map-marker-check-outline'),
    array('count' => $team_count, 'label' => 'With Team', 'note' => 'Entries with management or composite teams.', 'icon' => 'mdi-account-group-outline'),
);
?>

<style>
    .district-tech-page {
        --ta-navy: var(--llcm-navy, #3b0718);
        --ta-blue: var(--llcm-blue, #922c43);
        --ta-sky: var(--llcm-sky, #fbf4f5);
        --ta-line: var(--llcm-border, #ead7dc);
        --ta-ink: var(--llcm-ink, #233342);
        --ta-muted: #6b7f92;
        --ta-radius: 10px;
        --ta-shadow: 0 4px 16px rgba(20, 62, 94, .05);
        margin-bottom: 24px;
        color: var(--ta-ink);
    }

    .district-tech-page .alert {
        border: 0;
        border-radius: var(--ta-radius);
        box-shadow: var(--ta-shadow);
    }

    /* ---------- Page header ---------- */
    .ta-header {
        display: flex;
        flex-wrap: wrap;
        gap: 14px 18px;
        align-items: center;
        justify-content: space-between;
        margin: 16px 0 18px;
        padding: 18px 22px;
        border-radius: var(--ta-radius);
        color: #fff;
        background: linear-gradient(118deg, var(--ta-navy), var(--ta-blue));
    }

    .ta-eyebrow {
        display: block;
        margin-bottom: 3px;
        color: rgba(255, 255, 255, .75);
        font-size: .72rem;
        font-weight: 600;
        letter-spacing: .09em;
        text-transform: uppercase;
    }

    .ta-header h1 {
        margin: 0;
        color: #fff;
        font-size: 1.45rem;
        font-weight: 600;
        line-height: 1.25;
    }

    .ta-header-meta {
        margin: 6px 0 0;
        color: rgba(255, 255, 255, .85);
        font-size: .85rem;
    }

    .ta-header-meta span + span::before {
        content: "\00b7";
        margin: 0 .5rem;
        color: rgba(255, 255, 255, .5);
    }

    .ta-header-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .ta-btn {
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

    .ta-btn-ghost {
        color: #fff;
        border-color: rgba(255, 255, 255, .45);
        background: transparent;
    }

    .ta-btn-ghost:hover {
        color: var(--ta-navy);
        background: #fff;
        border-color: #fff;
        text-decoration: none;
    }

    .ta-btn-solid {
        color: var(--ta-navy);
        background: #fff;
        border-color: #fff;
    }

    .ta-btn-solid:hover {
        color: var(--ta-navy);
        background: var(--ta-sky);
        border-color: var(--ta-sky);
        text-decoration: none;
    }

    /* ---------- Metric tiles ---------- */
    .ta-metrics {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 18px;
    }

    .ta-metric {
        display: flex;
        gap: 12px;
        align-items: center;
        padding: 14px 16px;
        border: 1px solid var(--ta-line);
        border-radius: var(--ta-radius);
        background: #fff;
        box-shadow: var(--ta-shadow);
    }

    .ta-metric-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 32px;
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: var(--ta-sky);
        color: var(--ta-blue);
        font-size: 1rem;
    }

    .ta-metric-value {
        display: block;
        color: var(--ta-navy);
        font-size: 1.35rem;
        font-weight: 600;
        line-height: 1.1;
        font-variant-numeric: tabular-nums;
    }

    .ta-metric small {
        display: block;
        color: var(--ta-muted);
        font-size: .72rem;
        font-weight: 600;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    /* ---------- Entries panel ---------- */
    .ta-panel {
        border: 1px solid var(--ta-line);
        border-radius: var(--ta-radius);
        background: #fff;
        box-shadow: var(--ta-shadow);
        overflow: hidden;
    }

    .ta-panel-head {
        display: flex;
        flex-wrap: wrap;
        gap: 6px 14px;
        align-items: center;
        justify-content: space-between;
        padding: 15px 20px;
        border-bottom: 1px solid var(--ta-line);
    }

    .ta-panel-head .ta-head-text { flex: 1 1 auto; min-width: 200px; }

    .ta-panel-head h4 {
        margin: 0;
        color: var(--ta-navy);
        font-size: 1.02rem;
        font-weight: 600;
    }

    .ta-panel-head p {
        margin: 2px 0 0;
        color: var(--ta-muted);
        font-size: .84rem;
    }

    .ta-tag {
        display: inline-flex;
        gap: 5px;
        align-items: center;
        padding: 3px 11px;
        border-radius: 999px;
        background: var(--ta-sky);
        color: var(--ta-navy);
        font-size: .74rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .ta-list { padding: 14px 20px 18px; }

    .ta-card {
        margin-bottom: 14px;
        border: 1px solid var(--ta-line);
        border-radius: var(--ta-radius);
        background: #fff;
        overflow: hidden;
    }

    .ta-card:last-child { margin-bottom: 0; }

    .ta-card-head {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px 16px;
        padding: 13px 16px;
        border-bottom: 1px solid var(--ta-line);
        background: #fbfdff;
    }

    .ta-card-head h5 {
        margin: 0 0 5px;
        color: var(--ta-navy);
        font-size: .95rem;
        font-weight: 600;
        line-height: 1.45;
    }

    .ta-entry-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    .ta-entry-meta span {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 2px 9px;
        border-radius: 999px;
        background: #f1f5f8;
        color: var(--ta-muted);
        font-size: .72rem;
        font-weight: 600;
    }

    .ta-manage {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 6px;
    }

    .ta-action {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 10px;
        border: 1px solid transparent;
        border-radius: 7px;
        font-size: .76rem;
        font-weight: 600;
        text-decoration: none;
        transition: background .15s ease, color .15s ease;
    }

    .ta-action-update {
        color: var(--ta-blue);
        background: var(--ta-sky);
    }

    .ta-action-update:hover {
        color: #fff;
        background: var(--ta-blue);
        text-decoration: none;
    }

    .ta-action-delete {
        color: #a33b32;
        background: #fbeeec;
        cursor: pointer;
    }

    .ta-action-delete:hover {
        color: #fff;
        background: #b04a41;
    }

    .ta-card-body { padding: 4px 16px 14px; }

    .ta-field {
        display: grid;
        grid-template-columns: 190px minmax(0, 1fr);
        gap: 8px 16px;
        padding: 11px 0;
        border-top: 1px solid #edf3f8;
    }

    .ta-field:first-child { border-top: 0; }

    .ta-field-label small {
        display: block;
        color: var(--ta-muted);
        font-size: .68rem;
        font-weight: 700;
        letter-spacing: .05em;
        text-transform: uppercase;
    }

    .ta-field-label strong {
        display: block;
        margin-top: 2px;
        color: var(--ta-navy);
        font-size: .82rem;
        font-weight: 600;
        line-height: 1.4;
    }

    .ta-text {
        color: #445065;
        font-size: .85rem;
        line-height: 1.6;
        word-break: break-word;
    }

    .ta-text.is-empty {
        color: var(--ta-muted);
        font-style: italic;
    }

    .ta-empty {
        padding: 44px 24px;
        color: var(--ta-muted);
        text-align: center;
    }

    .ta-empty > i {
        display: block;
        margin-bottom: 10px;
        color: #b9cdde;
        font-size: 2.2rem;
    }

    .ta-empty h5 {
        margin-bottom: 6px;
        color: var(--ta-navy);
        font-size: 1rem;
        font-weight: 600;
    }

    .ta-empty p {
        max-width: 46ch;
        margin: 0 auto 16px;
        font-size: .87rem;
        line-height: 1.6;
    }

    .ta-empty .ta-btn-primary {
        gap: 5px;
        padding: 4px 10px;
        border-radius: 7px;
        font-size: .74rem;
        font-weight: 600;
        color: #fff;
        background: var(--ta-blue);
        border-color: var(--ta-blue);
    }

    .ta-empty .ta-btn-primary:hover {
        color: #fff;
        background: var(--ta-navy);
        border-color: var(--ta-navy);
        text-decoration: none;
    }

    @media (max-width: 1199.98px) {
        .ta-metrics { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }

    @media (max-width: 767.98px) {
        .ta-header { padding: 16px 18px; }
        .ta-header h1 { font-size: 1.25rem; }
        .ta-header-actions { width: 100%; }
        .ta-header-actions .ta-btn { flex: 1 1 auto; justify-content: center; }
        .ta-metrics { grid-template-columns: 1fr; gap: 10px; }
        .ta-panel-head { padding: 14px 16px; }
        .ta-list { padding: 12px 14px 14px; }
        .ta-field { grid-template-columns: 1fr; gap: 4px; }
        .ta-manage { width: 100%; }
    }
</style>

<div class="district-tech-page">
    <header class="ta-header">
        <div>
            <span class="ta-eyebrow"><i class="mdi mdi-wrench-outline"></i> Technical Assistance</span>
            <h1>District TA Workspace</h1>
            <p class="ta-header-meta">
                <span><?= $escape($district_name); ?></span>
                <span>Fiscal Year <?= $escape($fiscal_year); ?></span>
                <span><?= $entry_count; ?> <?= $entry_count === 1 ? 'entry' : 'entries'; ?></span>
            </p>
        </div>
        <div class="ta-header-actions">
            <a href="<?= $dashboard_url; ?>" class="ta-btn ta-btn-ghost">
                <i class="mdi mdi-view-dashboard-outline"></i> Dashboard
            </a>
            <a href="<?= $new_entry_url; ?>" class="ta-btn ta-btn-solid">
                <i class="mdi mdi-plus-circle-outline"></i> Add TA Entry
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

    <div class="ta-metrics">
        <?php foreach ($ta_metrics as $metric) : ?>
            <div class="ta-metric">
                <span class="ta-metric-icon"><i class="mdi <?= $escape($metric['icon']); ?>"></i></span>
                <div>
                    <span class="ta-metric-value"><?= (int) $metric['count']; ?></span>
                    <small><?= $escape($metric['label']); ?></small>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <section class="ta-panel">
        <div class="ta-panel-head">
            <div class="ta-head-text">
                <h4><?= $escape($title); ?></h4>
                <p>District-level recommendations, activity plans, coordination scope, and team assignments.</p>
            </div>
            <span class="ta-tag"><i class="mdi mdi-filter-outline"></i> Fiscal Year <?= $escape($fiscal_year); ?></span>
        </div>

        <?php if (!empty($entries)) { ?>
            <div class="ta-list">
                <?php foreach ($entries as $index => $row) :
                    $entry_number = $index + 1;
                    $schedule_text = trim((string) $row->schedule) !== '' ? (string) $row->schedule : 'Schedule not set';
                ?>
                    <article class="ta-card">
                        <div class="ta-card-head">
                            <div>
                                <h5><?= $escape(trim((string) $row->ta_rec) !== '' ? (string) $row->ta_rec : 'Technical Assistance Entry ' . $entry_number); ?></h5>
                                <div class="ta-entry-meta">
                                    <span><i class="mdi mdi-pound"></i>Entry <?= $entry_number; ?></span>
                                    <span><i class="mdi mdi-calendar-range"></i><?= $escape($schedule_text); ?></span>
                                    <span><i class="mdi mdi-map-marker-outline"></i><?= trim((string) $row->cd) !== '' ? 'Scope set' : 'Scope pending'; ?></span>
                                </div>
                            </div>
                            <div class="ta-manage">
                                <a href="<?= base_url(); ?>Pages/sbm_district_tech_edit/<?= (int) $row->id; ?>" class="ta-action ta-action-update">
                                    <i class="mdi mdi-pencil-outline"></i> Update
                                </a>
                                <?= form_open('Pages/sbm_district_tech_del', array('style' => 'display:inline;', 'onsubmit' => "return confirm('Are you sure you want to delete this technical assistance entry?');")); ?>
                                    <input type="hidden" name="id" value="<?= (int) $row->id; ?>">
                                    <button type="submit" class="ta-action ta-action-delete"><i class="mdi mdi-trash-can-outline"></i> Delete</button>
                                <?= form_close(); ?>
                            </div>
                        </div>

                        <div class="ta-card-body">
                            <div class="ta-field">
                                <div class="ta-field-label">
                                    <small>Recommendation</small>
                                    <strong>Technical assistance recommendation</strong>
                                </div>
                                <?= $render_text_block($row->ta_rec, 'No recommendation recorded yet.'); ?>
                            </div>

                            <div class="ta-field">
                                <div class="ta-field-label">
                                    <small>Strategies / Activities</small>
                                    <strong>Planned interventions and activities</strong>
                                </div>
                                <?= $render_text_block($row->sa, 'No strategies or activities recorded yet.'); ?>
                            </div>

                            <div class="ta-field">
                                <div class="ta-field-label">
                                    <small>Concerned Districts / SDO</small>
                                    <strong>Coordination scope</strong>
                                </div>
                                <?= $render_text_block($row->cd, 'No concerned districts or SDO scope recorded yet.'); ?>
                            </div>

                            <div class="ta-field">
                                <div class="ta-field-label">
                                    <small>Management Team</small>
                                    <strong>Management team district / SDO</strong>
                                </div>
                                <?= $render_text_block($row->mtd, 'No management team recorded yet.'); ?>
                            </div>

                            <div class="ta-field">
                                <div class="ta-field-label">
                                    <small>Schedule</small>
                                    <strong>Planned implementation schedule</strong>
                                </div>
                                <?= $render_text_block($row->schedule, 'No schedule recorded yet.'); ?>
                            </div>

                            <div class="ta-field">
                                <div class="ta-field-label">
                                    <small>Composite Team</small>
                                    <strong>Assigned composite team</strong>
                                </div>
                                <?= $render_text_block($row->ct, 'No composite team recorded yet.'); ?>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php } else { ?>
            <div class="ta-empty">
                <i class="mdi mdi-wrench-clock"></i>
                <h5>No district technical assistance entries yet</h5>
                <p>Start building your district technical assistance plan by adding recommendations, strategies, schedules, and team assignments for the active fiscal year.</p>
                <a href="<?= $new_entry_url; ?>" class="ta-btn ta-btn-primary">
                    <i class="mdi mdi-plus-circle-outline"></i> Add First TA Entry
                </a>
            </div>
        <?php } ?>
    </section>
</div>
