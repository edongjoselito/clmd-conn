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

$field_value = static function ($field, $default = '') {
    $default = (string) $default;

    if (function_exists('set_value')) {
        return (string) set_value($field, $default);
    }

    return isset($_POST[$field]) ? (string) $_POST[$field] : $default;
};

$district_name = isset($district) && !empty($district->description)
    ? $format_title($district->description)
    : $format_title($this->session->user);
$district_name = $district_name !== '' ? $district_name : 'District';
$fiscal_year = (string) $this->session->fy;
$workspace_url = isset($back_url) ? $back_url : base_url() . 'Pages/sbm_district_tech';
$entry_id = isset($entry->id) ? (int) $entry->id : 0;

$ta_rec = $field_value('ta_rec', isset($entry->ta_rec) ? $entry->ta_rec : '');
$sa = $field_value('sa', isset($entry->sa) ? $entry->sa : '');
$cd = $field_value('cd', isset($entry->cd) ? $entry->cd : '');
$mtd = $field_value('mtd', isset($entry->mtd) ? $entry->mtd : '');
$schedule = $field_value('schedule', isset($entry->schedule) ? $entry->schedule : '');
$ct = $field_value('ct', isset($entry->ct) ? $entry->ct : '');
?>

<style>
    .district-tech-form-page {
        --tf-navy: var(--llcm-navy, #3b0718);
        --tf-blue: var(--llcm-blue, #922c43);
        --tf-sky: var(--llcm-sky, #fbf4f5);
        --tf-line: var(--llcm-border, #ead7dc);
        --tf-ink: var(--llcm-ink, #233342);
        --tf-muted: #6b7f92;
        --tf-radius: 10px;
        --tf-shadow: 0 4px 16px rgba(20, 62, 94, .05);
        margin-bottom: 24px;
        color: var(--tf-ink);
    }

    .district-tech-form-page .alert {
        border: 0;
        border-radius: var(--tf-radius);
        box-shadow: var(--tf-shadow);
    }

    /* ---------- Page header ---------- */
    .tf-header {
        display: flex;
        flex-wrap: wrap;
        gap: 14px 18px;
        align-items: center;
        justify-content: space-between;
        margin: 16px 0 18px;
        padding: 18px 22px;
        border-radius: var(--tf-radius);
        color: #fff;
        background: linear-gradient(118deg, var(--tf-navy), var(--tf-blue));
    }

    .tf-eyebrow {
        display: block;
        margin-bottom: 3px;
        color: rgba(255, 255, 255, .75);
        font-size: .72rem;
        font-weight: 600;
        letter-spacing: .09em;
        text-transform: uppercase;
    }

    .tf-header h1 {
        margin: 0;
        color: #fff;
        font-size: 1.45rem;
        font-weight: 600;
        line-height: 1.25;
    }

    .tf-header-meta {
        margin: 6px 0 0;
        color: rgba(255, 255, 255, .85);
        font-size: .85rem;
    }

    .tf-header-meta span + span::before {
        content: "\00b7";
        margin: 0 .5rem;
        color: rgba(255, 255, 255, .5);
    }

    .tf-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 8px 13px;
        border: 1px solid transparent;
        border-radius: 8px;
        font-size: .83rem;
        font-weight: 600;
        text-decoration: none;
        white-space: nowrap;
        cursor: pointer;
        transition: background .15s ease, border-color .15s ease, color .15s ease;
    }

    .tf-btn-ghost {
        color: #fff;
        border-color: rgba(255, 255, 255, .45);
        background: transparent;
    }

    .tf-btn-ghost:hover {
        color: var(--tf-navy);
        background: #fff;
        border-color: #fff;
        text-decoration: none;
    }

    /* ---------- Layout ---------- */
    .tf-shell {
        display: grid;
        grid-template-columns: minmax(0, 1.7fr) minmax(260px, .8fr);
        gap: 16px;
        align-items: start;
    }

    .tf-panel,
    .tf-aside {
        border: 1px solid var(--tf-line);
        border-radius: var(--tf-radius);
        background: #fff;
        box-shadow: var(--tf-shadow);
        overflow: hidden;
    }

    .tf-panel-head,
    .tf-aside-head {
        padding: 15px 20px;
        border-bottom: 1px solid var(--tf-line);
    }

    .tf-panel-head h4,
    .tf-aside-head h4 {
        margin: 0 0 2px;
        color: var(--tf-navy);
        font-size: 1.02rem;
        font-weight: 600;
    }

    .tf-panel-head p,
    .tf-aside-head p {
        margin: 0;
        color: var(--tf-muted);
        font-size: .84rem;
        line-height: 1.5;
    }

    .tf-body { padding: 18px 20px; }

    .tf-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .tf-field {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .tf-field.full-width { grid-column: 1 / -1; }

    .tf-field label {
        margin: 0;
        color: var(--tf-navy);
        font-size: .82rem;
        font-weight: 600;
    }

    .tf-field small {
        color: var(--tf-muted);
        font-size: .76rem;
        line-height: 1.5;
    }

    .tf-field textarea,
    .tf-field input[type="text"] {
        width: 100%;
        border: 1px solid var(--tf-line);
        border-radius: 8px;
        color: var(--tf-ink);
        background: #fbfdff;
        box-shadow: none;
        transition: border-color .15s ease, box-shadow .15s ease, background .15s ease;
    }

    .tf-field textarea {
        min-height: 110px;
        resize: vertical;
    }

    .tf-field textarea.form-control,
    .tf-field input.form-control {
        padding: 9px 12px;
        font-size: .87rem;
    }

    .tf-field textarea:focus,
    .tf-field input[type="text"]:focus {
        border-color: var(--tf-blue);
        background: #fff;
        box-shadow: 0 0 0 .15rem rgba(146, 44, 67, .16);
    }

    .tf-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        margin-top: 18px;
        padding-top: 14px;
        border-top: 1px solid var(--tf-line);
    }

    .tf-actions p {
        margin: 0;
        color: var(--tf-muted);
        font-size: .8rem;
        line-height: 1.5;
    }

    .tf-action-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        justify-content: flex-end;
    }

    .tf-btn-outline {
        color: var(--tf-blue);
        background: #fff;
        border-color: var(--tf-blue);
    }

    .tf-btn-outline:hover {
        color: #fff;
        background: var(--tf-blue);
        text-decoration: none;
    }

    .tf-btn-primary {
        min-width: 140px;
        color: #fff;
        background: var(--tf-blue);
        border-color: var(--tf-blue);
    }

    .tf-btn-primary:hover {
        color: #fff;
        background: var(--tf-navy);
        border-color: var(--tf-navy);
        text-decoration: none;
    }

    /* ---------- Aside guide ---------- */
    .tf-note-list {
        display: grid;
        padding: 6px 16px 12px;
    }

    .tf-note {
        display: flex;
        gap: 10px;
        align-items: flex-start;
        padding: 11px 0;
        border-top: 1px solid #edf3f8;
    }

    .tf-note:first-child { border-top: 0; }

    .tf-note i {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 28px;
        width: 28px;
        height: 28px;
        margin-top: 1px;
        border-radius: 8px;
        background: var(--tf-sky);
        color: var(--tf-blue);
        font-size: .95rem;
    }

    .tf-note strong {
        display: block;
        margin-bottom: 2px;
        color: var(--tf-navy);
        font-size: .82rem;
        font-weight: 600;
    }

    .tf-note p {
        margin: 0;
        color: var(--tf-muted);
        font-size: .77rem;
        line-height: 1.55;
    }

    @media (max-width: 991.98px) {
        .tf-shell { grid-template-columns: 1fr; }
    }

    @media (max-width: 767.98px) {
        .tf-header { padding: 16px 18px; }
        .tf-header h1 { font-size: 1.25rem; }
        .tf-header .tf-btn { flex: 1 1 auto; }
        .tf-grid { grid-template-columns: 1fr; }
        .tf-actions {
            align-items: flex-start;
            flex-direction: column;
        }
        .tf-action-buttons,
        .tf-action-buttons .tf-btn { width: 100%; }
        .tf-btn-primary { min-width: 0; }
        .tf-body { padding: 16px; }
        .tf-panel-head, .tf-aside-head { padding: 14px 16px; }
    }
</style>

<div class="district-tech-form-page">
    <header class="tf-header">
        <div>
            <span class="tf-eyebrow"><i class="mdi mdi-file-document-edit-outline"></i> Technical Assistance</span>
            <h1><?= $escape(isset($hero_title) ? $hero_title : $title); ?></h1>
            <p class="tf-header-meta">
                <span><?= $escape($district_name); ?></span>
                <span>Fiscal Year <?= $escape($fiscal_year); ?></span>
                <span>TA Recommendation is required</span>
            </p>
        </div>
        <a href="<?= $workspace_url; ?>" class="tf-btn tf-btn-ghost">
            <i class="mdi mdi-arrow-left"></i> Back to Workspace
        </a>
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

    <?= validation_errors(); ?>

    <div class="tf-shell">
        <section class="tf-panel">
            <div class="tf-panel-head">
                <h4><?= $escape($title); ?></h4>
                <p><?= $escape(isset($hero_description) ? $hero_description : 'Use concise, action-oriented details so the district can review, update, and monitor the support plan more easily.'); ?></p>
            </div>

            <div class="tf-body">
                <?= form_open($form_action); ?>
                    <div class="tf-grid">
                        <div class="tf-field full-width">
                            <label for="district-tech-ta-rec">TA Recommendation</label>
                            <textarea class="form-control" rows="4" name="ta_rec" id="district-tech-ta-rec"><?= $escape($ta_rec); ?></textarea>
                            <small>Describe the main technical assistance need or recommendation for this district entry.</small>
                        </div>

                        <div class="tf-field full-width">
                            <label for="district-tech-sa">Strategies / Activities</label>
                            <textarea class="form-control" rows="5" name="sa" id="district-tech-sa"><?= $escape($sa); ?></textarea>
                            <small>List the interventions, follow-up activities, or support actions that will address the recommendation.</small>
                        </div>

                        <div class="tf-field">
                            <label for="district-tech-cd">Concerned Districts / SDO</label>
                            <textarea class="form-control" rows="4" name="cd" id="district-tech-cd"><?= $escape($cd); ?></textarea>
                            <small>Specify which district offices or SDO units should be involved or informed.</small>
                        </div>

                        <div class="tf-field">
                            <label for="district-tech-mtd">Management Team District / SDO</label>
                            <textarea class="form-control" rows="4" name="mtd" id="district-tech-mtd"><?= $escape($mtd); ?></textarea>
                            <small>Identify the management team or focal persons responsible for the district-side coordination.</small>
                        </div>

                        <div class="tf-field">
                            <label for="district-tech-schedule">Schedule</label>
                            <input type="text" name="schedule" id="district-tech-schedule" class="form-control" value="<?= $escape($schedule); ?>">
                            <small>Enter a target date, month, quarter, or timeline window for implementation.</small>
                        </div>

                        <div class="tf-field">
                            <label for="district-tech-ct">Composite Team</label>
                            <input type="text" name="ct" id="district-tech-ct" class="form-control" value="<?= $escape($ct); ?>">
                            <small>Name the supporting composite team, cluster, or assigned technical group when applicable.</small>
                        </div>
                    </div>

                    <?php if ($entry_id > 0) : ?>
                        <input type="hidden" name="id" value="<?= $entry_id; ?>">
                    <?php endif; ?>

                    <div class="tf-actions">
                        <p>Keep entries specific enough for district-level monitoring, but readable enough for quick review during validation.</p>
                        <div class="tf-action-buttons">
                            <a href="<?= $workspace_url; ?>" class="tf-btn tf-btn-outline">
                                <i class="mdi mdi-arrow-left"></i> Cancel
                            </a>
                            <button type="submit" name="submit" class="tf-btn tf-btn-primary">
                                <i class="mdi <?= $escape(isset($submit_icon) ? $submit_icon : 'mdi-content-save-outline'); ?>"></i>
                                <?= $escape(isset($submit_label) ? $submit_label : 'Save Entry'); ?>
                            </button>
                        </div>
                    </div>
                <?= form_close(); ?>
            </div>
        </section>

        <aside class="tf-aside">
            <div class="tf-aside-head">
                <h4>Writing Guide</h4>
                <p>Prompts to keep each TA record actionable and easy to scan.</p>
            </div>

            <div class="tf-note-list">
                <div class="tf-note">
                    <i class="mdi mdi-bullseye-arrow"></i>
                    <div>
                        <strong>Lead with the need</strong>
                        <p>Start the recommendation with the actual issue, gap, or support area that requires district action.</p>
                    </div>
                </div>

                <div class="tf-note">
                    <i class="mdi mdi-format-list-checks"></i>
                    <div>
                        <strong>Be concrete with activities</strong>
                        <p>Use short, direct activity descriptions so the implementation steps are easier to monitor later.</p>
                    </div>
                </div>

                <div class="tf-note">
                    <i class="mdi mdi-account-group-outline"></i>
                    <div>
                        <strong>Name the responsible teams</strong>
                        <p>Clarify which management or composite teams will lead, support, or coordinate the work.</p>
                    </div>
                </div>

                <div class="tf-note">
                    <i class="mdi mdi-calendar-check-outline"></i>
                    <div>
                        <strong>Use a usable schedule</strong>
                        <p>A month, quarter, or date range is enough as long as the timing can be understood at a glance.</p>
                    </div>
                </div>
            </div>
        </aside>
    </div>
</div>
