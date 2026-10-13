<?php
$principles = isset($sbm) && is_array($sbm) ? $sbm : array();
$indicators = isset($sbm_sub) && is_array($sbm_sub) ? $sbm_sub : array();
$view_school_id = isset($view_school_id) ? (string) $view_school_id : (string) $this->uri->segment(3);
$school = isset($school) ? $school : $this->Common->one_cond_row('schools', 'schoolID', $view_school_id);
$division = isset($division) ? $division : (($school && !empty($school->division_id)) ? $this->Page_model->one_cond_row('division', 'id', $school->division_id) : null);
$district = isset($district) ? $district : (($school && !empty($school->district_id)) ? $this->Page_model->one_cond_row('district', 'id', $school->district_id) : null);
$checklist_record = isset($checklist_record) ? $checklist_record : $this->Common->two_cond_row('sbm', 'school_id', $view_school_id, 'fy', $this->session->fy);
$ta_record = isset($sbmc) ? $sbmc : null;
$review_record = isset($sbm_remark) ? $sbm_remark : null;
$has_ta_submission = !empty($ta_record);
$is_locked = $has_ta_submission && isset($ta_record->stat) && (int) $ta_record->stat === 1;
$can_save_review = $has_ta_submission && !$is_locked;
$form_action = $review_record ? 'Pages/tapr_district_update' : 'Pages/tapr_admin';
$submit_label = $review_record ? 'Update Review' : 'Save Review';
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

$render_text_block = static function ($value, $placeholder = 'No entry provided.') use ($escape) {
    $value = trim((string) $value);
    $display_value = $value === '' ? $placeholder : $value;
    $empty_class = $value === '' ? ' is-empty' : '';

    return '<div class="read-only-text' . $empty_class . '">' . nl2br($escape($display_value)) . '</div>';
};

$manifestation_details = array(
    0 => array(
        'label' => 'Checklist Pending',
        'class' => 'manifestation-pending',
        'note' => 'No checklist basis recorded yet.',
    ),
    1 => array(
        'label' => 'Not Yet Manifested',
        'class' => 'manifestation-one',
        'note' => 'Immediate support need',
    ),
    2 => array(
        'label' => 'Rarely Manifested',
        'class' => 'manifestation-two',
        'note' => 'Inconsistent practice',
    ),
    3 => array(
        'label' => 'Frequently Manifested',
        'class' => 'manifestation-three',
        'note' => 'Needs stronger sustainment',
    ),
    4 => array(
        'label' => 'Always Manifested',
        'class' => 'manifestation-four',
        'note' => 'Strength to preserve',
    ),
    5 => array(
        'label' => 'No Data',
        'class' => 'manifestation-na',
        'note' => 'Missing evidence',
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

$school_name = $school && trim((string) $school->schoolName) !== ''
    ? $title_case($school->schoolName)
    : 'Unknown School';
$division_name = $division && trim((string) $division->description) !== ''
    ? $title_case($division->description)
    : 'Not assigned';
$district_name = $district && trim((string) $district->description) !== ''
    ? $title_case($district->description)
    : 'Not assigned';
$fiscal_year_label = 'Fiscal Year ' . $this->session->fy;
$status_label = !$has_ta_submission
    ? 'No TA form submitted yet'
    : ($is_locked ? 'Finalized and locked' : ($review_record ? 'Review in progress' : 'Ready for review'));
$status_class = !$has_ta_submission
    ? 'status-missing'
    : ($is_locked ? 'status-locked' : 'status-active');
$review_count = 0;
$school_entry_count = 0;
$always_manifested_count = 0;
$manifestation_gap_count = 0;
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

foreach ($indicators as $indicator) {
    $index = isset($indicator->i_no) ? (int) $indicator->i_no : 0;
    if ($index <= 0) {
        continue;
    }

    $manifestation_field = 'q' . $index;
    $concern_field = 'q' . $index;
    $facilitating_field = 'qq' . $index;
    $category_field = 'a' . $index;
    $commitment_field = 'f' . $index;
    $review_findings_field = 'fs' . $index;
    $review_remarks_field = 'q' . $index;

    $manifestation_value = ($checklist_record && isset($checklist_record->$manifestation_field))
        ? (int) $checklist_record->$manifestation_field
        : 0;
    if (!isset($manifestation_details[$manifestation_value])) {
        $manifestation_value = 0;
    }

    if ($manifestation_value === 4) {
        $always_manifested_count++;
    } else {
        $manifestation_gap_count++;
    }

    $school_values = array(
        trim((string) ($ta_record && isset($ta_record->$concern_field) ? $ta_record->$concern_field : '')),
        trim((string) ($ta_record && isset($ta_record->$facilitating_field) ? $ta_record->$facilitating_field : '')),
        trim((string) ($ta_record && isset($ta_record->$category_field) ? $ta_record->$category_field : '')),
        trim((string) ($ta_record && isset($ta_record->$commitment_field) ? $ta_record->$commitment_field : '')),
    );

    foreach ($school_values as $value) {
        if ($value !== '') {
            $school_entry_count++;
            break;
        }
    }

    $review_findings_value = trim((string) ($review_record && isset($review_record->$review_findings_field) ? $review_record->$review_findings_field : ''));
    $review_remarks_value = trim((string) ($review_record && isset($review_record->$review_remarks_field) ? $review_record->$review_remarks_field : ''));
    if ($review_findings_value !== '' || $review_remarks_value !== '') {
        $review_count++;
    }
}

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
    : 'TA';

$form_attributes = array(
    'class' => 'ta-review-form',
    'autocomplete' => 'off',
);
?>

<style>
    .ta-review-page {
        --review-primary: #7f1d1d;
        --review-primary-dark: #5f1515;
        --review-accent: #d6a84b;
        --review-ink: #172033;
        --review-muted: #687386;
        --review-border: #e4e9f0;
        --review-surface: #f6f8fb;
        --review-surface-strong: #fff9ef;
        --review-success: #166534;
        --review-warning: #b45309;
        --review-danger: #b91c1c;
        --review-shadow: 0 24px 60px rgba(15, 23, 42, 0.08);
        color: var(--review-ink);
        padding-bottom: 2rem;
    }

    .ta-review-page .review-hero {
        position: relative;
        overflow: hidden;
        display: grid;
        grid-template-columns: minmax(0, 1.55fr) minmax(280px, 0.75fr);
        gap: 1.5rem;
        margin-top: 1rem;
        margin-bottom: 1.5rem;
        padding: 2rem;
        border-radius: 28px;
        background:
            radial-gradient(circle at top right, rgba(214, 168, 75, 0.3), transparent 34%),
            linear-gradient(135deg, #fff9ef 0%, #ffffff 42%, #f8f2e6 100%);
        border: 1px solid rgba(214, 168, 75, 0.26);
        box-shadow: var(--review-shadow);
    }

    .ta-review-page .review-hero::after {
        content: "";
        position: absolute;
        right: -90px;
        bottom: -110px;
        width: 260px;
        height: 260px;
        border-radius: 50%;
        background: rgba(127, 29, 29, 0.08);
        pointer-events: none;
    }

    .ta-review-page .review-kicker {
        display: inline-flex;
        align-items: center;
        gap: 0.55rem;
        padding: 0.45rem 0.9rem;
        border-radius: 999px;
        background: rgba(127, 29, 29, 0.1);
        color: var(--review-primary);
        font-size: 0.82rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }

    .ta-review-page .review-hero h1 {
        margin: 1rem 0 0.8rem;
        color: var(--review-primary);
        font-size: clamp(2rem, 3.3vw, 2.9rem);
        line-height: 1.08;
    }

    .ta-review-page .review-hero p {
        margin: 0;
        color: var(--review-muted);
        font-size: 1rem;
        line-height: 1.75;
        max-width: 760px;
    }

    .ta-review-page .review-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.7rem;
        margin-top: 1.15rem;
    }

    .ta-review-page .review-meta span {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.55rem 0.95rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.76);
        border: 1px solid rgba(127, 29, 29, 0.08);
        color: var(--review-ink);
        font-size: 0.9rem;
    }

    .ta-review-page .review-side {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
    }

    .ta-review-page .school-badge {
        align-self: flex-end;
        width: 148px;
        min-height: 148px;
        padding: 1.2rem;
        border-radius: 26px;
        background: linear-gradient(160deg, var(--review-primary), var(--review-primary-dark) 100%);
        color: #fff;
        box-shadow: 0 22px 44px rgba(127, 29, 29, 0.22);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .ta-review-page .school-badge small {
        font-size: 0.72rem;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        opacity: 0.8;
    }

    .ta-review-page .school-badge strong {
        font-size: 2rem;
        letter-spacing: 0.08em;
        line-height: 1;
    }

    .ta-review-page .hero-status-card {
        display: grid;
        gap: 0.75rem;
        padding: 1rem 1.1rem;
        border-radius: 20px;
        background: rgba(255, 255, 255, 0.88);
        border: 1px solid rgba(127, 29, 29, 0.08);
    }

    .ta-review-page .hero-status-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
    }

    .ta-review-page .hero-status-row small {
        display: block;
        color: var(--review-muted);
        text-transform: uppercase;
        letter-spacing: 0.06em;
        font-size: 0.72rem;
        margin-bottom: 0.2rem;
    }

    .ta-review-page .hero-status-row strong {
        color: var(--review-ink);
        font-size: 1rem;
    }

    .ta-review-page .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.5rem 0.8rem;
        border-radius: 999px;
        font-size: 0.82rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .ta-review-page .status-active {
        color: var(--review-primary);
        background: rgba(127, 29, 29, 0.1);
    }

    .ta-review-page .status-locked {
        color: #0f4d92;
        background: rgba(59, 130, 246, 0.12);
    }

    .ta-review-page .status-missing {
        color: var(--review-warning);
        background: rgba(217, 119, 6, 0.14);
    }

    .ta-review-page .review-alert {
        border-radius: 16px;
        border-width: 1px;
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.06);
    }

    .ta-review-page .summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .ta-review-page .summary-card {
        padding: 1.1rem 1.15rem;
        border-radius: 20px;
        background: #fff;
        border: 1px solid var(--review-border);
        box-shadow: 0 16px 32px rgba(15, 23, 42, 0.05);
    }

    .ta-review-page .summary-card small {
        display: block;
        color: var(--review-muted);
        text-transform: uppercase;
        letter-spacing: 0.06em;
        font-size: 0.75rem;
        margin-bottom: 0.45rem;
    }

    .ta-review-page .summary-card strong {
        display: block;
        color: var(--review-ink);
        font-size: 1.8rem;
        line-height: 1;
        margin-bottom: 0.35rem;
    }

    .ta-review-page .summary-card span {
        color: var(--review-muted);
        font-size: 0.92rem;
        line-height: 1.5;
    }

    .ta-review-page .review-actions,
    .ta-review-page .review-actions-bottom {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 1.2rem;
        border-radius: 20px;
        background: #fff;
        border: 1px solid var(--review-border);
        box-shadow: 0 16px 32px rgba(15, 23, 42, 0.05);
    }

    .ta-review-page .review-actions {
        margin-bottom: 1.5rem;
    }

    .ta-review-page .review-actions-bottom {
        margin-top: 1.5rem;
    }

    .ta-review-page .action-copy strong {
        display: block;
        color: var(--review-ink);
        margin-bottom: 0.25rem;
    }

    .ta-review-page .action-copy span {
        color: var(--review-muted);
        line-height: 1.5;
    }

    .ta-review-page .action-buttons {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.75rem;
        justify-content: flex-end;
    }

    .ta-review-page .review-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.55rem;
        padding: 0.9rem 1.2rem;
        border-radius: 14px;
        border: 1px solid transparent;
        font-weight: 700;
        text-decoration: none;
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
    }

    .ta-review-page .review-button:hover {
        transform: translateY(-1px);
        text-decoration: none;
    }

    .ta-review-page .review-button-primary {
        color: #fff;
        background: linear-gradient(135deg, var(--review-primary) 0%, #a52838 100%);
        box-shadow: 0 18px 30px rgba(127, 29, 29, 0.18);
    }

    .ta-review-page .review-button-primary:hover {
        color: #fff;
        box-shadow: 0 22px 34px rgba(127, 29, 29, 0.24);
    }

    .ta-review-page .review-button-secondary {
        color: var(--review-primary);
        background: rgba(127, 29, 29, 0.08);
        border-color: rgba(127, 29, 29, 0.14);
    }

    .ta-review-page .review-button-secondary:hover {
        color: var(--review-primary-dark);
        border-color: rgba(127, 29, 29, 0.22);
    }

    .ta-review-page .principle-panel {
        margin-bottom: 1rem;
        border-radius: 24px;
        overflow: hidden;
        border: 1px solid var(--review-border);
        background: #fff;
        box-shadow: 0 16px 36px rgba(15, 23, 42, 0.06);
    }

    .ta-review-page .principle-toggle {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1.2rem 1.35rem;
        border: 0;
        background: linear-gradient(135deg, #fff 0%, #f9fbfd 100%);
        text-align: left;
        cursor: pointer;
    }

    .ta-review-page .principle-toggle:focus {
        outline: none;
    }

    .ta-review-page .principle-title {
        display: grid;
        gap: 0.3rem;
    }

    .ta-review-page .principle-title small {
        color: var(--review-muted);
        text-transform: uppercase;
        letter-spacing: 0.06em;
        font-size: 0.75rem;
    }

    .ta-review-page .principle-title strong {
        color: var(--review-ink);
        font-size: 1.05rem;
        line-height: 1.45;
    }

    .ta-review-page .principle-progress {
        display: inline-flex;
        align-items: center;
        gap: 0.6rem;
        padding: 0.5rem 0.8rem;
        border-radius: 999px;
        background: rgba(127, 29, 29, 0.08);
        color: var(--review-primary);
        font-size: 0.84rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .ta-review-page .principle-body {
        padding: 0 1.35rem 1.35rem;
        background: linear-gradient(180deg, #ffffff 0%, #fcfdfd 100%);
    }

    .ta-review-page .principle-description {
        margin: 0 0 1rem;
        padding: 1rem 1.1rem;
        border-radius: 16px;
        background: var(--review-surface);
        color: var(--review-muted);
        line-height: 1.7;
    }

    .ta-review-page .indicator-list {
        display: grid;
        gap: 1rem;
    }

    .ta-review-page .indicator-card {
        border: 1px solid var(--review-border);
        border-radius: 22px;
        overflow: hidden;
        background: #fff;
    }

    .ta-review-page .indicator-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        padding: 1.1rem 1.2rem;
        border-bottom: 1px solid var(--review-border);
        background: linear-gradient(135deg, rgba(214, 168, 75, 0.08) 0%, rgba(255, 255, 255, 0.92) 100%);
    }

    .ta-review-page .indicator-title {
        display: grid;
        gap: 0.35rem;
    }

    .ta-review-page .indicator-title small {
        color: var(--review-muted);
        text-transform: uppercase;
        letter-spacing: 0.06em;
        font-size: 0.75rem;
    }

    .ta-review-page .indicator-title strong {
        color: var(--review-ink);
        font-size: 1rem;
        line-height: 1.6;
    }

    .ta-review-page .manifestation-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.55rem;
        padding: 0.65rem 0.95rem;
        border-radius: 999px;
        font-size: 0.82rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .ta-review-page .manifestation-pill span {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 1.6rem;
        height: 1.6rem;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.45);
        font-size: 0.78rem;
    }

    .ta-review-page .manifestation-pending {
        color: #5b6474;
        background: rgba(100, 116, 139, 0.12);
    }

    .ta-review-page .manifestation-one {
        color: #8a1f1f;
        background: rgba(185, 28, 28, 0.12);
    }

    .ta-review-page .manifestation-two {
        color: #a34b0f;
        background: rgba(234, 88, 12, 0.12);
    }

    .ta-review-page .manifestation-three {
        color: #956400;
        background: rgba(245, 158, 11, 0.16);
    }

    .ta-review-page .manifestation-four {
        color: var(--review-success);
        background: rgba(34, 197, 94, 0.14);
    }

    .ta-review-page .manifestation-na {
        color: #374151;
        background: rgba(148, 163, 184, 0.16);
    }

    .ta-review-page .indicator-body {
        padding: 1.2rem;
    }

    .ta-review-page .indicator-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
    }

    .ta-review-page .indicator-panel {
        padding: 1rem;
        border: 1px solid var(--review-border);
        border-radius: 18px;
        background: #fff;
    }

    .ta-review-page .indicator-panel.panel-highlight {
        background: linear-gradient(180deg, #fffaf1 0%, #ffffff 100%);
    }

    .ta-review-page .indicator-panel.panel-review {
        background: linear-gradient(180deg, #faf5f6 0%, #ffffff 100%);
    }

    .ta-review-page .indicator-panel small {
        display: block;
        margin-bottom: 0.55rem;
        color: var(--review-muted);
        text-transform: uppercase;
        letter-spacing: 0.06em;
        font-size: 0.74rem;
    }

    .ta-review-page .indicator-panel strong {
        display: block;
        margin-bottom: 0.5rem;
        color: var(--review-ink);
        font-size: 0.96rem;
    }

    .ta-review-page .read-only-text {
        min-height: 78px;
        padding: 0.95rem 1rem;
        border-radius: 14px;
        background: var(--review-surface);
        color: var(--review-ink);
        line-height: 1.7;
        white-space: normal;
        word-break: break-word;
    }

    .ta-review-page .read-only-text.is-empty {
        color: var(--review-muted);
        font-style: italic;
    }

    .ta-review-page .category-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.55rem 0.85rem;
        border-radius: 999px;
        background: rgba(127, 29, 29, 0.08);
        color: var(--review-primary);
        font-weight: 700;
        font-size: 0.86rem;
    }

    .ta-review-page .category-chip.is-empty {
        color: var(--review-muted);
        background: rgba(100, 116, 139, 0.12);
        font-style: italic;
    }

    .ta-review-page .review-textarea {
        width: 100%;
        min-height: 128px;
        padding: 0.95rem 1rem;
        border: 1px solid #d7deea;
        border-radius: 14px;
        background: #fff;
        color: var(--review-ink);
        line-height: 1.7;
        resize: vertical;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .ta-review-page .review-textarea:focus {
        border-color: rgba(127, 29, 29, 0.45);
        box-shadow: 0 0 0 4px rgba(127, 29, 29, 0.08);
        outline: none;
    }

    .ta-review-page .review-textarea[readonly] {
        background: #f8fafc;
        color: var(--review-muted);
        cursor: not-allowed;
    }

    .ta-review-page .review-hint {
        margin-top: 0.65rem;
        color: var(--review-muted);
        font-size: 0.85rem;
        line-height: 1.5;
    }

    .ta-review-page .empty-state {
        padding: 2rem;
        border-radius: 24px;
        background: #fff;
        border: 1px solid var(--review-border);
        box-shadow: 0 16px 36px rgba(15, 23, 42, 0.06);
        text-align: center;
    }

    .ta-review-page .empty-state i {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 72px;
        height: 72px;
        margin-bottom: 1rem;
        border-radius: 22px;
        background: rgba(214, 168, 75, 0.16);
        color: var(--review-primary);
        font-size: 2rem;
    }

    .ta-review-page .empty-state h3 {
        margin-bottom: 0.55rem;
        color: var(--review-ink);
    }

    .ta-review-page .empty-state p {
        margin: 0;
        color: var(--review-muted);
        line-height: 1.7;
    }

    @media (max-width: 1199.98px) {
        .ta-review-page .summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 991.98px) {
        .ta-review-page .review-hero {
            grid-template-columns: 1fr;
            padding: 1.5rem;
        }

        .ta-review-page .review-side {
            align-items: flex-start;
        }

        .ta-review-page .school-badge {
            align-self: flex-start;
        }

        .ta-review-page .indicator-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 767.98px) {
        .ta-review-page .summary-grid {
            grid-template-columns: 1fr;
        }

        .ta-review-page .review-actions,
        .ta-review-page .review-actions-bottom,
        .ta-review-page .indicator-head,
        .ta-review-page .principle-toggle,
        .ta-review-page .hero-status-row {
            flex-direction: column;
            align-items: flex-start;
        }

        .ta-review-page .action-buttons {
            width: 100%;
            justify-content: stretch;
        }

        .ta-review-page .review-button {
            width: 100%;
        }

        .ta-review-page .indicator-body,
        .ta-review-page .principle-body,
        .ta-review-page .review-hero {
            padding-left: 1rem;
            padding-right: 1rem;
        }
    }
</style>

<div class="ta-review-page">
    <div class="row">
        <div class="col-12">
            <section class="review-hero">
                <div class="review-copy">
                    <span class="review-kicker">
                        <i class="mdi mdi-lifebuoy"></i>
                        District TA Review
                    </span>
                    <h1><?= $escape($school_name); ?></h1>
                    <div class="review-meta">
                        <span><i class="mdi mdi-card-account-details-outline"></i><?= $escape($view_school_id); ?></span>
                        <span><i class="mdi mdi-map-marker-outline"></i><?= $escape($district_name); ?></span>
                        <span><i class="mdi mdi-domain"></i><?= $escape($division_name); ?></span>
                        <span><i class="mdi mdi-calendar-range"></i><?= $escape($fiscal_year_label); ?></span>
                    </div>
                </div>
            </section>

            <?php if ($this->session->flashdata('success')) : ?>
                <div class="alert alert-success alert-dismissible fade show review-alert" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <?= $this->session->flashdata('success'); ?>
                </div>
            <?php endif; ?>

            <?php if ($this->session->flashdata('danger')) : ?>
                <div class="alert alert-danger alert-dismissible fade show review-alert" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <?= $this->session->flashdata('danger'); ?>
                </div>
            <?php endif; ?>

            <?php if ($validation_markup) : ?>
                <div class="alert alert-danger review-alert" role="alert">
                    <?= $validation_markup; ?>
                </div>
            <?php endif; ?>

            <?php if (!$has_ta_submission) : ?>
                <section class="empty-state">
                    <i class="mdi mdi-alert-circle-outline"></i>
                    <h3>No submitted TA form was found for this school.</h3>
                    <p>
                        There is nothing to review yet for <?= $escape($school_name); ?> under <?= $escape($fiscal_year_label); ?>. Once the school saves its TA Form, the reviewer fields for findings and remarks will appear here.
                    </p>
                </section>
            <?php else : ?>
                <?= form_open($form_action, $form_attributes); ?>
                    <section class="review-actions" id="taReviewActions">
                        <div class="action-copy">
                            <strong>Reviewer actions</strong>
                            <span>
                                School entries are shown as read-only references. Use the reviewer fields to capture significant findings and district remarks for each indicator.
                            </span>
                        </div>
                        <div class="action-buttons">
                            <?php if ($can_save_review) : ?>
                                <button type="submit" class="review-button review-button-primary">
                                    <i class="mdi mdi-content-save-outline"></i>
                                    <?= $escape($submit_label); ?>
                                </button>
                            <?php endif; ?>

                            <?php if ($is_locked && $this->session->position === 'division') : ?>
                                <button type="submit"
                                    formaction="<?= base_url(); ?>Pages/sbm_ta_unlock"
                                    formmethod="post"
                                    name="id"
                                    value="<?= (int) $ta_record->id; ?>"
                                    onclick="return confirm('Are you sure you want to unlock this TA form?');"
                                    class="review-button review-button-secondary"
                                >
                                    <i class="mdi mdi-lock-open-variant-outline"></i>
                                    Unlock TA Form
                                </button>
                            <?php elseif ($is_locked) : ?>
                                <span class="review-button review-button-secondary">
                                    <i class="mdi mdi-lock-outline"></i>
                                    Reviewer editing is locked
                                </span>
                            <?php endif; ?>
                        </div>
                    </section>

                    <div id="taReviewAccordion">
                        <?php foreach ($principles as $principle_index => $principle) :
                            $principle_id = isset($principle->id) ? (string) $principle->id : '';
                            $principle_questions = isset($questions_by_principle[$principle_id]) ? $questions_by_principle[$principle_id] : array();
                            $reviewed_in_principle = 0;

                            foreach ($principle_questions as $indicator) {
                                $indicator_no = isset($indicator->i_no) ? (int) $indicator->i_no : 0;
                                if ($indicator_no <= 0) {
                                    continue;
                                }

                                $review_findings_field = 'fs' . $indicator_no;
                                $review_remarks_field = 'q' . $indicator_no;
                                $review_findings_value = trim((string) ($review_record && isset($review_record->$review_findings_field) ? $review_record->$review_findings_field : ''));
                                $review_remarks_value = trim((string) ($review_record && isset($review_record->$review_remarks_field) ? $review_record->$review_remarks_field : ''));
                                if ($review_findings_value !== '' || $review_remarks_value !== '') {
                                    $reviewed_in_principle++;
                                }
                            }

                            $collapse_id = 'principleReview' . $principle_id;
                            $is_open = $principle_index === 0;
                        ?>
                            <section class="principle-panel">
                                <button
                                    class="principle-toggle"
                                    type="button"
                                    data-toggle="collapse"
                                    data-target="#<?= $escape($collapse_id); ?>"
                                    aria-expanded="<?= $is_open ? 'true' : 'false'; ?>"
                                    aria-controls="<?= $escape($collapse_id); ?>"
                                >
                                    <div class="principle-title">
                                        <small>SBM Principle</small>
                                        <strong><?= $escape((string) $principle->indicator); ?></strong>
                                    </div>
                                    <span class="principle-progress">
                                        <i class="mdi mdi-clipboard-check-outline"></i>
                                        <?= (int) $reviewed_in_principle; ?>/<?= count($principle_questions); ?> reviewed
                                    </span>
                                </button>

                                <div id="<?= $escape($collapse_id); ?>" class="collapse <?= $is_open ? 'show' : ''; ?>" data-parent="#taReviewAccordion">
                                    <div class="principle-body">
                                        <p class="principle-description"><?= $escape((string) $principle->description); ?></p>

                                        <div class="indicator-list">
                                            <?php foreach ($principle_questions as $indicator) :
                                                $indicator_no = isset($indicator->i_no) ? (int) $indicator->i_no : 0;
                                                if ($indicator_no <= 0) {
                                                    continue;
                                                }

                                                $manifestation_field = 'q' . $indicator_no;
                                                $concern_field = 'q' . $indicator_no;
                                                $facilitating_field = 'qq' . $indicator_no;
                                                $category_field = 'a' . $indicator_no;
                                                $commitment_field = 'f' . $indicator_no;
                                                $review_findings_field = 'fs' . $indicator_no;
                                                $review_remarks_field = 'q' . $indicator_no;

                                                $manifestation_value = ($checklist_record && isset($checklist_record->$manifestation_field))
                                                    ? (int) $checklist_record->$manifestation_field
                                                    : 0;
                                                if (!isset($manifestation_details[$manifestation_value])) {
                                                    $manifestation_value = 0;
                                                }
                                                $manifestation = $manifestation_details[$manifestation_value];

                                                $school_concern = $ta_record && isset($ta_record->$concern_field) ? trim((string) $ta_record->$concern_field) : '';
                                                $school_facilitating = $ta_record && isset($ta_record->$facilitating_field) ? trim((string) $ta_record->$facilitating_field) : '';
                                                $school_category = $ta_record && isset($ta_record->$category_field) ? trim((string) $ta_record->$category_field) : '';
                                                $school_commitment = $ta_record && isset($ta_record->$commitment_field) ? trim((string) $ta_record->$commitment_field) : '';
                                                $review_findings = $review_record && isset($review_record->$review_findings_field) ? trim((string) $review_record->$review_findings_field) : '';
                                                $review_remarks = $review_record && isset($review_record->$review_remarks_field) ? trim((string) $review_record->$review_remarks_field) : '';
                                            ?>
                                                <article class="indicator-card">
                                                    <div class="indicator-head">
                                                        <div class="indicator-title">
                                                            <small>Indicator <?= (int) $indicator_no; ?></small>
                                                            <strong><?= $escape((string) $indicator->description); ?></strong>
                                                        </div>
                                                        <div class="manifestation-pill <?= $escape($manifestation['class']); ?>">
                                                            <span><?= (int) $indicator_no; ?></span>
                                                            <?= $escape($manifestation['label']); ?>
                                                        </div>
                                                    </div>

                                                    <div class="indicator-body">
                                                        <div class="indicator-grid">
                                                            <section class="indicator-panel panel-highlight">
                                                                <small>Checklist basis</small>
                                                                <strong><?= $escape($manifestation['note']); ?></strong>
                                                                <?= $render_text_block($school_concern, 'No school entry under concerns, gaps, or bottlenecks.'); ?>
                                                            </section>

                                                            <section class="indicator-panel">
                                                                <small>Facilitating factors</small>
                                                                <strong>School context for always manifested indicators</strong>
                                                                <?= $render_text_block($school_facilitating, 'No facilitating factor recorded by the school.'); ?>
                                                            </section>

                                                            <section class="indicator-panel">
                                                                <small>Category</small>
                                                                <strong>Submitted support category</strong>
                                                                <?php if ($school_category !== '' && isset($category_labels[$school_category])) : ?>
                                                                    <span class="category-chip">
                                                                        <i class="mdi mdi-tag-outline"></i>
                                                                        <?= $escape($category_labels[$school_category]); ?>
                                                                    </span>
                                                                <?php elseif ($school_category !== '') : ?>
                                                                    <span class="category-chip">
                                                                        <i class="mdi mdi-tag-outline"></i>
                                                                        <?= $escape($school_category); ?>
                                                                    </span>
                                                                <?php else : ?>
                                                                    <span class="category-chip is-empty">
                                                                        <i class="mdi mdi-tag-outline"></i>
                                                                        No category selected
                                                                    </span>
                                                                <?php endif; ?>
                                                            </section>

                                                            <section class="indicator-panel">
                                                                <small>School commitment</small>
                                                                <strong>Proposed resolution or next step</strong>
                                                                <?= $render_text_block($school_commitment, 'No school commitment recorded for this indicator.'); ?>
                                                            </section>

                                                            <section class="indicator-panel panel-review">
                                                                <small>Reviewer finding</small>
                                                                <strong>Significant Findings</strong>
                                                                <textarea
                                                                    class="review-textarea"
                                                                    name="fs<?= (int) $indicator_no; ?>"
                                                                    rows="5"
                                                                    <?= $can_save_review ? '' : 'readonly'; ?>
                                                                ><?= $escape($review_findings); ?></textarea>
                                                                <div class="review-hint">Capture unusual positive or negative observations that may affect the organization or district response.</div>
                                                            </section>

                                                            <section class="indicator-panel panel-review">
                                                                <small>Reviewer remark</small>
                                                                <strong>District remark</strong>
                                                                <textarea
                                                                    class="review-textarea"
                                                                    name="r<?= (int) $indicator_no; ?>"
                                                                    rows="5"
                                                                    <?= $can_save_review ? '' : 'readonly'; ?>
                                                                ><?= $escape($review_remarks); ?></textarea>
                                                                <div class="review-hint">Use this field for reviewer direction, validation notes, or district-level follow-up guidance.</div>
                                                            </section>
                                                        </div>
                                                    </div>
                                                </article>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        <?php endforeach; ?>
                    </div>

                    <input type="hidden" name="school_id" value="<?= $escape($view_school_id); ?>">
                    <?php if ($review_record && isset($review_record->id)) : ?>
                        <input type="hidden" name="id" value="<?= (int) $review_record->id; ?>">
                    <?php endif; ?>

                    <section class="review-actions-bottom">
                        <div class="action-copy">
                            <strong>Final check before saving</strong>
                            <span>
                                Reviewer text is saved exactly as entered. Scroll through the principle cards to confirm all findings and remarks are complete for the indicators you reviewed.
                            </span>
                        </div>
                        <div class="action-buttons">
                            <?php if ($can_save_review) : ?>
                                <button type="submit" class="review-button review-button-primary">
                                    <i class="mdi mdi-content-save-all-outline"></i>
                                    <?= $escape($submit_label); ?>
                                </button>
                            <?php endif; ?>

                            <?php if ($is_locked && $this->session->position === 'division') : ?>
                                <button type="submit"
                                    formaction="<?= base_url(); ?>Pages/sbm_ta_unlock"
                                    formmethod="post"
                                    name="id"
                                    value="<?= (int) $ta_record->id; ?>"
                                    onclick="return confirm('Are you sure you want to unlock this TA form?');"
                                    class="review-button review-button-secondary"
                                >
                                    <i class="mdi mdi-lock-open-variant-outline"></i>
                                    Unlock TA Form
                                </button>
                            <?php endif; ?>
                        </div>
                    </section>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>
