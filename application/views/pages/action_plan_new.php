<?php
$escape = static function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};

$title_case = static function ($value) {
    $value = trim((string) $value);
    if ($value === '') {
        return '';
    }

    return function_exists('mb_convert_case')
        ? mb_convert_case($value, MB_CASE_TITLE, 'UTF-8')
        : ucwords(strtolower($value));
};

$school = $this->Common->one_cond_row('schools', 'schoolID', $this->session->username);
$school_name = $school && trim((string) $school->schoolName) !== ''
    ? $title_case($school->schoolName)
    : $title_case((string) $this->session->user);
$fiscal_year_label = 'Fiscal Year ' . $this->session->fy;
$action_plan_url = base_url() . 'Pages/sbm_action_plan';
$print_view_url = base_url() . 'Pages/sbm_action_plan_pview';
$checklist_url = base_url() . 'Pages/sbm_checklist';
$ta_url = base_url() . 'Pages/tapr_form';

$form_values = array(
    'activity' => set_value('activity'),
    'objective' => set_value('objective'),
    'ex_output' => set_value('ex_output'),
    'metho_strategy' => set_value('metho_strategy'),
    'time_frame' => set_value('time_frame'),
    'person_involved' => set_value('person_involved'),
    'bud_req' => set_value('bud_req'),
    'remarks' => set_value('remarks'),
);

$filled_fields = 0;
foreach ($form_values as $value) {
    if (trim((string) $value) !== '') {
        $filled_fields++;
    }
}

$budget_numeric = preg_replace('/[^0-9.]/', '', str_replace(',', '', (string) $form_values['bud_req']));
$budget_preview = ($budget_numeric !== '' && is_numeric($budget_numeric))
    ? 'PHP ' . number_format((float) $budget_numeric, 2)
    : 'PHP 0.00';

$validation_markup = validation_errors();
?>

<style>
    .action-plan-entry-page {
        --entry-primary: #7f1d1d;
        --entry-primary-light: #b83a4b;
        --entry-accent: #d6a84b;
        --entry-ink: #172033;
        --entry-muted: #687386;
        --entry-border: #e4e9f0;
        --entry-surface: #f6f8fb;
        --entry-success: #15803d;
        --entry-info: #1d4ed8;
        --entry-shadow: 0 18px 44px rgba(15, 23, 42, 0.08);
        color: var(--entry-ink);
    }

    .action-plan-entry-page .alert {
        border-radius: 16px;
    }

    .action-plan-entry-page .entry-hero {
        position: relative;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 24px;
        margin: 18px 0 22px;
        padding: 32px;
        border-radius: 26px;
        color: #fff;
        background:
            radial-gradient(circle at 100% 0%, rgba(255, 255, 255, .15), transparent 30%),
            radial-gradient(circle at 0% 100%, rgba(214, 168, 75, .18), transparent 24%),
            linear-gradient(135deg, #541117 0%, #7f1d1d 46%, #b83a4b 100%);
        box-shadow: 0 22px 44px rgba(84, 17, 23, .20);
        overflow: hidden;
    }

    .action-plan-entry-page .entry-hero::after {
        content: '';
        position: absolute;
        right: -42px;
        bottom: -56px;
        width: 210px;
        height: 210px;
        border-radius: 50%;
        background: rgba(255, 255, 255, .08);
    }

    .action-plan-entry-page .entry-hero-copy,
    .action-plan-entry-page .entry-hero-actions {
        position: relative;
        z-index: 1;
    }

    .action-plan-entry-page .entry-hero-copy {
        max-width: 760px;
    }

    .action-plan-entry-page .entry-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 14px;
        padding: 8px 12px;
        border-radius: 999px;
        background: rgba(255, 255, 255, .12);
        color: rgba(255, 255, 255, .92);
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .action-plan-entry-page .entry-hero h1 {
        margin: 0;
        color: #fff;
        font-size: 30px;
        font-weight: 800;
        line-height: 1.1;
        letter-spacing: -.03em;
    }

    .action-plan-entry-page .entry-hero p {
        max-width: 700px;
        margin: 14px 0 18px;
        color: rgba(255, 255, 255, .86);
        font-size: 14px;
        line-height: 1.75;
    }

    .action-plan-entry-page .entry-hero-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .action-plan-entry-page .entry-hero-meta span {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 8px 12px;
        border-radius: 999px;
        background: rgba(255, 255, 255, .10);
        color: rgba(255, 255, 255, .92);
        font-size: 12px;
        font-weight: 600;
    }

    .action-plan-entry-page .entry-hero-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        justify-content: flex-end;
        min-width: 240px;
    }

    .action-plan-entry-page .entry-button,
    .action-plan-entry-page .entry-link,
    .action-plan-entry-page .submit-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        border: 0;
        border-radius: 12px;
        text-decoration: none !important;
        transition: transform .18s ease, box-shadow .18s ease, background .18s ease, color .18s ease;
    }

    .action-plan-entry-page .entry-button:hover,
    .action-plan-entry-page .entry-link:hover,
    .action-plan-entry-page .submit-button:hover {
        transform: translateY(-2px);
    }

    .action-plan-entry-page .entry-button {
        padding: 11px 16px;
        font-size: 12px;
        font-weight: 700;
    }

    .action-plan-entry-page .entry-button-primary {
        color: var(--entry-primary);
        background: #fff;
        box-shadow: 0 10px 20px rgba(61, 12, 29, .18);
    }

    .action-plan-entry-page .entry-button-secondary {
        color: #fff;
        background: rgba(255, 255, 255, .12);
        border: 1px solid rgba(255, 255, 255, .18);
    }

    .action-plan-entry-page .entry-panel,
    .action-plan-entry-page .entry-sidebar-card {
        margin-bottom: 22px;
        border: 1px solid var(--entry-border);
        border-radius: 20px;
        background: #fff;
        box-shadow: var(--entry-shadow);
        overflow: hidden;
    }

    .action-plan-entry-page .entry-panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 22px 24px;
        border-bottom: 1px solid var(--entry-border);
    }

    .action-plan-entry-page .entry-panel-header h4,
    .action-plan-entry-page .entry-sidebar-card h5 {
        margin: 0;
        color: var(--entry-ink);
        font-weight: 800;
    }

    .action-plan-entry-page .entry-panel-header h4 {
        font-size: 20px;
    }

    .action-plan-entry-page .entry-panel-header p,
    .action-plan-entry-page .entry-sidebar-card p {
        margin: 6px 0 0;
        color: var(--entry-muted);
        font-size: 13px;
        line-height: 1.7;
    }

    .action-plan-entry-page .entry-panel-header small {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 9px 12px;
        border-radius: 999px;
        background: #f4f7fb;
        color: var(--entry-ink);
        font-size: 12px;
        font-weight: 700;
    }

    .action-plan-entry-page .entry-panel-body,
    .action-plan-entry-page .entry-sidebar-card-body {
        padding: 24px;
    }

    .action-plan-entry-page .entry-form {
        display: flex;
        flex-direction: column;
        gap: 22px;
    }

    .action-plan-entry-page .entry-section {
        padding: 20px;
        border: 1px solid var(--entry-border);
        border-radius: 18px;
        background: linear-gradient(180deg, #fff 0%, #fbfcff 100%);
    }

    .action-plan-entry-page .entry-section-head {
        margin-bottom: 18px;
    }

    .action-plan-entry-page .entry-section-head span {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        margin-bottom: 8px;
        padding: 7px 11px;
        border-radius: 999px;
        background: #f4f7fb;
        color: var(--entry-primary);
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .06em;
        text-transform: uppercase;
    }

    .action-plan-entry-page .entry-section-head h5 {
        margin: 0 0 6px;
        color: var(--entry-ink);
        font-size: 18px;
        font-weight: 800;
    }

    .action-plan-entry-page .entry-section-head p {
        margin: 0;
        color: var(--entry-muted);
        font-size: 13px;
        line-height: 1.7;
    }

    .action-plan-entry-page .entry-field + .entry-field {
        margin-top: 16px;
    }

    .action-plan-entry-page .entry-field label {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 8px;
        color: var(--entry-ink);
        font-size: 13px;
        font-weight: 800;
    }

    .action-plan-entry-page .entry-field label span {
        color: var(--entry-muted);
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .05em;
    }

    .action-plan-entry-page .entry-field small {
        display: block;
        margin-top: 8px;
        color: var(--entry-muted);
        font-size: 12px;
        line-height: 1.6;
    }

    .action-plan-entry-page .entry-control {
        width: 100%;
        padding: 13px 15px;
        border: 1px solid #d9e0ea;
        border-radius: 14px;
        color: var(--entry-ink);
        background: #fff;
        font-size: 14px;
        line-height: 1.6;
        transition: border-color .16s ease, box-shadow .16s ease, background .16s ease;
    }

    .action-plan-entry-page textarea.entry-control {
        min-height: 116px;
        resize: vertical;
    }

    .action-plan-entry-page .entry-control:focus {
        border-color: rgba(127, 29, 29, .34);
        box-shadow: 0 0 0 4px rgba(127, 29, 29, .08);
        outline: 0;
    }

    .action-plan-entry-page .entry-control::placeholder {
        color: #98a3b5;
    }

    .action-plan-entry-page .entry-grid {
        display: grid;
        grid-template-columns: repeat(12, minmax(0, 1fr));
        gap: 16px;
    }

    .action-plan-entry-page .span-12 {
        grid-column: span 12;
    }

    .action-plan-entry-page .span-8 {
        grid-column: span 8;
    }

    .action-plan-entry-page .span-6 {
        grid-column: span 6;
    }

    .action-plan-entry-page .span-4 {
        grid-column: span 4;
    }

    .action-plan-entry-page .budget-input {
        display: flex;
        align-items: stretch;
        border: 1px solid #d9e0ea;
        border-radius: 14px;
        background: #fff;
        overflow: hidden;
    }

    .action-plan-entry-page .budget-input span {
        display: inline-flex;
        align-items: center;
        padding: 0 14px;
        color: var(--entry-primary);
        background: #f8f1f2;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: .05em;
        text-transform: uppercase;
        border-right: 1px solid #ead6da;
    }

    .action-plan-entry-page .budget-input input {
        border: 0;
        border-radius: 0;
        box-shadow: none !important;
    }

    .action-plan-entry-page .entry-actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        margin-top: 6px;
        padding-top: 20px;
        border-top: 1px solid var(--entry-border);
    }

    .action-plan-entry-page .entry-actions-copy {
        color: var(--entry-muted);
        font-size: 13px;
        line-height: 1.7;
    }

    .action-plan-entry-page .entry-actions-group {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .action-plan-entry-page .submit-button {
        padding: 12px 18px;
        color: #fff;
        background: linear-gradient(135deg, var(--entry-primary), var(--entry-primary-light));
        box-shadow: 0 14px 26px rgba(127, 29, 29, .16);
        font-size: 13px;
        font-weight: 800;
    }

    .action-plan-entry-page .entry-link {
        padding: 12px 16px;
        border: 1px solid var(--entry-border);
        color: var(--entry-ink);
        background: #fff;
        font-size: 13px;
        font-weight: 700;
    }

    .action-plan-entry-page .entry-sidebar-card {
        position: sticky;
        top: 18px;
    }

    .action-plan-entry-page .entry-metric-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 18px;
    }

    .action-plan-entry-page .entry-metric {
        padding: 16px;
        border: 1px solid var(--entry-border);
        border-radius: 16px;
        background: #fbfcff;
    }

    .action-plan-entry-page .entry-metric small {
        display: block;
        margin-bottom: 6px;
        color: var(--entry-muted);
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .06em;
    }

    .action-plan-entry-page .entry-metric strong {
        display: block;
        color: var(--entry-ink);
        font-size: 22px;
        font-weight: 800;
        line-height: 1.1;
    }

    .action-plan-entry-page .entry-progress {
        height: 8px;
        margin-top: 10px;
        border-radius: 999px;
        background: #edf1f6;
        overflow: hidden;
    }

    .action-plan-entry-page .entry-progress span {
        display: block;
        height: 100%;
        width: <?= (int) round((count($form_values) > 0 ? ($filled_fields / count($form_values)) * 100 : 0)); ?>%;
        border-radius: inherit;
        background: linear-gradient(90deg, var(--entry-primary), var(--entry-accent));
    }

    .action-plan-entry-page .entry-tip-list,
    .action-plan-entry-page .entry-link-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .action-plan-entry-page .entry-tip {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 12px 14px;
        border: 1px solid var(--entry-border);
        border-radius: 14px;
        background: #fbfcff;
        color: var(--entry-muted);
        font-size: 13px;
        line-height: 1.7;
    }

    .action-plan-entry-page .entry-tip i {
        margin-top: 2px;
        color: var(--entry-primary);
        font-size: 16px;
    }

    .action-plan-entry-page .entry-link-list a {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 13px 14px;
        border: 1px solid var(--entry-border);
        border-radius: 14px;
        color: var(--entry-ink);
        background: #fff;
        font-size: 13px;
        font-weight: 700;
        text-decoration: none !important;
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
    }

    .action-plan-entry-page .entry-link-list a:hover {
        transform: translateY(-2px);
        border-color: #d5dce7;
        box-shadow: 0 12px 24px rgba(31, 45, 75, .06);
    }

    .action-plan-entry-page .entry-link-list span {
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    @media (max-width: 1199.98px) {
        .action-plan-entry-page .entry-sidebar-card {
            position: static;
        }
    }

    @media (max-width: 991.98px) {
        .action-plan-entry-page .entry-hero {
            flex-direction: column;
        }

        .action-plan-entry-page .entry-hero-actions {
            justify-content: flex-start;
            min-width: 0;
        }

        .action-plan-entry-page .span-8,
        .action-plan-entry-page .span-6,
        .action-plan-entry-page .span-4 {
            grid-column: span 12;
        }
    }

    @media (max-width: 767.98px) {
        .action-plan-entry-page .entry-hero,
        .action-plan-entry-page .entry-panel-body,
        .action-plan-entry-page .entry-panel-header,
        .action-plan-entry-page .entry-sidebar-card-body {
            padding-left: 18px;
            padding-right: 18px;
        }

        .action-plan-entry-page .entry-hero h1 {
            font-size: 24px;
        }

        .action-plan-entry-page .entry-hero-actions,
        .action-plan-entry-page .entry-actions-group {
            width: 100%;
        }

        .action-plan-entry-page .entry-button,
        .action-plan-entry-page .submit-button,
        .action-plan-entry-page .entry-link {
            width: 100%;
        }

        .action-plan-entry-page .entry-actions {
            flex-direction: column;
            align-items: stretch;
        }

        .action-plan-entry-page .entry-metric-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="action-plan-entry-page">
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

    <?php if ($validation_markup) : ?>
        <?= $validation_markup; ?>
    <?php endif; ?>

    <section class="entry-hero">
        <div class="entry-hero-copy">
            <span class="entry-eyebrow">
                <i class="mdi mdi-clipboard-text-outline"></i>
                School Action Plan Builder
            </span>
            <h1><?= $escape($title); ?></h1>
            <p>Capture one action plan entry at a time with a clear activity, intended output, delivery strategy, ownership, schedule, and budget so the school priorities can move into implementation.</p>
            <div class="entry-hero-meta">
                <span><i class="mdi mdi-school-outline"></i> <?= $escape($school_name); ?></span>
                <span><i class="mdi mdi-calendar-range"></i> <?= $escape($fiscal_year_label); ?></span>
            </div>
        </div>

        <div class="entry-hero-actions">
            <a href="<?= $action_plan_url; ?>" class="entry-button entry-button-primary">
                <i class="mdi mdi-arrow-left"></i>
                Back to Action Plan
            </a>
            <a href="<?= $print_view_url; ?>" target="_blank" class="entry-button entry-button-secondary">
                <i class="mdi mdi-printer-outline"></i>
                Print View
            </a>
        </div>
    </section>

    <div class="row">
        <div class="col-xl-8">
            <section class="entry-panel">
                <div class="entry-panel-header">
                    <div>
                        <h4>Build the entry</h4>
                        <p>Use direct, measurable language so the activity is easy to implement, monitor, and review later on.</p>
                    </div>
                    <small>
                        <i class="mdi mdi-alert-circle-outline"></i>
                        Activity is required
                    </small>
                </div>

                <div class="entry-panel-body">
                    <?php
                    $attributes = array('class' => 'parsley-examples entry-form', 'id' => 'actionPlanEntryForm');
                    echo form_open_multipart('pages/action_plan_new', $attributes);
                    ?>

                    <section class="entry-section">
                        <div class="entry-section-head">
                            <span><i class="mdi mdi-bullseye-arrow"></i> Core Direction</span>
                            <h5>Activity and objectives</h5>
                            <p>Start with the main intervention, then describe the objective it is supposed to achieve for the school.</p>
                        </div>

                        <div class="entry-grid">
                            <div class="entry-field span-4">
                                <label for="activityField">Activity <span>Required</span></label>
                                <textarea class="entry-control" rows="5" name="activity" id="activityField" placeholder="Example: Conduct quarterly reading intervention sessions for struggling learners." required><?= $escape($form_values['activity']); ?></textarea>
                                <small>Name the actual activity or intervention the school will implement.</small>
                            </div>

                            <div class="entry-field span-8">
                                <label for="objectiveField">Objectives <span>Recommended</span></label>
                                <textarea class="entry-control" rows="5" name="objective" id="objectiveField" placeholder="State what improvement, target, or change the activity is expected to accomplish."><?= $escape($form_values['objective']); ?></textarea>
                                <small>Focus on the intended result, not just the steps to be done.</small>
                            </div>
                        </div>
                    </section>

                    <section class="entry-section">
                        <div class="entry-section-head">
                            <span><i class="mdi mdi-file-chart-outline"></i> Delivery Design</span>
                            <h5>Outputs and strategy</h5>
                            <p>Clarify what tangible output should come out of the activity and how the school plans to deliver it.</p>
                        </div>

                        <div class="entry-grid">
                            <div class="entry-field span-4">
                                <label for="outputsField">Expected Outputs <span>Optional</span></label>
                                <textarea class="entry-control" rows="5" name="ex_output" id="outputsField" placeholder="Example: Intervention plan, learner attendance list, progress monitoring report."><?= $escape($form_values['ex_output']); ?></textarea>
                                <small>List the deliverable, evidence, or product expected from the activity.</small>
                            </div>

                            <div class="entry-field span-8">
                                <label for="strategyField">Methodology Strategy <span>Optional</span></label>
                                <textarea class="entry-control" rows="5" name="metho_strategy" id="strategyField" placeholder="Describe the implementation approach, sequence, or strategy the school will use."><?= $escape($form_values['metho_strategy']); ?></textarea>
                                <small>Note the process, coordination approach, or major delivery method.</small>
                            </div>
                        </div>
                    </section>

                    <section class="entry-section">
                        <div class="entry-section-head">
                            <span><i class="mdi mdi-account-group-outline"></i> Execution Plan</span>
                            <h5>Schedule, ownership, and budget</h5>
                            <p>Identify when the activity will happen, who is involved, and what financial requirement should be considered.</p>
                        </div>

                        <div class="entry-grid">
                            <div class="entry-field span-4">
                                <label for="timeFrameField">Time Frame <span>Optional</span></label>
                                <input type="text" name="time_frame" id="timeFrameField" class="entry-control" value="<?= $escape($form_values['time_frame']); ?>" placeholder="Example: July to September 2026">
                                <small>Use a quarter, month range, or implementation window that is easy to track.</small>
                            </div>

                            <div class="entry-field span-4">
                                <label for="personInvolvedField">Person Involved <span>Optional</span></label>
                                <input type="text" name="person_involved" id="personInvolvedField" class="entry-control" value="<?= $escape($form_values['person_involved']); ?>" placeholder="Example: Reading coordinator, class advisers, school head">
                                <small>Enter the lead person, office, team, or stakeholders responsible.</small>
                            </div>

                            <div class="entry-field span-4">
                                <label for="budgetRequirementField">Budgetary Requirement <span>Optional</span></label>
                                <div class="budget-input">
                                    <span>PHP</span>
                                    <input type="text" name="bud_req" id="budgetRequirementField" class="entry-control" value="<?= $escape($form_values['bud_req']); ?>" placeholder="0.00" inputmode="decimal">
                                </div>
                                <small>Enter numbers only. The page will keep the live budget preview updated.</small>
                            </div>
                        </div>
                    </section>

                    <section class="entry-section">
                        <div class="entry-section-head">
                            <span><i class="mdi mdi-comment-text-outline"></i> Monitoring Note</span>
                            <h5>Remarks</h5>
                            <p>Use remarks for implementation notes, dependencies, follow-up points, or coordination reminders.</p>
                        </div>

                        <div class="entry-field">
                            <label for="remarksField">Remarks <span>Optional</span></label>
                            <textarea class="entry-control" rows="4" name="remarks" id="remarksField" placeholder="Add short notes about readiness, coordination needs, risks, or review reminders."><?= $escape($form_values['remarks']); ?></textarea>
                            <small>Keep remarks short and practical so they stay useful during follow-up meetings.</small>
                        </div>
                    </section>

                    <div class="entry-actions">
                        <div class="entry-actions-copy">
                            Review the activity statement first, then check whether the output, strategy, timeline, ownership, and budget are aligned before saving.
                        </div>

                        <div class="entry-actions-group">
                            <a href="<?= $action_plan_url; ?>" class="entry-link">
                                <i class="mdi mdi-close-circle-outline"></i>
                                Cancel
                            </a>
                            <button type="submit" name="submit" class="submit-button">
                                <i class="mdi mdi-content-save-outline"></i>
                                Save Action Plan Entry
                            </button>
                        </div>
                    </div>

                    </form>
                </div>
            </section>
        </div>

        <div class="col-xl-4">
            <aside class="entry-sidebar-card">
                <div class="entry-sidebar-card-body">
                    <h5>Entry Snapshot</h5>
                    <p>Use this side panel to check how complete the current entry looks before saving it.</p>

                    <div class="entry-metric-grid">
                        <div class="entry-metric">
                            <small>Filled Fields</small>
                            <strong id="fieldCompletionCount"><?= $filled_fields; ?>/<?= count($form_values); ?></strong>
                            <div class="entry-progress"><span id="fieldCompletionBar"></span></div>
                        </div>
                        <div class="entry-metric">
                            <small>Budget Preview</small>
                            <strong id="budgetPreviewLabel"><?= $escape($budget_preview); ?></strong>
                            <p class="mb-0" style="margin-top: 8px; color: var(--entry-muted); font-size: 12px;">Live estimate based on the current budget input.</p>
                        </div>
                    </div>

                    <div class="entry-tip-list mb-4">
                        <div class="entry-tip">
                            <i class="mdi mdi-check-decagram-outline"></i>
                            <div>Use action verbs in the activity field so the intervention is easy to understand at a glance.</div>
                        </div>
                        <div class="entry-tip">
                            <i class="mdi mdi-check-decagram-outline"></i>
                            <div>Keep objectives outcome-focused, while methodology explains how the team will deliver the work.</div>
                        </div>
                        <div class="entry-tip">
                            <i class="mdi mdi-check-decagram-outline"></i>
                            <div>Budget and remarks are easier to review when written in simple, specific terms instead of long paragraphs.</div>
                        </div>
                    </div>

                    <h5>Related Pages</h5>
                    <p>Jump to the connected planning pages when you need supporting context.</p>
                    <div class="entry-link-list">
                        <a href="<?= $action_plan_url; ?>">
                            <span><i class="mdi mdi-format-list-bulleted-square"></i> Open current action plan</span>
                            <i class="mdi mdi-arrow-right"></i>
                        </a>
                        <a href="<?= $checklist_url; ?>">
                            <span><i class="mdi mdi-format-list-checks"></i> Review checklist</span>
                            <i class="mdi mdi-arrow-right"></i>
                        </a>
                        <a href="<?= $ta_url; ?>">
                            <span><i class="mdi mdi-file-document-edit-outline"></i> Open TA form</span>
                            <i class="mdi mdi-arrow-right"></i>
                        </a>
                        <a href="<?= $print_view_url; ?>" target="_blank">
                            <span><i class="mdi mdi-printer-outline"></i> Print current plan</span>
                            <i class="mdi mdi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</div>

<script>
    (function () {
        var form = document.getElementById('actionPlanEntryForm');
        if (!form) {
            return;
        }

        var trackedNames = [
            'activity',
            'objective',
            'ex_output',
            'metho_strategy',
            'time_frame',
            'person_involved',
            'bud_req',
            'remarks'
        ];
        var completionCount = document.getElementById('fieldCompletionCount');
        var completionBar = document.getElementById('fieldCompletionBar');
        var budgetInput = document.getElementById('budgetRequirementField');
        var budgetPreview = document.getElementById('budgetPreviewLabel');

        function updateCompletion() {
            var filled = 0;

            trackedNames.forEach(function (name) {
                var field = form.elements[name];
                if (!field) {
                    return;
                }

                if (String(field.value || '').trim() !== '') {
                    filled += 1;
                }
            });

            if (completionCount) {
                completionCount.textContent = filled + '/' + trackedNames.length;
            }

            if (completionBar) {
                completionBar.style.width = (filled / trackedNames.length * 100) + '%';
            }
        }

        function updateBudget() {
            if (!budgetInput) {
                return;
            }

            var cleaned = budgetInput.value
                .replace(/[^0-9.]/g, '')
                .replace(/(\..*)\./g, '$1');

            if (budgetInput.value !== cleaned) {
                budgetInput.value = cleaned;
            }

            if (!budgetPreview) {
                return;
            }

            var numeric = parseFloat(cleaned);
            budgetPreview.textContent = Number.isFinite(numeric)
                ? 'PHP ' + numeric.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })
                : 'PHP 0.00';
        }

        trackedNames.forEach(function (name) {
            var field = form.elements[name];
            if (!field) {
                return;
            }

            field.addEventListener('input', function () {
                updateCompletion();
                if (name === 'bud_req') {
                    updateBudget();
                }
            });
        });

        updateCompletion();
        updateBudget();
    })();
</script>
