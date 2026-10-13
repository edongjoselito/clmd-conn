<?php
$checklist_record = isset($sbmc) && $sbmc ? $sbmc : null;
$principles = isset($sbm) && is_array($sbm) ? $sbm : array();
$indicators = isset($sbm_sub) && is_array($sbm_sub) ? $sbm_sub : array();
$position = strtolower(trim((string) $this->session->position));
$is_school_user = $position === 'school';
$is_division_user = $position === 'division';
$is_region_user = $position === 'region';
$view_school_id = $is_school_user ? (string) $this->session->username : (string) $this->uri->segment(3);
$school = $this->Common->one_cond_row('schools', 'schoolID', $view_school_id);
$division = ($school && !empty($school->division_id)) ? $this->Page_model->one_cond_row('division', 'id', $school->division_id) : null;
$district = ($school && !empty($school->district_id)) ? $this->Page_model->one_cond_row('district', 'id', $school->district_id) : null;
$is_finalized = $checklist_record && isset($checklist_record->stat) && (int) $checklist_record->stat === 1;
$can_edit = $is_school_user && (!$checklist_record || !$is_finalized);
$can_unlock = !$is_school_user && $is_division_user && $checklist_record && $is_finalized;
$can_finalize = $is_school_user && $checklist_record && !$is_finalized;
$all_answered = $checklist_record ? $this->Page_model->all_fields_positive($checklist_record->id) : false;
$validation_markup = validation_errors();

$title_case = static function ($value) {
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

$school_name = $school && trim((string) $school->schoolName) !== ''
    ? $title_case($school->schoolName)
    : $title_case((string) $this->session->user);
$division_name = $division && trim((string) $division->description) !== ''
    ? $title_case($division->description)
    : 'Not assigned';
$district_name = $district && trim((string) $district->description) !== ''
    ? $title_case($district->description)
    : 'Not assigned';

$school_initials = '';
foreach (preg_split('/\s+/', trim($school_name)) as $word) {
    if ($word === '') {
        continue;
    }

    $school_initials .= function_exists('mb_substr')
        ? mb_substr($word, 0, 1, 'UTF-8')
        : substr($word, 0, 1);

    if ((function_exists('mb_strlen') ? mb_strlen($school_initials, 'UTF-8') : strlen($school_initials)) >= 3) {
        break;
    }
}
$school_initials = $school_initials !== ''
    ? (function_exists('mb_strtoupper') ? mb_strtoupper($school_initials, 'UTF-8') : strtoupper($school_initials))
    : 'SBM';

$questions_by_principle = array();
foreach ($indicators as $indicator) {
    $principle_id = isset($indicator->priciple_id) ? (string) $indicator->priciple_id : '';
    if ($principle_id === '') {
        continue;
    }
    if (!isset($questions_by_principle[$principle_id])) {
        $questions_by_principle[$principle_id] = array();
    }
    $questions_by_principle[$principle_id][] = $indicator;
}

$rating_details = array(
    5 => array(
        'label' => 'N/A',
        'short' => 'Not Applicable',
        'hint' => 'Use when the indicator does not apply or there is no valid basis yet.',
        'class' => 'rating-na',
    ),
    1 => array(
        'label' => '1',
        'short' => 'Not Yet Manifested',
        'hint' => 'The practice or outcome is not yet evident in the school.',
        'class' => 'rating-one',
    ),
    2 => array(
        'label' => '2',
        'short' => 'Rarely Manifested',
        'hint' => 'The practice appears inconsistently and still needs strengthening.',
        'class' => 'rating-two',
    ),
    3 => array(
        'label' => '3',
        'short' => 'Frequently Manifested',
        'hint' => 'The practice is often evident and already embedded in many situations.',
        'class' => 'rating-three',
    ),
    4 => array(
        'label' => '4',
        'short' => 'Always Manifested',
        'hint' => 'The practice is fully embedded and consistently demonstrated.',
        'class' => 'rating-four',
    ),
);

$selected_counts = array(1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0);
$principle_progress = array();
$answered_count = 0;
$completed_principles = 0;
$total_questions = count($indicators);

foreach ($principles as $principle) {
    $principle_id = isset($principle->id) ? (string) $principle->id : '';
    $principle_questions = isset($questions_by_principle[$principle_id]) ? $questions_by_principle[$principle_id] : array();
    $principle_total = count($principle_questions);
    $principle_answered = 0;

    foreach ($principle_questions as $question) {
        $field = 'q' . (int) $question->i_no;
        $value = ($checklist_record && isset($checklist_record->$field)) ? (int) $checklist_record->$field : 0;
        if ($value > 0) {
            $principle_answered++;
            $answered_count++;
            if (isset($selected_counts[$value])) {
                $selected_counts[$value]++;
            }
        }
    }

    if ($principle_total > 0 && $principle_answered === $principle_total) {
        $completed_principles++;
    }

    $principle_progress[$principle_id] = array(
        'answered' => $principle_answered,
        'total' => $principle_total,
        'percent' => $principle_total > 0 ? ($principle_answered / $principle_total) * 100 : 0,
    );
}

$completion_rate = $total_questions > 0 ? ($answered_count / $total_questions) * 100 : 0;
$unanswered_count = max(0, $total_questions - $answered_count);
$status_label = !$checklist_record ? 'Not started' : ($is_finalized ? 'Finalized' : 'Draft saved');
$status_class = !$checklist_record ? 'status-empty' : ($is_finalized ? 'status-complete' : 'status-draft');
$form_action = $checklist_record ? 'Pages/sbm_checklist_update' : 'Pages/sbm_checklist';
$profile_url = base_url() . 'school/' . rawurlencode($view_school_id);
$dashboard_url = base_url();
$action_plan_url = $is_school_user
    ? base_url() . 'Pages/sbm_action_plan'
    : base_url() . 'Pages/sbm_action_plan_pview_district/' . rawurlencode($view_school_id);
$ta_url = $is_school_user
    ? base_url() . 'Pages/tapr_form'
    : base_url() . 'Pages/tapr_form_district/' . rawurlencode($view_school_id);
$tana_url = $is_school_user ? base_url() . 'Pages/tana_summary' : $profile_url;
$checklist_pdf_url = $is_school_user
    ? base_url() . 'Pages/sbm_checklist_pdf'
    : base_url() . 'Pages/sbm_checklist_pdf/' . rawurlencode($view_school_id);
$fiscal_year_label = 'Fiscal Year ' . $this->session->fy;

$next_step = array(
    'eyebrow' => 'Start here',
    'title' => 'Begin the self-assessment',
    'description' => 'Review each indicator and choose the degree of manifestation that best matches the current school situation.',
    'url' => '#principleCollapse' . (isset($principles[0]) ? $principles[0]->id : '1'),
    'cta' => 'Open first principle',
    'secondary_url' => $profile_url,
    'secondary_cta' => 'Review school profile',
);

if ($checklist_record && !$all_answered && $can_edit) {
    $next_step = array(
        'eyebrow' => 'Continue progress',
        'title' => 'Finish the remaining indicators',
        'description' => 'You have answered ' . $answered_count . ' of ' . $total_questions . ' indicators. Complete the rest before finalizing the checklist.',
        'url' => '#checklistWorkspace',
        'cta' => 'Continue checklist',
        'secondary_url' => $ta_url,
        'secondary_cta' => 'Preview TA form',
    );
} elseif ($checklist_record && $all_answered && !$is_finalized && $can_edit) {
    $next_step = array(
        'eyebrow' => 'Ready for review',
        'title' => 'Finalize the checklist when everything is correct',
        'description' => 'All indicators already have a response. Review the entries carefully, then finalize the checklist to lock the submission.',
        'url' => '#checklistActions',
        'cta' => 'Go to actions',
        'secondary_url' => $ta_url,
        'secondary_cta' => 'Open TA form',
    );
} elseif ($is_finalized && $is_school_user) {
    $next_step = array(
        'eyebrow' => 'Submission completed',
        'title' => 'Use the finalized checklist to continue planning',
        'description' => 'Your checklist is finalized for this fiscal year. Move to the TA form, TANA priorities, and action plan to turn the findings into concrete next steps.',
        'url' => $ta_url,
        'cta' => 'Open TA form',
        'secondary_url' => $action_plan_url,
        'secondary_cta' => 'Open action plan',
    );
} elseif ($checklist_record && !$is_school_user) {
    $next_step = array(
        'eyebrow' => 'Reviewer mode',
        'title' => 'Review the school responses against each indicator',
        'description' => 'This page is currently in read-only mode. Check the completed responses and unlock the checklist only if the school needs to revise it.',
        'url' => $profile_url,
        'cta' => 'View school profile',
        'secondary_url' => $action_plan_url,
        'secondary_cta' => 'View action plan',
    );
}
?>

<style>
    .sbm-checklist-page {
        --checklist-primary: #7f1d1d;
        --checklist-primary-light: #b83a4b;
        --checklist-accent: #d6a84b;
        --checklist-ink: #172033;
        --checklist-muted: #687386;
        --checklist-border: #e4e9f0;
        --checklist-surface: #f6f8fb;
        --checklist-success: #15803d;
        --checklist-warning: #c97a11;
        --checklist-info: #1d4ed8;
        --checklist-purple: #7c3aed;
    }

    .sbm-checklist-page .alert {
        border-radius: 14px;
    }

    .sbm-checklist-page .checklist-hero {
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

    .sbm-checklist-page .checklist-hero::after {
        content: '';
        position: absolute;
        right: -42px;
        bottom: -56px;
        width: 210px;
        height: 210px;
        border-radius: 50%;
        background: rgba(255, 255, 255, .08);
    }

    .sbm-checklist-page .checklist-hero-copy,
    .sbm-checklist-page .checklist-hero-side {
        position: relative;
        z-index: 1;
    }

    .sbm-checklist-page .checklist-hero-copy {
        max-width: 760px;
    }

    .sbm-checklist-page .checklist-hero-side {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        justify-content: space-between;
        gap: 16px;
        min-width: 220px;
    }

    .sbm-checklist-page .hero-eyebrow {
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

    .sbm-checklist-page .hero-eyebrow i {
        font-size: 15px;
    }

    .sbm-checklist-page .checklist-hero h1 {
        margin: 0;
        color: #fff;
        font-size: 30px;
        font-weight: 800;
        line-height: 1.1;
        letter-spacing: -.03em;
    }

    .sbm-checklist-page .checklist-hero p {
        max-width: 700px;
        margin: 14px 0 18px;
        color: rgba(255, 255, 255, .86);
        font-size: 14px;
        line-height: 1.75;
    }

    .sbm-checklist-page .hero-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .sbm-checklist-page .hero-meta span {
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

    .sbm-checklist-page .school-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        min-width: 88px;
        min-height: 88px;
        padding: 18px 20px;
        border: 3px solid rgba(255, 255, 255, .20);
        border-radius: 24px;
        color: var(--checklist-primary);
        background: #fff;
        text-align: center;
        box-shadow: 0 14px 26px rgba(58, 10, 17, .18);
    }

    .sbm-checklist-page .school-badge small {
        display: block;
        color: var(--checklist-muted);
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
    }

    .sbm-checklist-page .school-badge strong {
        display: block;
        margin-top: 2px;
        color: var(--checklist-primary);
        font-size: 22px;
        font-weight: 800;
        letter-spacing: .08em;
    }

    .sbm-checklist-page .hero-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        justify-content: flex-end;
    }

    .sbm-checklist-page .hero-button,
    .sbm-checklist-page .workspace-button,
    .sbm-checklist-page .quick-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        border: 0;
        border-radius: 12px;
        text-decoration: none !important;
        transition: transform .18s ease, box-shadow .18s ease, background .18s ease, color .18s ease;
    }

    .sbm-checklist-page .hero-button:hover,
    .sbm-checklist-page .workspace-button:hover,
    .sbm-checklist-page .quick-link:hover {
        transform: translateY(-2px);
    }

    .sbm-checklist-page .hero-button {
        padding: 11px 16px;
        font-size: 12px;
        font-weight: 700;
    }

    .sbm-checklist-page .hero-button-primary {
        color: var(--checklist-primary);
        background: #fff;
        box-shadow: 0 10px 20px rgba(61, 12, 29, .18);
    }

    .sbm-checklist-page .hero-button-secondary {
        color: #fff;
        background: rgba(255, 255, 255, .10);
        border: 1px solid rgba(255, 255, 255, .18);
    }

    .sbm-checklist-page .hero-button-tertiary {
        color: #fff;
        background: rgba(255, 255, 255, .16);
        border: 1px solid rgba(255, 255, 255, .22);
    }

    .sbm-checklist-page .checklist-stats {
        margin-bottom: 2px;
    }

    .sbm-checklist-page .checklist-stat-card {
        height: calc(100% - 20px);
        margin-bottom: 20px;
        padding: 20px;
        border: 1px solid var(--checklist-border);
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 8px 24px rgba(31, 45, 75, .06);
    }

    .sbm-checklist-page .checklist-stat-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
    }

    .sbm-checklist-page .checklist-stat-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 46px;
        height: 46px;
        border-radius: 14px;
        color: #fff;
        background: linear-gradient(135deg, var(--checklist-primary), var(--checklist-primary-light));
        font-size: 20px;
        box-shadow: 0 10px 18px rgba(127, 29, 29, .15);
    }

    .sbm-checklist-page .checklist-stat-card small {
        display: block;
        margin-bottom: 8px;
        color: var(--checklist-muted);
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
    }

    .sbm-checklist-page .checklist-stat-card h3 {
        margin: 0;
        color: var(--checklist-ink);
        font-size: 28px;
        font-weight: 800;
        line-height: 1;
    }

    .sbm-checklist-page .checklist-stat-card p {
        margin: 10px 0 0;
        color: var(--checklist-muted);
        font-size: 13px;
        line-height: 1.6;
    }

    .sbm-checklist-page .mini-progress {
        height: 8px;
        margin-top: 14px;
        border-radius: 999px;
        background: #edf1f6;
        overflow: hidden;
    }

    .sbm-checklist-page .mini-progress span {
        display: block;
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg, var(--checklist-primary), var(--checklist-accent));
    }

    .sbm-checklist-page .workspace-panel {
        margin-bottom: 22px;
        border: 1px solid var(--checklist-border);
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 10px 30px rgba(31, 45, 75, .07);
        overflow: hidden;
    }

    .sbm-checklist-page .workspace-panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 20px 24px;
        border-bottom: 1px solid var(--checklist-border);
    }

    .sbm-checklist-page .workspace-panel-header h4 {
        margin: 0 0 4px;
        color: var(--checklist-ink);
        font-size: 18px;
        font-weight: 700;
    }

    .sbm-checklist-page .workspace-panel-header p,
    .sbm-checklist-page .workspace-panel-header small {
        margin: 0;
        color: var(--checklist-muted);
        font-size: 12px;
        line-height: 1.6;
    }

    .sbm-checklist-page .workspace-panel-body {
        padding: 22px 24px;
    }

    .sbm-checklist-page .next-step-card,
    .sbm-checklist-page .sidebar-card {
        padding: 18px;
        border: 1px solid var(--checklist-border);
        border-radius: 16px;
        background: #fbfcff;
    }

    .sbm-checklist-page .sidebar-card + .sidebar-card {
        margin-top: 16px;
    }

    .sbm-checklist-page .next-step-card {
        background:
            radial-gradient(circle at top right, rgba(214, 168, 75, .18), transparent 34%),
            linear-gradient(180deg, #fff9ec 0%, #fffdf8 100%);
    }

    .sbm-checklist-page .next-step-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        margin-bottom: 10px;
        padding: 7px 10px;
        border-radius: 999px;
        background: rgba(127, 29, 29, .08);
        color: var(--checklist-primary);
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .sbm-checklist-page .next-step-card h5,
    .sbm-checklist-page .sidebar-card h5 {
        margin: 0;
        color: var(--checklist-ink);
        font-size: 18px;
        font-weight: 800;
        line-height: 1.4;
    }

    .sbm-checklist-page .next-step-card p,
    .sbm-checklist-page .sidebar-card p {
        margin: 12px 0 0;
        color: var(--checklist-muted);
        font-size: 12px;
        line-height: 1.75;
    }

    .sbm-checklist-page .next-step-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 18px;
    }

    .sbm-checklist-page .workspace-button {
        padding: 11px 16px;
        font-size: 12px;
        font-weight: 700;
    }

    .sbm-checklist-page .workspace-button-primary {
        color: #fff;
        background: linear-gradient(135deg, var(--checklist-primary), var(--checklist-primary-light));
        box-shadow: 0 10px 20px rgba(127, 29, 29, .18);
    }

    .sbm-checklist-page .workspace-button-secondary {
        color: var(--checklist-primary);
        background: #fbeef1;
    }

    .sbm-checklist-page .workspace-button-success {
        color: #fff;
        background: linear-gradient(135deg, #0f766e, #22a06b);
        box-shadow: 0 10px 20px rgba(15, 118, 110, .18);
    }

    .sbm-checklist-page .workspace-button-warning {
        color: #fff;
        background: linear-gradient(135deg, #9a3412, #ea580c);
        box-shadow: 0 10px 20px rgba(154, 52, 18, .18);
    }

    .sbm-checklist-page .workspace-button-disabled {
        cursor: pointer;
        color: #fff;
        background: linear-gradient(135deg, #94a3b8, #64748b);
        box-shadow: 0 10px 20px rgba(100, 116, 139, .18);
    }

    .sbm-checklist-page .rating-legend {
        display: flex;
        flex-direction: column;
        gap: 12px;
        margin-top: 14px;
    }

    .sbm-checklist-page .rating-legend-item {
        padding: 14px;
        border: 1px solid var(--checklist-border);
        border-radius: 14px;
        background: #fff;
    }

    .sbm-checklist-page .rating-legend-item strong {
        display: block;
        margin-bottom: 5px;
        color: var(--checklist-ink);
        font-size: 13px;
        font-weight: 700;
    }

    .sbm-checklist-page .rating-legend-item span {
        display: block;
        color: var(--checklist-muted);
        font-size: 12px;
        line-height: 1.7;
    }

    .sbm-checklist-page .principle-links {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 14px;
    }

    .sbm-checklist-page .principle-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 12px;
        border-radius: 999px;
        border: 1px solid var(--checklist-border);
        color: var(--checklist-ink);
        background: #fff;
        font-size: 12px;
        font-weight: 700;
        text-decoration: none !important;
        transition: transform .18s ease, box-shadow .18s ease;
    }

    .sbm-checklist-page .principle-link:hover {
        transform: translateY(-1px);
        box-shadow: 0 10px 22px rgba(31, 45, 75, .08);
    }

    .sbm-checklist-page .quick-link-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-top: 14px;
    }

    .sbm-checklist-page .quick-link {
        justify-content: space-between;
        gap: 10px;
        padding: 13px 14px;
        border: 1px solid var(--checklist-border);
        color: var(--checklist-ink);
        background: #fff;
        font-size: 13px;
        font-weight: 700;
    }

    .sbm-checklist-page .quick-link span {
        display: inline-flex;
        align-items: center;
        gap: 10px;
    }

    .sbm-checklist-page .quick-link i:first-child {
        color: var(--checklist-primary);
        font-size: 16px;
    }

    .sbm-checklist-page .checklist-workspace {
        border: 1px solid var(--checklist-border);
        border-radius: 18px;
        background: #fff;
        overflow: hidden;
    }

    .sbm-checklist-page .principle-card {
        border-top: 1px solid var(--checklist-border);
    }

    .sbm-checklist-page .principle-card:first-child {
        border-top: 0;
    }

    .sbm-checklist-page .principle-header {
        padding: 0;
        border: 0;
        background: #fff;
    }

    .sbm-checklist-page .principle-toggle {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        width: 100%;
        padding: 18px 20px;
        color: var(--checklist-ink);
        text-decoration: none !important;
        transition: background .18s ease, color .18s ease;
    }

    .sbm-checklist-page .principle-toggle:hover {
        color: var(--checklist-primary);
        background: #fff7f9;
    }

    .sbm-checklist-page .principle-toggle[aria-expanded="true"] .principle-icon {
        transform: rotate(180deg);
    }

    .sbm-checklist-page .principle-main strong {
        display: block;
        color: inherit;
        font-size: 15px;
        font-weight: 700;
    }

    .sbm-checklist-page .principle-main span {
        display: block;
        margin-top: 4px;
        color: var(--checklist-muted);
        font-size: 12px;
        line-height: 1.6;
    }

    .sbm-checklist-page .principle-meta {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-shrink: 0;
    }

    .sbm-checklist-page .principle-progress-pill {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 8px 12px;
        border-radius: 999px;
        background: #f4f7fb;
        color: var(--checklist-ink);
        font-size: 12px;
        font-weight: 700;
    }

    .sbm-checklist-page .principle-icon {
        color: var(--checklist-muted);
        font-size: 18px;
        transition: transform .18s ease;
    }

    .sbm-checklist-page .principle-body {
        padding: 0 20px 20px;
        background: #fbfcff;
    }

    .sbm-checklist-page .principle-description {
        padding: 16px 0 8px;
        color: var(--checklist-muted);
        font-size: 13px;
        line-height: 1.8;
    }

    .sbm-checklist-page .indicator-list {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .sbm-checklist-page .indicator-card {
        padding: 18px;
        border: 1px solid var(--checklist-border);
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 8px 20px rgba(31, 45, 75, .04);
    }

    .sbm-checklist-page .indicator-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 14px;
    }

    .sbm-checklist-page .indicator-copy {
        flex: 1 1 auto;
        min-width: 0;
    }

    .sbm-checklist-page .indicator-copy h5 {
        margin: 0;
        color: var(--checklist-ink);
        font-size: 14px;
        font-weight: 700;
        line-height: 1.7;
    }

    .sbm-checklist-page .indicator-copy p {
        margin: 8px 0 0;
        color: var(--checklist-muted);
        font-size: 12px;
        line-height: 1.7;
    }

    .sbm-checklist-page .indicator-number {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        flex: 0 0 38px;
        border-radius: 12px;
        color: var(--checklist-primary);
        background: #fbeef1;
        font-size: 13px;
        font-weight: 800;
    }

    .sbm-checklist-page .indicator-status {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        flex-shrink: 0;
        padding: 8px 12px;
        border-radius: 999px;
        background: #f4f7fb;
        color: var(--checklist-ink);
        font-size: 12px;
        font-weight: 700;
        line-height: 1.4;
    }

    .sbm-checklist-page .indicator-status.rating-na {
        background: #f8fafc;
        color: #475569;
    }

    .sbm-checklist-page .indicator-status.rating-one {
        background: #f5f3ff;
        color: var(--checklist-purple);
    }

    .sbm-checklist-page .indicator-status.rating-two {
        background: #fff7ed;
        color: var(--checklist-warning);
    }

    .sbm-checklist-page .indicator-status.rating-three {
        background: #ecfeff;
        color: #0f766e;
    }

    .sbm-checklist-page .indicator-status.rating-four {
        background: #ecfdf5;
        color: var(--checklist-success);
    }

    .sbm-checklist-page .indicator-rating-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 12px;
    }

    .sbm-checklist-page .indicator-rating-head strong {
        color: var(--checklist-ink);
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 0.03em;
        text-transform: uppercase;
    }

    .sbm-checklist-page .indicator-rating-head span {
        color: var(--checklist-muted);
        font-size: 12px;
        line-height: 1.6;
        text-align: right;
    }

    .sbm-checklist-page .indicator-options {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 10px;
    }

    .sbm-checklist-page .indicator-choice {
        display: block;
        cursor: pointer;
        margin: 0;
    }

    .sbm-checklist-page .indicator-choice.is-readonly {
        cursor: default;
    }

    .sbm-checklist-page .indicator-choice input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .sbm-checklist-page .indicator-choice-body {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        grid-template-areas:
            "badge check"
            "copy copy";
        align-items: flex-start;
        column-gap: 8px;
        row-gap: 10px;
        height: 100%;
        min-height: 88px;
        padding: 12px;
        border: 1px solid var(--checklist-border);
        border-radius: 14px;
        background: #fff;
        transition: transform .16s ease, border-color .16s ease, box-shadow .16s ease, background .16s ease;
    }

    .sbm-checklist-page .indicator-choice:hover .indicator-choice-body {
        transform: translateY(-1px);
        border-color: #d4dce8;
        box-shadow: 0 10px 22px rgba(31, 45, 75, .06);
    }

    .sbm-checklist-page .indicator-choice.is-readonly:hover .indicator-choice-body {
        transform: none;
        box-shadow: none;
    }

    .sbm-checklist-page .indicator-choice.is-selected .indicator-choice-body,
    .sbm-checklist-page .indicator-choice input:checked + .indicator-choice-body {
        border-width: 2px;
        box-shadow: 0 12px 24px rgba(31, 45, 75, .08);
    }

    .sbm-checklist-page .indicator-choice.rating-na.is-selected .indicator-choice-body,
    .sbm-checklist-page .indicator-choice.rating-na input:checked + .indicator-choice-body {
        border-color: #475569;
        background: #f8fafc;
    }

    .sbm-checklist-page .indicator-choice.rating-one.is-selected .indicator-choice-body,
    .sbm-checklist-page .indicator-choice.rating-one input:checked + .indicator-choice-body {
        border-color: var(--checklist-purple);
        background: #f5f3ff;
    }

    .sbm-checklist-page .indicator-choice.rating-two.is-selected .indicator-choice-body,
    .sbm-checklist-page .indicator-choice.rating-two input:checked + .indicator-choice-body {
        border-color: var(--checklist-warning);
        background: #fff7ed;
    }

    .sbm-checklist-page .indicator-choice.rating-three.is-selected .indicator-choice-body,
    .sbm-checklist-page .indicator-choice.rating-three input:checked + .indicator-choice-body {
        border-color: #0f766e;
        background: #ecfeff;
    }

    .sbm-checklist-page .indicator-choice.rating-four.is-selected .indicator-choice-body,
    .sbm-checklist-page .indicator-choice.rating-four input:checked + .indicator-choice-body {
        border-color: var(--checklist-success);
        background: #ecfdf5;
    }

    .sbm-checklist-page .indicator-choice input:focus-visible + .indicator-choice-body {
        box-shadow: 0 0 0 4px rgba(127, 29, 29, .10);
    }

    .sbm-checklist-page .indicator-choice-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        grid-area: badge;
        justify-self: flex-start;
        min-width: 34px;
        height: 34px;
        padding: 0 8px;
        border-radius: 10px;
        background: rgba(127, 29, 29, .08);
        color: var(--checklist-primary);
        font-size: 12px;
        font-weight: 800;
        line-height: 1;
        flex-shrink: 0;
    }

    .sbm-checklist-page .indicator-choice-copy {
        grid-area: copy;
        flex: 1 1 auto;
        min-width: 0;
    }

    .sbm-checklist-page .indicator-choice-label {
        display: block;
        color: var(--checklist-ink);
        font-size: 11px;
        font-weight: 800;
        line-height: 1.4;
        overflow-wrap: anywhere;
        word-break: break-word;
    }

    .sbm-checklist-page .indicator-choice-check {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        grid-area: check;
        justify-self: flex-end;
        align-self: start;
        color: transparent;
        font-size: 18px;
        line-height: 1;
        flex-shrink: 0;
        transition: color .16s ease;
    }

    .sbm-checklist-page .indicator-choice.is-selected .indicator-choice-check,
    .sbm-checklist-page .indicator-choice input:checked + .indicator-choice-body .indicator-choice-check {
        color: currentColor;
    }

    .sbm-checklist-page .indicator-choice-summary {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        margin-top: 12px;
        padding: 12px 14px;
        border: 1px solid var(--checklist-border);
        border-radius: 14px;
        background: #f8fafc;
    }

    .sbm-checklist-page .indicator-choice-summary strong {
        display: block;
        margin-bottom: 2px;
        color: var(--checklist-ink);
        font-size: 12px;
        font-weight: 800;
        line-height: 1.45;
    }

    .sbm-checklist-page .indicator-choice-summary span {
        color: var(--checklist-muted);
        font-size: 11px;
        line-height: 1.65;
    }

    .sbm-checklist-page .indicator-choice-summary.rating-na {
        background: #f8fafc;
        border-color: #cbd5e1;
    }

    .sbm-checklist-page .indicator-choice-summary.rating-one {
        background: #f5f3ff;
        border-color: #ddd6fe;
    }

    .sbm-checklist-page .indicator-choice-summary.rating-two {
        background: #fff7ed;
        border-color: #fed7aa;
    }

    .sbm-checklist-page .indicator-choice-summary.rating-three {
        background: #ecfeff;
        border-color: #a5f3fc;
    }

    .sbm-checklist-page .indicator-choice-summary.rating-four {
        background: #ecfdf5;
        border-color: #bbf7d0;
    }

    .sbm-checklist-page .workspace-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 22px;
        padding-top: 20px;
        border-top: 1px solid var(--checklist-border);
    }

    .sbm-checklist-page .workspace-note {
        margin-top: 12px;
        color: var(--checklist-muted);
        font-size: 12px;
        line-height: 1.7;
    }

    .sbm-checklist-page .pdf-link-panel {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        margin-top: 16px;
        padding: 16px 18px;
        border: 1px solid rgba(194, 65, 12, .16);
        border-radius: 16px;
        background: linear-gradient(135deg, rgba(255, 247, 237, .95), #fff);
    }

    .sbm-checklist-page .pdf-link-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        border-radius: 14px;
        flex-shrink: 0;
        color: #fff;
        background: linear-gradient(135deg, #c2410c, #ea580c);
        font-size: 20px;
        box-shadow: 0 10px 18px rgba(194, 65, 12, .18);
    }

    .sbm-checklist-page .pdf-link-copy {
        min-width: 0;
        flex: 1;
    }

    .sbm-checklist-page .pdf-link-copy strong {
        display: block;
        margin-bottom: 4px;
        color: var(--checklist-ink);
        font-size: 14px;
        font-weight: 700;
    }

    .sbm-checklist-page .pdf-link-copy p {
        margin: 0 0 8px;
        color: var(--checklist-muted);
        font-size: 12px;
        line-height: 1.6;
    }

    .sbm-checklist-page .pdf-link-url {
        display: block;
        padding: 10px 12px;
        border: 1px solid rgba(234, 88, 12, .16);
        border-radius: 12px;
        color: #9a3412;
        background: rgba(255, 255, 255, .92);
        font-size: 12px;
        font-weight: 600;
        line-height: 1.55;
        word-break: break-all;
        overflow-wrap: anywhere;
        text-decoration: none !important;
    }

    .sbm-checklist-page .pdf-link-url:hover {
        color: #7c2d12;
        background: #fff;
    }

    .sbm-checklist-page .pdf-link-url.is-disabled,
    .sbm-checklist-page .pdf-link-url.is-disabled:hover {
        color: #7c8699;
        background: rgba(248, 250, 252, .96);
        border-color: rgba(148, 163, 184, .22);
        cursor: default;
    }

    .sbm-checklist-page .empty-state {
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

    .sbm-checklist-page .empty-state i {
        font-size: 28px;
        color: var(--checklist-primary-light);
    }

    .sbm-checklist-page .empty-state strong {
        color: var(--checklist-ink);
        font-size: 15px;
        font-weight: 700;
    }

    .sbm-checklist-page .empty-state p {
        margin: 0;
        color: var(--checklist-muted);
        font-size: 12px;
        line-height: 1.7;
    }

    .sbm-checklist-page .fy-button {
        color: #fff;
        background: rgba(255, 255, 255, .10);
        border: 1px solid rgba(255, 255, 255, .18);
    }

    .sbm-checklist-page .dashboard-modal .modal-content {
        border: 0;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 18px 40px rgba(23, 32, 51, .14);
    }

    .sbm-checklist-page .dashboard-modal .modal-header {
        border-bottom: 0;
        background: linear-gradient(135deg, var(--checklist-primary), var(--checklist-primary-light));
    }

    .sbm-checklist-page .dashboard-modal .modal-title {
        color: #fff;
        font-weight: 700;
    }

    .sbm-checklist-page .dashboard-modal .modal-body {
        padding: 22px;
    }

    .sbm-checklist-page .dashboard-modal label {
        display: block;
        margin-bottom: 8px;
        color: var(--checklist-ink);
        font-size: 12px;
        font-weight: 700;
    }

    .sbm-checklist-page .dashboard-modal .form-control {
        height: 46px;
        border-radius: 12px;
        border: 1px solid var(--checklist-border);
    }

    @media (max-width: 1199.98px) {
        .sbm-checklist-page .indicator-options {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media (max-width: 991.98px) {
        .sbm-checklist-page .checklist-hero {
            flex-direction: column;
            align-items: flex-start;
        }

        .sbm-checklist-page .checklist-hero-side {
            width: 100%;
            min-width: 0;
            align-items: flex-start;
        }

        .sbm-checklist-page .hero-actions {
            justify-content: flex-start;
        }
    }

    @media (max-width: 767.98px) {
        .sbm-checklist-page .checklist-hero,
        .sbm-checklist-page .workspace-panel-body,
        .sbm-checklist-page .workspace-panel-header {
            padding-left: 18px;
            padding-right: 18px;
        }

        .sbm-checklist-page .checklist-hero h1 {
            font-size: 24px;
        }

        .sbm-checklist-page .hero-meta,
        .sbm-checklist-page .next-step-actions,
        .sbm-checklist-page .workspace-actions {
            flex-direction: column;
            align-items: stretch;
        }

        .sbm-checklist-page .pdf-link-panel {
            padding: 14px;
        }

        .sbm-checklist-page .hero-button,
        .sbm-checklist-page .workspace-button,
        .sbm-checklist-page .quick-link {
            width: 100%;
        }

        .sbm-checklist-page .indicator-top {
            flex-direction: column;
        }

        .sbm-checklist-page .indicator-rating-head {
            flex-direction: column;
            align-items: flex-start;
        }

        .sbm-checklist-page .indicator-rating-head span {
            text-align: left;
        }

        .sbm-checklist-page .indicator-options {
            grid-template-columns: 1fr;
        }

        .sbm-checklist-page .principle-toggle {
            flex-direction: column;
            align-items: flex-start;
        }

        .sbm-checklist-page .principle-meta {
            width: 100%;
            justify-content: space-between;
        }
    }
</style>

<div class="sbm-checklist-page">
    <div class="checklist-hero">
        <div class="checklist-hero-copy">
            <span class="hero-eyebrow">
                <i class="mdi mdi-format-list-checks"></i>
                SBM Assessment Workspace
            </span>
            <h1>School-Based Management Self-Assessment Checklist</h1>
            <?php if ($is_region_user) : ?>
                <p>Review the finalized checklist submission for this school and use the division context for regional monitoring.</p>
            <?php elseif ($is_division_user) : ?>
                <p>Review the checklist submission for this school.</p>
            <?php else : ?>
                <p>Complete the self-assessment checklist for your school.</p>
            <?php endif; ?>
            <div class="hero-meta">
                <span>
                    <i class="mdi mdi-school-outline"></i>
                    School: <?= $escape($school_name); ?>
                </span>
                <?php if ($is_region_user || $is_division_user) : ?>
                <span>
                    <i class="mdi mdi-domain"></i>
                    Division: <?= $escape($division_name); ?>
                </span>
                <?php endif; ?>
            </div>
        </div>

        <div class="checklist-hero-side">
            <div class="hero-actions">
                <a href="<?= $profile_url; ?>" class="hero-button hero-button-primary">
                    <i class="mdi mdi-account-school-outline"></i>
                    View Profile
                </a>
                <a href="#" class="hero-button hero-button-secondary fy-button" data-toggle="modal" data-target="#checklistFiscalYearModal">
                    <i class="mdi mdi-calendar-edit"></i>
                    Change Fiscal Year
                </a>
                <a href="<?= $dashboard_url; ?>" class="hero-button hero-button-tertiary">
                    <i class="mdi mdi-view-dashboard-outline"></i>
                    Dashboard
                </a>
            </div>
        </div>
    </div>

    <?php if ($validation_markup) : ?>
        <?= $validation_markup; ?>
    <?php endif; ?>

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

    <div class="row checklist-stats">
        <div class="col-md-6 col-xl-3">
            <div class="checklist-stat-card">
                <div class="checklist-stat-top">
                    <div>
                        <small>Answered Indicators</small>
                        <h3><?= $answered_count; ?>/<?= $total_questions; ?></h3>
                        <p><?= $unanswered_count > 0 ? $unanswered_count . ' indicator(s) still need a response.' : 'Every indicator currently has a selected value.'; ?></p>
                    </div>
                    <span class="checklist-stat-icon"><i class="mdi mdi-clipboard-check-outline"></i></span>
                </div>
                <div class="mini-progress"><span style="width: <?= min(100, max(0, $completion_rate)); ?>%;"></span></div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="checklist-stat-card">
                <div class="checklist-stat-top">
                    <div>
                        <small>Completion Rate</small>
                        <h3><?= number_format($completion_rate, 1); ?>%</h3>
                        <p><?= $completed_principles; ?> of <?= count($principles); ?> principle section(s) are fully answered.</p>
                    </div>
                    <span class="checklist-stat-icon"><i class="mdi mdi-chart-donut"></i></span>
                </div>
                <div class="mini-progress"><span style="width: <?= min(100, max(0, $completion_rate)); ?>%;"></span></div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="checklist-stat-card">
                <div class="checklist-stat-top">
                    <div>
                        <small>Submission Status</small>
                        <h3><?= $escape($status_label); ?></h3>
                        <p><?= !$checklist_record ? 'The checklist has not been saved yet for this fiscal year.' : ($is_finalized ? 'The submission is locked until a division reviewer unlocks it.' : 'Draft responses can still be updated before finalizing.'); ?></p>
                    </div>
                    <span class="checklist-stat-icon"><i class="mdi mdi-shield-check-outline"></i></span>
                </div>
                <div class="mini-progress"><span style="width: <?= !$checklist_record ? 0 : ($is_finalized ? 100 : min(100, max(20, $completion_rate))); ?>%;"></span></div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="checklist-stat-card">
                <div class="checklist-stat-top">
                    <div>
                        <small>N/A Indicators</small>
                        <h3><?= (int) $selected_counts[5]; ?></h3>
                        <p><?= $selected_counts[5] > 0 ? 'Indicators marked as not applicable or without a current basis.' : 'No indicator is currently tagged as N/A.'; ?></p>
                    </div>
                    <span class="checklist-stat-icon"><i class="mdi mdi-help-circle-outline"></i></span>
                </div>
                <div class="mini-progress"><span style="width: <?= $total_questions > 0 ? min(100, ($selected_counts[5] / $total_questions) * 100) : 0; ?>%;"></span></div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-8">
            <section class="workspace-panel">
                <div class="workspace-panel-header">
                    <div>
                        <h4>Checklist Workspace</h4>
                    </div>
                </div>
                <div class="workspace-panel-body" id="checklistWorkspace">
                    <?= form_open($form_action, array('class' => 'sbm-checklist-form')); ?>
                    <?php if (!$checklist_record) : ?>
                        <input type="hidden" name="district" value="<?= $escape($this->session->district); ?>">
                    <?php endif; ?>
                    <?php if ($checklist_record) : ?>
                        <input type="hidden" name="id" value="<?= $escape($checklist_record->id); ?>">
                    <?php endif; ?>
                    <?php if (!$is_school_user && $view_school_id !== '') : ?>
                        <input type="hidden" name="school_id" value="<?= $escape($view_school_id); ?>">
                    <?php endif; ?>

                    <div class="principle-links">
                        <?php foreach ($principles as $principle) :
                            $principle_id = isset($principle->id) ? (string) $principle->id : '';
                            $progress = isset($principle_progress[$principle_id]) ? $principle_progress[$principle_id] : array('answered' => 0, 'total' => 0);
                        ?>
                            <a href="#principleCollapse<?= $principle_id; ?>" class="principle-link" data-toggle="collapse" aria-controls="principleCollapse<?= $principle_id; ?>">
                                <i class="mdi mdi-view-day-outline"></i>
                                <?= $escape($principle->indicator); ?>
                                <span><?= $progress['answered']; ?>/<?= $progress['total']; ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>

                    <div class="checklist-workspace mt-3">
                        <?php if (empty($principles)) : ?>
                            <div class="empty-state">
                                <i class="mdi mdi-file-document-alert-outline"></i>
                                <strong>No principles are available</strong>
                                <p>The checklist cannot be displayed because the SBM principle list is currently empty.</p>
                            </div>
                        <?php endif; ?>

                        <?php foreach ($principles as $principle) :
                            $principle_id = isset($principle->id) ? (string) $principle->id : '';
                            $principle_questions = isset($questions_by_principle[$principle_id]) ? $questions_by_principle[$principle_id] : array();
                            $progress = isset($principle_progress[$principle_id]) ? $principle_progress[$principle_id] : array('answered' => 0, 'total' => 0, 'percent' => 0);
                        ?>
                            <div class="principle-card">
                                <div class="principle-header" id="principleHeading<?= $principle_id; ?>">
                                    <a
                                        href="#principleCollapse<?= $principle_id; ?>"
                                        class="principle-toggle"
                                        data-toggle="collapse"
                                        aria-expanded="<?= $principle_id === '1' ? 'true' : 'false'; ?>"
                                        aria-controls="principleCollapse<?= $principle_id; ?>"
                                    >
                                        <div class="principle-main">
                                            <strong><?= $escape($principle->indicator); ?></strong>
                                            <span><?= $escape($principle->description); ?></span>
                                        </div>
                                        <div class="principle-meta">
                                            <span class="principle-progress-pill">
                                                <i class="mdi mdi-check-circle-outline"></i>
                                                <?= $progress['answered']; ?>/<?= $progress['total']; ?>
                                            </span>
                                            <i class="mdi mdi-chevron-down principle-icon"></i>
                                        </div>
                                    </a>
                                </div>

                                <div
                                    id="principleCollapse<?= $principle_id; ?>"
                                    class="collapse <?= $principle_id === '1' ? 'show' : ''; ?>"
                                    aria-labelledby="principleHeading<?= $principle_id; ?>"
                                    data-parent=".checklist-workspace"
                                >
                                    <div class="principle-body">
                                        <div class="principle-description">
                                            <?= $escape($principle->description); ?>
                                        </div>

                                        <div class="indicator-list">
                                            <?php foreach ($principle_questions as $question) :
                                                $field_name = 'q' . (int) $question->i_no;
                                                $selected_value = ($checklist_record && isset($checklist_record->$field_name)) ? (int) $checklist_record->$field_name : 0;
                                                $selected_label = ($selected_value > 0 && isset($rating_details[$selected_value])) ? $rating_details[$selected_value]['short'] : 'No selection yet';
                                                $selected_hint = ($selected_value > 0 && isset($rating_details[$selected_value]))
                                                    ? $rating_details[$selected_value]['hint']
                                                    : 'Select the level that best matches the current school practice or learning outcome.';
                                            ?>
                                                <div
                                                    class="indicator-card"
                                                    data-indicator-card
                                                    data-default-label="No selection yet"
                                                    data-default-hint="Select the level that best matches the current school practice or learning outcome."
                                                >
                                                    <div class="indicator-top">
                                                        <span class="indicator-number"><?= $escape($question->i_no); ?></span>
                                                        <div class="indicator-copy">
                                                            <h5><?= $escape($question->description); ?></h5>
                                                            <p>Choose the degree of manifestation that best describes the school’s current practice or result for this indicator.</p>
                                                        </div>
                                                        <span class="indicator-status <?= $selected_value > 0 && isset($rating_details[$selected_value]) ? $rating_details[$selected_value]['class'] : ''; ?>" data-indicator-status>
                                                            <i class="mdi mdi-check-decagram-outline"></i>
                                                            <span data-indicator-status-label><?= $escape($selected_label); ?></span>
                                                        </span>
                                                    </div>

                                                    <div class="indicator-rating-head">
                                                        <strong>Select one rating</strong>
                                                        <span>Pick the option that best reflects the current evidence for this indicator.</span>
                                                    </div>

                                                    <div class="indicator-options">
                                                        <?php foreach ($rating_details as $rating_value => $rating) :
                                                            $is_selected = $selected_value === (int) $rating_value;
                                                        ?>
                                                            <label
                                                                class="indicator-choice <?= $rating['class']; ?><?= $is_selected ? ' is-selected' : ''; ?><?= !$can_edit ? ' is-readonly' : ''; ?>"
                                                                data-rating-class="<?= $escape($rating['class']); ?>"
                                                                data-rating-short="<?= $escape($rating['short']); ?>"
                                                                data-rating-hint="<?= $escape($rating['hint']); ?>"
                                                            >
                                                                <input
                                                                    type="radio"
                                                                    name="<?= $escape($field_name); ?>"
                                                                    value="<?= $rating_value; ?>"
                                                                    <?= $is_selected ? 'checked' : ''; ?>
                                                                    <?= !$can_edit ? 'disabled' : ''; ?>
                                                                >
                                                                <span class="indicator-choice-body">
                                                                    <span class="indicator-choice-badge"><?= $escape($rating['label']); ?></span>
                                                                    <span class="indicator-choice-copy">
                                                                        <span class="indicator-choice-label"><?= $escape($rating['short']); ?></span>
                                                                    </span>
                                                                    <span class="indicator-choice-check">
                                                                        <i class="mdi mdi-check-circle"></i>
                                                                    </span>
                                                                </span>
                                                            </label>
                                                        <?php endforeach; ?>
                                                    </div>

                                                    <div class="indicator-choice-summary<?= $selected_value > 0 && isset($rating_details[$selected_value]) ? ' ' . $rating_details[$selected_value]['class'] : ''; ?>" data-indicator-summary>
                                                        <div>
                                                            <strong data-indicator-summary-title><?= $escape($selected_label); ?></strong>
                                                            <span data-indicator-summary-hint><?= $escape($selected_hint); ?></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="workspace-actions" id="checklistActions">
                        <?php if ($can_edit) : ?>
                            <button type="submit" name="<?= $checklist_record ? 'submit_edit' : 'submit'; ?>" class="workspace-button workspace-button-primary">
                                <i class="mdi mdi-content-save-outline"></i>
                                Save Draft
                            </button>
                        <?php endif; ?>

                        <?php if ($can_finalize && $all_answered) : ?>
                            <button type="submit" formaction="<?= base_url(); ?>Pages/sbm_checklist_final" formmethod="post" name="id" value="<?= (int) $checklist_record->id; ?>" onclick="return confirm('Are you sure you want to finalize this checklist? Once finalized, it will be locked until a division reviewer unlocks it.')" class="workspace-button workspace-button-success">
                                <i class="mdi mdi-lock-check-outline"></i>
                                Finalize Checklist
                            </button>
                        <?php elseif ($can_finalize) : ?>
                            <button type="button" class="workspace-button workspace-button-disabled" onclick="alert('Please answer every indicator before finalizing the checklist.')">
                                <i class="mdi mdi-alert-circle-outline"></i>
                                Finalize Checklist
                            </button>
                        <?php elseif ($is_finalized && $is_school_user) : ?>
                            <button type="submit" formaction="<?= base_url(); ?>Pages/sbm_checklist_unlock_request" formmethod="post" name="id" value="<?= (int) $checklist_record->id; ?>" onclick="return confirm('Are you sure you want to request an unlock for this checklist? A notification will be sent to your division reviewer.')" class="workspace-button workspace-button-warning">
                                <i class="mdi mdi-lock-open-variant-outline"></i>
                                Request to Unlock
                            </button>
                        <?php endif; ?>

                        <?php if ($can_unlock) : ?>
                            <button type="submit" formaction="<?= base_url(); ?>Pages/sbm_checklist_unlock" formmethod="post" name="id" value="<?= (int) $checklist_record->id; ?>" onclick="return confirm('Unlock this finalized checklist so the school can edit it again?')" class="workspace-button workspace-button-warning">
                                <i class="mdi mdi-lock-open-variant-outline"></i>
                                Unlock Checklist
                            </button>
                        <?php endif; ?>

                        <?php if ($checklist_record && $is_finalized) : ?>
                            <a href="<?= $checklist_pdf_url; ?>" target="_blank" class="workspace-button workspace-button-secondary">
                                <i class="mdi mdi-file-pdf-box"></i>
                                Download PDF
                            </a>
                        <?php endif; ?>

                        <a href="<?= $dashboard_url; ?>" class="workspace-button workspace-button-secondary">
                            <i class="mdi mdi-view-dashboard-outline"></i>
                            Back to Dashboard
                        </a>
                    </div>

                    <p class="workspace-note">
                        <?php if ($can_edit) : ?>
                            Save Draft keeps the checklist editable. Finalizing the checklist locks the answers until a division reviewer unlocks it.
                        <?php elseif ($is_finalized && $is_school_user) : ?>
                            This checklist is already finalized and locked for editing. If a revision is needed, coordinate with your division reviewer.
                        <?php else : ?>
                            This checklist is currently in read-only mode for review purposes.
                        <?php endif; ?>
                    </p>

                    <div class="pdf-link-panel">
                        <span class="pdf-link-icon">
                            <i class="mdi mdi-link-variant"></i>
                        </span>
                        <div class="pdf-link-copy">
                            <strong>PDF Link</strong>
                            <p>
                                <?= $checklist_record && $is_finalized
                                    ? 'Open or copy this direct link to generate the finalized checklist PDF.'
                                    : 'This direct PDF link becomes active after the checklist is finalized.'; ?>
                            </p>
                            <?php if ($checklist_record && $is_finalized) : ?>
                                <a href="<?= $checklist_pdf_url; ?>" target="_blank" class="pdf-link-url"><?= $escape($checklist_pdf_url); ?></a>
                            <?php else : ?>
                                <span class="pdf-link-url is-disabled"><?= $escape($checklist_pdf_url); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    </form>
                </div>
            </section>
        </div>

        <div class="col-xl-4">
            <section class="workspace-panel">
                <div class="workspace-panel-body">
                    <div class="next-step-card">
                        <span class="next-step-eyebrow">
                            <i class="mdi mdi-compass-outline"></i>
                            <?= $escape($next_step['eyebrow']); ?>
                        </span>
                        <h5><?= $escape($next_step['title']); ?></h5>
                        <p><?= $escape($next_step['description']); ?></p>
                        <div class="next-step-actions">
                            <a href="<?= $next_step['url']; ?>" class="workspace-button workspace-button-primary">
                                <i class="mdi mdi-arrow-right"></i>
                                <?= $escape($next_step['cta']); ?>
                            </a>
                            <a href="<?= $next_step['secondary_url']; ?>" class="workspace-button workspace-button-secondary">
                                <i class="mdi mdi-open-in-new"></i>
                                <?= $escape($next_step['secondary_cta']); ?>
                            </a>
                        </div>
                    </div>

                    <div class="sidebar-card">
                        <h5>Rating Guide</h5>
                        <p>Use the same interpretation throughout the checklist so the assessment remains consistent across all indicators.</p>
                        <div class="rating-legend">
                            <?php foreach ($rating_details as $rating) : ?>
                                <div class="rating-legend-item <?= $rating['class']; ?>">
                                    <strong><?= $escape($rating['short']); ?></strong>
                                    <span><?= $escape($rating['hint']); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="sidebar-card">
                        <h5>Principle Progress</h5>
                        <p>Jump quickly to any principle section and see how much of it already has responses.</p>
                        <div class="principle-links">
                            <?php foreach ($principles as $principle) :
                                $principle_id = isset($principle->id) ? (string) $principle->id : '';
                                $progress = isset($principle_progress[$principle_id]) ? $principle_progress[$principle_id] : array('answered' => 0, 'total' => 0);
                            ?>
                                <a href="#principleCollapse<?= $principle_id; ?>" class="principle-link" data-toggle="collapse" aria-controls="principleCollapse<?= $principle_id; ?>">
                                    <i class="mdi mdi-file-tree-outline"></i>
                                    <?= $escape($principle->indicator); ?>
                                    <span><?= $progress['answered']; ?>/<?= $progress['total']; ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="sidebar-card">
                        <h5>Quick Links</h5>
                        <p>Move between the school’s related modules without returning to the side menu.</p>
                        <div class="quick-link-list">
                            <a href="<?= $profile_url; ?>" class="quick-link">
                                <span><i class="mdi mdi-account-school-outline"></i> View school profile</span>
                                <i class="mdi mdi-arrow-right"></i>
                            </a>
                            <a href="<?= $action_plan_url; ?>" class="quick-link">
                                <span><i class="mdi mdi-clipboard-text-outline"></i> Open action plan</span>
                                <i class="mdi mdi-arrow-right"></i>
                            </a>
                            <a href="<?= $ta_url; ?>" class="quick-link">
                                <span><i class="mdi mdi-wrench-outline"></i> Open TA form</span>
                                <i class="mdi mdi-arrow-right"></i>
                            </a>
                            <a href="<?= $tana_url; ?>" class="quick-link">
                                <span><i class="mdi mdi-chart-line"></i> <?= $is_school_user ? 'Open TANA priorities' : 'Back to school profile'; ?></span>
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

    <div id="checklistFiscalYearModal" class="modal fade dashboard-modal" tabindex="-1" role="dialog" aria-labelledby="checklistFiscalYearModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="checklistFiscalYearModalLabel">Change Fiscal Year</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="<?= base_url('Pages/change_fy'); ?>" method="post">
                        <input type="hidden" name="<?= html_escape($this->security->get_csrf_token_name()); ?>" value="<?= html_escape($this->security->get_csrf_hash()); ?>">
                        <label for="checklistFiscalYear">Fiscal Year</label>
                        <select id="checklistFiscalYear" name="new_fy" class="form-control" onchange="this.form.submit()">
                            <?php for ($year = 2023; $year <= 2030; $year++) : ?>
                                <option value="<?= $year; ?>" <?= (string) $this->session->fy === (string) $year ? 'selected' : ''; ?>>
                                    <?= $year; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    var cards = document.querySelectorAll("[data-indicator-card]");
    var ratingClasses = ["rating-na", "rating-one", "rating-two", "rating-three", "rating-four"];

    function syncCard(card) {
        var status = card.querySelector("[data-indicator-status]");
        var statusLabel = card.querySelector("[data-indicator-status-label]");
        var summary = card.querySelector("[data-indicator-summary]");
        var summaryTitle = card.querySelector("[data-indicator-summary-title]");
        var summaryHint = card.querySelector("[data-indicator-summary-hint]");
        var labels = card.querySelectorAll(".indicator-choice");
        var checkedInput = card.querySelector(".indicator-choice input:checked");
        var defaultLabel = card.getAttribute("data-default-label") || "No selection yet";
        var defaultHint = card.getAttribute("data-default-hint") || "Select the level that best matches the current school practice or learning outcome.";
        var activeLabel = checkedInput ? checkedInput.closest(".indicator-choice") : null;
        var activeShort = defaultLabel;
        var activeHint = defaultHint;
        var activeClass = "";

        labels.forEach(function (label) {
            var input = label.querySelector("input");
            var isChecked = !!input && input.checked;
            label.classList.toggle("is-selected", isChecked);
        });

        if (activeLabel) {
            activeShort = activeLabel.getAttribute("data-rating-short") || defaultLabel;
            activeHint = activeLabel.getAttribute("data-rating-hint") || defaultHint;
            activeClass = activeLabel.getAttribute("data-rating-class") || "";
        }

        if (statusLabel) {
            statusLabel.textContent = activeShort;
        }

        if (summaryTitle) {
            summaryTitle.textContent = activeShort;
        }

        if (summaryHint) {
            summaryHint.textContent = activeHint;
        }

        if (status) {
            ratingClasses.forEach(function (className) {
                status.classList.remove(className);
            });
            if (activeClass) {
                status.classList.add(activeClass);
            }
        }

        if (summary) {
            ratingClasses.forEach(function (className) {
                summary.classList.remove(className);
            });
            if (activeClass) {
                summary.classList.add(activeClass);
            }
        }
    }

    cards.forEach(function (card) {
        syncCard(card);
        card.querySelectorAll(".indicator-choice input").forEach(function (input) {
            input.addEventListener("change", function () {
                syncCard(card);
            });
        });
    });
});
</script>
