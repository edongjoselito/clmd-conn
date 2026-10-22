<?php
$totals = array('learners_assessed'=>0,'low_emerging'=>0,'high_emerging'=>0,'developing'=>0,'transitioning'=>0,'at_grade_level'=>0);
foreach ($records as $row) foreach ($totals as $field => $unused) $totals[$field] += (int)$row->$field;
?>
<style>
    .crla-page{--wine:#3b0718;--accent:#922c43;--line:#ead7dc;--muted:#786971;color:#38252b;padding:12px 0 34px}.crla-hero{display:flex;justify-content:space-between;align-items:center;gap:20px;margin:8px 0 20px;padding:25px 28px;border-radius:16px;color:#fff;background:linear-gradient(120deg,var(--wine),#68182f 60%,var(--accent));box-shadow:0 10px 25px rgba(59,7,24,.14)}.crla-hero h1{margin:0 0 6px;color:#fff;font-size:24px}.crla-hero p{margin:0;color:#f1dce1;font-size:13px}.crla-label{font-size:10px;text-transform:uppercase;letter-spacing:.08em;font-weight:800;color:#f1dce1}.crla-year{font-size:21px;font-weight:800}.crla-card{overflow:hidden;margin-bottom:18px;border:1px solid var(--line);border-radius:14px;background:#fff;box-shadow:0 5px 17px rgba(59,7,24,.045)}.crla-card-head{padding:15px 18px;border-bottom:1px solid #f0e4e7;background:#fdf9fa}.crla-card-head h2{margin:0;color:var(--wine);font-size:15px;font-weight:800}.crla-card-head p{margin:4px 0 0;color:var(--muted);font-size:11px}.crla-card-body{padding:18px}.crla-filter{display:flex;align-items:end;gap:10px;flex-wrap:wrap}.crla-filter .form-group{margin:0;min-width:180px}.crla-page label{display:block;margin-bottom:5px;color:#59454c;font-size:11px;font-weight:700}.crla-page .form-control{height:39px;border:1px solid #dfcbd1;border-radius:7px}.crla-page .btn-primary{border-color:var(--accent);background:var(--accent);font-weight:700}.crla-metrics{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:13px;margin-bottom:18px}.crla-metric{padding:15px 17px;border:1px solid var(--line);border-radius:12px;background:#fff}.crla-metric span{display:block;color:var(--muted);font-size:10px;font-weight:800;letter-spacing:.05em;text-transform:uppercase}.crla-metric strong{display:block;margin-top:7px;color:var(--wine);font-size:24px;line-height:1}.crla-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px}.crla-grid .form-group{margin:0}.crla-help{margin-top:14px;color:var(--muted);font-size:11px}.crla-actions{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:16px;padding-top:14px;border-top:1px solid #f0e4e7}.crla-table-wrap{overflow:auto}.crla-table{min-width:920px;margin:0}.crla-table th{padding:10px 11px;border-top:0;background:#fbf4f5;color:#70545d;font-size:10px;text-transform:uppercase;letter-spacing:.04em;white-space:nowrap}.crla-table td{padding:11px;border-color:#f1e8ea;font-size:12px;vertical-align:middle}.crla-num{text-align:center;font-weight:700;font-variant-numeric:tabular-nums}.crla-empty{text-align:center;padding:28px!important;color:var(--muted)}
    @media(max-width:767px){.crla-hero{align-items:flex-start;flex-direction:column;padding:21px}.crla-metrics{grid-template-columns:repeat(2,minmax(0,1fr));gap:9px}.crla-metric{padding:13px}.crla-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.crla-actions{align-items:stretch;flex-direction:column}.crla-actions .btn{width:100%}}
</style>
<div class="container-fluid crla-page">
    <header class="crla-hero"><div><div class="crla-label"><i class="mdi mdi-book-open-page-variant mr-1"></i> School literacy assessment</div><h1>Comprehensive Rapid Literacy Assessment</h1><p>Record aggregate CRLA reading profiles by grade, language, and assessment period.</p></div><div><div class="crla-label">Active School Year</div><div class="crla-year"><?= html_escape($school_year); ?></div></div></header>
    <?php if ($this->session->flashdata('success')): ?><div class="alert alert-success"><?= html_escape($this->session->flashdata('success')); ?></div><?php endif; ?>
    <?php if ($this->session->flashdata('danger')): ?><div class="alert alert-danger"><?= $this->session->flashdata('danger'); ?></div><?php endif; ?>
    <section class="crla-card"><div class="crla-card-head"><h2>Default School Year</h2><p>This school-only setting controls CRLA entry and the saved results shown below.</p></div><div class="crla-card-body"><form method="post" action="<?= base_url('Pages/crla_setup_sy'); ?>" class="crla-filter">
        <?php if ($this->config->item('csrf_protection')): ?><input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>"><?php endif; ?>
        <div class="form-group"><label for="crla-school-year">School Year</label><select id="crla-school-year" name="school_year" class="form-control" required><?php foreach ($school_year_options as $year=>$label): ?><option value="<?= (int)$year; ?>" <?= (int)$year === (int)$selected_sy ? 'selected' : ''; ?>><?= html_escape($label); ?></option><?php endforeach; ?></select></div>
        <button type="submit" class="btn btn-outline-primary"><i class="mdi mdi-content-save-outline mr-1"></i>Save Default SY</button>
    </form></div></section>
    <section class="crla-metrics" aria-label="CRLA results summary">
        <div class="crla-metric"><span>Grade/language/period entries</span><strong><?= number_format(count($records)); ?></strong></div>
        <div class="crla-metric"><span>Learners assessed</span><strong><?= number_format($totals['learners_assessed']); ?></strong></div>
        <div class="crla-metric"><span>Low Emerging</span><strong><?= number_format($totals['low_emerging']); ?></strong></div>
        <div class="crla-metric"><span>At Grade Level</span><strong><?= number_format($totals['at_grade_level']); ?></strong></div>
    </section>
    <section class="crla-card"><div class="crla-card-head"><h2>Enter CRLA Results</h2><p>Use one aggregate entry for each grade, language, and assessment period.</p></div><div class="crla-card-body"><form method="post" action="<?= base_url('Pages/crla_save'); ?>">
        <?php if ($this->config->item('csrf_protection')): ?><input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>"><?php endif; ?>
        <div class="crla-grid">
            <div class="form-group"><label for="crla-grade">Grade Level</label><select id="crla-grade" name="grade_level" class="form-control" required><option value="1">Grade 1</option><option value="2">Grade 2</option><option value="3">Grade 3</option></select></div>
            <div class="form-group"><label for="crla-language">Assessment Language</label><select id="crla-language" name="language" class="form-control" required></select></div>
            <div class="form-group"><label for="crla-period">Assessment Period</label><select id="crla-period" name="assessment_period" class="form-control" required><option value="BOSY">Beginning of School Year (BOSY)</option><option value="EOSY">End of School Year (EOSY)</option></select></div>
            <?php foreach (array('learners_assessed'=>'Learners Assessed','low_emerging'=>'Low Emerging','high_emerging'=>'High Emerging','developing'=>'Developing','transitioning'=>'Transitioning','at_grade_level'=>'At Grade Level') as $field=>$label): ?><div class="form-group"><label for="crla-<?= $field; ?>"><?= $label; ?></label><input id="crla-<?= $field; ?>" name="<?= $field; ?>" type="number" class="form-control" min="0" step="1" inputmode="numeric" value="0" required></div><?php endforeach; ?>
        </div>
        <p class="crla-help"><i class="mdi mdi-information-outline mr-1"></i>Language choices follow the grade-level CRLA language options. Profile totals must not exceed learners assessed.</p>
        <div class="crla-actions"><small class="text-muted">Saving the same grade, language, period, and school year updates its existing entry.</small><button class="btn btn-primary" type="submit"><i class="mdi mdi-content-save-outline mr-1"></i>Save Results</button></div>
    </form></div></section>
    <section class="crla-card"><div class="crla-card-head"><h2>Saved CRLA Results</h2><p>Entries for School Year <?= html_escape($school_year); ?>.</p></div><div class="crla-table-wrap"><table class="table crla-table"><thead><tr><th>Grade</th><th>Language</th><th>Assessment Period</th><th>Learners Assessed</th><th>Low Emerging</th><th>High Emerging</th><th>Developing</th><th>Transitioning</th><th>At Grade Level</th></tr></thead><tbody><?php if (empty($records)): ?><tr><td colspan="9" class="crla-empty">No results for this school year yet. Add the school’s first CRLA entry above.</td></tr><?php else: foreach ($records as $row): ?><tr><td><strong>Grade <?= (int)$row->grade_level; ?></strong></td><td><?= html_escape($row->language); ?></td><td><?= $row->assessment_period === 'BOSY' ? 'Beginning of School Year' : 'End of School Year'; ?></td><td class="crla-num"><?= number_format((int)$row->learners_assessed); ?></td><td class="crla-num"><?= number_format((int)$row->low_emerging); ?></td><td class="crla-num"><?= number_format((int)$row->high_emerging); ?></td><td class="crla-num"><?= number_format((int)$row->developing); ?></td><td class="crla-num"><?= number_format((int)$row->transitioning); ?></td><td class="crla-num"><?= number_format((int)$row->at_grade_level); ?></td></tr><?php endforeach; endif; ?></tbody></table></div></section>
</div>
<script>
(function () {
    var grade = document.getElementById('crla-grade');
    var language = document.getElementById('crla-language');
    function updateLanguages() {
        var gradeLevel = parseInt(grade.value, 10);
        var allowed = [{value:'Mother Tongue (L1)',label:'Mother Tongue (L1)'}];
        if (gradeLevel >= 2) allowed.push({value:'Filipino',label:'Filipino'});
        if (gradeLevel >= 3) allowed.push({value:'English',label:'English'});
        var previous = language.value;
        language.innerHTML = '';
        allowed.forEach(function (option) {
            var element = document.createElement('option');
            element.value = option.value;
            element.textContent = option.label;
            language.appendChild(element);
        });
        if (allowed.some(function (option) { return option.value === previous; })) language.value = previous;
    }
    grade.addEventListener('change', updateLanguages);
    updateLanguages();
}());
</script>
