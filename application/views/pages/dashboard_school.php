<?php
$school = $this->Common->one_cond_row('schools', 'schoolID', $this->session->username);
$division = $school ? $this->Page_model->one_cond_row('division', 'id', $school->division_id) : null;
$district = $school ? $this->Page_model->one_cond_row('district', 'id', $school->district_id) : null;
$checklist = $this->Common->two_cond_row('sbm', 'school_id', $this->session->username, 'fy', $this->session->fy);
$ta_form = $this->Common->two_cond_row('sbm_ta', 'school_id', $this->session->username, 'fy', $this->session->fy);
$tana_form = $this->Common->two_cond_row('tana', 'school_id', $this->session->username, 'fy', $this->session->fy);
$action_plan_count = $this->Common->two_cond_count_row('sgod_action_plan', 'school_id', $this->session->username, 'fy', $this->session->fy)->num_rows();
$tana_summary_count = $this->Common->two_cond_count_row('tana_summary', 'school_id', $this->session->username, 'fy', $this->session->fy)->num_rows();
$tana_summary_finalized = $this->Common->three_cond_count_row('tana_summary', 'school_id', $this->session->username, 'fy', $this->session->fy, 'stat', 1)->num_rows() > 0;
$indicator_rows = $this->Common->no_cond('sbm_sub_indicator');

$indicator_map = array();
foreach ($indicator_rows as $indicator_row) {
    $indicator_map[(int) $indicator_row->i_no] = $indicator_row;
}

$school_name = $school && trim((string) $school->schoolName) !== ''
    ? mb_convert_case((string) $school->schoolName, MB_CASE_TITLE, 'UTF-8')
    : mb_convert_case((string) $this->session->user, MB_CASE_TITLE, 'UTF-8');
$school_id = $school && trim((string) $school->schoolID) !== ''
    ? (string) $school->schoolID
    : (string) $this->session->username;
$division_name = $division && trim((string) $division->description) !== ''
    ? mb_convert_case((string) $division->description, MB_CASE_TITLE, 'UTF-8')
    : 'Not assigned';
$district_name = $district && trim((string) $district->description) !== ''
    ? mb_convert_case((string) $district->description, MB_CASE_TITLE, 'UTF-8')
    : 'Not assigned';
$school_email = $school && trim((string) $school->schoolEmail) !== ''
    ? (string) $school->schoolEmail
    : 'Not provided';
$school_head_name = $school
    ? trim(implode(' ', array_filter(array(
        trim((string) $school->adminFName),
        trim((string) $school->adminMName),
        trim((string) $school->adminLName),
    ))))
    : '';
$school_head_name = $school_head_name !== ''
    ? mb_convert_case($school_head_name, MB_CASE_TITLE, 'UTF-8')
    : 'Not provided';
$location = $school
    ? implode(', ', array_filter(array(
        trim((string) $school->brgy) !== '' ? mb_convert_case((string) $school->brgy, MB_CASE_TITLE, 'UTF-8') : '',
        trim((string) $school->city) !== '' ? mb_convert_case((string) $school->city, MB_CASE_TITLE, 'UTF-8') : '',
        trim((string) $school->province) !== '' ? mb_convert_case((string) $school->province, MB_CASE_TITLE, 'UTF-8') : '',
    )))
    : '';
$location = $location !== '' ? $location : 'Not provided';

$school_initials = '';
foreach (preg_split('/\s+/', trim($school_name)) as $word) {
    if ($word === '') {
        continue;
    }
    $school_initials .= mb_substr($word, 0, 1, 'UTF-8');
    if (mb_strlen($school_initials, 'UTF-8') >= 3) {
        break;
    }
}
$school_initials = $school_initials !== '' ? mb_strtoupper($school_initials, 'UTF-8') : 'SCH';

$category_labels = array(
    1 => 'Elementary',
    2 => 'Integrated (Elementary & JHS)',
    3 => 'Integrated (Elementary, JHS & SHS)',
    4 => 'Secondary (JHS only)',
    5 => 'Secondary (JHS & SHS)',
    6 => 'SHS - Stand Alone',
);
$offering_labels = array(
    1 => 'None',
    2 => 'School-Based ALS Program',
    3 => 'TLE-TVL Course Offerings',
    4 => 'School-Based ALS and TLE-TVL',
);
$sgc_labels = array(
    1 => 'Not Yet Organized',
    2 => 'Organized, Not Functional',
    3 => 'Functional',
);
$manifestation_labels = array(
    1 => 'Not Yet Manifested',
    2 => 'Rarely Manifested',
    3 => 'Frequently Manifested',
    4 => 'Always Manifested',
);
$manifestation_classes = array(
    1 => 'manifestation-one',
    2 => 'manifestation-two',
    3 => 'manifestation-three',
    4 => 'manifestation-four',
);

$school_category = $school && isset($category_labels[(int) $school->category])
    ? $category_labels[(int) $school->category]
    : 'Not provided';
$school_offering = $school && isset($offering_labels[(int) $school->schoolType])
    ? $offering_labels[(int) $school->schoolType]
    : 'Not provided';
$school_sgc = $school && isset($sgc_labels[(int) $school->sgc])
    ? $sgc_labels[(int) $school->sgc]
    : 'Not provided';

$profile_checks = array(
    $school && trim((string) $school->schoolName) !== '',
    $school && trim((string) $school->schoolEmail) !== '',
    $school_head_name !== 'Not provided',
    $school && trim((string) $school->adminEmail) !== '',
    $school && trim((string) $school->adminMobile) !== '',
    $school && trim((string) $school->province) !== '',
    $school && trim((string) $school->city) !== '',
    $school && trim((string) $school->brgy) !== '',
    $school && !empty($school->division_id),
    $school && !empty($school->district_id),
    $school && !empty($school->category),
    $school && !empty($school->schoolType),
    $school && !empty($school->sgc),
);
$profile_completed = 0;
foreach ($profile_checks as $profile_check) {
    if ($profile_check) {
        $profile_completed++;
    }
}
$profile_total = count($profile_checks);
$profile_completion_rate = $profile_total > 0 ? ($profile_completed / $profile_total) * 100 : 0;

$checklist_answered = 0;
$manifestation_counts = array(1 => 0, 2 => 0, 3 => 0, 4 => 0);
if ($checklist) {
    for ($i = 1; $i <= 42; $i++) {
        $field = 'q' . $i;
        $value = isset($checklist->$field) ? (int) $checklist->$field : 0;
        if ($value > 0) {
            $checklist_answered++;
            if (isset($manifestation_counts[$value])) {
                $manifestation_counts[$value]++;
            }
        }
    }
}
$checklist_completion_rate = ($checklist_answered / 42) * 100;
$checklist_is_final = $checklist && isset($checklist->stat) && (int) $checklist->stat === 1;
$checklist_ready_for_final = $checklist && $checklist_answered === 42;

$ta_indicator_count = 0;
if ($ta_form) {
    for ($i = 1; $i <= 42; $i++) {
        $has_value = false;
        foreach (array('q', 'qq', 'a', 'f') as $prefix) {
            $field = $prefix . $i;
            if (!isset($ta_form->$field)) {
                continue;
            }
            $value = $ta_form->$field;
            if (trim((string) $value) !== '' && (string) $value !== '0') {
                $has_value = true;
                break;
            }
        }
        if ($has_value) {
            $ta_indicator_count++;
        }
    }
}
$ta_is_final = $ta_form && isset($ta_form->stat) && (int) $ta_form->stat === 1;

$tana_indicator_count = 0;
if ($tana_form) {
    for ($i = 1; $i <= 42; $i++) {
        $has_score = false;
        foreach (array('a', 'b', 'c', 'd') as $prefix) {
            $field = $prefix . $i;
            $value = isset($tana_form->$field) ? (float) $tana_form->$field : 0;
            if ($value > 0) {
                $has_score = true;
                break;
            }
        }
        if ($has_score) {
            $tana_indicator_count++;
        }
    }
}

$averages = $this->Page_model->get_averages($this->session->username, $this->session->fy);
arsort($averages);
$top_priority_scores = array_slice($averages, 0, 3, true);

$profile_url = $school ? base_url() . 'school/' . rawurlencode($school_id) : '#';
$school_update_url = $school ? base_url() . 'Pages/school_update/' . rawurlencode($school->recID) : '#';
$fiscal_year_label = 'Fiscal Year ' . $this->session->fy;
$action_plan_url = base_url() . 'Pages/sbm_action_plan';
$action_plan_print_url = base_url() . 'Pages/sbm_action_plan_pview';
$checklist_url = base_url() . 'Pages/sbm_checklist';
$ta_url = base_url() . 'Pages/tapr_form';
$tana_url = base_url() . 'Pages/tana_form';
$tana_summary_url = base_url() . 'Pages/tana_summary';

$workflow_cards = array(
    array(
        'title' => 'School Profile',
        'icon' => 'mdi-school-outline',
        'status' => $profile_completion_rate >= 85 ? 'Updated' : ($profile_completion_rate >= 50 ? 'Needs review' : 'Needs setup'),
        'state_class' => $profile_completion_rate >= 85 ? 'state-complete' : 'state-warning',
        'summary' => $profile_completed . ' of ' . $profile_total . ' profile fields are filled.',
        'url' => $school_update_url,
        'cta' => 'Update school info',
    ),
    array(
        'title' => 'Self-Assessment Checklist',
        'icon' => 'mdi-format-list-checks',
        'status' => !$checklist ? 'Not started' : ($checklist_is_final ? 'Finalized' : 'Draft saved'),
        'state_class' => !$checklist ? 'state-empty' : ($checklist_is_final ? 'state-complete' : 'state-draft'),
        'summary' => !$checklist
            ? 'No checklist saved for this fiscal year yet.'
            : $checklist_answered . ' of 42 indicators answered.',
        'url' => $checklist_url,
        'cta' => !$checklist ? 'Start checklist' : 'Open checklist',
    ),
    array(
        'title' => 'TA Form',
        'icon' => 'mdi-wrench-outline',
        'status' => !$ta_form ? 'Not started' : ($ta_is_final ? 'Finalized' : 'Draft saved'),
        'state_class' => !$ta_form ? 'state-empty' : ($ta_is_final ? 'state-complete' : 'state-draft'),
        'summary' => !$ta_form
            ? 'Capture concerns, enabling factors, and support needs.'
            : $ta_indicator_count . ' indicators have TA details recorded.',
        'url' => $ta_url,
        'cta' => !$ta_form ? 'Start TA form' : 'Open TA form',
    ),
    array(
        'title' => 'TANA Scoring',
        'icon' => 'mdi-chart-line',
        'status' => !$tana_form ? 'Not started' : ($tana_indicator_count === 42 ? 'Completed' : 'Draft saved'),
        'state_class' => !$tana_form ? 'state-empty' : ($tana_indicator_count === 42 ? 'state-complete' : 'state-draft'),
        'summary' => !$tana_form
            ? 'Score indicators to surface the strongest technical assistance needs.'
            : $tana_indicator_count . ' of 42 indicators have TANA scores.',
        'url' => $tana_url,
        'cta' => !$tana_form ? 'Start TANA form' : 'Open TANA form',
    ),
    array(
        'title' => 'Priority Ranking',
        'icon' => 'mdi-format-list-numbered',
        'status' => !$tana_summary_count ? 'Not started' : ($tana_summary_finalized ? 'Finalized' : ''),
        'state_class' => !$tana_summary_count ? 'state-empty' : ($tana_summary_finalized ? 'state-complete' : 'state-draft'),
        'summary' => !$tana_summary_count
            ? 'Rank the top concerns after scoring your TANA indicators.'
            : $tana_summary_count . ' ranked priorities saved for this fiscal year.',
        'url' => $tana_summary_url,
        'cta' => !$tana_summary_count ? 'Rank priorities' : 'Open priority list',
    ),
    array(
        'title' => 'Action Plan',
        'icon' => 'mdi-clipboard-text-outline',
        'status' => $action_plan_count > 0 ? 'Active' : 'No entries yet',
        'state_class' => $action_plan_count > 0 ? 'state-complete' : 'state-empty',
        'summary' => $action_plan_count > 0
            ? $action_plan_count . ' action plan item(s) recorded for this fiscal year.'
            : 'Build the school action plan from the issues identified in your assessments.',
        'url' => $action_plan_url,
        'cta' => $action_plan_count > 0 ? 'Manage action plan' : 'Create action plan',
    ),
);

$next_step = array(
    'eyebrow' => 'Recommended next step',
    'title' => 'Keep your school profile accurate',
    'description' => 'Review the school identity, school head, and contact details so the rest of the submissions reflect current information.',
    'url' => $school_update_url,
    'cta' => 'Update school profile',
    'secondary_url' => $profile_url,
    'secondary_cta' => 'View full profile',
);

if (!$checklist) {
    $next_step = array(
        'eyebrow' => 'Start here',
        'title' => 'Begin the self-assessment checklist',
        'description' => 'The checklist is the foundation for the TA form, TANA scoring, and your action plan. Complete it first for Fiscal Year ' . $this->session->fy . '.',
        'url' => $checklist_url,
        'cta' => 'Start checklist',
        'secondary_url' => $school_update_url,
        'secondary_cta' => 'Review school info',
    );
} elseif (!$checklist_ready_for_final) {
    $next_step = array(
        'eyebrow' => 'Continue progress',
        'title' => 'Finish the remaining checklist indicators',
        'description' => 'You have answered ' . $checklist_answered . ' of 42 indicators. Complete the remaining items before finalizing your self-assessment.',
        'url' => $checklist_url,
        'cta' => 'Continue checklist',
        'secondary_url' => $profile_url,
        'secondary_cta' => 'View school profile',
    );
} elseif (!$checklist_is_final) {
    $next_step = array(
        'eyebrow' => 'Ready to finalize',
        'title' => 'Review and finalize the checklist',
        'description' => 'All 42 indicators are answered. Open the checklist, review the entries, and finalize the submission when you are ready.',
        'url' => $checklist_url,
        'cta' => 'Review checklist',
        'secondary_url' => $ta_url,
        'secondary_cta' => 'Preview TA form',
    );
} elseif (!$ta_form) {
    $next_step = array(
        'eyebrow' => 'Next in workflow',
        'title' => 'Prepare the TA form',
        'description' => 'Use the finalized checklist results to document concerns, enabling factors, and support needed for each indicator.',
        'url' => $ta_url,
        'cta' => 'Start TA form',
        'secondary_url' => $checklist_url,
        'secondary_cta' => 'Review checklist',
    );
} elseif (!$ta_is_final) {
    $next_step = array(
        'eyebrow' => 'Next in workflow',
        'title' => 'Review and finalize the TA form',
        'description' => 'Your TA form already has content. Review it, complete any missing fields, and finalize it once the assistance needs are ready.',
        'url' => $ta_url,
        'cta' => 'Open TA form',
        'secondary_url' => $tana_url,
        'secondary_cta' => 'Open TANA scoring',
    );
} elseif (!$tana_form) {
    $next_step = array(
        'eyebrow' => 'Next in workflow',
        'title' => 'Score your TANA indicators',
        'description' => 'Complete the TANA scoring sheet so you can identify which indicators should rise to the top of the school’s support priorities.',
        'url' => $tana_url,
        'cta' => 'Start TANA scoring',
        'secondary_url' => $ta_url,
        'secondary_cta' => 'Review TA form',
    );
} elseif (!$tana_summary_count) {
    $next_step = array(
        'eyebrow' => 'Prioritize now',
        'title' => 'Rank the top TANA priorities',
        'description' => 'Your TANA scores are in place. The next step is to arrange the highest-priority indicators into the ranked list for action planning.',
        'url' => $tana_summary_url,
        'cta' => 'Rank priorities',
        'secondary_url' => $tana_url,
        'secondary_cta' => 'Review TANA scores',
    );
} elseif (!$tana_summary_finalized) {
    $next_step = array(
        'eyebrow' => 'Almost done',
        'title' => 'Finalize the priority ranking',
        'description' => 'Your ranked list is saved. Open it again to review the sequence and finalize the priorities for this fiscal year.',
        'url' => $tana_summary_url,
        'cta' => 'Review ranking',
        'secondary_url' => $action_plan_url,
        'secondary_cta' => 'Open action plan',
    );
} elseif ($action_plan_count === 0) {
    $next_step = array(
        'eyebrow' => 'Turn plans into action',
        'title' => 'Build the action plan',
        'description' => 'Use the finalized TANA priorities to create concrete action plan items, timelines, expected outputs, and responsible people.',
        'url' => $action_plan_url,
        'cta' => 'Create action plan',
        'secondary_url' => $action_plan_print_url,
        'secondary_cta' => 'Print plan view',
    );
} else {
    $next_step = array(
        'eyebrow' => 'Dashboard is in good shape',
        'title' => 'Review progress and keep documents updated',
        'description' => 'Your major SBM workflow items are already in place. Use this dashboard to revisit details, refine the action plan, and keep the school profile current.',
        'url' => $action_plan_url,
        'cta' => 'Manage action plan',
        'secondary_url' => $action_plan_print_url,
        'secondary_cta' => 'Print plan view',
    );
}
?>

<style>
    .school-dashboard-page {
        --school-primary: #7f1d1d;
        --school-primary-light: #b83a4b;
        --school-accent: #d6a84b;
        --school-ink: #172033;
        --school-muted: #687386;
        --school-border: #e4e9f0;
        --school-surface: #f6f8fb;
        --school-success: #15803d;
        --school-warning: #c97a11;
        --school-info: #1d4ed8;
        --school-purple: #7c3aed;
    }

    .school-dashboard-page .alert {
        border-radius: 14px;
    }

    .school-dashboard-page .dashboard-hero {
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
            radial-gradient(circle at 100% 0%, rgba(255, 255, 255, .14), transparent 28%),
            radial-gradient(circle at 0% 100%, rgba(214, 168, 75, .18), transparent 24%),
            linear-gradient(135deg, #541117 0%, #7f1d1d 42%, #b83a4b 100%);
        box-shadow: 0 22px 44px rgba(84, 17, 23, .20);
        overflow: hidden;
    }

    .school-dashboard-page .dashboard-hero::after {
        content: '';
        position: absolute;
        right: -46px;
        bottom: -62px;
        width: 210px;
        height: 210px;
        border-radius: 50%;
        background: rgba(255, 255, 255, .08);
    }

    .school-dashboard-page .hero-copy,
    .school-dashboard-page .hero-side {
        position: relative;
        z-index: 1;
    }

    .school-dashboard-page .hero-copy {
        max-width: 760px;
    }

    .school-dashboard-page .hero-side {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        justify-content: space-between;
        gap: 16px;
        min-width: 220px;
    }

    .school-dashboard-page .hero-eyebrow {
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

    .school-dashboard-page .hero-eyebrow i {
        font-size: 15px;
    }

    .school-dashboard-page .dashboard-hero h1 {
        margin: 0;
        color: #fff;
        font-size: 30px;
        font-weight: 800;
        line-height: 1.1;
        letter-spacing: -.03em;
    }

    .school-dashboard-page .hero-summary {
        max-width: 700px;
        margin: 14px 0 18px;
        color: rgba(255, 255, 255, .86);
        font-size: 14px;
        line-height: 1.75;
    }

    .school-dashboard-page .hero-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .school-dashboard-page .hero-meta span {
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

    .school-dashboard-page .hero-avatar {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 88px;
        height: 88px;
        border: 3px solid rgba(255, 255, 255, .22);
        border-radius: 24px;
        color: var(--school-primary);
        background: #fff;
        font-size: 28px;
        font-weight: 800;
        box-shadow: 0 14px 26px rgba(58, 10, 17, .18);
    }

    .school-dashboard-page .hero-action-stack {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        justify-content: flex-end;
    }

    .school-dashboard-page .hero-button,
    .school-dashboard-page .hero-year-button,
    .school-dashboard-page .module-link,
    .school-dashboard-page .quick-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        border: 0;
        text-decoration: none !important;
        transition: transform .18s ease, box-shadow .18s ease, background .18s ease, color .18s ease;
    }

    .school-dashboard-page .hero-button:hover,
    .school-dashboard-page .hero-year-button:hover,
    .school-dashboard-page .module-link:hover,
    .school-dashboard-page .quick-link:hover {
        transform: translateY(-2px);
    }

    .school-dashboard-page .hero-year-button {
        padding: 11px 16px;
        border-radius: 999px;
        color: #fff;
        background: rgba(255, 255, 255, .14);
        border: 1px solid rgba(255, 255, 255, .22);
        font-size: 12px;
        font-weight: 700;
    }

    .school-dashboard-page .hero-button {
        padding: 11px 16px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 700;
    }

    .school-dashboard-page .hero-button-primary {
        color: var(--school-primary);
        background: #fff;
        box-shadow: 0 10px 20px rgba(61, 12, 29, .18);
    }

    .school-dashboard-page .hero-button-secondary {
        color: #fff;
        background: rgba(255, 255, 255, .10);
        border: 1px solid rgba(255, 255, 255, .18);
    }

    .school-dashboard-page .dashboard-stats {
        margin-bottom: 2px;
    }

    .school-dashboard-page .dashboard-stat-card {
        height: calc(100% - 20px);
        margin-bottom: 20px;
        padding: 20px;
        border: 1px solid var(--school-border);
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 8px 24px rgba(31, 45, 75, .06);
    }

    .school-dashboard-page .dashboard-stat-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
    }

    .school-dashboard-page .dashboard-stat-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 46px;
        height: 46px;
        border-radius: 14px;
        color: #fff;
        background: linear-gradient(135deg, var(--school-primary), var(--school-primary-light));
        font-size: 20px;
        box-shadow: 0 10px 18px rgba(127, 29, 29, .15);
    }

    .school-dashboard-page .dashboard-stat-card small {
        display: block;
        margin-bottom: 8px;
        color: var(--school-muted);
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
    }

    .school-dashboard-page .dashboard-stat-card h3 {
        margin: 0;
        color: var(--school-ink);
        font-size: 28px;
        font-weight: 800;
        line-height: 1;
    }

    .school-dashboard-page .dashboard-stat-card p {
        margin: 10px 0 0;
        color: var(--school-muted);
        font-size: 13px;
        line-height: 1.6;
    }

    .school-dashboard-page .mini-progress {
        height: 8px;
        margin-top: 14px;
        border-radius: 999px;
        background: #edf1f6;
        overflow: hidden;
    }

    .school-dashboard-page .mini-progress span {
        display: block;
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg, var(--school-primary), var(--school-accent));
    }

    .school-dashboard-page .dashboard-panel {
        margin-bottom: 22px;
        border: 1px solid var(--school-border);
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 10px 30px rgba(31, 45, 75, .07);
        overflow: hidden;
    }

    .school-dashboard-page .dashboard-panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 20px 24px;
        border-bottom: 1px solid var(--school-border);
    }

    .school-dashboard-page .dashboard-panel-header h4 {
        margin: 0 0 4px;
        color: var(--school-ink);
        font-size: 18px;
        font-weight: 700;
    }

    .school-dashboard-page .dashboard-panel-header p,
    .school-dashboard-page .dashboard-panel-header small {
        margin: 0;
        color: var(--school-muted);
        font-size: 12px;
        line-height: 1.6;
    }

    .school-dashboard-page .dashboard-panel-body {
        padding: 22px 24px;
    }

    .school-dashboard-page .workflow-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }

    .school-dashboard-page .workflow-card {
        display: flex;
        flex-direction: column;
        gap: 14px;
        padding: 18px;
        border: 1px solid var(--school-border);
        border-radius: 16px;
        background: linear-gradient(180deg, #fff 0%, #fbfcff 100%);
    }

    .school-dashboard-page .workflow-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
    }

    .school-dashboard-page .workflow-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 44px;
        height: 44px;
        border-radius: 14px;
        color: #fff;
        background: linear-gradient(135deg, var(--school-primary), var(--school-primary-light));
        font-size: 19px;
    }

    .school-dashboard-page .workflow-card h5 {
        margin: 0 0 5px;
        color: var(--school-ink);
        font-size: 15px;
        font-weight: 700;
    }

    .school-dashboard-page .workflow-card p {
        margin: 0;
        color: var(--school-muted);
        font-size: 12px;
        line-height: 1.7;
    }

    .school-dashboard-page .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 11px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
    }

    .school-dashboard-page .state-complete {
        color: #0f5132;
        background: #dbf7e8;
    }

    .school-dashboard-page .state-draft {
        color: #8a5b00;
        background: #fff2cf;
    }

    .school-dashboard-page .state-warning {
        color: #92400e;
        background: #ffedd5;
    }

    .school-dashboard-page .state-empty {
        color: #475569;
        background: #e9eef6;
    }

    .school-dashboard-page .module-link {
        padding: 10px 14px;
        border-radius: 12px;
        color: #fff;
        background: linear-gradient(135deg, var(--school-primary), var(--school-primary-light));
        font-size: 12px;
        font-weight: 700;
        align-self: flex-start;
    }

    .school-dashboard-page .module-link.module-link-secondary {
        color: var(--school-primary);
        background: #fbeef1;
    }

    .school-dashboard-page .assessment-summary-grid,
    .school-dashboard-page .focus-grid,
    .school-dashboard-page .snapshot-grid {
        display: grid;
        gap: 16px;
    }

    .school-dashboard-page .assessment-summary-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        margin-bottom: 18px;
    }

    .school-dashboard-page .assessment-summary-card,
    .school-dashboard-page .focus-card,
    .school-dashboard-page .snapshot-card,
    .school-dashboard-page .next-step-card {
        padding: 18px;
        border: 1px solid var(--school-border);
        border-radius: 16px;
        background: #fbfcff;
    }

    .school-dashboard-page .assessment-summary-card small,
    .school-dashboard-page .focus-card small,
    .school-dashboard-page .snapshot-card small {
        display: block;
        margin-bottom: 8px;
        color: var(--school-muted);
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
    }

    .school-dashboard-page .assessment-summary-card strong,
    .school-dashboard-page .focus-card strong,
    .school-dashboard-page .snapshot-card strong {
        display: block;
        color: var(--school-ink);
        font-size: 18px;
        font-weight: 800;
        line-height: 1.35;
    }

    .school-dashboard-page .assessment-summary-card p,
    .school-dashboard-page .focus-card p,
    .school-dashboard-page .snapshot-card p,
    .school-dashboard-page .next-step-card p {
        margin: 10px 0 0;
        color: var(--school-muted);
        font-size: 12px;
        line-height: 1.7;
    }

    .school-dashboard-page .manifestation-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .school-dashboard-page .manifestation-card {
        padding: 16px;
        border: 1px solid var(--school-border);
        border-radius: 14px;
        background: #fff;
    }

    .school-dashboard-page .manifestation-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 12px;
    }

    .school-dashboard-page .manifestation-top h6 {
        margin: 0 0 4px;
        color: var(--school-ink);
        font-size: 13px;
        font-weight: 700;
    }

    .school-dashboard-page .manifestation-top small {
        color: var(--school-muted);
    }

    .school-dashboard-page .manifestation-value {
        font-size: 18px;
        font-weight: 800;
    }

    .school-dashboard-page .manifestation-progress {
        height: 8px;
        border-radius: 999px;
        background: #edf1f6;
        overflow: hidden;
    }

    .school-dashboard-page .manifestation-progress span {
        display: block;
        height: 100%;
        border-radius: inherit;
    }

    .school-dashboard-page .manifestation-one .manifestation-value,
    .school-dashboard-page .manifestation-one .focus-rank {
        color: var(--school-purple);
    }

    .school-dashboard-page .manifestation-two .manifestation-value,
    .school-dashboard-page .manifestation-two .focus-rank {
        color: var(--school-warning);
    }

    .school-dashboard-page .manifestation-three .manifestation-value,
    .school-dashboard-page .manifestation-three .focus-rank {
        color: #0f766e;
    }

    .school-dashboard-page .manifestation-four .manifestation-value,
    .school-dashboard-page .manifestation-four .focus-rank {
        color: var(--school-success);
    }

    .school-dashboard-page .manifestation-one .manifestation-progress span { background: var(--school-purple); }
    .school-dashboard-page .manifestation-two .manifestation-progress span { background: var(--school-warning); }
    .school-dashboard-page .manifestation-three .manifestation-progress span { background: #0f766e; }
    .school-dashboard-page .manifestation-four .manifestation-progress span { background: var(--school-success); }

    .school-dashboard-page .empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 10px;
        padding: 28px 22px;
        border: 1px dashed #d9e0ea;
        border-radius: 16px;
        background: #fbfcff;
        text-align: center;
    }

    .school-dashboard-page .empty-state i {
        font-size: 28px;
        color: var(--school-primary-light);
    }

    .school-dashboard-page .empty-state strong {
        color: var(--school-ink);
        font-size: 15px;
        font-weight: 700;
    }

    .school-dashboard-page .empty-state p {
        margin: 0;
        color: var(--school-muted);
        font-size: 12px;
        line-height: 1.7;
    }

    .school-dashboard-page .focus-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .school-dashboard-page .focus-rank {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 10px;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .school-dashboard-page .focus-card h5 {
        margin: 0;
        color: var(--school-ink);
        font-size: 14px;
        font-weight: 700;
        line-height: 1.6;
    }

    .school-dashboard-page .next-step-card {
        background:
            radial-gradient(circle at top right, rgba(214, 168, 75, .18), transparent 34%),
            linear-gradient(180deg, #fff9ec 0%, #fffdf8 100%);
    }

    .school-dashboard-page .next-step-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        margin-bottom: 10px;
        padding: 7px 10px;
        border-radius: 999px;
        background: rgba(127, 29, 29, .08);
        color: var(--school-primary);
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .school-dashboard-page .next-step-card h5 {
        margin: 0;
        color: var(--school-ink);
        font-size: 20px;
        font-weight: 800;
        line-height: 1.35;
    }

    .school-dashboard-page .next-step-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 18px;
    }

    .school-dashboard-page .quick-link {
        width: 100%;
        justify-content: space-between;
        padding: 14px 16px;
        border-radius: 14px;
        color: var(--school-ink);
        background: #f8fafc;
        border: 1px solid var(--school-border);
        font-size: 13px;
        font-weight: 700;
    }

    .school-dashboard-page .quick-link span {
        display: inline-flex;
        align-items: center;
        gap: 10px;
    }

    .school-dashboard-page .snapshot-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .school-dashboard-page .snapshot-card i {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        margin-bottom: 12px;
        border-radius: 12px;
        color: var(--school-primary);
        background: #fbeef1;
        font-size: 18px;
    }

    .school-dashboard-page .snapshot-card .email-box {
        display: inline-block;
        padding: 8px 12px;
        margin: 8px 0;
        border: 1px solid var(--school-border);
        border-radius: 8px;
        background: #f8f9fa;
        word-break: break-all;
    }

    .school-dashboard-page .snapshot-card .email-box strong {
        font-size: 14px;
        color: var(--school-ink);
    }

    .school-dashboard-page .dashboard-modal .modal-content {
        border: 0;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 18px 40px rgba(23, 32, 51, .14);
    }

    .school-dashboard-page .dashboard-modal .modal-header {
        border-bottom: 0;
        background: linear-gradient(135deg, var(--school-primary), var(--school-primary-light));
    }

    .school-dashboard-page .dashboard-modal .modal-title {
        color: #fff;
        font-weight: 700;
    }

    .school-dashboard-page .dashboard-modal .modal-body {
        padding: 22px;
    }

    .school-dashboard-page .dashboard-modal label {
        display: block;
        margin-bottom: 8px;
        color: var(--school-ink);
        font-size: 12px;
        font-weight: 700;
    }

    .school-dashboard-page .dashboard-modal .form-control {
        height: 46px;
        border-radius: 12px;
        border: 1px solid var(--school-border);
    }

    @media (max-width: 1199.98px) {
        .school-dashboard-page .workflow-grid,
        .school-dashboard-page .focus-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 991.98px) {
        .school-dashboard-page .dashboard-hero {
            flex-direction: column;
            align-items: flex-start;
        }

        .school-dashboard-page .hero-side {
            width: 100%;
            min-width: 0;
            align-items: flex-start;
        }

        .school-dashboard-page .hero-action-stack {
            justify-content: flex-start;
        }

        .school-dashboard-page .workflow-grid,
        .school-dashboard-page .assessment-summary-grid,
        .school-dashboard-page .manifestation-grid,
        .school-dashboard-page .focus-grid,
        .school-dashboard-page .snapshot-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 575.98px) {
        .school-dashboard-page .dashboard-hero,
        .school-dashboard-page .dashboard-panel-body,
        .school-dashboard-page .dashboard-panel-header {
            padding-left: 18px;
            padding-right: 18px;
        }

        .school-dashboard-page .dashboard-hero h1 {
            font-size: 24px;
        }

        .school-dashboard-page .hero-meta,
        .school-dashboard-page .next-step-actions {
            flex-direction: column;
            align-items: stretch;
        }

        .school-dashboard-page .hero-year-button,
        .school-dashboard-page .hero-button,
        .school-dashboard-page .module-link {
            width: 100%;
        }
    }
</style>

<div class="school-dashboard-page">
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

    <div class="dashboard-hero">
        <div class="hero-copy">
            <span class="hero-eyebrow">
                <i class="mdi mdi-school-outline"></i>
                School Access Dashboard
            </span>
            <h1><?= html_escape($school_name); ?></h1>
            <div class="hero-meta">
                <span><i class="mdi mdi-card-account-details-outline"></i> School ID: <?= html_escape($school_id); ?></span>
                <span><i class="mdi mdi-map-marker-path"></i> <?= html_escape($division_name); ?></span>
                <span><i class="mdi mdi-map-marker-outline"></i> <?= html_escape($district_name); ?></span>
            </div>
        </div>

        <div class="hero-side">
            <div class="hero-action-stack">
                <a href="<?= $profile_url; ?>" class="hero-button hero-button-primary">
                    <i class="mdi mdi-account-school-outline"></i>
                    View Profile
                </a>
            </div>
        </div>
    </div>

    <?php if (!$school) : ?>
        <div class="dashboard-panel">
            <div class="dashboard-panel-body">
                <div class="empty-state">
                    <i class="mdi mdi-alert-circle-outline"></i>
                    <strong>School profile record was not found.</strong>
                    <p>This account can still access the workflow modules, but the school information card and profile links need a matching school record to display properly.</p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="row dashboard-stats">
        <div class="col-md-6 col-xl-3">
            <div class="dashboard-stat-card">
                <div class="dashboard-stat-top">
                    <div>
                        <small>Profile Readiness</small>
                        <h3><?= number_format($profile_completion_rate, 0); ?>%</h3>
                        <p><?= $profile_completed; ?> of <?= $profile_total; ?> key school fields are currently filled.</p>
                    </div>
                    <span class="dashboard-stat-icon"><i class="mdi mdi-account-check-outline"></i></span>
                </div>
                <div class="mini-progress"><span style="width: <?= min(100, max(0, $profile_completion_rate)); ?>%;"></span></div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="dashboard-stat-card">
                <div class="dashboard-stat-top">
                    <div>
                        <small>Checklist Progress</small>
                        <h3><?= $checklist_answered; ?>/42</h3>
                        <p><?= $checklist_is_final ? 'Checklist finalized for this fiscal year.' : 'Indicators answered in the self-assessment checklist.'; ?></p>
                    </div>
                    <span class="dashboard-stat-icon"><i class="mdi mdi-clipboard-check-outline"></i></span>
                </div>
                <div class="mini-progress"><span style="width: <?= min(100, max(0, $checklist_completion_rate)); ?>%;"></span></div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="dashboard-stat-card">
                <div class="dashboard-stat-top">
                    <div>
                        <small>Action Plan Items</small>
                        <h3><?= (int) $action_plan_count; ?></h3>
                        <p><?= $action_plan_count > 0 ? 'Existing action plan entries can be reviewed or updated.' : 'No action plan entries have been created yet.'; ?></p>
                    </div>
                    <span class="dashboard-stat-icon"><i class="mdi mdi-clipboard-text-outline"></i></span>
                </div>
                <div class="mini-progress"><span style="width: <?= min(100, ($action_plan_count / 10) * 100); ?>%;"></span></div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="dashboard-stat-card">
                <div class="dashboard-stat-top">
                    <div>
                        <small>TANA Priorities</small>
                        <h3><?= (int) $tana_summary_count; ?></h3>
                        <p><?= $tana_summary_finalized ? 'Priority list finalized and ready for follow-through.' : 'Ranked TANA priorities saved for this fiscal year.'; ?></p>
                    </div>
                    <span class="dashboard-stat-icon"><i class="mdi mdi-format-list-numbered"></i></span>
                </div>
                <div class="mini-progress"><span style="width: <?= min(100, ($tana_summary_count / 20) * 100); ?>%;"></span></div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-8">
            <section class="dashboard-panel">
                <div class="dashboard-panel-header">
                    <div>
                        <h4>Workflow Status</h4>
                        <p>Jump into the exact school workflow that needs attention, and see which modules are complete, saved as draft, or not started yet.</p>
                    </div>
                    <small><?= html_escape($fiscal_year_label); ?></small>
                </div>
                <div class="dashboard-panel-body">
                    <div class="workflow-grid">
                        <?php foreach ($workflow_cards as $workflow_card) : ?>
                            <div class="workflow-card">
                                <div class="workflow-head">
                                    <span class="workflow-icon"><i class="mdi <?= html_escape($workflow_card['icon']); ?>"></i></span>
                                    <span class="status-pill <?= html_escape($workflow_card['state_class']); ?>"><?= html_escape($workflow_card['status']); ?></span>
                                </div>
                                <div>
                                    <h5><?= html_escape($workflow_card['title']); ?></h5>
                                    <p><?= html_escape($workflow_card['summary']); ?></p>
                                </div>
                                <a href="<?= $workflow_card['url']; ?>" class="module-link<?= $workflow_card['state_class'] === 'state-empty' ? ' module-link-secondary' : ''; ?>">
                                    <i class="mdi mdi-arrow-right"></i>
                                    <?= html_escape($workflow_card['cta']); ?>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>

            <section class="dashboard-panel">
                <div class="dashboard-panel-header">
                    <div>
                        <h4>Assessment Pulse</h4>
                        <p>See how the current self-assessment entries are distributed across the four manifestation levels.</p>
                    </div>
                    <small>
                        <?= $checklist ? ($checklist_is_final ? 'Checklist finalized' : 'Checklist in draft') : 'No checklist yet'; ?>
                    </small>
                </div>
                <div class="dashboard-panel-body">
                    <?php if ($checklist) : ?>
                        <div class="assessment-summary-grid">
                            <div class="assessment-summary-card">
                                <small>Checklist Readiness</small>
                                <strong><?= number_format($checklist_completion_rate, 1); ?>%</strong>
                                <p><?= $checklist_answered; ?> of 42 indicators have values. <?= $checklist_ready_for_final ? 'The checklist is ready for final review.' : 'Continue answering the remaining indicators.'; ?></p>
                            </div>
                            <div class="assessment-summary-card">
                                <small>TA Documentation</small>
                                <strong><?= $ta_indicator_count; ?> indicators noted</strong>
                                <p><?= $ta_form ? ($ta_is_final ? 'The TA form is finalized for this fiscal year.' : 'TA details are saved as draft and can still be refined.') : 'The TA form has not been started yet.'; ?></p>
                            </div>
                        </div>

                        <div class="manifestation-grid">
                            <?php foreach ($manifestation_labels as $manifestation_key => $manifestation_label) :
                                $count = isset($manifestation_counts[$manifestation_key]) ? (int) $manifestation_counts[$manifestation_key] : 0;
                                $percentage = $checklist_answered > 0 ? ($count / $checklist_answered) * 100 : 0;
                            ?>
                                <div class="manifestation-card <?= html_escape($manifestation_classes[$manifestation_key]); ?>">
                                    <div class="manifestation-top">
                                        <div>
                                            <h6><?= html_escape($manifestation_label); ?></h6>
                                            <small><?= $count; ?> indicator<?= $count === 1 ? '' : 's'; ?></small>
                                        </div>
                                        <span class="manifestation-value"><?= number_format($percentage, 1); ?>%</span>
                                    </div>
                                    <div class="manifestation-progress">
                                        <span style="width: <?= min(100, max(0, $percentage)); ?>%;"></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <div class="empty-state">
                            <i class="mdi mdi-clipboard-outline"></i>
                            <strong>No self-assessment checklist saved yet</strong>
                            <p>Start the checklist first so the dashboard can show manifestation trends, completion progress, and follow-on workflow guidance.</p>
                            <a href="<?= $checklist_url; ?>" class="module-link"><i class="mdi mdi-arrow-right"></i> Start checklist</a>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            <section class="dashboard-panel">
                <div class="dashboard-panel-header">
                    <div>
                        <h4>Top Focus Indicators</h4>
                        <p>The highest current TANA averages help show which indicators may need the earliest attention in your support planning.</p>
                    </div>
                    <small><?= html_escape($fiscal_year_label); ?></small>
                </div>
                <div class="dashboard-panel-body">
                    <?php if (!empty($top_priority_scores)) : ?>
                        <div class="focus-grid">
                            <?php $focus_rank = 1; ?>
                            <?php foreach ($top_priority_scores as $indicator_number => $average_value) :
                                $indicator_detail = isset($indicator_map[(int) $indicator_number]) ? $indicator_map[(int) $indicator_number] : null;
                                $focus_class = isset($manifestation_classes[$focus_rank]) ? $manifestation_classes[$focus_rank] : 'manifestation-four';
                            ?>
                                <div class="focus-card <?= html_escape($focus_class); ?>">
                                    <span class="focus-rank">
                                        <i class="mdi mdi-target"></i>
                                        Priority <?= $focus_rank; ?>
                                    </span>
                                    <h5>
                                        <?= $indicator_detail ? html_escape($indicator_detail->description) : 'Indicator ' . html_escape($indicator_number); ?>
                                    </h5>
                                    <p>Indicator <?= html_escape($indicator_number); ?> has a current average score of <?= number_format((float) $average_value, 2); ?> based on the school’s TANA scoring.</p>
                                </div>
                                <?php $focus_rank++; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <div class="empty-state">
                            <i class="mdi mdi-chart-timeline-variant"></i>
                            <strong>No TANA scoring insights yet</strong>
                            <p>Complete the TANA form so the dashboard can surface the indicators with the strongest current support needs.</p>
                            <a href="<?= $tana_url; ?>" class="module-link"><i class="mdi mdi-arrow-right"></i> Open TANA form</a>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </div>

        <div class="col-xl-4">
            <section class="dashboard-panel">
                <div class="dashboard-panel-header">
                    <div>
                        <h4>Quick Links</h4>
                        <p>Open the most-used school pages without going through the side menu.</p>
                    </div>
                </div>
                <div class="dashboard-panel-body">
                    <div class="d-flex flex-column" style="gap: 12px;">
                        <a href="<?= $profile_url; ?>" class="quick-link">
                            <span><i class="mdi mdi-account-school-outline"></i> View school profile</span>
                            <i class="mdi mdi-arrow-right"></i>
                        </a>
                        <a href="<?= $checklist_url; ?>" class="quick-link">
                            <span><i class="mdi mdi-format-list-checks"></i> Open self-assessment checklist</span>
                            <i class="mdi mdi-arrow-right"></i>
                        </a>
                        <a href="<?= $ta_url; ?>" class="quick-link">
                            <span><i class="mdi mdi-wrench-outline"></i> Open TA form</span>
                            <i class="mdi mdi-arrow-right"></i>
                        </a>
                        <a href="<?= $tana_url; ?>" class="quick-link">
                            <span><i class="mdi mdi-chart-line"></i> Open TANA scoring</span>
                            <i class="mdi mdi-arrow-right"></i>
                        </a>
                        <a href="<?= $tana_summary_url; ?>" class="quick-link">
                            <span><i class="mdi mdi-format-list-numbered"></i> Open TANA priority ranking</span>
                            <i class="mdi mdi-arrow-right"></i>
                        </a>
                        <a href="<?= $action_plan_url; ?>" class="quick-link">
                            <span><i class="mdi mdi-clipboard-text-outline"></i> Manage action plan</span>
                            <i class="mdi mdi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </section>
        </div>
    </div>

</div>
