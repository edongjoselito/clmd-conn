<?php
/**
 * Division dashboard — served at /division for division, division_head and ict.
 * The regional view lives in dashboard_region_learning_gap.php.
 */
$scope = array('type' => 'division', 'id' => (int) $this->session->division);
$summary = $this->Page_model->learning_gap_summary($scope);
$term_performance = $this->Page_model->learning_gap_term_performance($scope);
$area_performance = $this->Page_model->learning_gap_area_performance($scope);
$thematic_analysis = $this->Page_model->learning_gap_thematic_analysis($scope);
$records_url = base_url('Pages/learning_gap_records');
$overview_url = base_url('Pages/learning_gap');
$record_count = (int) $summary->record_count;
$school_count = (int) $summary->school_count;
$learners_assessed = (int) $summary->learners_assessed;
$learners_with_gap = (int) $summary->learners_with_gap;
$gap_rate = $learners_assessed > 0 ? ($learners_with_gap / $learners_assessed) * 100 : 0;
$cpl_record_count = array_sum(array_map(function ($row) { return (int) $row->cpl_record_count; }, $term_performance));
$theme_max_count = !empty($thematic_analysis['themes']) ? max(array_map(function ($theme) { return (int) $theme->record_count; }, $thematic_analysis['themes'])) : 0;
$div_setup_name = isset($division) && !empty($division->description) ? html_escape($division->description) : '';
$div_encoded_total = isset($encoded_total_schools) ? (int) $encoded_total_schools : 0;
$div_registered = isset($registered_school_count) ? (int) $registered_school_count : 0;
$div_signup_pct = isset($signup_percentage) ? (float) $signup_percentage : 0;
$div_district_count = isset($district_count) ? (int) $district_count : 0;
$div_fy = isset($this->session->fy) ? (int) $this->session->fy : (int) date('Y');
$div_submission_url = base_url('Pages/school_submission_monitoring');
$div_setup_url = base_url('Pages/division_setup');
$div_districts_url = base_url('pages/district_account/' . (int) $this->session->division);
$div_schools_url = base_url('pages/schools_division/' . (int) $this->session->division);

$this->load->helper('learning_gap');
$term_interp = lg_term_interpretation($term_performance, $record_count);

$theme_interp = array();
if (!empty($thematic_analysis['themes'])) {
    $source_count = (int) $thematic_analysis['source_count'];
    $theme_schools = (int) $thematic_analysis['school_count'];
    $lead = $thematic_analysis['themes'][0];
    $lead_records = (int) $lead->record_count;
    $lead_schools = (int) $lead->school_count;
    if ($source_count > 0) {
        $theme_interp[] = '<strong>' . html_escape($lead->theme) . '</strong> appears in ' . number_format(($lead_records / $source_count) * 100, 1) . '% of analyzed records.';
    }
    if ($lead_schools <= 1) {
        $theme_interp[] = 'It comes from a single school, so it may reflect one school\'s situation rather than a division-wide pattern.';
    } elseif ($theme_schools > 0 && $lead_schools / $theme_schools >= 0.5) {
        $theme_interp[] = 'It is reported by ' . number_format($lead_schools) . ' of ' . number_format($theme_schools) . ' reporting schools, which points to a division-wide priority for technical assistance.';
    } else {
        $theme_interp[] = 'It is reported by ' . number_format($lead_schools) . ' of ' . number_format($theme_schools) . ' reporting schools, so support can be targeted to those schools first.';
    }
    if ((int) $lead->intervention_count > (int) $lead->remarks_count) {
        $theme_interp[] = 'Most mentions are under Intervention / Action, meaning schools are already acting on it; monitor whether these actions lower the gap rate.';
    } elseif ((int) $lead->remarks_count > (int) $lead->intervention_count) {
        $theme_interp[] = 'Most mentions are under Remarks, meaning schools raise it as a concern more often than they report an action for it; this may need division guidance.';
    }
}

$reg_interp = array();
if ($div_encoded_total > 0) {
    $reg_remaining = $div_encoded_total - $div_registered;
    if ($reg_remaining < 0) {
        $reg_interp[] = 'Registered schools exceed the encoded total by ' . number_format(-$reg_remaining) . '. Update the total number of schools in Division Setup.';
    } elseif ($reg_remaining === 0) {
        $reg_interp[] = 'All encoded schools are registered, so registration is complete.';
    } else {
        $reg_status = $div_signup_pct >= 90 ? 'Registration is nearly complete' : ($div_signup_pct >= 50 ? 'Registration is in progress' : 'Registration is still low');
        $reg_interp[] = $reg_status . ': <strong>' . lg_count_label($reg_remaining, 'school') . '</strong> still need to register before their data can be included in division results.';
    }
    if ($div_registered > 0) {
        $not_reporting = max(0, $div_registered - $school_count);
        $reg_interp[] = number_format(min($school_count, $div_registered)) . ' of ' . lg_count_label($div_registered, 'registered school') . ' (' . number_format((min($school_count, $div_registered) / $div_registered) * 100, 1) . '%) ' . (min($school_count, $div_registered) === 1 ? 'has' : 'have') . ' submitted learning gap records for FY ' . $div_fy . '.'
            . ($not_reporting > 0 ? ' Follow up with the remaining ' . number_format($not_reporting) . ' through School Submissions.' : '');
    }
}
?>
<style>
    .scope-lg { --navy:var(--llcm-navy, #3b0718); --blue:var(--llcm-blue, #922c43); --sky:var(--llcm-sky, #fbf4f5); --line:var(--llcm-border, #ead7dc); --ink:#243447; --muted:#6d7e8e; --amber:#c98616; --green:#21815c; }
    .scope-lg .lg-hero { position:relative; overflow:hidden; display:flex; justify-content:space-between; align-items:center; gap:24px; margin:18px 0 22px; padding:31px; border-radius:18px; color:#fff; background:linear-gradient(125deg,var(--navy),var(--blue)); box-shadow:0 14px 30px rgba(18,63,99,.18); }
    .scope-lg .lg-hero:after { content:''; position:absolute; width:250px; height:250px; right:-75px; bottom:-155px; border:34px solid rgba(255,255,255,.12); border-radius:50%; }
    .scope-lg .lg-hero-copy, .scope-lg .lg-hero-actions { position:relative; z-index:1; }
    .scope-lg .lg-eyebrow { display:block; margin-bottom:7px; color:#c9eafa; font-size:11px; font-weight:700; letter-spacing:.1em; text-transform:uppercase; }
    .scope-lg .lg-hero h1 { margin:0 0 7px; color:#fff; font-size:28px; font-weight:700; }.scope-lg .lg-hero p { max-width:700px; margin:0; color:#dceefa; }
    .scope-lg .lg-hero-actions { display:flex; flex-wrap:wrap; gap:9px; }.scope-lg .lg-hero .btn { border-radius:999px; font-weight:700; white-space:nowrap; }.scope-lg .lg-hero .btn-outline-light { border-color:rgba(255,255,255,.65); }
    .scope-lg .lg-stat-link { display:block; height:calc(100% - 20px); margin-bottom:20px; color:inherit; text-decoration:none; }.scope-lg .lg-stat { height:100%; padding:19px; border:1px solid var(--line); border-radius:14px; background:#fff; box-shadow:0 6px 19px rgba(18,63,99,.06); transition:transform .18s ease,box-shadow .18s ease; }.scope-lg .lg-stat-link:hover .lg-stat { transform:translateY(-3px); box-shadow:0 12px 25px rgba(18,63,99,.12); }
    .scope-lg .lg-stat-top { display:flex; align-items:center; justify-content:space-between; gap:12px; }.scope-lg .lg-stat-icon { display:inline-flex; align-items:center; justify-content:center; width:43px; height:43px; border-radius:12px; color:var(--blue); background:var(--sky); font-size:21px; }.scope-lg .lg-stat small { display:block; margin-top:13px; color:var(--muted); font-size:11px; font-weight:700; letter-spacing:.06em; text-transform:uppercase; }.scope-lg .lg-stat strong { display:block; margin-top:4px; color:var(--navy); font-size:27px; line-height:1.1; }.scope-lg .lg-stat-hint { display:block; margin-top:10px; color:var(--blue); font-size:12px; font-weight:600; }
    .scope-lg .lg-card { height:calc(100% - 22px); margin-bottom:22px; border:1px solid var(--line); border-radius:15px; background:#fff; box-shadow:0 6px 19px rgba(18,63,99,.06); overflow:hidden; }.scope-lg .lg-card-head { display:flex; align-items:flex-start; justify-content:space-between; gap:15px; padding:20px 22px; border-bottom:1px solid var(--line); background:#fbfdfe; }.scope-lg .lg-card-head h4 { margin:0 0 4px; color:var(--navy); font-size:16px; font-weight:700; }.scope-lg .lg-card-head p,.scope-lg .lg-card-head small { margin:0; color:var(--muted); font-size:12px; }.scope-lg .lg-card-body { padding:20px 22px; }
    .scope-lg .empty-state { padding:25px; color:var(--muted); text-align:center; }
    .scope-lg .lg-glance-stat { display:flex; align-items:center; gap:16px; padding:14px 0; border-bottom:1px solid #edf2f5; }.scope-lg .lg-glance-stat:last-child { border-bottom:0; padding-bottom:0; }.scope-lg .lg-glance-stat:first-child { padding-top:0; }.scope-lg .lg-glance-icon { display:inline-flex; align-items:center; justify-content:center; flex:0 0 48px; width:48px; height:48px; border-radius:13px; font-size:22px; }.scope-lg .lg-glance-icon.navy { color:var(--navy); background:var(--sky); }.scope-lg .lg-glance-icon.green { color:var(--green); background:#e9f7f0; }.scope-lg .lg-glance-text { flex:1; min-width:0; }.scope-lg .lg-glance-text strong { display:block; color:var(--navy); font-size:22px; line-height:1.1; }.scope-lg .lg-glance-text small { color:var(--muted); font-size:12px; }.scope-lg .lg-glance-link { color:var(--blue); font-size:12px; font-weight:600; text-decoration:none; white-space:nowrap; }.scope-lg .lg-glance-link:hover { text-decoration:underline; }
    .scope-lg .lg-quick-grid { display:grid; grid-template-columns:1fr 1fr; gap:10px; }.scope-lg .lg-quick-action { display:flex; flex-direction:column; align-items:flex-start; gap:8px; padding:16px; border:1px solid var(--line); border-radius:12px; background:#fbfdfe; color:var(--ink); text-decoration:none; transition:border-color .18s ease,box-shadow .18s ease,transform .18s ease; }.scope-lg .lg-quick-action:hover { border-color:var(--blue); box-shadow:0 6px 16px rgba(18,63,99,.10); transform:translateY(-2px); text-decoration:none; }.scope-lg .lg-quick-action i { color:var(--blue); font-size:22px; }.scope-lg .lg-quick-action strong { color:var(--navy); font-size:13px; font-weight:700; }.scope-lg .lg-quick-action small { color:var(--muted); font-size:11px; }
    .scope-lg .performance-chart-wrap { position:relative; height:330px; }.scope-lg .chart-empty { display:none; align-items:center; justify-content:center; height:100%; color:var(--muted); text-align:center; }.scope-lg .chart-empty i { display:block; margin-bottom:8px; color:#9cb6c7; font-size:36px; }
    .scope-lg .analysis-badge { display:inline-flex; align-items:center; gap:5px; padding:5px 9px; border-radius:999px; color:#176a50; background:#e7f6ef; font-size:10px; font-weight:700; letter-spacing:.04em; text-transform:uppercase; }.scope-lg .theme-summary { display:flex; flex-wrap:wrap; gap:9px; margin-bottom:13px; }.scope-lg .theme-summary span { padding:6px 10px; border-radius:8px; color:#516879; background:#eef5f8; font-size:11px; }    .scope-lg .theme-insight { margin-bottom:17px; padding:13px 15px; border-left:4px solid var(--blue); border-radius:0 9px 9px 0; color:#38556a; background:var(--sky); font-size:12px; line-height:1.55; }.scope-lg .theme-insight strong { color:var(--navy); }.scope-lg .theme-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:12px; }.scope-lg .theme-item { padding:15px; border:1px solid var(--line); border-radius:12px; background:#fbfdfe; }.scope-lg .theme-item-head { display:flex; align-items:flex-start; justify-content:space-between; gap:10px; }.scope-lg .theme-item h5 { margin:0; color:var(--navy); font-size:13px; line-height:1.4; }.scope-lg .theme-count { flex:0 0 auto; color:var(--blue); font-size:17px; font-weight:800; }.scope-lg .theme-bar { height:6px; margin:11px 0 9px; border-radius:999px; background:#e8f0f4; overflow:hidden; }.scope-lg .theme-bar span { display:block; height:100%; border-radius:inherit; background:linear-gradient(90deg,var(--blue),#c66b7c); }.scope-lg .theme-meta { color:var(--muted); font-size:10px; }.scope-lg .theme-example { margin:10px 0 0; color:#526778; font-size:11px; font-style:italic; line-height:1.45; }.scope-lg .analysis-note { margin:14px 0 0; color:var(--muted); font-size:10px; }
    @media (max-width:991.98px) { .scope-lg .theme-grid { grid-template-columns:repeat(2,minmax(0,1fr)); } }
    @media (max-width:767.98px) { .scope-lg .lg-hero { align-items:flex-start; flex-direction:column; padding:24px; }.scope-lg .lg-hero-actions,.scope-lg .lg-hero .btn { width:100%; }.scope-lg .lg-card-head { padding:18px; }.scope-lg .lg-card-body { padding:18px; }.scope-lg .lg-quick-grid,.scope-lg .theme-grid { grid-template-columns:1fr; }.scope-lg .performance-chart-wrap { height:300px; } }
    /* Division workspace layout. */
    .division-workspace { padding-bottom: 10px; }
    .division-workspace a:focus-visible { outline:3px solid var(--blue); outline-offset:3px; }

    /* Hero */
    .division-workspace .lg-hero { align-items:center; padding:34px 36px; border-radius:20px; box-shadow:0 16px 36px rgba(18,63,99,.20); }
    .division-workspace .lg-hero:after { width:320px; height:320px; right:-90px; bottom:-180px; border-width:40px; }
    .division-workspace .lg-hero-copy { flex:1; min-width:0; }
    .division-workspace .lg-eyebrow { margin-bottom:10px; }
    .division-workspace .lg-hero h1 { font-size:30px; }
    .division-workspace .lg-hero p { font-size:15px; line-height:1.55; }
    .division-workspace .lg-hero-actions { flex:0 0 auto; gap:10px; }
    .division-workspace .lg-hero .btn { width:auto; padding:11px 18px; border-radius:10px; font-size:13px; white-space:nowrap; }
    .division-workspace .lg-hero .btn-light { color:var(--navy); }
    .division-workspace .lg-fy-badge { display:inline-flex; align-items:center; gap:6px; margin-top:16px; padding:6px 12px; border-radius:999px; color:#fff; background:rgba(255,255,255,.16); font-size:11px; font-weight:700; letter-spacing:.04em; }

    /* Stat cards */
    .division-workspace .lg-stat-link { height:calc(100% - 24px); margin-bottom:24px; }
    .division-workspace .lg-stat { padding:22px; border-radius:16px; box-shadow:0 8px 22px rgba(18,63,99,.07); transition:transform .18s ease, box-shadow .18s ease; }
    .division-workspace .lg-stat-link:hover .lg-stat { transform:translateY(-3px); box-shadow:0 14px 30px rgba(18,63,99,.13); }
    .division-workspace .lg-stat-icon { width:46px; height:46px; border-radius:13px; font-size:22px; }
    .division-workspace .lg-stat strong { font-size:30px; font-variant-numeric:tabular-nums; overflow-wrap:anywhere; }
    .division-workspace .lg-stat small { font-size:11px; }
    .division-workspace .lg-stat-hint { font-size:12px; }

    /* Shared chart/thematic cards: restore depth and breathing room */
    .division-workspace .lg-card { margin-bottom:24px; border-radius:16px; box-shadow:0 8px 22px rgba(18,63,99,.07); }
    .division-workspace .lg-card-head { padding:22px 26px; border-radius:16px 16px 0 0; flex-wrap:wrap; }
    .division-workspace .lg-card-head h4 { font-size:17px; }
    .division-workspace .lg-card-body { padding:24px 26px; }

    /* Section heading */
    .division-workspace .division-section-heading { margin:6px 0 22px; }
    .division-workspace .division-section-heading h2 { margin:0 0 6px; color:var(--navy); font-size:20px; font-weight:700; }
    .division-workspace .division-section-heading p { margin:0; color:var(--muted); font-size:13px; }

    /* Division network + registration cards */
    .divd-card { height:calc(100% - 24px); margin-bottom:24px; border:1px solid var(--line); border-radius:16px; background:#fff; box-shadow:0 8px 22px rgba(18,63,99,.07); overflow:hidden; }
    .divd-card-head { display:flex; align-items:flex-start; justify-content:space-between; gap:16px; padding:22px 26px; border-bottom:1px solid var(--line); background:#fbfdfe; }
    .divd-card-head h4 { margin:0 0 4px; color:var(--navy); font-size:16px; font-weight:700; }
    .divd-card-head p { margin:0; color:var(--muted); font-size:12px; }
    .divd-card-body { padding:24px 26px; }

    /* Registration progress */
    .divd-reg-number { color:var(--navy); font-size:34px; font-weight:700; line-height:1; font-variant-numeric:tabular-nums; }
    .divd-reg-label { margin:10px 0 16px; color:var(--muted); font-size:13px; line-height:1.5; }
    .divd-reg-bar { height:12px; border-radius:999px; background:#e9f0f4; overflow:hidden; }
    .divd-reg-bar span { display:block; height:100%; border-radius:inherit; background:linear-gradient(90deg,var(--blue),#c66b7c); transition:width .4s ease; }
    .divd-reg-foot { display:flex; justify-content:space-between; gap:10px; margin-top:14px; color:var(--muted); font-size:12px; }
    .divd-reg-foot strong { color:var(--navy); font-size:14px; }

    /* Network glance */
    .divd-glance { display:flex; align-items:center; gap:16px; padding:16px 0; border-bottom:1px solid #edf2f5; }
    .divd-glance:first-child { padding-top:0; }
    .divd-glance:last-child { padding-bottom:0; border-bottom:0; }
    .divd-glance-icon { display:inline-flex; align-items:center; justify-content:center; flex:0 0 50px; width:50px; height:50px; border-radius:14px; font-size:24px; }
    .divd-glance-icon.navy { color:var(--navy); background:var(--sky); }
    .divd-glance-icon.green { color:var(--green); background:#e9f7f0; }
    .divd-glance-text { flex:1; min-width:0; }
    .divd-glance-text strong { display:block; color:var(--navy); font-size:24px; line-height:1.1; font-variant-numeric:tabular-nums; }
    .divd-glance-text small { color:var(--muted); font-size:12px; }
    .divd-glance-link { color:var(--blue); font-size:12px; font-weight:600; text-decoration:none; white-space:nowrap; }
    .divd-glance-link:hover { text-decoration:underline; }

    /* Quick actions */
    .divd-quick-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:14px; }
    .divd-quick { display:flex; flex-direction:column; gap:12px; padding:20px; border:1px solid var(--line); border-radius:14px; background:#fbfdfe; color:var(--ink); text-decoration:none; transition:border-color .18s ease, box-shadow .18s ease, transform .18s ease; }
    .divd-quick:hover { border-color:var(--blue); box-shadow:0 10px 22px rgba(18,63,99,.10); transform:translateY(-3px); text-decoration:none; }
    .divd-quick-icon { display:inline-flex; align-items:center; justify-content:center; width:44px; height:44px; border-radius:12px; color:var(--blue); background:var(--sky); font-size:22px; }
    .divd-quick-body { flex:1; min-width:0; }
    .divd-quick-body strong { display:block; color:var(--navy); font-size:14px; font-weight:700; }
    .divd-quick-body small { display:block; margin-top:3px; color:var(--muted); font-size:12px; line-height:1.45; }
    .divd-quick-arrow { color:var(--blue); font-size:14px; font-weight:700; }

    @media (max-width:1199.98px) { .divd-quick-grid { grid-template-columns:repeat(2,minmax(0,1fr)); } }
    @media (max-width:767.98px) {
        .division-workspace .lg-hero { align-items:flex-start; flex-direction:column; padding:26px; border-radius:16px; }
        .division-workspace .lg-hero-actions { width:100%; }
        .division-workspace .lg-hero .btn { width:100%; }
        .division-workspace .lg-hero h1 { font-size:24px; }
        .divd-quick-grid { grid-template-columns:1fr; }
    }
    @media (max-width:479.98px) { .divd-glance { gap:12px; flex-wrap:wrap; } }

    /* Interpretation panels */
    .lg-interp { display:flex; align-items:flex-start; gap:10px; margin-top:20px; padding:13px 16px; border:1px solid #d6e9f2; border-radius:12px; background:#f4fafd; }
    .lg-interp > i { flex:0 0 auto; color:var(--amber); font-size:18px; line-height:1.1; }
    .lg-interp ul { flex:1; min-width:0; margin:0; padding-left:16px; }
    .lg-interp li { margin-bottom:5px; color:#38556a; font-size:13px; line-height:1.55; }
    .lg-interp li:last-child { margin-bottom:0; }
    .lg-interp strong { color:var(--navy); }
</style>
<div class="scope-lg division-workspace">
    <section class="lg-hero"><div class="lg-hero-copy"><span class="lg-eyebrow">CLMD Learning Portal<?= $div_setup_name !== '' ? ' · ' . $div_setup_name : ''; ?></span><h1>Division Dashboard</h1><p>Track submitted assessment results and learning needs across learning areas in your division.</p><span class="lg-fy-badge"><i class="mdi mdi-calendar-month-outline"></i> Fiscal Year <?= html_escape($div_fy); ?></span></div><div class="lg-hero-actions"><a class="btn btn-light" href="<?= $overview_url; ?>"><i class="mdi mdi-chart-bar mr-1"></i> Learning Gap Summary</a><a class="btn btn-outline-light" href="<?= $records_url; ?>"><i class="mdi mdi-format-list-bulleted mr-1"></i> View Records</a></div></section>
    <div class="row">
        <div class="col-sm-6 col-xl-3"><a class="lg-stat-link" href="<?= $records_url; ?>"><div class="lg-stat"><div class="lg-stat-top"><span>Reporting schools</span><span class="lg-stat-icon"><i class="mdi mdi-school-outline"></i></span></div><small>Schools with submissions</small><strong><?= number_format($school_count); ?></strong><span class="lg-stat-hint">Review submitted records <i class="mdi mdi-arrow-right"></i></span></div></a></div>
        <div class="col-sm-6 col-xl-3"><a class="lg-stat-link" href="<?= $records_url; ?>"><div class="lg-stat"><div class="lg-stat-top"><span>Encoded records</span><span class="lg-stat-icon"><i class="mdi mdi-file-document-outline"></i></span></div><small>Competency gap entries</small><strong><?= number_format($record_count); ?></strong><span class="lg-stat-hint">View all entries <i class="mdi mdi-arrow-right"></i></span></div></a></div>
        <div class="col-sm-6 col-xl-3"><a class="lg-stat-link" href="<?= $records_url; ?>"><div class="lg-stat"><div class="lg-stat-top"><span>Recorded assessed count</span><span class="lg-stat-icon"><i class="mdi mdi-account-group-outline"></i></span></div><small>Sum across submitted records</small><strong><?= number_format($learners_assessed); ?></strong><span class="lg-stat-hint">Open source data <i class="mdi mdi-arrow-right"></i></span></div></a></div>
        <div class="col-sm-6 col-xl-3"><a class="lg-stat-link" href="<?= $records_url; ?>"><div class="lg-stat"><div class="lg-stat-top"><span>Recorded gap count</span><span class="lg-stat-icon"><i class="mdi mdi-alert-circle-outline"></i></span></div><small><?= $learners_assessed > 0 ? number_format($gap_rate, 1) . '% recorded gap rate' : 'No assessed records'; ?></small><strong><?= number_format($learners_with_gap); ?></strong><span class="lg-stat-hint">Identify priority support <i class="mdi mdi-arrow-right"></i></span></div></a></div>
    </div>

    <section class="lg-card">
        <div class="lg-card-head">
            <div><h4>Division CPL and Recorded Gap Rate per Term</h4><p>Gap rate = sum of recorded gap counts ÷ sum of recorded assessed counts. CPL is weighted by assessed count for records with a CPL value.</p></div>
            <span class="analysis-badge"><i class="mdi mdi-chart-timeline-variant"></i> CPL coverage: <?= number_format($cpl_record_count); ?>/<?= number_format($record_count); ?> records</span>
        </div>
        <div class="lg-card-body">
            <div class="performance-chart-wrap">
                <canvas id="scopeTermPerformanceChart" role="img" aria-label="CPL and percentage of learners with gap per term"></canvas>
                <div id="scopeTermPerformanceEmpty" class="chart-empty"><div><i class="mdi mdi-chart-line-variant"></i>No per-term assessment data has been submitted yet.</div></div>
            </div>
            <?php
            $has_summative = false;
            foreach ($term_performance as $term_row) {
                if (isset($term_row->cpl_summative_avg) && $term_row->cpl_summative_avg !== null) {
                    $has_summative = true;
                    break;
                }
            }
            ?>
            <?php if ($has_summative) : ?>
                <div class="table-responsive mt-3">
                    <table class="table table-sm mb-0">
                        <thead><tr><th>Term</th><th class="text-center">CPL &ndash; Summative 1</th><th class="text-center">CPL &ndash; Summative 2</th><th class="text-center">Term CPL (summative avg)</th></tr></thead>
                        <tbody>
                            <?php foreach ($term_performance as $term_row) : ?>
                                <tr>
                                    <td><strong><?= html_escape($term_row->term); ?></strong></td>
                                    <td class="text-center"><?= $term_row->cpl_summative_1 !== null ? number_format((float) $term_row->cpl_summative_1, 2) . '%' : '—'; ?></td>
                                    <td class="text-center"><?= $term_row->cpl_summative_2 !== null ? number_format((float) $term_row->cpl_summative_2, 2) . '%' : '—'; ?></td>
                                    <td class="text-center"><strong><?= $term_row->cpl_summative_avg !== null ? number_format((float) $term_row->cpl_summative_avg, 2) . '%' : '—'; ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <p class="analysis-note">Summative CPL is encoded only by schools with the feature enabled; term average is the mean of the encoded summatives, weighted by assessed learners.</p>
                </div>
            <?php endif; ?>
            <?= lg_interpretation_box($term_interp); ?>
        </div>
    </section>

    <section class="lg-card">
        <div class="lg-card-head">
            <div><h4>Division CPL and Recorded Gap Rate per Learning Area</h4><p>All terms in fiscal year <?= html_escape($div_fy); ?>. Gap rate uses total recorded gap counts divided by total assessed counts. CPL is weighted by assessed count and excludes missing CPL values.</p></div>
        </div>
        <div class="lg-card-body">
            <div class="performance-chart-wrap">
                <canvas id="scopeAreaPerformanceChart" role="img" aria-label="Division class proficiency level and recorded gap rate per learning area"></canvas>
                <div id="scopeAreaPerformanceEmpty" class="chart-empty"><div><i class="mdi mdi-chart-bar"></i>No learning area assessment data has been submitted yet.</div></div>
            </div>
        </div>
    </section>

    <section class="lg-card">
        <div class="lg-card-head">
            <div><h4>Automated Thematic Analysis</h4><p>Recurring themes detected from school-submitted Intervention / Action and Remarks.</p></div>
            <span class="analysis-badge"><i class="mdi mdi-auto-fix"></i> NLP-assisted</span>
        </div>
        <div class="lg-card-body">
            <?php if (empty($thematic_analysis['themes'])) : ?>
                <div class="empty-state">No intervention or remarks text is available for thematic analysis yet.</div>
            <?php else : ?>
                <div class="theme-summary">
                    <span><strong><?= number_format((int) $thematic_analysis['source_count']); ?></strong> records analyzed</span>
                    <span><strong><?= number_format((int) $thematic_analysis['school_count']); ?></strong> reporting schools</span>
                    <span><strong><?= number_format(count($thematic_analysis['themes'])); ?></strong> leading themes shown</span>
                </div>
                <?php $leading_theme = $thematic_analysis['themes'][0]; $secondary_themes = array_slice($thematic_analysis['themes'], 1, 2); ?>
                <div class="theme-insight"><strong>Key finding:</strong> The leading theme is <?= html_escape($leading_theme->theme); ?>, appearing in <?= number_format((int) $leading_theme->record_count); ?> submitted record<?= (int) $leading_theme->record_count === 1 ? '' : 's'; ?> across <?= number_format((int) $leading_theme->school_count); ?> school<?= (int) $leading_theme->school_count === 1 ? '' : 's'; ?>.<?php if (!empty($secondary_themes)) : ?> Other recurring priorities include <?= html_escape(implode(' and ', array_map(function ($theme) { return $theme->theme; }, $secondary_themes))); ?>.<?php endif; ?></div>
                <div class="theme-grid">
                    <?php foreach ($thematic_analysis['themes'] as $theme) : $theme_width = $theme_max_count > 0 ? ((int) $theme->record_count / $theme_max_count) * 100 : 0; ?>
                        <article class="theme-item">
                            <div class="theme-item-head"><h5><?= html_escape($theme->theme); ?></h5><span class="theme-count"><?= number_format((int) $theme->record_count); ?></span></div>
                            <div class="theme-bar" aria-hidden="true"><span style="width:<?= min(100, max(0, $theme_width)); ?>%"></span></div>
                            <div class="theme-meta"><?= number_format((int) $theme->school_count); ?> school<?= (int) $theme->school_count === 1 ? '' : 's'; ?> · <?= number_format((int) $theme->intervention_count); ?> intervention mentions · <?= number_format((int) $theme->remarks_count); ?> remark mentions</div>
                            <?php if (!empty($theme->examples)) : ?><p class="theme-example">“<?= html_escape($theme->examples[0]); ?>”</p><?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
                <?= lg_interpretation_box($theme_interp); ?>
                <p class="analysis-note">Themes are generated locally using transparent education-focused language-analysis rules. Results are decision-support indicators and should be validated against the source records.</p>
            <?php endif; ?>
        </div>
    </section>

    <div class="division-section-heading"><h2>School participation & administration</h2><p>Review registration, manage your network, and follow up on school submissions.</p></div>
    <div class="row">
        <div class="col-lg-7">
            <section class="divd-card">
                <div class="divd-card-head">
                    <div><h4>School registration progress</h4><p>Registered schools out of the total encoded in Division Setup.</p></div>
                    <a href="<?= $div_setup_url; ?>" class="divd-glance-link">Division setup <i class="mdi mdi-arrow-right" aria-hidden="true"></i></a>
                </div>
                <div class="divd-card-body">
                    <?php if ($div_encoded_total > 0) : ?>
                        <div class="divd-reg-number"><?= number_format($div_signup_pct, 1); ?>%</div>
                        <p class="divd-reg-label">of encoded schools are registered in the system</p>
                        <div class="divd-reg-bar" aria-hidden="true"><span style="width:<?= min(100, max(0, $div_signup_pct)); ?>%"></span></div>
                        <div class="divd-reg-foot">
                            <span><strong><?= number_format($div_registered); ?></strong> registered</span>
                            <span><strong><?= number_format($div_encoded_total); ?></strong> encoded total</span>
                        </div>
                        <?= lg_interpretation_box($reg_interp); ?>
                    <?php else : ?>
                        <div class="divd-reg-number">Not configured</div>
                        <p class="divd-reg-label">Set your total number of schools in Division Setup to track registration progress.</p>
                        <a href="<?= $div_setup_url; ?>" class="divd-glance-link">Configure now <i class="mdi mdi-arrow-right" aria-hidden="true"></i></a>
                    <?php endif; ?>
                </div>
            </section>
        </div>
        <div class="col-lg-5">
            <section class="divd-card">
                <div class="divd-card-head"><div><h4>Division network</h4><p>Districts and schools under your division.</p></div></div>
                <div class="divd-card-body">
                    <div class="divd-glance">
                        <span class="divd-glance-icon navy"><i class="mdi mdi-map-marker-multiple"></i></span>
                        <div class="divd-glance-text"><strong><?= number_format($div_district_count); ?></strong><small>District<?= $div_district_count === 1 ? '' : 's'; ?></small></div>
                        <a class="divd-glance-link" href="<?= $div_districts_url; ?>">Manage <i class="mdi mdi-arrow-right"></i></a>
                    </div>
                    <div class="divd-glance">
                        <span class="divd-glance-icon green"><i class="mdi mdi-school-outline"></i></span>
                        <div class="divd-glance-text"><strong><?= number_format($div_registered); ?></strong><small>Registered school<?= $div_registered === 1 ? '' : 's'; ?></small></div>
                        <a class="divd-glance-link" href="<?= $div_schools_url; ?>">Manage <i class="mdi mdi-arrow-right"></i></a>
                    </div>
                </div>
            </section>
        </div>
    </div>
    <section class="divd-card">
        <div class="divd-card-head"><div><h4>Quick actions</h4><p>Jump to your key division tasks.</p></div></div>
        <div class="divd-card-body">
            <div class="divd-quick-grid">
                <a class="divd-quick" href="<?= $overview_url; ?>">
                    <span class="divd-quick-icon"><i class="mdi mdi-chart-bar"></i></span>
                    <span class="divd-quick-body"><strong>Learning Gap Summary</strong><small>Review competency gaps</small></span>
                    <span class="divd-quick-arrow"><i class="mdi mdi-arrow-right"></i></span>
                </a>
                <a class="divd-quick" href="<?= $records_url; ?>">
                    <span class="divd-quick-icon"><i class="mdi mdi-format-list-bulleted"></i></span>
                    <span class="divd-quick-body"><strong>Submitted Records</strong><small>View all entries</small></span>
                    <span class="divd-quick-arrow"><i class="mdi mdi-arrow-right"></i></span>
                </a>
                <a class="divd-quick" href="<?= $div_submission_url; ?>">
                    <span class="divd-quick-icon"><i class="mdi mdi-clipboard-check-outline"></i></span>
                    <span class="divd-quick-body"><strong>School Submissions</strong><small>Monitor reporting</small></span>
                    <span class="divd-quick-arrow"><i class="mdi mdi-arrow-right"></i></span>
                </a>
                <a class="divd-quick" href="<?= $div_setup_url; ?>">
                    <span class="divd-quick-icon"><i class="mdi mdi-cogs"></i></span>
                    <span class="divd-quick-body"><strong>Division Setup</strong><small>Configure details</small></span>
                    <span class="divd-quick-arrow"><i class="mdi mdi-arrow-right"></i></span>
                </a>
            </div>
        </div>
    </section>
</div>
<script src="<?= base_url('assets/libs/chart-js/Chart.bundle.min.js'); ?>"></script>
<script>
    (function () {
        if (typeof Chart === 'undefined') return;

        var termRows = <?= json_encode(array_map(function ($row) {
            $assessed = (int) $row->learners_assessed;
            return array(
                'term' => (string) $row->term,
                'cpl' => $row->class_proficiency_level === null ? null : round((float) $row->class_proficiency_level, 2),
                's1' => $row->cpl_summative_1 === null ? null : round((float) $row->cpl_summative_1, 2),
                's2' => $row->cpl_summative_2 === null ? null : round((float) $row->cpl_summative_2, 2),
                'gapPercentage' => $assessed > 0 ? round(((int) $row->learners_with_gap / $assessed) * 100, 2) : null,
                'assessed' => $assessed,
            );
        }, $term_performance), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        var termCanvas = document.getElementById('scopeTermPerformanceChart');
        var termEmpty = document.getElementById('scopeTermPerformanceEmpty');
        var hasTermData = termRows.some(function (row) { return row.assessed > 0 || row.cpl !== null; });
        var hasSummative = termRows.some(function (row) { return row.s1 !== null || row.s2 !== null; });
        if (termCanvas && (hasTermData || hasSummative)) {
            var termDatasets = [
                { label: 'Recorded Gap Rate (%)', data: termRows.map(function (row) { return row.gapPercentage; }), backgroundColor: 'rgba(229,145,43,.78)', borderColor: '#d57d16', borderWidth: 1 },
                { type: 'line', label: 'CPL (%)', data: termRows.map(function (row) { return row.cpl; }), borderColor: '#20805c', backgroundColor: 'rgba(32,128,92,.10)', pointBackgroundColor: '#20805c', pointBorderColor: '#fff', pointBorderWidth: 2, pointRadius: 5, fill: false, spanGaps: true, lineTension: .25 }
            ];
            if (hasSummative) {
                termDatasets.push(
                    { type: 'line', label: 'CPL Summative 1 (%)', data: termRows.map(function (row) { return row.s1; }), borderColor: '#7a5fb0', backgroundColor: 'rgba(122,95,176,.10)', pointBackgroundColor: '#7a5fb0', pointBorderColor: '#fff', pointBorderWidth: 2, pointRadius: 4, borderDash: [6, 4], fill: false, spanGaps: true, lineTension: .25 },
                    { type: 'line', label: 'CPL Summative 2 (%)', data: termRows.map(function (row) { return row.s2; }), borderColor: '#b0653a', backgroundColor: 'rgba(176,101,58,.10)', pointBackgroundColor: '#b0653a', pointBorderColor: '#fff', pointBorderWidth: 2, pointRadius: 4, borderDash: [2, 3], fill: false, spanGaps: true, lineTension: .25 }
                );
            }
            new Chart(termCanvas.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: termRows.map(function (row) { return row.term; }),
                    datasets: termDatasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } },
                    tooltips: { mode: 'index', intersect: false, callbacks: { label: function (item, data) { return data.datasets[item.datasetIndex].label + ': ' + (item.yLabel === null ? 'No data' : Number(item.yLabel).toFixed(1) + '%'); } } },
                    scales: {
                        xAxes: [{ gridLines: { display: false }, barPercentage: .58, categoryPercentage: .68 }],
                        yAxes: [{ ticks: { beginAtZero: true, max: 100, callback: function (value) { return value + '%'; } }, scaleLabel: { display: true, labelString: 'Percentage' }, gridLines: { color: 'rgba(28,72,101,.08)' } }]
                    }
                }
            });
        } else if (termCanvas && termEmpty) {
            termCanvas.style.display = 'none';
            termEmpty.style.display = 'flex';
        }
        var areaRows = <?= json_encode(array_map(function ($row) {
            $assessed = (int) $row->learners_assessed;
            return array(
                'area' => (string) $row->learning_area,
                'cpl' => $row->class_proficiency_level === null ? null : (float) $row->class_proficiency_level,
                'gapPercentage' => $assessed > 0 ? round((int) $row->learners_with_gap / $assessed * 100, 2) : null,
            );
        }, $area_performance), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        var areaCanvas = document.getElementById('scopeAreaPerformanceChart');
        var areaEmpty = document.getElementById('scopeAreaPerformanceEmpty');
        if (areaCanvas && areaRows.some(function (row) { return row.cpl !== null || row.gapPercentage !== null; })) {
            new Chart(areaCanvas.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: areaRows.map(function (row) { return row.area; }),
                    datasets: [
                        { label: 'Recorded Gap Rate (%)', data: areaRows.map(function (row) { return row.gapPercentage; }), backgroundColor: 'rgba(229,145,43,.78)', borderColor: '#d57d16', borderWidth: 1 },
                        { label: 'CPL (%)', data: areaRows.map(function (row) { return row.cpl; }), backgroundColor: 'rgba(32,128,92,.78)', borderColor: '#20805c', borderWidth: 1 }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } },
                    tooltips: { mode: 'index', intersect: false, callbacks: { label: function (item, data) { return data.datasets[item.datasetIndex].label + ': ' + (item.yLabel === null ? 'No data' : Number(item.yLabel).toFixed(1) + '%'); } } },
                    scales: {
                        xAxes: [{ gridLines: { display: false }, ticks: { autoSkip: false }, barPercentage: .75, categoryPercentage: .7 }],
                        yAxes: [{ ticks: { beginAtZero: true, max: 100, callback: function (value) { return value + '%'; } }, scaleLabel: { display: true, labelString: 'Percentage' }, gridLines: { color: 'rgba(28,72,101,.08)' } }]
                    }
                }
            });
        } else if (areaCanvas && areaEmpty) {
            areaCanvas.style.display = 'none';
            areaEmpty.style.display = 'flex';
        }
    }());
</script>
