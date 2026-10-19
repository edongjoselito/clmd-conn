<?php
$summary = $summary ?: (object) array('record_count' => 0, 'school_count' => 0, 'learners_assessed' => 0, 'learners_with_gap' => 0);
$scope_label = $scope['type'] === 'school' ? 'School workspace' : ($scope['type'] === 'district' ? 'District AP overview' : ($scope['type'] === 'division' ? 'Division summary' : 'Regional overview'));
$school_name = $school && trim((string) $school->schoolName) !== '' ? $school->schoolName : 'School profile not set up';
$division_name = !empty($division->description) ? $division->description : 'Division not assigned';
$district_name = !empty($district->description) ? $district->description : 'District not assigned';
$form = !empty($edit_record) ? $edit_record : (object) array();
$value = function ($field) use ($form) { return isset($form->{$field}) ? (string) $form->{$field} : ''; };
$this->load->helper('learning_gap');
$term_interp = $entry_mode ? array() : lg_term_interpretation($term_performance, (int) $summary->record_count);
$has_summative = false;
foreach ($term_performance as $term_row) {
    if (isset($term_row->cpl_summative_avg) && $term_row->cpl_summative_avg !== null) {
        $has_summative = true;
        break;
    }
}
$division_interp = $entry_mode ? array() : lg_division_summary_interpretation($division_summary);
$ranking_interp = $entry_mode || !$has_ranking_filter ? array() : lg_ranking_interpretation($competency_ranking, $learning_area_filter, $term_filter);
?>
<style>
    .lgm-page { --blue:#164b73; --sky:#eaf5fc; --line:#dce6ef; color:#233342; display:flex; flex-direction:column; }
    .lgm-hero { position:relative; overflow:hidden; background:linear-gradient(120deg,#123d61,#2877a9); color:#fff; border-radius:18px; padding:29px 32px; margin:18px 0 22px; box-shadow:0 12px 26px rgba(18,61,97,.16); }.lgm-hero:after{content:'';position:absolute;width:210px;height:210px;right:-75px;top:-110px;border:28px solid rgba(255,255,255,.09);border-radius:50%}
    .lgm-hero h1 { color:#fff; font-size:26px; margin:0 0 7px; }.lgm-hero p{margin:0;color:#dceefa}
    .lgm-stat { position:relative; overflow:hidden; text-align:center; background:#fff;border:1px solid var(--line);border-radius:14px;padding:19px 20px;margin-bottom:18px;height:calc(100% - 18px);box-shadow:0 5px 18px rgba(20,62,94,.06);transition:transform .18s ease,box-shadow .18s ease}.lgm-stat:hover{transform:translateY(-3px);box-shadow:0 10px 24px rgba(20,62,94,.12)}.lgm-stat:after{content:'';position:absolute;width:86px;height:86px;right:-28px;bottom:-34px;border-radius:50%;background:#edf7fc}
    .lgm-stat small { color:#6b7d8d;text-transform:uppercase;font-weight:700;letter-spacing:.05em }.lgm-stat strong{display:block;font-size:30px;color:var(--blue);margin-top:6px;line-height:1}.lgm-stat-hint{display:block;margin-top:9px;color:#718392;font-size:12px}.lgm-stat-icon{position:absolute;right:18px;top:17px;width:36px;height:36px;display:grid;place-items:center;border-radius:10px;background:#e8f5fb;color:#2877a9;font-size:19px}.lgm-stat-link{display:block;color:inherit;text-decoration:none}.lgm-stat-link:hover{color:inherit}
    .lgm-card{border:1px solid var(--line);border-radius:14px;background:#fff;box-shadow:0 5px 18px rgba(20,62,94,.05);margin-bottom:22px;overflow:hidden}.lgm-card-head{padding:18px 20px;border-bottom:1px solid var(--line);background:#f8fbfd}.lgm-card-head h4{margin:0;color:var(--blue);font-size:17px}.lgm-card-body{padding:20px}.summary-table{margin-bottom:0}.summary-table th{padding:14px 16px;border-top:0;background:#eaf4fa!important;color:#28516e;text-transform:uppercase;font-size:11px;letter-spacing:.04em}.summary-table th:not(:first-child),.summary-table td:not(:first-child){text-align:center}.summary-table td{padding:15px 16px;vertical-align:middle;border-color:#edf2f5}.summary-table tbody tr{transition:background .16s ease}.summary-table tbody tr:hover{background:#f4faff}.summary-table a{display:inline-flex;align-items:center;justify-content:center;min-width:36px;padding:4px 8px;border-radius:6px;color:#164b73;font-weight:700;text-decoration:none;background:#eef7fc;transition:background .16s ease,color .16s ease}.summary-table a:hover{background:#d9edf8;color:#0f527d}
    .lgm-page label{font-weight:600;font-size:12px;color:#415769}.lgm-page .form-control,.lgm-page .custom-select{border-color:#cbd9e5}.lgm-page textarea.form-control{min-height:82px}.lgm-table{font-size:13px}.lgm-table th{background:#dcecf8;color:#173e5d;white-space:nowrap}.lgm-table td{vertical-align:top;min-width:115px}.lgm-table .wide{min-width:230px}.badge-status{background:#e4f3e9;color:#277241;padding:5px 8px;border-radius:20px;font-weight:600}.summary-table th{background:#edf5fa;color:#28516e}.competency-options{display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:8px;padding:12px;border:1px solid #cbd9e5;border-radius:5px;background:#f8fbfd}.competency-option{display:flex;align-items:flex-start;gap:8px;padding:8px 10px;margin:0!important;border:1px solid #dce6ef;border-radius:6px;background:#fff;font-weight:400!important;cursor:pointer}.competency-option:hover{border-color:#2877a9;background:#f2f9fd}.competency-option input{margin-top:3px}.competency-options .text-muted{grid-column:1/-1;margin:0}
    .school-account-strip{display:flex;align-items:center;gap:15px;padding:17px 20px;margin:-2px 0 20px;border:1px solid #cfe2f0;border-radius:14px;background:#f3f9fd}.school-account-icon{display:flex;align-items:center;justify-content:center;width:48px;height:48px;border-radius:50%;background:#d8edf9;color:var(--blue);font-size:24px}.school-account-strip strong{display:block;color:#163f5d;font-size:16px}.school-account-strip small{color:#587183}.school-account-lock{margin-left:auto;color:#317149;font-size:12px;font-weight:700}.rate-preview{display:flex;align-items:center;min-height:38px;padding:0 12px;border:1px solid #b9d9c6;border-radius:5px;background:#edf8f1;color:#257142;font-weight:700}.form-section-title{margin:4px 0 14px;padding-bottom:9px;border-bottom:1px solid var(--line);color:var(--blue);font-size:14px;font-weight:700}.required-note{color:#748697;font-size:12px}
    .term-chart-card .lgm-card-head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px}.term-chart-card .lgm-card-head small{color:#6b7d8d}.term-chart-wrap{position:relative;height:340px}.term-chart-empty{display:none;align-items:center;justify-content:center;height:100%;color:#748697;text-align:center}.term-chart-empty i{display:block;margin-bottom:8px;color:#9bb4c5;font-size:34px}
    .records-card{order:4}.data-entry-card{order:5}.lgm-page>.lgm-hero{order:1}.lgm-page>.alert{order:2}.lgm-page>.row{order:3}.lgm-page>.lgm-card:not(.records-card):not(.data-entry-card){order:4}.record-tools{display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap}.record-filter{display:flex;align-items:end;gap:8px}.record-filter label{margin:0}.record-filter .custom-select{min-width:145px}@media(max-width:575px){.lgm-hero{padding:24px 22px}.lgm-hero h1{font-size:22px}.lgm-stat strong{font-size:27px}.summary-table th,.summary-table td{padding-left:11px;padding-right:11px}}
    /* Dashboard refinements */
    .lgm-page{--navy:#123d61;--accent:#2f8fc1;--mint:#e8f6ef;--muted:#64798a;gap:0}.lgm-hero{padding:31px 34px;background:linear-gradient(125deg,#103956 0%,#16618e 58%,#2f91bb 100%)}.lgm-hero:before{content:'';position:absolute;inset:auto 12% -120px auto;width:280px;height:280px;border-radius:50%;background:radial-gradient(circle,rgba(255,255,255,.14),rgba(255,255,255,0) 67%)}.lgm-eyebrow{position:relative;z-index:1;display:inline-flex;align-items:center;gap:6px;margin-bottom:10px;padding:5px 9px;border:1px solid rgba(255,255,255,.28);border-radius:20px;background:rgba(10,44,68,.16);font-size:11px;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:#e5f6ff}.lgm-hero h1{position:relative;z-index:1;font-size:29px;letter-spacing:-.02em}.lgm-hero p{position:relative;z-index:1;max-width:630px;font-size:14px}.lgm-stat{display:flex;flex-direction:column;align-items:flex-start;text-align:left;padding:21px 24px;border:0;border-radius:16px;box-shadow:0 7px 21px rgba(17,65,94,.08)}.lgm-stat:hover{transform:translateY(-4px);box-shadow:0 14px 29px rgba(17,65,94,.15)}.lgm-stat small{font-size:10px}.lgm-stat strong{font-size:34px;margin-top:9px}.lgm-stat-hint{margin-top:auto;padding-top:12px}.lgm-stat-icon{right:20px;top:20px;width:42px;height:42px;border-radius:12px}.lgm-card{border:0;border-radius:16px;box-shadow:0 7px 23px rgba(19,61,88,.07)}.lgm-card-head{padding:20px 24px;background:linear-gradient(180deg,#fbfdff,#f4f9fc);border-bottom-color:#e4edf3}.lgm-card-head h4{font-weight:700;letter-spacing:-.01em}.lgm-card-body{padding:25px 24px}.lgm-page .form-control,.lgm-page .custom-select{min-height:40px;border-color:#c9d9e4;border-radius:8px;background-color:#fff;box-shadow:none;transition:border-color .16s ease,box-shadow .16s ease}.lgm-page .form-control:focus,.lgm-page .custom-select:focus{border-color:#318fc0;box-shadow:0 0 0 3px rgba(49,143,192,.14)}.lgm-page textarea.form-control{min-height:100px;padding-top:10px}.form-section-title{display:flex;align-items:center;gap:8px;margin:8px 0 18px;padding:0 0 11px;border-bottom-color:#dfe9ef;font-size:15px}.form-section-title:before{content:'';width:4px;height:18px;border-radius:4px;background:linear-gradient(#2f91bb,#17527b)}.required-note{margin-left:auto}.competency-options{padding:14px;border:0;border-radius:12px;background:#f2f8fb}.competency-option{min-height:62px;padding:11px 12px;border-color:#d8e5ed;border-radius:10px;line-height:1.38;transition:transform .16s ease,border-color .16s ease,box-shadow .16s ease,background .16s ease}.competency-option:hover{transform:translateY(-1px);border-color:#62a7ca;background:#fff;box-shadow:0 4px 10px rgba(23,82,123,.08)}.competency-option:has(input:checked){border-color:#2f8fc1;background:#e8f6fc;box-shadow:inset 3px 0 #2f8fc1}.competency-option input{width:16px;height:16px;accent-color:#247dac}.competency-options .text-muted{padding:12px;text-align:center;color:#74899a!important}.rate-preview{min-height:40px;border:0;border-radius:8px;background:var(--mint);color:#1e7451;box-shadow:inset 0 0 0 1px #cbe8d9}.school-account-strip{border:0;border-radius:14px;background:linear-gradient(120deg,#eff8fd,#f7fbfe);box-shadow:inset 0 0 0 1px #d3e7f3}.summary-table th{padding:13px 16px;background:#edf6fa!important}.summary-table td{padding:16px;border-color:#edf2f5}.summary-table td:first-child{font-weight:600;color:#244d6b}.summary-table tr:nth-child(even) td{background:#fbfdfe}.summary-table .competency-column{text-align:left!important}.record-filter{padding:8px 10px;border:1px solid #d7e4ec;border-radius:11px;background:#fff;box-shadow:0 3px 9px rgba(15,55,79,.04)}.record-filter .custom-select{min-width:155px;min-height:35px;font-size:13px}.record-filter .btn{min-height:35px;border-radius:7px}.ranking-context{display:inline-flex;align-items:center;margin-left:8px;padding:4px 8px;border-radius:20px;background:#dff1f9;color:#17658f;font-size:11px;font-weight:700;letter-spacing:0}.rank-pill{display:inline-flex;align-items:center;justify-content:center;min-width:34px;height:28px;padding:0 7px;border-radius:8px;background:#e1f1f8;color:#135f8d;font-size:12px;font-weight:800}.summary-table tbody tr:first-child .rank-pill{background:#fff0c9;color:#966300}.summary-table tbody tr:nth-child(2) .rank-pill{background:#e8edf1;color:#536a79}.summary-table tbody tr:nth-child(3) .rank-pill{background:#f4e6d8;color:#93633d}@media(max-width:767px){.lgm-hero{padding:25px 23px;margin-top:14px}.lgm-hero h1{font-size:24px}.lgm-card-head,.lgm-card-body{padding-left:18px;padding-right:18px}.lgm-stat{margin-bottom:14px}.record-filter{width:100%;align-items:stretch;flex-wrap:wrap}.record-filter>div{flex:1 1 140px}.record-filter .custom-select{width:100%;min-width:0}.record-filter .btn{flex:1 1 100%}.required-note{margin-left:0}.form-section-title{align-items:flex-start;flex-wrap:wrap}}
    /* Interpretation panels */
    .lg-interp{display:flex;align-items:flex-start;gap:10px;margin-top:20px;padding:13px 16px;border:1px solid #d6e9f2;border-radius:12px;background:#f4fafd}.lg-interp>i{flex:0 0 auto;color:#c98616;font-size:18px;line-height:1.1}.lg-interp ul{flex:1;min-width:0;margin:0;padding-left:16px}.lg-interp li{margin-bottom:5px;color:#38556a;font-size:13px;line-height:1.55}.lg-interp li:last-child{margin-bottom:0}.lg-interp strong{color:#123d61}.lgm-interp-foot{padding:0 24px 22px}.lgm-interp-foot .lg-interp{margin-top:18px}@media(max-width:767px){.lgm-interp-foot{padding:0 18px 18px}}
</style>
<style>
    .lgm-page { --blue:var(--llcm-blue, #922c43); --navy:var(--llcm-navy, #3b0718); --sky:var(--llcm-sky, #fbf4f5); --line:var(--llcm-border, #ead7dc); --accent:#c66b7c; }
    .lgm-hero { background:linear-gradient(125deg, #3b0718 0%, #5b1026 58%, #922c43 100%); box-shadow:0 12px 26px rgba(59,7,24,.16); }
    .lgm-stat strong,.lgm-card-head h4,.form-section-title,.summary-table a { color:var(--llcm-blue, #922c43); }
    .lgm-stat:hover { box-shadow:0 14px 29px rgba(59,7,24,.15); }
    .lgm-card { box-shadow:0 7px 23px rgba(59,7,24,.07); }
    .lgm-stat-icon { background:#f5e9ec; color:#922c43; }
    .lgm-card-head { background:linear-gradient(180deg,#fff,#fbf4f5); }
    .lgm-page .form-control,.lgm-page .custom-select,.competency-option { border-color:#ead7dc; }
    .lgm-page .form-control:focus,.lgm-page .custom-select:focus,.competency-option:has(input:checked) { border-color:#922c43; box-shadow:0 0 0 3px rgba(146,44,67,.14); }
    .form-section-title:before { background:linear-gradient(#c66b7c,#5b1026); }
    .competency-options { background:#fbf4f5; }
    .competency-option:hover { border-color:#c66b7c; box-shadow:0 4px 10px rgba(91,16,38,.08); }
    .competency-option:has(input:checked) { background:#f5e9ec; box-shadow:inset 3px 0 #922c43; }
    .competency-option input { accent-color:#922c43; }
    .lgm-table th { background:#f5e9ec; color:#5b1026; }
    .school-account-strip { background:linear-gradient(120deg,#fbf4f5,#fff); box-shadow:inset 0 0 0 1px #ead7dc; }
    .summary-table th { background:#f5e9ec!important; }
    .summary-table a:hover { background:#ead7dc; color:#3b0718; }
    .summary-table td:first-child { color:#5b1026; }
    .ranking-context,.rank-pill { background:#f5e9ec; color:#5b1026; }
    .record-filter { border-color:#ead7dc; }
    .lg-interp { border-color:#ead7dc; background:#fbf4f5; }
    .lg-interp li { color:#5b4650; }
    .lg-interp strong { color:#3b0718; }
</style>
<div class="lgm-page">
    <div class="lgm-hero">
        <span class="lgm-eyebrow"><i class="mdi mdi-chart-donut-variant"></i><?= html_escape($scope_label); ?></span>
        <h1><i class="mdi mdi-chart-box mr-2"></i>Learning Gap Monitoring</h1>
        <p><?= html_escape($scope_label); ?> · Fiscal Year <?= (int) $this->session->fy; ?> · Track least learned competencies, learner gaps, and interventions.</p>
    </div>

    <?php if ($this->session->flashdata('success')): ?><div class="alert alert-success"><?= $this->session->flashdata('success'); ?></div><?php endif; ?>
    <?php if ($this->session->flashdata('danger')): ?><div class="alert alert-danger"><?= $this->session->flashdata('danger'); ?></div><?php endif; ?>
    <?php if ($is_archive_year): ?><div class="alert alert-info"><i class="mdi mdi-lock-outline mr-1" aria-hidden="true"></i> Fiscal Year <?= (int) $this->session->fy; ?> is archived and read-only.</div><?php endif; ?>
    <?php if (!$entry_mode): ?><div class="row">
        <div class="col-md-6"><a class="lgm-stat-link" href="<?= base_url('Pages/learning_gap_records'); ?>"><div class="lgm-stat"><span class="lgm-stat-icon"><i class="mdi mdi-file-document-outline"></i></span><small>Encoded records</small><strong><?= (int) $summary->record_count; ?></strong><span class="lgm-stat-hint">View submitted learning gap records <i class="mdi mdi-arrow-right"></i></span></div></a></div>
        <div class="col-md-6"><a class="lgm-stat-link" href="<?= base_url('Pages/learning_gap_records'); ?>"><div class="lgm-stat"><span class="lgm-stat-icon"><i class="mdi mdi-account-group-outline"></i></span><small>Recorded assessed count</small><strong><?= number_format((int) $summary->learners_assessed); ?></strong><span class="lgm-stat-hint">Sum across submitted records <i class="mdi mdi-arrow-right"></i></span></div></a></div>
    </div><?php endif; ?>

    <?php if (!$entry_mode): ?>
    <div class="lgm-card term-chart-card">
        <div class="lgm-card-head">
            <div><h4><i class="mdi mdi-chart-bar mr-1"></i> CPL and Recorded Assessment Counts per Term</h4><small><?= html_escape($scope_label); ?> · Counts are direct sums from submitted records.</small></div>
            <small>CPL uses only records with an encoded CPL value.</small>
        </div>
        <div class="lgm-card-body">
            <div class="term-chart-wrap">
                <canvas id="termPerformanceChart" role="img" aria-label="Chart of class proficiency level, learners assessed, and learners with learning gap for each term"></canvas>
                <div id="termPerformanceEmpty" class="term-chart-empty"><div><i class="mdi mdi-chart-bar-stacked"></i>No per-term assessment data has been submitted yet.</div></div>
            </div>
            <?php if ($has_summative): ?>
            <div class="table-responsive mt-3">
                <table class="table summary-table mb-0">
                    <thead><tr><th>Term</th><th>CPL &ndash; Summative 1</th><th>CPL &ndash; Summative 2</th><th>Term CPL (summative avg)</th></tr></thead>
                    <tbody>
                        <?php foreach ($term_performance as $term_row): ?>
                        <tr>
                            <td><strong><?= html_escape($term_row->term); ?></strong></td>
                            <td><?= $term_row->cpl_summative_1 !== null ? number_format((float) $term_row->cpl_summative_1, 2) . '%' : '—'; ?></td>
                            <td><?= $term_row->cpl_summative_2 !== null ? number_format((float) $term_row->cpl_summative_2, 2) . '%' : '—'; ?></td>
                            <td><strong><?= $term_row->cpl_summative_avg !== null ? number_format((float) $term_row->cpl_summative_avg, 2) . '%' : '—'; ?></strong></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
            <?= lg_interpretation_box($term_interp); ?>
        </div>
    </div>
    <div class="lgm-card term-chart-card">
        <div class="lgm-card-head">
            <div><h4><i class="mdi mdi-chart-bar mr-1"></i> CPL and Recorded Assessment Counts per Learning Area</h4><small><?= html_escape($scope_label); ?> · All terms in fiscal year <?= (int) $this->session->fy; ?>. Counts are sums across submitted records, not unique learners.</small></div>
            <small>CPL is weighted by assessed learners and excludes missing CPL values.</small>
        </div>
        <div class="lgm-card-body">
            <div class="term-chart-wrap">
                <canvas id="areaPerformanceChart" role="img" aria-label="Chart of class proficiency level, recorded assessed counts, and recorded gap counts per learning area"></canvas>
                <div id="areaPerformanceEmpty" class="term-chart-empty"><div><i class="mdi mdi-chart-bar-stacked"></i>No learning area assessment data has been submitted yet.</div></div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($is_school && $entry_mode): ?>
    <div class="lgm-card data-entry-card" id="data-entry">
        <div class="lgm-card-head"><h4><?= !empty($edit_record) ? 'Edit learning gap data' : 'Encode learning gap data'; ?></h4><small>Fields follow the supplied Learning Gap Monitoring form. Your school, division, and district assignment are set automatically.</small></div>
        <div class="lgm-card-body">
            <?= form_open('Pages/learning_gap_save'); ?>
            <input type="hidden" name="record_id" value="<?= (int) $value('id'); ?>">
            <div class="form-section-title">1. Assessment details <span class="required-note">Fields marked * are required.</span></div>
            <div class="row">
                <div class="col-md-2 form-group"><label>Grade Level *</label><select name="grade_level" class="custom-select" required><option value="">Select grade</option><?php foreach (array('Kindergarten','Grade 1','Grade 2','Grade 3','Grade 4','Grade 5','Grade 6','Grade 7','Grade 8','Grade 9','Grade 10','Grade 11','Grade 12') as $grade): ?><option value="<?= $grade; ?>" <?= $value('grade_level') === $grade ? 'selected' : ''; ?>><?= $grade; ?></option><?php endforeach; ?></select></div>
                <div class="col-md-3 form-group"><label>Learning Area / Subject *</label><select name="learning_area" id="learningArea" class="custom-select" required disabled><option value="">Select grade level first</option></select></div>
                <div class="col-md-2 form-group"><label>Trimester *</label><select name="term" id="term" class="custom-select" required><option value="">Select trimester</option><?php foreach (array('Term 1','Term 2','Term 3') as $term): ?><option value="<?= $term; ?>" <?= $value('term') === $term ? 'selected' : ''; ?>><?= $term; ?></option><?php endforeach; ?></select></div>
                <div class="col-md-2 form-group"><label>Class Proficiency Level</label><div class="input-group"><input type="number" min="0" max="100" step="0.01" name="class_proficiency_level" class="form-control" value="<?= html_escape($value('class_proficiency_level')); ?>"><div class="input-group-append"><span class="input-group-text">%</span></div></div></div>
                <div class="col-md-3 form-group"><label>Proficiency Level</label><select name="proficiency_level" class="custom-select"><option value="">Select level</option><?php foreach (array('Beginning','Developing','Approaching Proficient','Proficient','Advanced') as $proficiency_level): ?><option value="<?= $proficiency_level; ?>" <?= $value('proficiency_level') === $proficiency_level ? 'selected' : ''; ?>><?= $proficiency_level; ?></option><?php endforeach; ?></select></div>
                <div class="col-12 form-group"><label>Least Learned Competency *</label><div id="learningCompetencies" class="competency-options"><p class="text-muted">Select grade level, learning area, and term first</p></div><small class="form-text text-muted">Check one or more competencies that were least learned.</small></div>
            </div>
            <?php if (!empty($summative_cpl_enabled)): ?>
            <div class="row">
                <div class="col-md-3 form-group"><label>CPL &ndash; Summative 1</label><div class="input-group"><input type="number" min="0" max="100" step="0.01" name="cpl_summative_1" class="form-control" value="<?= html_escape($value('cpl_summative_1')); ?>"><div class="input-group-append"><span class="input-group-text">%</span></div></div></div>
                <div class="col-md-3 form-group"><label>CPL &ndash; Summative 2</label><div class="input-group"><input type="number" min="0" max="100" step="0.01" name="cpl_summative_2" class="form-control" value="<?= html_escape($value('cpl_summative_2')); ?>"><div class="input-group-append"><span class="input-group-text">%</span></div></div></div>
                <div class="col-md-6 form-group"><label>&nbsp;</label><small class="form-text text-muted mt-2 mb-0">Optional per-term summative CPL. Both values are averaged into the term CPL shown to your division.</small></div>
            </div>
            <?php endif; ?>
            <div class="row">
                <div class="col-md-4 form-group"><label>Number of Learners Assessed *</label><input type="number" min="0" name="learners_assessed" id="learnersAssessed" class="form-control" value="<?= html_escape($value('learners_assessed')); ?>" required></div>
                <div class="col-md-4 form-group"><label>Learners with Learning Gap *</label><input type="number" min="0" name="learners_with_gap" id="learnersWithGap" class="form-control" value="<?= html_escape($value('learners_with_gap')); ?>" required></div>
                <div class="col-md-4 form-group"><label>Percentage of Learners Not Meeting</label><div class="rate-preview" id="gapRatePreview">Enter learner counts</div></div>
            </div>
            <div class="form-section-title">2. Intervention details</div>
            <div class="row">
                <div class="col-md-6 form-group"><label>Intervention / Action</label><textarea name="intervention_action" class="form-control"><?= html_escape($value('intervention_action')); ?></textarea></div>
                <div class="col-md-3 form-group"><label>Intervention Status</label><select name="intervention_status" class="custom-select"><?php foreach (array('Planned','Ongoing','Completed','For monitoring') as $status): ?><option value="<?= $status; ?>" <?= $value('intervention_status') === $status ? 'selected' : ''; ?>><?= $status; ?></option><?php endforeach; ?></select></div>
                <div class="col-md-3 form-group"><label>Remarks</label><textarea name="remarks" class="form-control"><?= html_escape($value('remarks')); ?></textarea></div>
            </div>
            <button class="btn btn-primary" type="submit"><i class="mdi mdi-content-save"></i> <?= !empty($edit_record) ? 'Update record' : 'Save record'; ?></button><?php if (!empty($edit_record)): ?> <a class="btn btn-light" href="<?= base_url('Pages/learning_gap'); ?>">Cancel edit</a><?php endif; ?>
            <?= form_close(); ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!$entry_mode && !empty($division_summary)): ?>
    <div class="lgm-card"><div class="lgm-card-head"><h4><i class="mdi mdi-office-building mr-1"></i> Summary by division</h4><small>Counts are direct sums from submitted records. Click any figure to review the source data.</small></div><div class="table-responsive"><table class="table summary-table mb-0"><thead><tr><th>Division</th><th>Schools reporting</th><th>Records</th><th>Recorded assessed</th><th>Recorded gap</th></tr></thead><tbody><?php foreach ($division_summary as $row): $record_link = base_url('Pages/learning_gap_records?division_id=' . (int) $row->division_id); ?><tr><td><strong><?= html_escape($row->division_name); ?></strong></td><td><a href="<?= $record_link; ?>" title="View <?= html_escape($row->division_name); ?> records"><?= (int) $row->school_count; ?></a></td><td><a href="<?= $record_link; ?>" title="View <?= html_escape($row->division_name); ?> records"><?= (int) $row->record_count; ?></a></td><td><a href="<?= $record_link; ?>" title="View <?= html_escape($row->division_name); ?> records"><?= number_format((int) $row->learners_assessed); ?></a></td><td><a href="<?= $record_link; ?>" title="View <?= html_escape($row->division_name); ?> records"><?= number_format((int) $row->learners_with_gap); ?></a></td></tr><?php endforeach; ?></tbody></table></div><?php if (!empty($division_interp)): ?><div class="lgm-interp-foot"><?= lg_interpretation_box($division_interp); ?></div><?php endif; ?></div>
    <?php endif; ?>

    <?php if (!$entry_mode && $scope['type'] === 'division'): ?>
    <div class="lgm-card">
        <div class="lgm-card-head">
            <div class="record-tools">
                <div><h4><i class="mdi mdi-format-list-numbered mr-1"></i> Least Learned Competencies Ranking<?php if ($has_ranking_filter): ?><span class="ranking-context"><?= html_escape($learning_area_filter); ?> &middot; <?= html_escape($term_filter); ?></span><?php endif; ?></h4><small>Ranked by the number of school submissions that selected each competency.</small></div>
                <form class="record-filter" method="get" action="<?= base_url('Pages/learning_gap'); ?>">
                    <div><label for="learningAreaFilter">Learning Area</label><select id="learningAreaFilter" name="learning_area" class="custom-select"><option value="">All learning areas</option><?php foreach ($learning_area_filters as $area): ?><option value="<?= html_escape($area); ?>" <?= $learning_area_filter === $area ? 'selected' : ''; ?>><?= html_escape($area); ?></option><?php endforeach; ?></select></div>
                    <div><label for="termFilter">Term</label><select id="termFilter" name="term" class="custom-select"><option value="">All terms</option><?php foreach (array('Term 1', 'Term 2', 'Term 3') as $filter_term): ?><option value="<?= $filter_term; ?>" <?= $term_filter === $filter_term ? 'selected' : ''; ?>><?= $filter_term; ?></option><?php endforeach; ?></select></div>
                    <button class="btn btn-outline-primary btn-sm" type="submit"><i class="mdi mdi-filter-outline mr-1"></i>Filter</button>
                </form>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table summary-table mb-0">
                <thead><tr><th>Rank</th><th>Grade</th><th class="competency-column">Least Learned Competency</th><th>Counts</th></tr></thead>
                <tbody>
                    <?php if (!$has_ranking_filter): ?>
                        <tr><td colspan="4" class="p-4 text-center text-muted">Select both Learning Area and Term, then click Filter to show the competency ranking.</td></tr>
                    <?php elseif (empty($competency_ranking)): ?>
                        <tr><td colspan="4" class="p-4 text-center text-muted">No school submissions with least learned competencies for this selection yet.</td></tr>
                    <?php else: foreach ($competency_ranking as $row): $record_link = base_url('Pages/learning_gap_records?' . http_build_query(array('grade_level' => $row->grade_level, 'learning_area' => $learning_area_filter, 'term' => $term_filter, 'competency' => $row->competency))); ?>
                        <tr><td><span class="rank-pill">#<?= (int) $row->rank; ?></span></td><td><?= html_escape($row->grade_level); ?></td><td class="wide competency-column"><?= html_escape($row->competency); ?></td><td><a href="<?= html_escape($record_link); ?>" title="View the <?= (int) $row->submission_count; ?> matching school submission<?= (int) $row->submission_count === 1 ? '' : 's'; ?>"><?= (int) $row->submission_count; ?></a></td></tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
        <?php if (!empty($ranking_interp)): ?><div class="lgm-interp-foot"><?= lg_interpretation_box($ranking_interp); ?></div><?php endif; ?>
    </div>
    <?php endif; ?>
</div>
<?php if (!$entry_mode): ?>
<script src="<?= base_url('assets/libs/chart-js/Chart.bundle.min.js'); ?>"></script>
<script>
    (function () {
        var rows = <?= json_encode(array_map(function ($row) {
            return array(
                'term' => (string) $row->term,
                'assessed' => (int) $row->record_count > 0 ? (int) $row->learners_assessed : null,
                'gap' => (int) $row->record_count > 0 ? (int) $row->learners_with_gap : null,
                'cpl' => $row->class_proficiency_level === null ? null : (float) $row->class_proficiency_level,
                's1' => $row->cpl_summative_1 === null ? null : (float) $row->cpl_summative_1,
                's2' => $row->cpl_summative_2 === null ? null : (float) $row->cpl_summative_2,
            );
        }, $term_performance), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        renderPerformanceChart(rows, 'termPerformanceChart', 'termPerformanceEmpty');
        var areaRows = <?= json_encode(array_map(function ($row) {
            return array(
                'term' => (string) $row->learning_area,
                'assessed' => (int) $row->learners_assessed,
                'gap' => (int) $row->learners_with_gap,
                'cpl' => $row->class_proficiency_level === null ? null : (float) $row->class_proficiency_level,
                's1' => null,
                's2' => null,
            );
        }, $area_performance), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        renderPerformanceChart(areaRows, 'areaPerformanceChart', 'areaPerformanceEmpty');

        function renderPerformanceChart(rows, canvasId, emptyId) {
        var canvas = document.getElementById(canvasId);
        var empty = document.getElementById(emptyId);
        if (!canvas || typeof Chart === 'undefined') return;
        var hasData = rows.some(function (row) { return row.assessed !== null || row.gap !== null || row.cpl !== null; });
        var hasSummative = rows.some(function (row) { return row.s1 !== null || row.s2 !== null; });
        if (!hasData && !hasSummative) {
            canvas.style.display = 'none';
            empty.style.display = 'flex';
            return;
        }
        var datasets = [
            { label: 'Recorded Assessed Count', data: rows.map(function (row) { return row.assessed; }), backgroundColor: '#922c43', borderColor: '#922c43', borderWidth: 1, yAxisID: 'learners-axis' },
            { label: 'Recorded Gap Count', data: rows.map(function (row) { return row.gap; }), backgroundColor: '#ef9f3d', borderColor: '#d9821b', borderWidth: 1, yAxisID: 'learners-axis' },
            { type: 'line', label: 'CPL (%)', data: rows.map(function (row) { return row.cpl; }), borderColor: '#23835f', backgroundColor: 'rgba(35,131,95,.12)', pointBackgroundColor: '#23835f', pointBorderColor: '#fff', pointBorderWidth: 2, pointRadius: 5, fill: false, spanGaps: true, lineTension: .25, yAxisID: 'cpl-axis' }
        ];
        if (hasSummative) {
            datasets.push(
                { type: 'line', label: 'CPL Summative 1 (%)', data: rows.map(function (row) { return row.s1; }), borderColor: '#7a5fb0', backgroundColor: 'rgba(122,95,176,.10)', pointBackgroundColor: '#7a5fb0', pointBorderColor: '#fff', pointBorderWidth: 2, pointRadius: 4, borderDash: [6, 4], fill: false, spanGaps: true, lineTension: .25, yAxisID: 'cpl-axis' },
                { type: 'line', label: 'CPL Summative 2 (%)', data: rows.map(function (row) { return row.s2; }), borderColor: '#b0653a', backgroundColor: 'rgba(176,101,58,.10)', pointBackgroundColor: '#b0653a', pointBorderColor: '#fff', pointBorderWidth: 2, pointRadius: 4, borderDash: [2, 3], fill: false, spanGaps: true, lineTension: .25, yAxisID: 'cpl-axis' }
            );
        }
        new Chart(canvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels: rows.map(function (row) { return row.term; }),
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } },
                tooltips: { mode: 'index', intersect: false, callbacks: { label: function (item, data) { var label = data.datasets[item.datasetIndex].label || ''; var isPercent = data.datasets[item.datasetIndex].yAxisID === 'cpl-axis'; return label + ': ' + (isPercent ? (item.yLabel === null ? 'No data' : Number(item.yLabel).toFixed(2) + '%') : Number(item.yLabel).toLocaleString()); } } },
                scales: {
                    xAxes: [{ gridLines: { display: false }, barPercentage: .72, categoryPercentage: .68 }],
                    yAxes: [
                        { id: 'learners-axis', position: 'left', ticks: { beginAtZero: true, precision: 0, callback: function (value) { return Number(value).toLocaleString(); } }, scaleLabel: { display: true, labelString: 'Number of learners' }, gridLines: { color: 'rgba(28,72,101,.08)' } },
                        { id: 'cpl-axis', position: 'right', ticks: { beginAtZero: true, max: 100, callback: function (value) { return value + '%'; } }, scaleLabel: { display: true, labelString: 'CPL' }, gridLines: { drawOnChartArea: false } }
                    ]
                }
            }
        });
        }
    }());
</script>
<?php endif; ?>
<script>
    (function () {
        var assessed = document.getElementById('learnersAssessed');
        var withGap = document.getElementById('learnersWithGap');
        var preview = document.getElementById('gapRatePreview');
        var grade = document.querySelector('[name="grade_level"]');
        var learningArea = document.getElementById('learningArea');
        var term = document.getElementById('term');
        var learningCompetencies = document.getElementById('learningCompetencies');
        var selectedArea = <?= json_encode($value('learning_area')); ?>;
        var selectedCompetencies = <?= json_encode(array_filter(preg_split('/\r\n|\r|\n/', $value('least_learned_competency')))); ?>;
        if (!assessed || !withGap || !preview) return;
        function updateRate() {
            var total = Number(assessed.value) || 0;
            var gap = Number(withGap.value) || 0;
            if (!total) { preview.textContent = 'Enter learner counts'; return; }
            preview.textContent = ((gap / total) * 100).toFixed(1) + '% not meeting';
            preview.style.color = gap > total ? '#b43838' : '#257142';
        }
        assessed.addEventListener('input', updateRate);
        withGap.addEventListener('input', updateRate);
        function loadCompetencies() {
            learningCompetencies.innerHTML = '<p class="text-muted">Loading learning competencies...</p>';
            if (!grade.value || !learningArea.value || !term.value) { learningCompetencies.innerHTML = '<p class="text-muted">Select grade level, learning area, and term first</p>'; return; }
            fetch('<?= base_url('Pages/learning_competency_options'); ?>?grade_level=' + encodeURIComponent(grade.value) + '&learning_area=' + encodeURIComponent(learningArea.value) + '&term=' + encodeURIComponent(term.value), { credentials: 'same-origin' })
                .then(function (response) { return response.json(); })
                .then(function (data) {
                    var competencies = data.competencies || [];
                    learningCompetencies.innerHTML = '';
                    competencies.forEach(function (competency) { var option = document.createElement('label'); option.className = 'competency-option'; var checkbox = document.createElement('input'); checkbox.type = 'checkbox'; checkbox.name = 'least_learned_competency[]'; checkbox.value = competency; checkbox.checked = selectedCompetencies.indexOf(competency) !== -1; option.appendChild(checkbox); option.appendChild(document.createTextNode(competency)); learningCompetencies.appendChild(option); });
                    if (!competencies.length) learningCompetencies.innerHTML = '<p class="text-muted">No competencies configured for this grade, learning area, and term</p>';
                })
                .catch(function () { learningCompetencies.innerHTML = '<p class="text-muted">Unable to load learning competencies</p>'; });
        }
        grade.addEventListener('change', function () {
            learningArea.disabled = true;
            learningArea.innerHTML = '<option value="">Loading learning areas...</option>';
            learningCompetencies.innerHTML = '<p class="text-muted">Select grade level, learning area, and term first</p>';
            if (!grade.value) { learningArea.innerHTML = '<option value="">Select grade level first</option>'; return; }
            fetch('<?= base_url('Pages/learning_area_options'); ?>?grade_level=' + encodeURIComponent(grade.value), { credentials: 'same-origin' })
                .then(function (response) { return response.json(); })
                .then(function (data) {
                    var areas = data.areas || [];
                    learningArea.innerHTML = '<option value="">Select learning area</option>';
                    areas.forEach(function (area) { var option = document.createElement('option'); option.value = area; option.textContent = area; option.selected = area === selectedArea; learningArea.appendChild(option); });
                    if (!areas.length) learningArea.innerHTML = '<option value="">No learning areas configured</option>';
                    learningArea.disabled = !areas.length;
                    loadCompetencies();
                })
                .catch(function () { learningArea.innerHTML = '<option value="">Unable to load learning areas</option>'; });
        });
        learningArea.addEventListener('change', loadCompetencies);
        term.addEventListener('change', loadCompetencies);
        if (grade.value) grade.dispatchEvent(new Event('change'));
        updateRate();
    }());
</script>
