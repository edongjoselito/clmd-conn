<?php
$tana_record = isset($tana) && $tana ? $tana : null;
$principles = isset($sbm) && is_array($sbm) ? $sbm : array();
$indicators = isset($sbm_sub) && is_array($sbm_sub) ? $sbm_sub : array();
$school_id = (string) $this->session->username;
$school = $this->Common->one_cond_row('schools', 'schoolID', $school_id);
$division = ($school && !empty($school->division_id)) ? $this->Page_model->one_cond_row('division', 'id', $school->division_id) : null;
$district = ($school && !empty($school->district_id)) ? $this->Page_model->one_cond_row('district', 'id', $school->district_id) : null;
$checklist_record = $this->Common->two_cond_row('sbm', 'school_id', $school_id, 'fy', $this->session->fy);
$ta_record = $this->Common->two_cond_row('sbm_ta', 'school_id', $school_id, 'fy', $this->session->fy);
$summary_records = $this->Common->two_cond_order_by('tana_summary', 'school_id', $school_id, 'fy', $this->session->fy, 'sequence', 'ASC');
$summary_record_count = $this->Common->two_cond_count_row('tana_summary', 'school_id', $school_id, 'fy', $this->session->fy)->num_rows();
$summary_finalized = $this->Common->three_cond_count_row('tana_summary', 'school_id', $school_id, 'fy', $this->session->fy, 'stat', 1)->num_rows() > 0;
$position = strtolower(trim((string) $this->session->position));
$is_school_user = $position === 'school';
$can_edit = $is_school_user;
$validation_markup = validation_errors();
$form_action = $tana_record ? 'Pages/tana_form_update' : 'Pages/tana_form';

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
    : 'TNA';

$manifestation_labels = array(
    0 => array('short' => 'Checklist Pending', 'class' => 'manifestation-pending'),
    1 => array('short' => 'Not Yet Manifested', 'class' => 'manifestation-one'),
    2 => array('short' => 'Rarely Manifested', 'class' => 'manifestation-two'),
    3 => array('short' => 'Frequently Manifested', 'class' => 'manifestation-three'),
    4 => array('short' => 'Always Manifested', 'class' => 'manifestation-four'),
    5 => array('short' => 'No Data', 'class' => 'manifestation-na'),
);

$score_dimensions = array(
    'a' => array(
        'label' => 'Strategic Importance',
        'hint' => 'How strongly this concern affects the school improvement agenda, targets, and key results.',
    ),
    'b' => array(
        'label' => 'Urgency',
        'hint' => 'How quickly the concern needs support or intervention based on present risks and timing.',
    ),
    'c' => array(
        'label' => 'Magnitude',
        'hint' => 'How broad or significant the effect is on learners, personnel, systems, or delivery.',
    ),
    'd' => array(
        'label' => 'Feasibility',
        'hint' => 'How realistic it is to respond with available partnerships, resources, or technical assistance.',
    ),
);

$score_scale = array(
    1 => 'Very Low',
    2 => 'Low',
    3 => 'Moderate',
    4 => 'High',
    5 => 'Critical',
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

$ready_concern_count = 0;
$fully_scored_count = 0;
$high_priority_count = 0;
$score_totals = array('a' => 0, 'b' => 0, 'c' => 0, 'd' => 0);
$score_counts = array('a' => 0, 'b' => 0, 'c' => 0, 'd' => 0);
$full_priority_total = 0;
$principle_progress = array();
$total_questions = count($indicators);

foreach ($principles as $principle) {
    $principle_id = isset($principle->id) ? (string) $principle->id : '';
    $principle_questions = isset($questions_by_principle[$principle_id]) ? $questions_by_principle[$principle_id] : array();
    $principle_total = count($principle_questions);
    $principle_ready = 0;
    $principle_scored = 0;

    foreach ($principle_questions as $question) {
        $index = (int) $question->i_no;
        $concern_field = 'q' . $index;
        $concern_text = ($ta_record && isset($ta_record->$concern_field)) ? trim((string) $ta_record->$concern_field) : '';
        if ($concern_text !== '') {
            $ready_concern_count++;
            $principle_ready++;
        }

        $scores = array();
        $complete = true;
        foreach ($score_dimensions as $prefix => $dimension) {
            $field = $prefix . $index;
            $value = ($tana_record && isset($tana_record->$field)) ? (int) $tana_record->$field : 0;
            $scores[$prefix] = $value;
            if ($value > 0) {
                $score_totals[$prefix] += $value;
                $score_counts[$prefix]++;
            } else {
                $complete = false;
            }
        }

        if ($complete) {
            $average = array_sum($scores) / 4;
            $fully_scored_count++;
            $principle_scored++;
            $full_priority_total += $average;
            if ($average >= 4) {
                $high_priority_count++;
            }
        }
    }

    $principle_progress[$principle_id] = array(
        'ready' => $principle_ready,
        'scored' => $principle_scored,
        'total' => $principle_total,
        'percent' => $principle_total > 0 ? ($principle_scored / $principle_total) * 100 : 0,
    );
}

$mean_priority_score = $fully_scored_count > 0 ? $full_priority_total / $fully_scored_count : 0;
$status_label = !$tana_record ? 'Not started' : ($fully_scored_count === $total_questions ? 'Completed' : 'Draft saved');
$summary_status_label = $summary_record_count === 0 ? 'Not generated' : ($summary_finalized ? 'Finalized' : 'Draft shortlist');
$dimension_averages = array();
foreach ($score_dimensions as $prefix => $dimension) {
    $dimension_averages[$prefix] = $score_counts[$prefix] > 0 ? $score_totals[$prefix] / $score_counts[$prefix] : 0;
}

$dashboard_url = base_url();
$profile_url = base_url() . 'school/' . rawurlencode($school_id);
$checklist_url = base_url() . 'Pages/sbm_checklist';
$ta_url = base_url() . 'Pages/tapr_form';
$summary_url = base_url() . 'Pages/tana_summary';
$action_plan_url = base_url() . 'Pages/sbm_action_plan';

$next_step = array(
    'eyebrow' => 'Start here',
    'title' => 'Begin rating each concern through the four TANA lenses',
    'description' => 'Use the TA concern narrative as your basis, then rate Strategic Importance, Urgency, Magnitude, and Feasibility for each indicator you want to prioritize.',
    'url' => '#tanaWorkspace',
    'cta' => 'Open the workspace',
    'secondary_url' => $ta_url,
    'secondary_cta' => 'Review TA form',
);

if (!$ta_record || $ready_concern_count === 0) {
    $next_step = array(
        'eyebrow' => 'Needs basis',
        'title' => 'Complete the TA concern narratives first',
        'description' => 'The TANA form is most useful after the TA provision report already identifies the actual concerns, gaps, or bottlenecks behind each indicator.',
        'url' => $ta_url,
        'cta' => 'Open TA form',
        'secondary_url' => '#tanaWorkspace',
        'secondary_cta' => 'Score what is ready',
    );
} elseif ($fully_scored_count === 0) {
    $next_step = array(
        'eyebrow' => 'Score priorities',
        'title' => 'Finish scoring the first set of concern indicators',
        'description' => 'Once all four lenses are scored for an indicator, the page can compute the average and help you move toward the TANA shortlist.',
        'url' => '#tanaActions',
        'cta' => 'Go to actions',
        'secondary_url' => $summary_url,
        'secondary_cta' => 'Preview summary',
    );
} elseif ($summary_record_count === 0) {
    $next_step = array(
        'eyebrow' => 'Ready for shortlist',
        'title' => 'Generate the TANA priority basis',
        'description' => 'You already have scored indicators. Open the TANA Summary page to rank the current averages and identify the top concerns for the fiscal year.',
        'url' => $summary_url,
        'cta' => 'Open TANA summary',
        'secondary_url' => '#tanaActions',
        'secondary_cta' => 'Save draft first',
    );
} elseif ($summary_finalized) {
    $next_step = array(
        'eyebrow' => 'Shortlist finalized',
        'title' => 'Use the finalized priorities for planning',
        'description' => 'Your TANA shortlist is already finalized. Continue to the action plan or review the summary to confirm the selected top concerns.',
        'url' => $summary_url,
        'cta' => 'Review summary',
        'secondary_url' => $action_plan_url,
        'secondary_cta' => 'Open action plan',
    );
} else {
    $next_step = array(
        'eyebrow' => 'Refine shortlist',
        'title' => 'Review the current priority ranking',
        'description' => 'A TANA shortlist already exists for this fiscal year. Revisit the summary after saving if you want to refresh the ranking based on updated scores.',
        'url' => $summary_url,
        'cta' => 'Open TANA summary',
        'secondary_url' => '#tanaActions',
        'secondary_cta' => 'Save current ratings',
    );
}
?>

<style>
    .tana-workspace-page {
        --tana-primary: #7f1d1d;
        --tana-primary-light: #b83a4b;
        --tana-accent: #d6a84b;
        --tana-ink: #172033;
        --tana-muted: #687386;
        --tana-border: #e4e9f0;
        --tana-surface: #f6f8fb;
        --tana-success: #15803d;
        --tana-warning: #b45309;
        --tana-danger: #b91c1c;
        --tana-shadow: 0 24px 60px rgba(15, 23, 42, 0.08);
        color: var(--tana-ink);
        padding-bottom: 2rem;
    }

    .tana-workspace-page .tana-hero {
        position: relative;
        overflow: hidden;
        display: grid;
        grid-template-columns: minmax(0, 1.45fr) minmax(280px, 0.75fr);
        gap: 1.5rem;
        padding: 2rem;
        border-radius: 28px;
        background:
            radial-gradient(circle at top right, rgba(214, 168, 75, 0.28), transparent 34%),
            linear-gradient(135deg, #fff9ef 0%, #fff 40%, #f7f2e7 100%);
        border: 1px solid rgba(214, 168, 75, 0.25);
        box-shadow: var(--tana-shadow);
        margin-top: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .tana-workspace-page .tana-hero::after {
        content: "";
        position: absolute;
        inset: auto -80px -120px auto;
        width: 260px;
        height: 260px;
        border-radius: 50%;
        background: rgba(127, 29, 29, 0.08);
        pointer-events: none;
    }

    .tana-workspace-page .hero-copy,
    .tana-workspace-page .hero-side {
        position: relative;
        z-index: 1;
    }

    .tana-workspace-page .hero-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.45rem 0.85rem;
        border-radius: 999px;
        background: rgba(127, 29, 29, 0.1);
        color: var(--tana-primary);
        font-size: 0.82rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }

    .tana-workspace-page .tana-hero h1 {
        margin: 1rem 0 0.75rem;
        font-size: clamp(2rem, 3.3vw, 2.8rem);
        line-height: 1.08;
        color: var(--tana-primary);
    }

    .tana-workspace-page .tana-hero p {
        margin: 0;
        max-width: 760px;
        font-size: 1rem;
        line-height: 1.75;
        color: var(--tana-muted);
    }

    .tana-workspace-page .hero-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.7rem;
        margin-top: 1.1rem;
    }

    .tana-workspace-page .hero-meta span {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.55rem 0.9rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.72);
        border: 1px solid rgba(127, 29, 29, 0.08);
        color: var(--tana-ink);
        font-size: 0.9rem;
    }

    .tana-workspace-page .hero-side {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        justify-content: space-between;
    }

    .tana-workspace-page .tana-badge {
        align-self: flex-end;
        width: 140px;
        min-height: 140px;
        padding: 1.2rem;
        border-radius: 26px;
        background: linear-gradient(160deg, var(--tana-primary), #5f1515 100%);
        color: #fff;
        box-shadow: 0 22px 44px rgba(127, 29, 29, 0.22);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .tana-workspace-page .tana-badge small {
        font-size: 0.72rem;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        opacity: 0.8;
    }

    .tana-workspace-page .tana-badge strong {
        font-size: 2rem;
        letter-spacing: 0.08em;
        line-height: 1;
    }

    .tana-workspace-page .hero-actions {
        display: grid;
        gap: 0.75rem;
        width: 100%;
        max-width: 260px;
        margin-left: auto;
    }

    .tana-workspace-page .hero-button,
    .tana-workspace-page .workspace-button {
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

    .tana-workspace-page .hero-button:hover,
    .tana-workspace-page .workspace-button:hover {
        transform: translateY(-1px);
        text-decoration: none;
    }

    .tana-workspace-page .hero-button-primary,
    .tana-workspace-page .workspace-button-primary {
        background: var(--tana-primary);
        color: #fff;
        box-shadow: 0 16px 30px rgba(127, 29, 29, 0.18);
    }

    .tana-workspace-page .hero-button-secondary,
    .tana-workspace-page .workspace-button-secondary {
        background: #fff;
        border-color: rgba(127, 29, 29, 0.14);
        color: var(--tana-primary);
    }

    .tana-workspace-page .hero-button-tertiary,
    .tana-workspace-page .workspace-button-tertiary {
        background: rgba(255, 255, 255, 0.82);
        border-color: rgba(214, 168, 75, 0.36);
        color: var(--tana-ink);
    }

    .tana-workspace-page .tana-stats {
        margin-bottom: 1.5rem;
    }

    .tana-workspace-page .tana-stat-card {
        height: 100%;
        border-radius: 22px;
        padding: 1.3rem;
        background: #fff;
        border: 1px solid var(--tana-border);
        box-shadow: 0 14px 36px rgba(15, 23, 42, 0.06);
    }

    .tana-workspace-page .tana-stat-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
    }

    .tana-workspace-page .tana-stat-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 3rem;
        height: 3rem;
        border-radius: 16px;
        background: rgba(127, 29, 29, 0.1);
        color: var(--tana-primary);
        font-size: 1.3rem;
        flex-shrink: 0;
    }

    .tana-workspace-page .tana-stat-card small {
        display: block;
        margin-bottom: 0.35rem;
        color: var(--tana-muted);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-size: 0.75rem;
        font-weight: 700;
    }

    .tana-workspace-page .tana-stat-card h3 {
        margin: 0;
        font-size: 1.65rem;
        color: var(--tana-ink);
    }

    .tana-workspace-page .tana-stat-card p {
        margin: 0.55rem 0 0;
        color: var(--tana-muted);
        line-height: 1.6;
        font-size: 0.94rem;
    }

    .tana-workspace-page .mini-progress {
        width: 100%;
        height: 0.48rem;
        border-radius: 999px;
        background: #ebeff5;
        overflow: hidden;
        margin-top: 1rem;
    }

    .tana-workspace-page .mini-progress span {
        display: block;
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg, var(--tana-primary), var(--tana-accent));
    }

    .tana-workspace-page .workspace-panel {
        background: #fff;
        border: 1px solid var(--tana-border);
        border-radius: 24px;
        box-shadow: 0 18px 44px rgba(15, 23, 42, 0.07);
        overflow: hidden;
    }

    .tana-workspace-page .workspace-panel-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        padding: 1.4rem 1.5rem 0;
    }

    .tana-workspace-page .workspace-panel-header h4 {
        margin: 0;
        color: var(--tana-ink);
        font-size: 1.2rem;
    }

    .tana-workspace-page .workspace-panel-header p {
        margin: 0.35rem 0 0;
        color: var(--tana-muted);
        line-height: 1.6;
    }

    .tana-workspace-page .workspace-panel-header small {
        color: var(--tana-primary);
        font-weight: 700;
        white-space: nowrap;
    }

    .tana-workspace-page .workspace-panel-body {
        padding: 1.5rem;
    }

    .tana-workspace-page .info-alert {
        display: flex;
        align-items: flex-start;
        gap: 0.9rem;
        padding: 1rem 1.1rem;
        border-radius: 18px;
        margin-bottom: 1.5rem;
        border: 1px solid rgba(214, 168, 75, 0.3);
        background: linear-gradient(135deg, rgba(255, 250, 235, 0.96), rgba(255, 255, 255, 0.96));
    }

    .tana-workspace-page .info-alert i {
        font-size: 1.35rem;
        color: var(--tana-accent);
        margin-top: 0.1rem;
    }

    .tana-workspace-page .info-alert strong {
        display: block;
        margin-bottom: 0.25rem;
        color: var(--tana-ink);
    }

    .tana-workspace-page .info-alert p {
        margin: 0;
        color: var(--tana-muted);
        line-height: 1.6;
    }

    .tana-workspace-page .principle-links {
        display: flex;
        flex-wrap: wrap;
        gap: 0.7rem;
    }

    .tana-workspace-page .principle-link {
        display: inline-flex;
        align-items: center;
        gap: 0.55rem;
        padding: 0.72rem 0.95rem;
        border-radius: 999px;
        background: var(--tana-surface);
        border: 1px solid var(--tana-border);
        color: var(--tana-ink);
        font-weight: 600;
        text-decoration: none;
    }

    .tana-workspace-page .principle-link span {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 2.1rem;
        padding: 0.2rem 0.55rem;
        border-radius: 999px;
        background: rgba(127, 29, 29, 0.08);
        color: var(--tana-primary);
        font-size: 0.78rem;
        font-weight: 700;
    }

    .tana-workspace-page .principle-link:hover {
        text-decoration: none;
        border-color: rgba(127, 29, 29, 0.24);
        color: var(--tana-primary);
    }

    .tana-workspace-page .tana-principle-list {
        display: grid;
        gap: 1rem;
        margin-top: 1rem;
    }

    .tana-workspace-page .principle-card {
        border: 1px solid var(--tana-border);
        border-radius: 22px;
        background: #fff;
        overflow: hidden;
    }

    .tana-workspace-page .principle-header {
        background: linear-gradient(180deg, #fff, #fbfcfe);
    }

    .tana-workspace-page .principle-toggle {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1.15rem 1.25rem;
        color: inherit;
        text-decoration: none;
    }

    .tana-workspace-page .principle-toggle:hover {
        text-decoration: none;
        color: inherit;
    }

    .tana-workspace-page .principle-main {
        display: grid;
        gap: 0.3rem;
    }

    .tana-workspace-page .principle-main strong {
        color: var(--tana-ink);
        font-size: 1rem;
    }

    .tana-workspace-page .principle-main span {
        color: var(--tana-muted);
        line-height: 1.55;
        font-size: 0.92rem;
    }

    .tana-workspace-page .principle-meta {
        display: flex;
        align-items: center;
        gap: 0.7rem;
        flex-shrink: 0;
    }

    .tana-workspace-page .principle-progress-pill {
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

    .tana-workspace-page .principle-icon {
        font-size: 1.2rem;
        color: var(--tana-primary);
    }

    .tana-workspace-page .principle-body {
        padding: 1.25rem;
        background: #fff;
    }

    .tana-workspace-page .indicator-list {
        display: grid;
        gap: 1rem;
    }

    .tana-workspace-page .indicator-card {
        border: 1px solid var(--tana-border);
        border-radius: 20px;
        padding: 1.1rem;
        background: linear-gradient(180deg, #fff, #fcfdff);
    }

    .tana-workspace-page .indicator-top {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
    }

    .tana-workspace-page .indicator-number {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.6rem;
        height: 2.6rem;
        border-radius: 16px;
        background: rgba(127, 29, 29, 0.1);
        color: var(--tana-primary);
        font-weight: 800;
        flex-shrink: 0;
    }

    .tana-workspace-page .indicator-copy {
        flex: 1;
        min-width: 0;
    }

    .tana-workspace-page .indicator-copy h5 {
        margin: 0;
        color: var(--tana-ink);
        font-size: 1rem;
        line-height: 1.55;
    }

    .tana-workspace-page .indicator-copy p {
        margin: 0.35rem 0 0;
        color: var(--tana-muted);
        line-height: 1.65;
        font-size: 0.9rem;
    }

    .tana-workspace-page .indicator-status {
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

    .tana-workspace-page .manifestation-pending {
        background: #eef2f7;
        color: #526075;
    }

    .tana-workspace-page .manifestation-one {
        background: rgba(185, 28, 28, 0.12);
        color: var(--tana-danger);
    }

    .tana-workspace-page .manifestation-two {
        background: rgba(180, 83, 9, 0.12);
        color: var(--tana-warning);
    }

    .tana-workspace-page .manifestation-three {
        background: rgba(214, 168, 75, 0.18);
        color: #8a5b0b;
    }

    .tana-workspace-page .manifestation-four {
        background: rgba(21, 128, 61, 0.12);
        color: var(--tana-success);
    }

    .tana-workspace-page .manifestation-na {
        background: rgba(100, 116, 139, 0.14);
        color: #475569;
    }

    .tana-workspace-page .concern-panel {
        margin-top: 1rem;
        padding: 1rem;
        border-radius: 18px;
        border: 1px solid var(--tana-border);
        background: var(--tana-surface);
    }

    .tana-workspace-page .concern-panel strong {
        display: block;
        margin-bottom: 0.35rem;
        color: var(--tana-ink);
        font-size: 0.95rem;
    }

    .tana-workspace-page .concern-panel p {
        margin: 0;
        color: var(--tana-muted);
        line-height: 1.7;
        white-space: pre-wrap;
    }

    .tana-workspace-page .concern-panel-empty {
        border-style: dashed;
        background: rgba(246, 248, 251, 0.8);
    }

    .tana-workspace-page .concern-panel-empty p {
        margin-bottom: 0.9rem;
    }

    .tana-workspace-page .score-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
        margin-top: 1rem;
    }

    .tana-workspace-page .score-card {
        border: 1px solid var(--tana-border);
        border-radius: 16px;
        padding: 0.95rem;
        background: #fff;
    }

    .tana-workspace-page .score-card h6 {
        margin: 0;
        color: var(--tana-ink);
        font-size: 0.94rem;
    }

    .tana-workspace-page .score-card p {
        margin: 0.35rem 0 0.8rem;
        color: var(--tana-muted);
        line-height: 1.55;
        font-size: 0.86rem;
    }

    .tana-workspace-page .score-select {
        width: 100%;
        border: 1px solid #d7deea;
        border-radius: 14px;
        background: #fff;
        color: var(--tana-ink);
        font-size: 0.94rem;
        padding: 0.82rem 0.9rem;
        transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
    }

    .tana-workspace-page .score-select:focus {
        outline: none;
        border-color: rgba(127, 29, 29, 0.35);
        box-shadow: 0 0 0 4px rgba(127, 29, 29, 0.08);
    }

    .tana-workspace-page .indicator-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-top: 1rem;
        padding-top: 0.9rem;
        border-top: 1px solid var(--tana-border);
    }

    .tana-workspace-page .average-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.65rem;
        padding: 0.68rem 0.9rem;
        border-radius: 16px;
        background: rgba(127, 29, 29, 0.08);
        color: var(--tana-primary);
    }

    .tana-workspace-page .average-chip strong {
        display: block;
        font-size: 1.1rem;
        line-height: 1;
    }

    .tana-workspace-page .average-chip small {
        display: block;
        margin-top: 0.15rem;
        color: var(--tana-muted);
        line-height: 1.4;
    }

    .tana-workspace-page .priority-note {
        color: var(--tana-muted);
        font-size: 0.88rem;
        line-height: 1.6;
        text-align: right;
    }

    .tana-workspace-page .workspace-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.85rem;
        margin-top: 1.4rem;
    }

    .tana-workspace-page .workspace-button-success {
        background: var(--tana-success);
        color: #fff;
        box-shadow: 0 14px 28px rgba(21, 128, 61, 0.18);
    }

    .tana-workspace-page .workspace-note {
        margin: 1rem 0 0;
        color: var(--tana-muted);
        line-height: 1.7;
    }

    .tana-workspace-page .next-step-card {
        padding: 1.25rem;
        border-radius: 20px;
        background: linear-gradient(180deg, rgba(127, 29, 29, 0.96), rgba(95, 21, 21, 0.98));
        color: #fff;
        box-shadow: 0 18px 40px rgba(95, 21, 21, 0.24);
    }

    .tana-workspace-page .next-step-eyebrow {
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

    .tana-workspace-page .next-step-card h5 {
        margin: 0.95rem 0 0.45rem;
        font-size: 1.3rem;
        line-height: 1.3;
        color: #fff;
    }

    .tana-workspace-page .next-step-card p {
        margin: 0;
        color: rgba(255, 255, 255, 0.82);
        line-height: 1.7;
    }

    .tana-workspace-page .next-step-actions {
        display: grid;
        gap: 0.7rem;
        margin-top: 1rem;
    }

    .tana-workspace-page .next-step-card .workspace-button-primary {
        background: #fff;
        color: var(--tana-primary);
    }

    .tana-workspace-page .next-step-card .workspace-button-secondary {
        background: transparent;
        border-color: rgba(255, 255, 255, 0.18);
        color: #fff;
    }

    .tana-workspace-page .sidebar-card {
        margin-top: 1rem;
        padding: 1.15rem;
        border: 1px solid var(--tana-border);
        border-radius: 20px;
        background: #fff;
    }

    .tana-workspace-page .sidebar-card h5 {
        margin: 0;
        color: var(--tana-ink);
        font-size: 1.02rem;
    }

    .tana-workspace-page .sidebar-card p {
        margin: 0.4rem 0 0.95rem;
        color: var(--tana-muted);
        line-height: 1.65;
    }

    .tana-workspace-page .dimension-list,
    .tana-workspace-page .priority-list {
        display: grid;
        gap: 0.75rem;
    }

    .tana-workspace-page .dimension-item,
    .tana-workspace-page .priority-item {
        padding: 0.85rem 0.95rem;
        border-radius: 16px;
        border: 1px solid var(--tana-border);
        background: #fff;
    }

    .tana-workspace-page .dimension-item strong,
    .tana-workspace-page .priority-item strong {
        display: block;
        margin-bottom: 0.2rem;
        color: var(--tana-ink);
        font-size: 0.92rem;
    }

    .tana-workspace-page .dimension-item span,
    .tana-workspace-page .priority-item span {
        color: var(--tana-muted);
        font-size: 0.84rem;
        line-height: 1.55;
    }

    .tana-workspace-page .quick-link-list {
        display: grid;
        gap: 0.75rem;
    }

    .tana-workspace-page .quick-link {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.95rem 1rem;
        border-radius: 16px;
        background: var(--tana-surface);
        border: 1px solid var(--tana-border);
        color: var(--tana-ink);
        font-weight: 600;
        text-decoration: none;
    }

    .tana-workspace-page .quick-link:hover {
        text-decoration: none;
        color: var(--tana-primary);
        border-color: rgba(127, 29, 29, 0.18);
    }

    .tana-workspace-page .empty-state {
        padding: 2rem 1.5rem;
        text-align: center;
        border: 1px dashed rgba(127, 29, 29, 0.2);
        border-radius: 20px;
        background: rgba(246, 248, 251, 0.88);
        color: var(--tana-muted);
    }

    .tana-workspace-page .empty-state i {
        display: inline-flex;
        margin-bottom: 0.75rem;
        font-size: 2rem;
        color: var(--tana-primary);
    }

    .tana-workspace-page .empty-state strong {
        display: block;
        margin-bottom: 0.35rem;
        color: var(--tana-ink);
        font-size: 1.05rem;
    }

    .tana-workspace-page .dashboard-modal .modal-content {
        border: 0;
        border-radius: 22px;
        overflow: hidden;
    }

    .tana-workspace-page .dashboard-modal .modal-header {
        background: linear-gradient(135deg, var(--tana-primary), #5f1515);
        color: #fff;
        border-bottom: 0;
    }

    .tana-workspace-page .dashboard-modal .modal-body {
        padding: 1.25rem;
    }

    .tana-workspace-page .dashboard-modal label {
        display: block;
        margin-bottom: 0.45rem;
        font-weight: 700;
        color: var(--tana-ink);
    }

    @media (max-width: 1199.98px) {
        .tana-workspace-page .tana-hero {
            grid-template-columns: 1fr;
        }

        .tana-workspace-page .hero-actions {
            max-width: 100%;
            margin-left: 0;
        }

        .tana-workspace-page .tana-badge {
            align-self: flex-start;
        }
    }

    @media (max-width: 991.98px) {
        .tana-workspace-page .score-grid {
            grid-template-columns: 1fr;
        }

        .tana-workspace-page .indicator-top {
            flex-direction: column;
        }

        .tana-workspace-page .indicator-status {
            white-space: normal;
        }
    }

    @media (max-width: 767.98px) {
        .tana-workspace-page .tana-hero,
        .tana-workspace-page .workspace-panel-body,
        .tana-workspace-page .workspace-panel-header,
        .tana-workspace-page .tana-stat-card {
            padding-left: 1rem;
            padding-right: 1rem;
        }

        .tana-workspace-page .workspace-panel-header {
            flex-direction: column;
        }

        .tana-workspace-page .workspace-actions,
        .tana-workspace-page .hero-actions,
        .tana-workspace-page .next-step-actions {
            grid-template-columns: 1fr;
        }

        .tana-workspace-page .hero-button,
        .tana-workspace-page .workspace-button,
        .tana-workspace-page .quick-link {
            width: 100%;
        }

        .tana-workspace-page .principle-toggle,
        .tana-workspace-page .indicator-footer {
            flex-direction: column;
            align-items: flex-start;
        }

        .tana-workspace-page .principle-meta {
            width: 100%;
            justify-content: space-between;
        }

        .tana-workspace-page .priority-note {
            text-align: left;
        }
    }
</style>

<div class="tana-workspace-page">
    <div class="tana-hero">
        <div class="hero-copy">
            <span class="hero-eyebrow">
                <i class="mdi mdi-chart-timeline-variant"></i>
                TANA Prioritization Workspace
            </span>
            <h1>Technical Assistance Needs Assessment Form</h1>
            <p>
                Score each concern using Strategic Importance, Urgency, Magnitude, and Feasibility so the school can build a defensible priority shortlist for <?= $escape($fiscal_year_label); ?>.
            </p>
        </div>

        <div class="hero-side">
            <div class="hero-actions">
                <a href="<?= $profile_url; ?>" class="hero-button hero-button-primary">
                    <i class="mdi mdi-account-school-outline"></i>
                    View Profile
                </a>
                <a href="#" class="hero-button hero-button-secondary" data-toggle="modal" data-target="#tanaFiscalYearModal">
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

    <?php if (!$ta_record) : ?>
        <div class="info-alert">
            <i class="mdi mdi-alert-circle-outline"></i>
            <div>
                <strong>TA concern narratives are not available yet for this fiscal year</strong>
                <p>The TANA form can still be opened, but the best scoring basis comes from the concern statements encoded in the TA Provision Report.</p>
            </div>
        </div>
    <?php elseif ($ready_concern_count < $total_questions) : ?>
        <div class="info-alert">
            <i class="mdi mdi-file-document-outline"></i>
            <div>
                <strong>Some indicators still have no concern narrative</strong>
                <p>Only <?= $ready_concern_count; ?> of <?= $total_questions; ?> indicators currently have a TA concern statement. The rest can be scored later after their TA notes are completed.</p>
            </div>
        </div>
    <?php endif; ?>

    <div class="row tana-stats">
        <div class="col-md-4 col-xl-4">
            <div class="tana-stat-card">
                <div class="tana-stat-top">
                    <div>
                        <small>Ready Concerns</small>
                        <h3 id="tanaReadyConcerns"><?= $ready_concern_count; ?>/<?= $total_questions; ?></h3>
                        <p>Indicators that already have a TA concern narrative and are ready for proper TANA scoring.</p>
                    </div>
                    <span class="tana-stat-icon"><i class="mdi mdi-file-check-outline"></i></span>
                </div>
                <div class="mini-progress"><span style="width: <?= $total_questions > 0 ? min(100, ($ready_concern_count / $total_questions) * 100) : 0; ?>%;"></span></div>
            </div>
        </div>

        <div class="col-md-4 col-xl-4">
            <div class="tana-stat-card">
                <div class="tana-stat-top">
                    <div>
                        <small>Fully Scored Indicators</small>
                        <h3 id="tanaFullyScored"><?= $fully_scored_count; ?>/<?= $total_questions; ?></h3>
                        <p>Indicators with all four decision lenses already scored and ready for averaging.</p>
                    </div>
                    <span class="tana-stat-icon"><i class="mdi mdi-numeric-4-box-multiple-outline"></i></span>
                </div>
                <div class="mini-progress"><span style="width: <?= $total_questions > 0 ? min(100, ($fully_scored_count / $total_questions) * 100) : 0; ?>%;"></span></div>
            </div>
        </div>

        <div class="col-md-4 col-xl-4">
            <div class="tana-stat-card">
                <div class="tana-stat-top">
                    <div>
                        <small>Mean Priority Score</small>
                        <h3 id="tanaMeanScore"><?= $fully_scored_count > 0 ? number_format($mean_priority_score, 2) : '--'; ?></h3>
                        <p><span id="tanaHighPriorityCount"><?= $high_priority_count; ?></span> indicator(s) currently sit in the stronger priority range based on saved averages.</p>
                    </div>
                    <span class="tana-stat-icon"><i class="mdi mdi-chart-box-outline"></i></span>
                </div>
                <div class="mini-progress"><span style="width: <?= $fully_scored_count > 0 ? min(100, ($mean_priority_score / 5) * 100) : 0; ?>%;"></span></div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-8">
            <section class="workspace-panel">
                <div class="workspace-panel-header">
                    <div>
                        <h4>TANA Scoring Workspace</h4>
                        <p>Open a principle, read the linked concern statement from the TA report, then rate the concern across the four TANA decision lenses. The average updates after all four scores are selected.</p>
                    </div>
                    <small><?= $escape($fiscal_year_label); ?></small>
                </div>
                <div class="workspace-panel-body" id="tanaWorkspace">
                    <?= form_open($form_action, array('class' => 'tana-workspace-form')); ?>
                    <?php if (!$tana_record) : ?>
                        <input type="hidden" name="district" value="<?= $escape($this->session->district); ?>">
                    <?php endif; ?>
                    <?php if ($tana_record) : ?>
                        <input type="hidden" name="id" value="<?= $escape($tana_record->id); ?>">
                    <?php endif; ?>

                    <div class="principle-links">
                        <?php foreach ($principles as $principle) :
                            $principle_id = isset($principle->id) ? (string) $principle->id : '';
                            $progress = isset($principle_progress[$principle_id]) ? $principle_progress[$principle_id] : array('scored' => 0, 'total' => 0);
                        ?>
                            <a href="#tanaPrincipleCollapse<?= $principle_id; ?>" class="principle-link" data-toggle="collapse" aria-controls="tanaPrincipleCollapse<?= $principle_id; ?>">
                                <i class="mdi mdi-view-day-outline"></i>
                                <?= $escape($principle->indicator); ?>
                                <span><?= $progress['scored']; ?>/<?= $progress['total']; ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>

                    <div class="tana-principle-list">
                        <?php if (empty($principles)) : ?>
                            <div class="empty-state">
                                <i class="mdi mdi-file-document-alert-outline"></i>
                                <strong>No TANA principles are available</strong>
                                <p>The form cannot be rendered because the SBM principle list is currently empty.</p>
                            </div>
                        <?php endif; ?>

                        <?php foreach ($principles as $principle) :
                            $principle_id = isset($principle->id) ? (string) $principle->id : '';
                            $principle_questions = isset($questions_by_principle[$principle_id]) ? $questions_by_principle[$principle_id] : array();
                            $progress = isset($principle_progress[$principle_id]) ? $principle_progress[$principle_id] : array('ready' => 0, 'scored' => 0, 'total' => 0);
                        ?>
                            <div class="principle-card">
                                <div class="principle-header" id="tanaPrincipleHeading<?= $principle_id; ?>">
                                    <a
                                        href="#tanaPrincipleCollapse<?= $principle_id; ?>"
                                        class="principle-toggle"
                                        data-toggle="collapse"
                                        aria-expanded="<?= $principle_id === '1' ? 'true' : 'false'; ?>"
                                        aria-controls="tanaPrincipleCollapse<?= $principle_id; ?>"
                                    >
                                        <div class="principle-main">
                                            <strong><?= $escape($principle->indicator); ?></strong>
                                            <span><?= $escape($principle->description); ?></span>
                                        </div>
                                        <div class="principle-meta">
                                            <span class="principle-progress-pill">
                                                <i class="mdi mdi-check-circle-outline"></i>
                                                <?= $progress['scored']; ?>/<?= $progress['total']; ?>
                                            </span>
                                            <i class="mdi mdi-chevron-down principle-icon"></i>
                                        </div>
                                    </a>
                                </div>

                                <div
                                    id="tanaPrincipleCollapse<?= $principle_id; ?>"
                                    class="collapse <?= $principle_id === '1' ? 'show' : ''; ?>"
                                    aria-labelledby="tanaPrincipleHeading<?= $principle_id; ?>"
                                    data-parent=".tana-principle-list"
                                >
                                    <div class="principle-body">
                                        <div class="indicator-list">
                                            <?php foreach ($principle_questions as $question) :
                                                $index = (int) $question->i_no;
                                                $manifestation_field = 'q' . $index;
                                                $concern_field = 'q' . $index;
                                                $manifestation_value = ($checklist_record && isset($checklist_record->$manifestation_field))
                                                    ? (int) $checklist_record->$manifestation_field
                                                    : 0;
                                                if (!isset($manifestation_labels[$manifestation_value])) {
                                                    $manifestation_value = 0;
                                                }
                                                $manifestation = $manifestation_labels[$manifestation_value];
                                                $concern_text = ($ta_record && isset($ta_record->$concern_field)) ? trim((string) $ta_record->$concern_field) : '';
                                                $score_values = array();
                                                $score_sum = 0;
                                                $score_count = 0;
                                                foreach ($score_dimensions as $prefix => $dimension) {
                                                    $field = $prefix . $index;
                                                    $score_values[$prefix] = ($tana_record && isset($tana_record->$field)) ? (int) $tana_record->$field : 0;
                                                    if ($score_values[$prefix] > 0) {
                                                        $score_sum += $score_values[$prefix];
                                                        $score_count++;
                                                    }
                                                }
                                                $average_score = $score_count === 4 ? $score_sum / 4 : null;
                                                $priority_note = $average_score === null
                                                    ? 'Complete all four scores to compute the average.'
                                                    : ($average_score >= 4
                                                        ? 'Currently trending as a strong shortlist candidate.'
                                                        : ($average_score >= 3
                                                            ? 'Moderate priority so far. Compare it with other concern averages.'
                                                            : 'Lower current priority based on the saved scores.'));
                                            ?>
                                                <div class="indicator-card" data-indicator-card>
                                                    <div class="indicator-top">
                                                        <span class="indicator-number"><?= $escape($question->i_no); ?></span>
                                                        <div class="indicator-copy">
                                                            <h5><?= $escape($question->description); ?></h5>
                                                            <p>Use the TA concern as the scoring basis for this indicator, then assess how strongly it deserves technical assistance attention this fiscal year.</p>
                                                        </div>
                                                        <span class="indicator-status <?= $manifestation['class']; ?>">
                                                            <i class="mdi mdi-clipboard-pulse-outline"></i>
                                                            <?= $escape($manifestation['short']); ?>
                                                        </span>
                                                    </div>

                                                    <div class="concern-panel<?= $concern_text === '' ? ' concern-panel-empty' : ''; ?>">
                                                        <strong>Concern Basis from TA Provision Report</strong>
                                                        <?php if ($concern_text !== '') : ?>
                                                            <p><?= $escape($concern_text); ?></p>
                                                        <?php else : ?>
                                                            <p>No TA concern narrative is saved for this indicator yet. You can still assign scores, but the priority basis will be clearer after the concern is written in the TA form.</p>
                                                            <a href="<?= $ta_url; ?>" class="workspace-button workspace-button-secondary">
                                                                <i class="mdi mdi-open-in-new"></i>
                                                                Open TA form
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>

                                                    <div class="score-grid">
                                                        <?php foreach ($score_dimensions as $prefix => $dimension) :
                                                            $field = $prefix . $index;
                                                            $selected_value = $score_values[$prefix];
                                                        ?>
                                                            <div class="score-card">
                                                                <h6><?= $escape($dimension['label']); ?></h6>
                                                                <p><?= $escape($dimension['hint']); ?></p>
                                                                <select class="score-select tana-score-select" name="<?= $escape($field); ?>">
                                                                    <option value=""></option>
                                                                    <?php foreach ($score_scale as $score_value => $score_label) : ?>
                                                                        <option value="<?= $score_value; ?>" <?= $selected_value === $score_value ? 'selected' : ''; ?>>
                                                                            <?= $score_value; ?> - <?= $escape($score_label); ?>
                                                                        </option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>

                                                    <div class="indicator-footer">
                                                        <div class="average-chip">
                                                            <div>
                                                                <strong data-average-display><?= $average_score !== null ? number_format($average_score, 2) : '--'; ?></strong>
                                                                <small>Current average</small>
                                                            </div>
                                                        </div>
                                                        <div class="priority-note" data-average-note><?= $escape($priority_note); ?></div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="workspace-actions" id="tanaActions">
                        <?php if ($can_edit) : ?>
                            <button type="submit" name="<?= $tana_record ? 'submit_edit' : 'submit'; ?>" class="workspace-button workspace-button-primary">
                                <i class="mdi mdi-content-save-outline"></i>
                                Save
                            </button>
                        <?php endif; ?>
                        <a href="<?= $summary_url; ?>" class="workspace-button workspace-button-success">
                            <i class="mdi mdi-format-list-numbered"></i>
                            Open TANA Summary
                        </a>
                        <a href="<?= $dashboard_url; ?>" class="workspace-button workspace-button-secondary">
                            <i class="mdi mdi-view-dashboard-outline"></i>
                            Back to Dashboard
                        </a>
                    </div>

                    <p class="workspace-note">
                        Saving the draft keeps the detailed scores available for the TANA Summary page. The shortlist itself is managed on the summary screen, where the computed averages are ranked and assigned priorities.
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
                        <h5>Score Dimensions</h5>
                        <p>Use a shared interpretation for the four lenses so the resulting averages are consistent and comparable across indicators.</p>
                        <div class="dimension-list">
                            <?php foreach ($score_dimensions as $prefix => $dimension) : ?>
                                <div class="dimension-item">
                                    <strong><?= $escape($dimension['label']); ?></strong>
                                    <span><?= $escape($dimension['hint']); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="sidebar-card">
                        <h5>Current Priority Signals</h5>
                        <p>These snapshots are based on the currently saved ratings, not yet on any unsaved edits you make in the form.</p>
                        <div class="priority-list">
                            <div class="priority-item">
                                <strong>Saved form status</strong>
                                <span><?= $escape($status_label); ?> for <?= $escape($fiscal_year_label); ?>.</span>
                            </div>
                            <div class="priority-item">
                                <strong>Average strategic importance</strong>
                                <span><?= $score_counts['a'] > 0 ? number_format($dimension_averages['a'], 2) : '--'; ?> across scored indicators.</span>
                            </div>
                            <div class="priority-item">
                                <strong>Average urgency</strong>
                                <span><?= $score_counts['b'] > 0 ? number_format($dimension_averages['b'], 2) : '--'; ?> across scored indicators.</span>
                            </div>
                            <div class="priority-item">
                                <strong>Average magnitude</strong>
                                <span><?= $score_counts['c'] > 0 ? number_format($dimension_averages['c'], 2) : '--'; ?> across scored indicators.</span>
                            </div>
                            <div class="priority-item">
                                <strong>Average feasibility</strong>
                                <span><?= $score_counts['d'] > 0 ? number_format($dimension_averages['d'], 2) : '--'; ?> across scored indicators.</span>
                            </div>
                        </div>
                    </div>

                    <div class="sidebar-card">
                        <h5>Quick Links</h5>
                        <p>Move between the connected assessment and planning modules without going back through the side menu.</p>
                        <div class="quick-link-list">
                            <a href="<?= $ta_url; ?>" class="quick-link">
                                <span><i class="mdi mdi-file-document-edit-outline"></i> Open TA form</span>
                                <i class="mdi mdi-arrow-right"></i>
                            </a>
                            <a href="<?= $summary_url; ?>" class="quick-link">
                                <span><i class="mdi mdi-format-list-numbered"></i> Open TANA summary</span>
                                <i class="mdi mdi-arrow-right"></i>
                            </a>
                            <a href="<?= $checklist_url; ?>" class="quick-link">
                                <span><i class="mdi mdi-format-list-checks"></i> Review checklist</span>
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
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <div id="tanaFiscalYearModal" class="modal fade dashboard-modal" tabindex="-1" role="dialog" aria-labelledby="tanaFiscalYearModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tanaFiscalYearModalLabel">Change Fiscal Year</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="<?= base_url('Pages/change_fy'); ?>" method="post">
                        <input type="hidden" name="<?= html_escape($this->security->get_csrf_token_name()); ?>" value="<?= html_escape($this->security->get_csrf_hash()); ?>">
                        <label for="tanaFiscalYear">Fiscal Year</label>
                        <select id="tanaFiscalYear" name="new_fy" class="form-control" onchange="this.form.submit()">
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
    const cards = Array.from(document.querySelectorAll("[data-indicator-card]"));
    const fullyScoredDisplay = document.getElementById("tanaFullyScored");
    const meanScoreDisplay = document.getElementById("tanaMeanScore");
    const highPriorityDisplay = document.getElementById("tanaHighPriorityCount");

    function updateCard(card) {
        const selects = Array.from(card.querySelectorAll(".tana-score-select"));
        const averageDisplay = card.querySelector("[data-average-display]");
        const averageNote = card.querySelector("[data-average-note]");
        const values = selects.map(function (select) {
            return parseInt(select.value, 10) || 0;
        });
        const filled = values.filter(function (value) {
            return value > 0;
        });

        if (filled.length === 4) {
            const average = values.reduce(function (sum, value) {
                return sum + value;
            }, 0) / 4;

            averageDisplay.textContent = average.toFixed(2);

            if (average >= 4) {
                averageNote.textContent = "Currently trending as a strong shortlist candidate.";
            } else if (average >= 3) {
                averageNote.textContent = "Moderate priority so far. Compare it with other concern averages.";
            } else {
                averageNote.textContent = "Lower current priority based on the scores now selected.";
            }

            return average;
        }

        averageDisplay.textContent = "--";
        averageNote.textContent = filled.length === 0
            ? "Complete all four scores to compute the average."
            : "Add scores for the remaining lenses to compute the average.";

        return null;
    }

    function updateStats() {
        let fullyScoredCount = 0;
        let highPriorityCount = 0;
        let averageTotal = 0;

        cards.forEach(function (card) {
            const average = updateCard(card);
            if (average !== null) {
                fullyScoredCount += 1;
                averageTotal += average;
                if (average >= 4) {
                    highPriorityCount += 1;
                }
            }
        });

        if (fullyScoredDisplay) {
            fullyScoredDisplay.textContent = fullyScoredCount + "/<?= $total_questions; ?>";
        }

        if (meanScoreDisplay) {
            meanScoreDisplay.textContent = fullyScoredCount > 0 ? (averageTotal / fullyScoredCount).toFixed(2) : "--";
        }

        if (highPriorityDisplay) {
            highPriorityDisplay.textContent = highPriorityCount.toString();
        }
    }

    cards.forEach(function (card) {
        card.querySelectorAll(".tana-score-select").forEach(function (select) {
            select.addEventListener("change", updateStats);
        });
    });

    updateStats();
});
</script>
