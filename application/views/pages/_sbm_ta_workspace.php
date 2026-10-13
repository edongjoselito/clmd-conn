<?php
$ta_record = isset($sbmc) && $sbmc ? $sbmc : null;
$principles = isset($sbm) && is_array($sbm) ? $sbm : array();
$indicators = isset($sbm_sub) && is_array($sbm_sub) ? $sbm_sub : array();
$school_id = (string) $this->session->username;
$school = $this->Common->one_cond_row('schools', 'schoolID', $school_id);
$division = ($school && !empty($school->division_id)) ? $this->Page_model->one_cond_row('division', 'id', $school->division_id) : null;
$district = ($school && !empty($school->district_id)) ? $this->Page_model->one_cond_row('district', 'id', $school->district_id) : null;
$checklist_record = $this->Common->two_cond_row('sbm', 'school_id', $school_id, 'fy', $this->session->fy);
$position = strtolower(trim((string) $this->session->position));
$is_school_user = $position === 'school';
$is_division_user = $position === 'division';
$is_finalized = $ta_record && isset($ta_record->stat) && (int) $ta_record->stat === 1;
$is_locked_for_user = $is_finalized;
$can_edit = !$is_locked_for_user;
$validation_markup = validation_errors();
$form_action = $ta_record ? 'Pages/tapr_form_update' : 'Pages/tapr_form';

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
$fiscal_year_label = 'Fiscal Year ' . $this->session->fy;

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
    : 'TAPR';

$manifestation_details = array(
    0 => array(
        'short' => 'Checklist Pending',
        'hint' => 'No checklist value is available yet. You may draft either narrative field while waiting for the assessment basis.',
        'class' => 'manifestation-pending',
        'primary' => 'either',
    ),
    1 => array(
        'short' => 'Not Yet Manifested',
        'hint' => 'Describe the concern, gap, or bottleneck that needs immediate technical assistance.',
        'class' => 'manifestation-one',
        'primary' => 'concern',
    ),
    2 => array(
        'short' => 'Rarely Manifested',
        'hint' => 'Capture the issue that keeps the indicator from becoming consistent and identify the support needed.',
        'class' => 'manifestation-two',
        'primary' => 'concern',
    ),
    3 => array(
        'short' => 'Frequently Manifested',
        'hint' => 'Note the remaining gaps that still prevent the indicator from being fully sustained at all times.',
        'class' => 'manifestation-three',
        'primary' => 'concern',
    ),
    4 => array(
        'short' => 'Always Manifested',
        'hint' => 'Document the enabling practice or condition that keeps this strength consistently present.',
        'class' => 'manifestation-four',
        'primary' => 'facilitating',
    ),
    5 => array(
        'short' => 'No Data',
        'hint' => 'Record the missing basis, unresolved issue, or evidence still needed for this indicator.',
        'class' => 'manifestation-na',
        'primary' => 'concern',
    ),
);

$category_labels = array(
    1 => 'Technical',
    2 => 'Institutional',
    3 => 'Financial',
    4 => 'Political',
    5 => 'Infrastructure',
    6 => 'Social',
    7 => 'Gender',
);

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

$manifestation_counts = array(0 => 0, 1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0);
$principle_progress = array();
$total_questions = count($indicators);
$guided_count = 0;
$touched_count = 0;
$concern_count = 0;
$facilitating_count = 0;
$category_count = 0;
$commitment_count = 0;
$always_manifested_count = 0;
$issue_track_count = 0;

foreach ($principles as $principle) {
    $principle_id = isset($principle->id) ? (string) $principle->id : '';
    $principle_questions = isset($questions_by_principle[$principle_id]) ? $questions_by_principle[$principle_id] : array();
    $principle_total = count($principle_questions);
    $principle_touched = 0;
    $principle_guided = 0;

    foreach ($principle_questions as $question) {
        $index = (int) $question->i_no;
        $checklist_field = 'q' . $index;
        $concern_field = 'q' . $index;
        $facilitating_field = 'qq' . $index;
        $category_field = 'a' . $index;
        $commitment_field = 'f' . $index;

        $manifestation_value = ($checklist_record && isset($checklist_record->$checklist_field))
            ? (int) $checklist_record->$checklist_field
            : 0;
        if (!isset($manifestation_details[$manifestation_value])) {
            $manifestation_value = 0;
        }
        $manifestation_counts[$manifestation_value]++;

        if ($manifestation_value === 4) {
            $always_manifested_count++;
        } else {
            $issue_track_count++;
        }

        $concern_value = ($ta_record && isset($ta_record->$concern_field)) ? trim((string) $ta_record->$concern_field) : '';
        $facilitating_value = ($ta_record && isset($ta_record->$facilitating_field)) ? trim((string) $ta_record->$facilitating_field) : '';
        $category_value = ($ta_record && isset($ta_record->$category_field)) ? trim((string) $ta_record->$category_field) : '';
        $commitment_value = ($ta_record && isset($ta_record->$commitment_field)) ? trim((string) $ta_record->$commitment_field) : '';

        if ($concern_value !== '') {
            $concern_count++;
        }
        if ($facilitating_value !== '') {
            $facilitating_count++;
        }
        if ($category_value !== '') {
            $category_count++;
        }
        if ($commitment_value !== '') {
            $commitment_count++;
        }

        $has_touched = $concern_value !== '' || $facilitating_value !== '' || $category_value !== '' || $commitment_value !== '';
        if ($has_touched) {
            $touched_count++;
            $principle_touched++;
        }

        $primary_mode = $manifestation_details[$manifestation_value]['primary'];
        $is_guided_complete = false;
        if ($primary_mode === 'concern') {
            $is_guided_complete = $concern_value !== '';
        } elseif ($primary_mode === 'facilitating') {
            $is_guided_complete = $facilitating_value !== '';
        } else {
            $is_guided_complete = $concern_value !== '' || $facilitating_value !== '';
        }

        if ($is_guided_complete) {
            $guided_count++;
            $principle_guided++;
        }
    }

    $principle_progress[$principle_id] = array(
        'touched' => $principle_touched,
        'guided' => $principle_guided,
        'total' => $principle_total,
        'percent' => $principle_total > 0 ? ($principle_guided / $principle_total) * 100 : 0,
    );
}

$guided_rate = $total_questions > 0 ? ($guided_count / $total_questions) * 100 : 0;
$touch_rate = $total_questions > 0 ? ($touched_count / $total_questions) * 100 : 0;
$status_label = !$ta_record ? 'Not started' : ($is_locked_for_user ? 'Finalized' : 'Draft saved');
$dashboard_url = base_url();
$profile_url = base_url() . 'school/' . rawurlencode($school_id);
$checklist_url = base_url() . 'Pages/sbm_checklist';
$action_plan_url = base_url() . 'Pages/sbm_action_plan';
$tana_url = base_url() . 'Pages/tana_summary';

$next_step = array(
    'eyebrow' => 'Start here',
    'title' => 'Begin drafting the TA provision report',
    'description' => 'Use the checklist manifestation level as the basis for each indicator, then capture the concern or enabling factor that best explains the current school situation.',
    'url' => '#taWorkspace',
    'cta' => 'Open the workspace',
    'secondary_url' => $checklist_url,
    'secondary_cta' => 'Review checklist',
);

if (!$checklist_record) {
    $next_step = array(
        'eyebrow' => 'Checklist basis',
        'title' => 'Review or complete the checklist first',
        'description' => 'The TA form works best when each indicator already has a manifestation rating. You can still draft notes now, but the checklist will give clearer guidance on which narrative field to prioritize.',
        'url' => $checklist_url,
        'cta' => 'Open checklist',
        'secondary_url' => '#taWorkspace',
        'secondary_cta' => 'Draft anyway',
    );
} elseif ($ta_record && !$is_finalized) {
    $next_step = array(
        'eyebrow' => 'Keep moving',
        'title' => 'Complete the remaining guided narratives',
        'description' => 'You already have guided entries for ' . $guided_count . ' of ' . $total_questions . ' indicators. Review the unresolved items, add commitments, and finalize only when the draft is ready.',
        'url' => '#taActions',
        'cta' => 'Go to actions',
        'secondary_url' => $tana_url,
        'secondary_cta' => 'Open TANA priorities',
    );
} elseif ($is_locked_for_user) {
    $next_step = array(
        'eyebrow' => 'Submission complete',
        'title' => 'Use the finalized TA report to support planning',
        'description' => 'This TA form is already locked for the current fiscal year. Continue with TANA prioritization and the school action plan, or coordinate with the division reviewer if revisions are needed.',
        'url' => $tana_url,
        'cta' => 'Open TANA priorities',
        'secondary_url' => $action_plan_url,
        'secondary_cta' => 'Open action plan',
    );
}
?>

<style>
    .ta-workspace-page {
        --ta-primary: #7f1d1d;
        --ta-primary-light: #b83a4b;
        --ta-accent: #d6a84b;
        --ta-ink: #172033;
        --ta-muted: #687386;
        --ta-border: #e4e9f0;
        --ta-surface: #f6f8fb;
        --ta-success: #15803d;
        --ta-warning: #b45309;
        --ta-danger: #b91c1c;
        --ta-shadow: 0 24px 60px rgba(15, 23, 42, 0.08);
        color: var(--ta-ink);
        padding-bottom: 2rem;
    }

    .ta-workspace-page .ta-hero {
        position: relative;
        overflow: hidden;
        display: grid;
        grid-template-columns: minmax(0, 1.45fr) minmax(280px, 0.75fr);
        gap: 1.5rem;
        margin-top: 1rem;
        padding: 2rem;
        border-radius: 28px;
        background:
            radial-gradient(circle at top right, rgba(214, 168, 75, 0.28), transparent 34%),
            linear-gradient(135deg, #fff9ef 0%, #fff 40%, #f7f2e7 100%);
        border: 1px solid rgba(214, 168, 75, 0.25);
        box-shadow: var(--ta-shadow);
        margin-bottom: 1.5rem;
    }

    .ta-workspace-page .ta-hero::after {
        content: "";
        position: absolute;
        inset: auto -80px -120px auto;
        width: 260px;
        height: 260px;
        border-radius: 50%;
        background: rgba(127, 29, 29, 0.08);
        pointer-events: none;
    }

    .ta-workspace-page .hero-copy,
    .ta-workspace-page .hero-side {
        position: relative;
        z-index: 1;
    }

    .ta-workspace-page .hero-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.45rem 0.85rem;
        border-radius: 999px;
        background: rgba(127, 29, 29, 0.1);
        color: var(--ta-primary);
        font-size: 0.82rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }

    .ta-workspace-page .ta-hero h1 {
        margin: 1rem 0 0.75rem;
        font-size: clamp(2rem, 3.3vw, 2.8rem);
        line-height: 1.08;
        color: var(--ta-primary);
    }

    .ta-workspace-page .ta-hero p {
        margin: 0;
        max-width: 760px;
        font-size: 1rem;
        line-height: 1.75;
        color: var(--ta-muted);
    }

    .ta-workspace-page .hero-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.7rem;
        margin-top: 1.1rem;
    }

    .ta-workspace-page .hero-meta span {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.55rem 0.9rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.72);
        border: 1px solid rgba(127, 29, 29, 0.08);
        color: var(--ta-ink);
        font-size: 0.9rem;
    }

    .ta-workspace-page .hero-side {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        justify-content: space-between;
    }

    .ta-workspace-page .ta-badge {
        align-self: flex-end;
        width: 140px;
        min-height: 140px;
        padding: 1.2rem;
        border-radius: 26px;
        background: linear-gradient(160deg, var(--ta-primary), #5f1515 100%);
        color: #fff;
        box-shadow: 0 22px 44px rgba(127, 29, 29, 0.22);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .ta-workspace-page .ta-badge small {
        font-size: 0.72rem;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        opacity: 0.8;
    }

    .ta-workspace-page .ta-badge strong {
        font-size: 2rem;
        letter-spacing: 0.08em;
        line-height: 1;
    }

    .ta-workspace-page .hero-actions {
        display: grid;
        gap: 0.75rem;
        width: 100%;
        max-width: 260px;
        margin-left: auto;
    }

    .ta-workspace-page .hero-button,
    .ta-workspace-page .workspace-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.55rem;
        padding: 0.9rem 1.15rem;
        border-radius: 14px;
        border: 1px solid transparent;
        font-weight: 700;
        text-decoration: none;
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
    }

    .ta-workspace-page .hero-button:hover,
    .ta-workspace-page .workspace-button:hover {
        transform: translateY(-1px);
        text-decoration: none;
    }

    .ta-workspace-page .hero-button-primary,
    .ta-workspace-page .workspace-button-primary {
        background: var(--ta-primary);
        color: #fff;
        box-shadow: 0 16px 30px rgba(127, 29, 29, 0.18);
    }

    .ta-workspace-page .hero-button-secondary,
    .ta-workspace-page .workspace-button-secondary {
        background: #fff;
        border-color: rgba(127, 29, 29, 0.14);
        color: var(--ta-primary);
    }

    .ta-workspace-page .hero-button-tertiary,
    .ta-workspace-page .workspace-button-tertiary {
        background: rgba(255, 255, 255, 0.82);
        border-color: rgba(214, 168, 75, 0.36);
        color: var(--ta-ink);
    }

    .ta-workspace-page .workspace-button-success {
        background: var(--ta-success);
        color: #fff;
        box-shadow: 0 14px 28px rgba(21, 128, 61, 0.18);
    }

    .ta-workspace-page .workspace-button-disabled {
        background: #eef2f7;
        color: #8b96a8;
        cursor: pointer;
    }

    .ta-workspace-page .ta-stats {
        margin-bottom: 1.5rem;
    }

    .ta-workspace-page .ta-stat-card {
        height: 100%;
        border-radius: 22px;
        padding: 1.3rem;
        background: #fff;
        border: 1px solid var(--ta-border);
        box-shadow: 0 14px 36px rgba(15, 23, 42, 0.06);
    }

    .ta-workspace-page .ta-stat-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
    }

    .ta-workspace-page .ta-stat-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 3rem;
        height: 3rem;
        border-radius: 16px;
        background: rgba(127, 29, 29, 0.1);
        color: var(--ta-primary);
        font-size: 1.3rem;
        flex-shrink: 0;
    }

    .ta-workspace-page .ta-stat-card small {
        display: block;
        margin-bottom: 0.35rem;
        color: var(--ta-muted);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-size: 0.75rem;
        font-weight: 700;
    }

    .ta-workspace-page .ta-stat-card h3 {
        margin: 0;
        font-size: 1.65rem;
        color: var(--ta-ink);
    }

    .ta-workspace-page .ta-stat-card p {
        margin: 0.55rem 0 0;
        color: var(--ta-muted);
        line-height: 1.6;
        font-size: 0.94rem;
    }

    .ta-workspace-page .mini-progress {
        width: 100%;
        height: 0.48rem;
        border-radius: 999px;
        background: #ebeff5;
        overflow: hidden;
        margin-top: 1rem;
    }

    .ta-workspace-page .mini-progress span {
        display: block;
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg, var(--ta-primary), var(--ta-accent));
    }

    .ta-workspace-page .workspace-panel {
        background: #fff;
        border: 1px solid var(--ta-border);
        border-radius: 24px;
        box-shadow: 0 18px 44px rgba(15, 23, 42, 0.07);
        overflow: hidden;
    }

    .ta-workspace-page .workspace-panel-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        padding: 1.4rem 1.5rem 0;
    }

    .ta-workspace-page .workspace-panel-header h4 {
        margin: 0;
        color: var(--ta-ink);
        font-size: 1.2rem;
    }

    .ta-workspace-page .workspace-panel-header p {
        margin: 0.35rem 0 0;
        color: var(--ta-muted);
        line-height: 1.6;
    }

    .ta-workspace-page .workspace-panel-header small {
        color: var(--ta-primary);
        font-weight: 700;
        white-space: nowrap;
    }

    .ta-workspace-page .workspace-panel-body {
        padding: 1.5rem;
    }

    .ta-workspace-page .info-alert {
        display: flex;
        align-items: flex-start;
        gap: 0.9rem;
        padding: 1rem 1.1rem;
        border-radius: 18px;
        margin-bottom: 1.5rem;
        border: 1px solid rgba(214, 168, 75, 0.3);
        background: linear-gradient(135deg, rgba(255, 250, 235, 0.96), rgba(255, 255, 255, 0.96));
    }

    .ta-workspace-page .info-alert i {
        font-size: 1.35rem;
        color: var(--ta-accent);
        margin-top: 0.1rem;
    }

    .ta-workspace-page .info-alert strong {
        display: block;
        margin-bottom: 0.25rem;
        color: var(--ta-ink);
    }

    .ta-workspace-page .info-alert p {
        margin: 0;
        color: var(--ta-muted);
        line-height: 1.6;
    }

    .ta-workspace-page .principle-links {
        display: flex;
        flex-wrap: wrap;
        gap: 0.7rem;
    }

    .ta-workspace-page .principle-link {
        display: inline-flex;
        align-items: center;
        gap: 0.55rem;
        padding: 0.72rem 0.95rem;
        border-radius: 999px;
        background: var(--ta-surface);
        border: 1px solid var(--ta-border);
        color: var(--ta-ink);
        font-weight: 600;
        text-decoration: none;
    }

    .ta-workspace-page .principle-link span {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 2.1rem;
        padding: 0.2rem 0.55rem;
        border-radius: 999px;
        background: rgba(127, 29, 29, 0.08);
        color: var(--ta-primary);
        font-size: 0.78rem;
        font-weight: 700;
    }

    .ta-workspace-page .principle-link:hover {
        text-decoration: none;
        border-color: rgba(127, 29, 29, 0.24);
        color: var(--ta-primary);
    }

    .ta-workspace-page .ta-principle-list {
        display: grid;
        gap: 1rem;
        margin-top: 1rem;
    }

    .ta-workspace-page .principle-card {
        border: 1px solid var(--ta-border);
        border-radius: 22px;
        background: #fff;
        overflow: hidden;
    }

    .ta-workspace-page .principle-header {
        background: linear-gradient(180deg, #fff, #fbfcfe);
    }

    .ta-workspace-page .principle-toggle {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1.15rem 1.25rem;
        color: inherit;
        text-decoration: none;
    }

    .ta-workspace-page .principle-toggle:hover {
        text-decoration: none;
        color: inherit;
    }

    .ta-workspace-page .principle-main {
        display: grid;
        gap: 0.3rem;
    }

    .ta-workspace-page .principle-main strong {
        color: var(--ta-ink);
        font-size: 1rem;
    }

    .ta-workspace-page .principle-main span {
        color: var(--ta-muted);
        line-height: 1.55;
        font-size: 0.92rem;
    }

    .ta-workspace-page .principle-meta {
        display: flex;
        align-items: center;
        gap: 0.7rem;
        flex-shrink: 0;
    }

    .ta-workspace-page .principle-progress-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.45rem 0.75rem;
        border-radius: 999px;
        background: rgba(214, 168, 75, 0.14);
        color: #8a5b0b;
        font-weight: 700;
        font-size: 0.82rem;
    }

    .ta-workspace-page .principle-icon {
        font-size: 1.2rem;
        color: var(--ta-primary);
    }

    .ta-workspace-page .principle-body {
        padding: 1.25rem;
        background: #fff;
    }

    .ta-workspace-page .indicator-list {
        display: grid;
        gap: 1rem;
    }

    .ta-workspace-page .indicator-card {
        border: 1px solid var(--ta-border);
        border-radius: 20px;
        padding: 1.1rem;
        background: linear-gradient(180deg, #fff, #fcfdff);
    }

    .ta-workspace-page .indicator-top {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
    }

    .ta-workspace-page .indicator-number {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.6rem;
        height: 2.6rem;
        border-radius: 16px;
        background: rgba(127, 29, 29, 0.1);
        color: var(--ta-primary);
        font-weight: 800;
        flex-shrink: 0;
    }

    .ta-workspace-page .indicator-copy {
        flex: 1;
        min-width: 0;
    }

    .ta-workspace-page .indicator-copy h5 {
        margin: 0;
        color: var(--ta-ink);
        font-size: 1rem;
        line-height: 1.55;
    }

    .ta-workspace-page .indicator-copy p {
        margin: 0.35rem 0 0;
        color: var(--ta-muted);
        line-height: 1.65;
        font-size: 0.9rem;
    }

    .ta-workspace-page .indicator-status {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.5rem 0.8rem;
        border-radius: 999px;
        font-size: 0.82rem;
        font-weight: 700;
        white-space: nowrap;
        flex-shrink: 0;
    }

    .ta-workspace-page .manifestation-pending {
        background: #eef2f7;
        color: #526075;
    }

    .ta-workspace-page .manifestation-one {
        background: rgba(185, 28, 28, 0.12);
        color: var(--ta-danger);
    }

    .ta-workspace-page .manifestation-two {
        background: rgba(180, 83, 9, 0.12);
        color: var(--ta-warning);
    }

    .ta-workspace-page .manifestation-three {
        background: rgba(214, 168, 75, 0.18);
        color: #8a5b0b;
    }

    .ta-workspace-page .manifestation-four {
        background: rgba(21, 128, 61, 0.12);
        color: var(--ta-success);
    }

    .ta-workspace-page .manifestation-na {
        background: rgba(100, 116, 139, 0.14);
        color: #475569;
    }

    .ta-workspace-page .guidance-strip {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        padding: 0.85rem 0.95rem;
        border-radius: 16px;
        background: var(--ta-surface);
        color: var(--ta-muted);
        margin: 1rem 0;
        line-height: 1.6;
        font-size: 0.9rem;
    }

    .ta-workspace-page .guidance-strip strong {
        color: var(--ta-ink);
    }

    .ta-workspace-page .guidance-strip i {
        color: var(--ta-primary);
        font-size: 1rem;
    }

    .ta-workspace-page .input-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
        margin-top: 0.9rem;
    }

    .ta-workspace-page .input-grid-secondary {
        display: grid;
        grid-template-columns: minmax(230px, 0.75fr) minmax(0, 1.25fr);
        gap: 1rem;
        margin-top: 1rem;
    }

    .ta-workspace-page .field-card {
        border: 1px solid var(--ta-border);
        border-radius: 16px;
        padding: 0.95rem;
        background: #fff;
    }

    .ta-workspace-page .field-card h6 {
        margin: 0;
        color: var(--ta-ink);
        font-size: 0.94rem;
    }

    .ta-workspace-page .field-card p {
        margin: 0.35rem 0 0.8rem;
        color: var(--ta-muted);
        line-height: 1.55;
        font-size: 0.86rem;
    }

    .ta-workspace-page .ta-input,
    .ta-workspace-page .ta-select {
        width: 100%;
        border: 1px solid #d7deea;
        border-radius: 14px;
        background: #fff;
        color: var(--ta-ink);
        font-size: 0.94rem;
        padding: 0.82rem 0.9rem;
        transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
    }

    .ta-workspace-page .ta-input:focus,
    .ta-workspace-page .ta-select:focus {
        outline: none;
        border-color: rgba(127, 29, 29, 0.35);
        box-shadow: 0 0 0 4px rgba(127, 29, 29, 0.08);
    }

    .ta-workspace-page textarea.ta-input {
        min-height: 132px;
        resize: vertical;
    }

    .ta-workspace-page .ta-input.is-readonly,
    .ta-workspace-page .ta-select.is-readonly,
    .ta-workspace-page .ta-input[readonly],
    .ta-workspace-page .ta-select[disabled] {
        background: #f4f6fa;
        color: #748092;
        cursor: default;
    }

    .ta-workspace-page .workspace-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.85rem;
        margin-top: 1.4rem;
    }

    .ta-workspace-page .workspace-note {
        margin: 1rem 0 0;
        color: var(--ta-muted);
        line-height: 1.7;
    }

    .ta-workspace-page .next-step-card {
        padding: 1.25rem;
        border-radius: 20px;
        background: linear-gradient(180deg, rgba(127, 29, 29, 0.96), rgba(95, 21, 21, 0.98));
        color: #fff;
        box-shadow: 0 18px 40px rgba(95, 21, 21, 0.24);
    }

    .ta-workspace-page .next-step-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.42rem 0.72rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.12);
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .ta-workspace-page .next-step-card h5 {
        margin: 0.95rem 0 0.45rem;
        font-size: 1.3rem;
        line-height: 1.3;
        color: #fff;
    }

    .ta-workspace-page .next-step-card p {
        margin: 0;
        color: rgba(255, 255, 255, 0.82);
        line-height: 1.7;
    }

    .ta-workspace-page .next-step-actions {
        display: grid;
        gap: 0.7rem;
        margin-top: 1rem;
    }

    .ta-workspace-page .next-step-card .workspace-button-primary {
        background: #fff;
        color: var(--ta-primary);
    }

    .ta-workspace-page .next-step-card .workspace-button-secondary {
        background: transparent;
        border-color: rgba(255, 255, 255, 0.18);
        color: #fff;
    }

    .ta-workspace-page .sidebar-card {
        margin-top: 1rem;
        padding: 1.15rem;
        border: 1px solid var(--ta-border);
        border-radius: 20px;
        background: #fff;
    }

    .ta-workspace-page .sidebar-card h5 {
        margin: 0;
        color: var(--ta-ink);
        font-size: 1.02rem;
    }

    .ta-workspace-page .sidebar-card p {
        margin: 0.4rem 0 0.95rem;
        color: var(--ta-muted);
        line-height: 1.65;
    }

    .ta-workspace-page .manifestation-summary {
        display: grid;
        gap: 0.7rem;
    }

    .ta-workspace-page .manifestation-summary-item {
        padding: 0.8rem 0.9rem;
        border-radius: 16px;
        border: 1px solid var(--ta-border);
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
    }

    .ta-workspace-page .manifestation-summary-item strong {
        display: block;
        margin-bottom: 0.2rem;
        font-size: 0.92rem;
        color: var(--ta-ink);
    }

    .ta-workspace-page .manifestation-summary-item span {
        color: var(--ta-muted);
        font-size: 0.84rem;
    }

    .ta-workspace-page .manifestation-summary-item em {
        min-width: 2.6rem;
        text-align: center;
        padding: 0.35rem 0.65rem;
        border-radius: 999px;
        font-style: normal;
        font-weight: 700;
    }

    .ta-workspace-page .quick-link-list {
        display: grid;
        gap: 0.75rem;
    }

    .ta-workspace-page .quick-link {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.95rem 1rem;
        border-radius: 16px;
        background: var(--ta-surface);
        border: 1px solid var(--ta-border);
        color: var(--ta-ink);
        font-weight: 600;
        text-decoration: none;
    }

    .ta-workspace-page .quick-link:hover {
        text-decoration: none;
        color: var(--ta-primary);
        border-color: rgba(127, 29, 29, 0.18);
    }

    .ta-workspace-page .empty-state {
        padding: 2rem 1.5rem;
        text-align: center;
        border: 1px dashed rgba(127, 29, 29, 0.2);
        border-radius: 20px;
        background: rgba(246, 248, 251, 0.88);
        color: var(--ta-muted);
    }

    .ta-workspace-page .empty-state i {
        display: inline-flex;
        margin-bottom: 0.75rem;
        font-size: 2rem;
        color: var(--ta-primary);
    }

    .ta-workspace-page .empty-state strong {
        display: block;
        margin-bottom: 0.35rem;
        color: var(--ta-ink);
        font-size: 1.05rem;
    }

    .ta-workspace-page .dashboard-modal .modal-content {
        border: 0;
        border-radius: 22px;
        overflow: hidden;
    }

    .ta-workspace-page .dashboard-modal .modal-header {
        background: linear-gradient(135deg, var(--ta-primary), #5f1515);
        color: #fff;
        border-bottom: 0;
    }

    .ta-workspace-page .dashboard-modal .modal-body {
        padding: 1.25rem;
    }

    .ta-workspace-page .dashboard-modal label {
        display: block;
        margin-bottom: 0.45rem;
        font-weight: 700;
        color: var(--ta-ink);
    }

    @media (max-width: 1199.98px) {
        .ta-workspace-page .ta-hero {
            grid-template-columns: 1fr;
        }

        .ta-workspace-page .hero-actions {
            max-width: 100%;
            margin-left: 0;
        }

        .ta-workspace-page .ta-badge {
            align-self: flex-start;
        }
    }

    @media (max-width: 991.98px) {
        .ta-workspace-page .input-grid,
        .ta-workspace-page .input-grid-secondary {
            grid-template-columns: 1fr;
        }

        .ta-workspace-page .indicator-top {
            flex-direction: column;
        }

        .ta-workspace-page .indicator-status {
            white-space: normal;
        }
    }

    @media (max-width: 767.98px) {
        .ta-workspace-page .ta-hero,
        .ta-workspace-page .workspace-panel-body,
        .ta-workspace-page .workspace-panel-header,
        .ta-workspace-page .ta-stat-card {
            padding-left: 1rem;
            padding-right: 1rem;
        }

        .ta-workspace-page .workspace-panel-header {
            flex-direction: column;
        }

        .ta-workspace-page .workspace-actions,
        .ta-workspace-page .hero-actions,
        .ta-workspace-page .next-step-actions {
            grid-template-columns: 1fr;
        }

        .ta-workspace-page .hero-button,
        .ta-workspace-page .workspace-button,
        .ta-workspace-page .quick-link {
            width: 100%;
        }

        .ta-workspace-page .principle-toggle {
            flex-direction: column;
            align-items: flex-start;
        }

        .ta-workspace-page .principle-meta {
            width: 100%;
            justify-content: space-between;
        }
    }
</style>

<div class="ta-workspace-page">
    <div class="ta-hero">
        <div class="hero-copy">
            <span class="hero-eyebrow">
                <i class="mdi mdi-file-document-edit-outline"></i>
                TA Provision Report Workspace
            </span>
            <h1>Technical Assistance Provision Report Form</h1>
            <p>
                Capture the school narrative behind each checklist indicator by documenting concerns, enabling factors, category tags, and proposed commitments for <?= $escape($fiscal_year_label); ?>.
            </p>
        </div>

        <div class="hero-side">
            <div class="hero-actions">
                <a href="<?= $profile_url; ?>" class="hero-button hero-button-primary">
                    <i class="mdi mdi-account-school-outline"></i>
                    View Profile
                </a>
                <a href="#" class="hero-button hero-button-secondary" data-toggle="modal" data-target="#taFiscalYearModal">
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

    <?php if (!$checklist_record) : ?>
        <div class="info-alert">
            <i class="mdi mdi-alert-circle-outline"></i>
            <div>
                <strong>Checklist basis not found yet for this fiscal year</strong>
                <p>The TA form will still accept drafts, but the manifestation-based guidance becomes more precise after the SBM checklist has been completed or updated.</p>
            </div>
        </div>
    <?php elseif ($is_locked_for_user) : ?>
        <div class="info-alert">
            <i class="mdi mdi-lock-check-outline"></i>
            <div>
                <strong>This TA report is finalized</strong>
                <p>The current submission is locked for editing. If changes are needed, coordinate with the division reviewer so the report can be unlocked first.</p>
            </div>
        </div>
    <?php endif; ?>

    <div class="row ta-stats">
        <div class="col-12 col-md-4 col-lg-4 col-xl-4">
            <div class="ta-stat-card">
                <div class="ta-stat-top">
                    <div>
                        <small>Guided Entries</small>
                        <h3><?= $guided_count; ?>/<?= $total_questions; ?></h3>
                        <p>Indicators with the primary narrative field already filled based on the linked manifestation rating.</p>
                    </div>
                    <span class="ta-stat-icon"><i class="mdi mdi-text-box-check-outline"></i></span>
                </div>
                <div class="mini-progress"><span style="width: <?= min(100, max(0, $guided_rate)); ?>%;"></span></div>
            </div>
        </div>

        <div class="col-12 col-md-4 col-lg-4 col-xl-4">
            <div class="ta-stat-card">
                <div class="ta-stat-top">
                    <div>
                        <small>Indicators Touched</small>
                        <h3><?= $touched_count; ?>/<?= $total_questions; ?></h3>
                        <p>Indicators with at least one note, category tag, or commitment already encoded in the TA draft.</p>
                    </div>
                    <span class="ta-stat-icon"><i class="mdi mdi-file-document-multiple-outline"></i></span>
                </div>
                <div class="mini-progress"><span style="width: <?= min(100, max(0, $touch_rate)); ?>%;"></span></div>
            </div>
        </div>

        <div class="col-12 col-md-4 col-lg-4 col-xl-4">
            <div class="ta-stat-card">
                <div class="ta-stat-top">
                    <div>
                        <small>Commitments Logged</small>
                        <h3><?= $commitment_count; ?>/<?= $total_questions; ?></h3>
                        <p><?= $category_count; ?> category tag(s) and <?= $concern_count; ?> concern note(s) are already captured across the report.</p>
                    </div>
                    <span class="ta-stat-icon"><i class="mdi mdi-clipboard-text-clock-outline"></i></span>
                </div>
                <div class="mini-progress"><span style="width: <?= $total_questions > 0 ? min(100, ($commitment_count / $total_questions) * 100) : 0; ?>%;"></span></div>
            </div>
        </div>

    </div>

    <div class="row">
        <div class="col-xl-8">
            <section class="workspace-panel">
                <div class="workspace-panel-header">
                    <div>
                        <h4>TA Narrative Workspace</h4>
                        <p>Each indicator pulls its manifestation basis from the checklist. Use the suggested primary field, tag the concern category, and add a concrete commitment or resolution.</p>
                    </div>
                    <small><?= $escape($fiscal_year_label); ?></small>
                </div>
                <div class="workspace-panel-body" id="taWorkspace">
                    <?= form_open($form_action, array('class' => 'ta-workspace-form')); ?>
                    <?php if (!$ta_record) : ?>
                        <input type="hidden" name="district" value="<?= $escape($this->session->district); ?>">
                    <?php endif; ?>
                    <?php if ($ta_record) : ?>
                        <input type="hidden" name="id" value="<?= $escape($ta_record->id); ?>">
                    <?php endif; ?>

                    <div class="principle-links">
                        <?php foreach ($principles as $principle) :
                            $principle_id = isset($principle->id) ? (string) $principle->id : '';
                            $progress = isset($principle_progress[$principle_id]) ? $principle_progress[$principle_id] : array('guided' => 0, 'total' => 0);
                        ?>
                            <a href="#taPrincipleCollapse<?= $principle_id; ?>" class="principle-link" data-toggle="collapse" aria-controls="taPrincipleCollapse<?= $principle_id; ?>">
                                <i class="mdi mdi-view-day-outline"></i>
                                <?= $escape($principle->indicator); ?>
                                <span><?= $progress['guided']; ?>/<?= $progress['total']; ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>

                    <div class="ta-principle-list">
                        <?php if (empty($principles)) : ?>
                            <div class="empty-state">
                                <i class="mdi mdi-file-document-alert-outline"></i>
                                <strong>No TA principles are available</strong>
                                <p>The form cannot be rendered because the SBM principle list is currently empty.</p>
                            </div>
                        <?php endif; ?>

                        <?php foreach ($principles as $principle) :
                            $principle_id = isset($principle->id) ? (string) $principle->id : '';
                            $principle_questions = isset($questions_by_principle[$principle_id]) ? $questions_by_principle[$principle_id] : array();
                            $progress = isset($principle_progress[$principle_id]) ? $principle_progress[$principle_id] : array('guided' => 0, 'total' => 0, 'percent' => 0);
                        ?>
                            <div class="principle-card">
                                <div class="principle-header" id="taPrincipleHeading<?= $principle_id; ?>">
                                    <a
                                        href="#taPrincipleCollapse<?= $principle_id; ?>"
                                        class="principle-toggle"
                                        data-toggle="collapse"
                                        aria-expanded="<?= $principle_id === '1' ? 'true' : 'false'; ?>"
                                        aria-controls="taPrincipleCollapse<?= $principle_id; ?>"
                                    >
                                        <div class="principle-main">
                                            <strong><?= $escape($principle->indicator); ?></strong>
                                            <span><?= $escape($principle->description); ?></span>
                                        </div>
                                        <div class="principle-meta">
                                            <span class="principle-progress-pill">
                                                <i class="mdi mdi-check-circle-outline"></i>
                                                <?= $progress['guided']; ?>/<?= $progress['total']; ?>
                                            </span>
                                            <i class="mdi mdi-chevron-down principle-icon"></i>
                                        </div>
                                    </a>
                                </div>

                                <div
                                    id="taPrincipleCollapse<?= $principle_id; ?>"
                                    class="collapse <?= $principle_id === '1' ? 'show' : ''; ?>"
                                    aria-labelledby="taPrincipleHeading<?= $principle_id; ?>"
                                    data-parent=".ta-principle-list"
                                >
                                    <div class="principle-body">
                                        <div class="indicator-list">
                                            <?php foreach ($principle_questions as $question) :
                                                $index = (int) $question->i_no;
                                                $checklist_field = 'q' . $index;
                                                $concern_field = 'q' . $index;
                                                $facilitating_field = 'qq' . $index;
                                                $category_field = 'a' . $index;
                                                $commitment_field = 'f' . $index;
                                                $manifestation_value = ($checklist_record && isset($checklist_record->$checklist_field))
                                                    ? (int) $checklist_record->$checklist_field
                                                    : 0;
                                                if (!isset($manifestation_details[$manifestation_value])) {
                                                    $manifestation_value = 0;
                                                }
                                                $manifestation = $manifestation_details[$manifestation_value];
                                                $concern_value = ($ta_record && isset($ta_record->$concern_field)) ? (string) $ta_record->$concern_field : '';
                                                $facilitating_value = ($ta_record && isset($ta_record->$facilitating_field)) ? (string) $ta_record->$facilitating_field : '';
                                                $category_value = ($ta_record && isset($ta_record->$category_field)) ? (string) $ta_record->$category_field : '';
                                                $commitment_value = ($ta_record && isset($ta_record->$commitment_field)) ? (string) $ta_record->$commitment_field : '';
                                                $concern_readonly = !$can_edit || $manifestation['primary'] === 'facilitating';
                                                $facilitating_readonly = !$can_edit || $manifestation['primary'] === 'concern';
                                                $guidance_title = $manifestation['primary'] === 'facilitating'
                                                    ? 'Primary field: Facilitating Factors'
                                                    : ($manifestation['primary'] === 'concern'
                                                        ? 'Primary field: Concerns and Gaps'
                                                        : 'Primary field: Either narrative field');
                                                $concern_placeholder = $manifestation['primary'] === 'facilitating'
                                                    ? 'This indicator is marked Always Manifested, so the concern field stays read-only.'
                                                    : 'Describe the concern, issue, gap, problem, or bottleneck affecting this indicator.';
                                                $facilitating_placeholder = $manifestation['primary'] === 'concern'
                                                    ? 'This field becomes active when the checklist basis is Always Manifested.'
                                                    : 'Document the practice, support, or condition sustaining this indicator.';
                                            ?>
                                                <div class="indicator-card">
                                                    <div class="indicator-top">
                                                        <span class="indicator-number"><?= $escape($question->i_no); ?></span>
                                                        <div class="indicator-copy">
                                                            <h5><?= $escape($question->description); ?></h5>
                                                            <p>Use the linked checklist result to decide whether this indicator needs a concern narrative or a facilitating factor narrative as the lead entry.</p>
                                                        </div>
                                                        <span class="indicator-status <?= $manifestation['class']; ?>">
                                                            <i class="mdi mdi-clipboard-pulse-outline"></i>
                                                            <?= $escape($manifestation['short']); ?>
                                                        </span>
                                                    </div>

                                                    <div class="guidance-strip">
                                                        <i class="mdi mdi-lightbulb-on-outline"></i>
                                                        <div>
                                                            <strong><?= $escape($guidance_title); ?></strong>
                                                            <?= $escape($manifestation['hint']); ?>
                                                        </div>
                                                    </div>

                                                    <div class="input-grid">
                                                        <div class="field-card">
                                                            <h6>Concerns, Issues, Gaps, Problems, and Bottlenecks</h6>
                                                            <p>Use this field when the indicator is below Always Manifested or when the checklist basis is still pending.</p>
                                                            <textarea
                                                                class="ta-input<?= $concern_readonly ? ' is-readonly' : ''; ?>"
                                                                name="<?= $escape($concern_field); ?>"
                                                                rows="4"
                                                                placeholder="<?= $escape($concern_placeholder); ?>"
                                                                <?= $concern_readonly ? 'readonly' : ''; ?>
                                                            ><?= $escape($concern_value); ?></textarea>
                                                        </div>

                                                        <div class="field-card">
                                                            <h6>Facilitating Factors for Sustained Indicators</h6>
                                                            <p>Use this field to capture the enabling conditions that keep an Always Manifested indicator strong.</p>
                                                            <textarea
                                                                class="ta-input<?= $facilitating_readonly ? ' is-readonly' : ''; ?>"
                                                                name="<?= $escape($facilitating_field); ?>"
                                                                rows="4"
                                                                placeholder="<?= $escape($facilitating_placeholder); ?>"
                                                                <?= $facilitating_readonly ? 'readonly' : ''; ?>
                                                            ><?= $escape($facilitating_value); ?></textarea>
                                                        </div>
                                                    </div>

                                                    <div class="input-grid-secondary">
                                                        <div class="field-card">
                                                            <h6>Concern Category</h6>
                                                            <p>Tag the dominant area of support needed for this indicator.</p>
                                                            <select
                                                                class="ta-select<?= !$can_edit ? ' is-readonly' : ''; ?>"
                                                                name="<?= $escape($category_field); ?>"
                                                                <?= !$can_edit ? 'disabled' : ''; ?>
                                                            >
                                                                <option value=""></option>
                                                                <?php foreach ($category_labels as $category_key => $category_label) : ?>
                                                                    <option value="<?= $category_key; ?>" <?= (string) $category_key === $category_value ? 'selected' : ''; ?>>
                                                                        <?= $escape($category_label); ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>

                                                        <div class="field-card">
                                                            <h6>Proposed Resolution or Commitment</h6>
                                                            <p>State the next step, response, or commitment that will address the gap or sustain the strong practice.</p>
                                                            <textarea
                                                                class="ta-input<?= !$can_edit ? ' is-readonly' : ''; ?>"
                                                                name="<?= $escape($commitment_field); ?>"
                                                                rows="4"
                                                                placeholder="Describe the intended response, support, or commitment for this indicator."
                                                                <?= !$can_edit ? 'readonly' : ''; ?>
                                                            ><?= $escape($commitment_value); ?></textarea>
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

                    <div class="workspace-actions" id="taActions">
                        <?php if ($can_edit && !$is_finalized) : ?>
                            <button type="submit" name="<?= $ta_record ? 'submit_edit' : 'submit'; ?>" class="workspace-button workspace-button-primary">
                                <i class="mdi mdi-content-save-outline"></i>
                                Save Draft
                            </button>
                        <?php endif; ?>

                        <?php if ($ta_record && !$is_finalized) : ?>
                            <button type="submit" formaction="<?= base_url(); ?>Pages/sbm_ta_final" formmethod="post" name="id" value="<?= (int) $ta_record->id; ?>" onclick="return confirm('Are you sure you want to finalize this TA report? Once finalized, it will be locked until a division reviewer unlocks it.')" class="workspace-button workspace-button-success">
                                <i class="mdi mdi-lock-check-outline"></i>
                                Finalize TA Report
                            </button>
                        <?php elseif ($is_locked_for_user && $is_division_user) : ?>
                            <button type="submit" formaction="<?= base_url(); ?>Pages/sbm_ta_unlock" formmethod="post" name="id" value="<?= (int) $ta_record->id; ?>" onclick="return confirm('Are you sure you want to unlock this TA report? The school will be able to edit it again.')" class="workspace-button workspace-button-primary">
                                <i class="mdi mdi-lock-open-outline"></i>
                                Unlock TA Report
                            </button>
                        <?php elseif ($is_locked_for_user && $is_school_user) : ?>
                            <button type="submit" formaction="<?= base_url(); ?>Pages/sbm_ta_unlock_request" formmethod="post" name="id" value="<?= (int) $ta_record->id; ?>" onclick="return confirm('Are you sure you want to request an unlock for this TA report? A notification will be sent to your division reviewer.')" class="workspace-button workspace-button-warning">
                                <i class="mdi mdi-lock-open-variant-outline"></i>
                                Request to Unlock
                            </button>
                        <?php endif; ?>

                        <a href="<?= $dashboard_url; ?>" class="workspace-button workspace-button-secondary">
                            <i class="mdi mdi-view-dashboard-outline"></i>
                            Back to Dashboard
                        </a>
                    </div>

                    <p class="workspace-note">
                        <?php if ($can_edit) : ?>
                            Save Draft keeps the report editable. School accounts can continue editing this TA report at any time.
                        <?php else : ?>
                            This finalized TA report is in read-only mode. Use the quick links to continue with TANA prioritization or the school action plan.
                        <?php endif; ?>
                    </p>

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
                        <h5>Manifestation Overview</h5>
                        <p>See how the current checklist results distribute the TA narrative between issue-based and strength-based indicators.</p>
                        <div class="manifestation-summary">
                            <?php foreach ($manifestation_details as $manifestation_key => $manifestation) : ?>
                                <div class="manifestation-summary-item">
                                    <div>
                                        <strong><?= $escape($manifestation['short']); ?></strong>
                                        <span><?= $escape($manifestation['hint']); ?></span>
                                    </div>
                                    <em class="<?= $manifestation['class']; ?>"><?= (int) $manifestation_counts[$manifestation_key]; ?></em>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="sidebar-card">
                        <h5>Progress Snapshot</h5>
                        <p><?= $always_manifested_count; ?> indicator(s) are currently marked Always Manifested, while <?= $issue_track_count; ?> still point to issues, gaps, or missing evidence.</p>
                        <div class="principle-links">
                            <?php foreach ($principles as $principle) :
                                $principle_id = isset($principle->id) ? (string) $principle->id : '';
                                $progress = isset($principle_progress[$principle_id]) ? $principle_progress[$principle_id] : array('guided' => 0, 'total' => 0);
                            ?>
                                <a href="#taPrincipleCollapse<?= $principle_id; ?>" class="principle-link" data-toggle="collapse" aria-controls="taPrincipleCollapse<?= $principle_id; ?>">
                                    <i class="mdi mdi-file-tree-outline"></i>
                                    <?= $escape($principle->indicator); ?>
                                    <span><?= $progress['guided']; ?>/<?= $progress['total']; ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="sidebar-card">
                        <h5>Quick Links</h5>
                        <p>Jump between the school modules that feed into the same planning cycle.</p>
                        <div class="quick-link-list">
                            <a href="<?= $checklist_url; ?>" class="quick-link">
                                <span><i class="mdi mdi-format-list-checks"></i> Review checklist</span>
                                <i class="mdi mdi-arrow-right"></i>
                            </a>
                            <a href="<?= $tana_url; ?>" class="quick-link">
                                <span><i class="mdi mdi-chart-line"></i> Open TANA priorities</span>
                                <i class="mdi mdi-arrow-right"></i>
                            </a>
                            <a href="<?= $action_plan_url; ?>" class="quick-link">
                                <span><i class="mdi mdi-clipboard-text-outline"></i> Open action plan</span>
                                <i class="mdi mdi-arrow-right"></i>
                            </a>
                            <a href="<?= $profile_url; ?>" class="quick-link">
                                <span><i class="mdi mdi-account-school-outline"></i> View school profile</span>
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

    <div id="taFiscalYearModal" class="modal fade dashboard-modal" tabindex="-1" role="dialog" aria-labelledby="taFiscalYearModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="taFiscalYearModalLabel">Change Fiscal Year</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="<?= base_url('Pages/change_fy'); ?>" method="post">
                        <input type="hidden" name="<?= html_escape($this->security->get_csrf_token_name()); ?>" value="<?= html_escape($this->security->get_csrf_hash()); ?>">
                        <label for="taFiscalYear">Fiscal Year</label>
                        <select id="taFiscalYear" name="new_fy" class="form-control" onchange="this.form.submit()">
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
