<?php
$totals = array('learners_assessed'=>0, 'independent'=>0, 'instructional'=>0, 'frustration'=>0, 'non_reader'=>0);
foreach ($records as $record) foreach ($totals as $key => $unused) $totals[$key] += (int)$record->$key;
$coverage = count($records);
?>
<style>
    .phil-page{--wine:#3b0718;--red:#922c43;--rose:#f8eef0;--line:#ead7dc;--ink:#38252b;--muted:#796970;color:var(--ink);padding:12px 0 34px}
    .phil-hero{position:relative;overflow:hidden;display:flex;align-items:center;justify-content:space-between;gap:24px;margin:7px 0 20px;padding:27px 30px;border-radius:18px;color:#fff;background:linear-gradient(120deg,var(--wine),#68182f 58%,var(--red));box-shadow:0 12px 28px rgba(59,7,24,.16)}
    .phil-hero:after{content:'';position:absolute;width:220px;height:220px;right:16%;top:-150px;border:28px solid rgba(255,255,255,.08);border-radius:50%}
    .phil-hero-main,.phil-hero-actions{position:relative;z-index:1}.phil-eyebrow{display:inline-flex;gap:7px;align-items:center;margin-bottom:8px;color:#f4dfe4;font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase}.phil-hero h1{margin:0 0 7px;color:#fff;font-size:25px;font-weight:700}.phil-hero p{margin:0;color:#f1dce1;font-size:13px}.phil-current-sy{padding:10px 13px;border:1px solid rgba(255,255,255,.24);border-radius:12px;background:rgba(255,255,255,.1);white-space:nowrap}.phil-current-sy small{display:block;color:#f1dce1;font-size:10px;font-weight:700;letter-spacing:.07em;text-transform:uppercase}.phil-current-sy strong{font-size:18px}
    .phil-alert{border:0;border-radius:10px;box-shadow:0 4px 12px rgba(59,7,24,.06)}.phil-card{overflow:hidden;margin-bottom:19px;border:1px solid var(--line);border-radius:15px;background:#fff;box-shadow:0 6px 20px rgba(59,7,24,.055)}.phil-card-head{display:flex;align-items:center;gap:12px;padding:17px 20px;border-bottom:1px solid #f0e4e7;background:linear-gradient(180deg,#fff,#fdf9fa)}.phil-head-icon{display:grid;place-items:center;flex:0 0 38px;height:38px;border-radius:11px;background:var(--rose);color:var(--red);font-size:19px}.phil-card-head h2{margin:0;color:var(--wine);font-size:16px;font-weight:700}.phil-card-head p{margin:3px 0 0;color:var(--muted);font-size:12px}.phil-card-body{padding:20px}
    .phil-sy-form{display:flex;align-items:end;gap:12px;flex-wrap:wrap}.phil-sy-form .form-group{margin:0;min-width:190px}.phil-page label{display:block;margin-bottom:6px;color:#59454c;font-size:11px;font-weight:700}.phil-page .form-control{height:41px;border:1px solid #dfcbd1;border-radius:8px;color:var(--ink);box-shadow:none}.phil-page .form-control:focus{border-color:var(--red);box-shadow:0 0 0 3px rgba(146,44,67,.12)}.phil-page .btn-primary{border-color:var(--red);border-radius:8px;background:var(--red);font-weight:700}.phil-page .btn-primary:hover{border-color:var(--wine);background:var(--wine)}
    .phil-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:13px;margin-bottom:19px}.phil-stat{position:relative;overflow:hidden;min-height:91px;padding:15px 17px;border:1px solid var(--line);border-radius:13px;background:#fff;box-shadow:0 4px 15px rgba(59,7,24,.045)}.phil-stat:after{content:'';position:absolute;right:-19px;bottom:-28px;width:75px;height:75px;border-radius:50%;background:var(--rose)}.phil-stat span,.phil-stat strong{position:relative;z-index:1;display:block}.phil-stat span{color:var(--muted);font-size:10px;font-weight:800;letter-spacing:.06em;text-transform:uppercase}.phil-stat strong{margin-top:7px;color:var(--wine);font-size:25px;line-height:1}
    .phil-entry-grid{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:13px}.phil-entry-grid .form-group{margin:0}.phil-entry-grid .form-group input,.phil-entry-grid .form-group select{width:100%}.phil-entry-grid .phil-field-count{grid-column:span 2}.phil-entry-grid .phil-field-select{grid-column:span 2}.phil-entry-footer{display:flex;align-items:center;justify-content:space-between;gap:14px;margin-top:16px;padding-top:15px;border-top:1px solid #f0e4e7}.phil-entry-footer small{color:var(--muted)}
    .phil-table-wrap{overflow-x:auto}.phil-table{min-width:760px;margin:0}.phil-table thead th{padding:12px 14px;border-top:0;border-bottom:1px solid var(--line);background:#fbf4f5;color:#70545d;font-size:10px;font-weight:800;letter-spacing:.045em;text-transform:uppercase;white-space:nowrap}.phil-table tbody td{padding:13px 14px;border-color:#f1e8ea;color:#55444a;font-size:13px;vertical-align:middle}.phil-table tbody tr:hover{background:#fdf9fa}.phil-table .grade-chip{display:inline-block;padding:5px 9px;border-radius:7px;background:#f8eef0;color:var(--wine);font-weight:700;white-space:nowrap}.phil-tag{display:inline-block;padding:5px 8px;border-radius:20px;background:#f8eef0;color:#713449;font-size:11px;font-weight:700}.phil-num{font-variant-numeric:tabular-nums;font-weight:700}.phil-empty{text-align:center;padding:34px 20px!important;color:var(--muted)!important}.phil-empty i{display:block;margin-bottom:7px;color:#c99ba7;font-size:27px}.phil-empty strong{display:block;margin-bottom:4px;color:#5d3c46}
    @media(max-width:991px){.phil-entry-grid{grid-template-columns:repeat(4,minmax(0,1fr))}.phil-entry-grid .phil-field-count,.phil-entry-grid .phil-field-select{grid-column:span 2}.phil-stats{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @media(max-width:575px){.phil-page{padding-top:4px}.phil-hero{align-items:flex-start;flex-direction:column;padding:22px}.phil-hero h1{font-size:21px}.phil-current-sy{padding:8px 11px}.phil-card-body{padding:16px}.phil-card-head{padding:14px 16px}.phil-sy-form{align-items:stretch}.phil-sy-form .form-group{width:100%}.phil-sy-form .btn{width:100%}.phil-entry-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:11px}.phil-entry-grid .phil-field-count,.phil-entry-grid .phil-field-select{grid-column:span 1}.phil-entry-footer{align-items:stretch;flex-direction:column}.phil-entry-footer .btn{width:100%}.phil-stats{gap:9px}.phil-stat{padding:13px;min-height:82px}.phil-stat strong{font-size:22px}}
</style>
<div class="container-fluid phil-page">
    <section class="phil-hero">
        <div class="phil-hero-main"><div class="phil-eyebrow"><i class="mdi mdi-book-open-page-variant"></i> School Reading Assessment</div><h1>Philippine Informal Reading Inventory</h1><p>Encode school-level reading results by grade, language, and assessment period.</p></div>
        <div class="phil-hero-actions"><div class="phil-current-sy"><small>Active School Year</small><strong><?= html_escape($school_year); ?></strong></div></div>
    </section>
    <?php if ($this->session->flashdata('success')): ?><div class="alert alert-success phil-alert"><i class="mdi mdi-check-circle-outline mr-1"></i><?= html_escape($this->session->flashdata('success')); ?></div><?php endif; ?>
    <?php if ($this->session->flashdata('danger')): ?><div class="alert alert-danger phil-alert"><i class="mdi mdi-alert-circle-outline mr-1"></i><?= $this->session->flashdata('danger'); ?></div><?php endif; ?>

    <section class="phil-card">
        <div class="phil-card-head"><span class="phil-head-icon"><i class="mdi mdi-calendar-cog-outline"></i></span><div><h2>Default school year</h2><p>This preference belongs to your school account and controls both entry and the results shown here.</p></div></div>
        <div class="phil-card-body"><form action="<?= base_url('Pages/phil_iri_setup_sy'); ?>" method="post" class="phil-sy-form">
            <?php if ($this->config->item('csrf_protection')): ?><input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>"><?php endif; ?>
            <div class="form-group"><label for="phil-iri-school-year">School Year</label><select id="phil-iri-school-year" name="school_year" class="form-control" required><?php foreach ($school_year_options as $year=>$label): ?><option value="<?= (int)$year; ?>" <?= (int)$year === (int)$selected_sy ? 'selected' : ''; ?>><?= html_escape($label); ?></option><?php endforeach; ?></select></div>
            <button type="submit" class="btn btn-outline-primary"><i class="mdi mdi-content-save-outline mr-1"></i>Save Default SY</button>
        </form></div>
    </section>

    <section class="phil-stats" aria-label="Phil-IRI results summary">
        <div class="phil-stat"><span>Grades reported</span><strong><?= (int)$coverage; ?></strong></div>
        <div class="phil-stat"><span>Learners assessed</span><strong><?= number_format($totals['learners_assessed']); ?></strong></div>
        <div class="phil-stat"><span>Independent readers</span><strong><?= number_format($totals['independent']); ?></strong></div>
        <div class="phil-stat"><span>Needs support</span><strong><?= number_format($totals['instructional'] + $totals['frustration'] + $totals['non_reader']); ?></strong></div>
    </section>

    <section class="phil-card">
        <div class="phil-card-head"><span class="phil-head-icon"><i class="mdi mdi-pencil-plus-outline"></i></span><div><h2>Enter reading results</h2><p>Use aggregate learner counts for the selected grade and assessment.</p></div></div>
        <div class="phil-card-body">
            <form action="<?= base_url('Pages/phil_iri_save'); ?>" method="post">
                <?php if ($this->config->item('csrf_protection')): ?><input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>"><?php endif; ?>
                <div class="phil-entry-grid">
                    <div class="form-group phil-field-select"><label for="phil-grade">Grade level</label><select id="phil-grade" class="form-control" name="grade_level" required><?php foreach ($grades as $grade): ?><option value="<?= (int)$grade; ?>">Grade <?= (int)$grade; ?></option><?php endforeach; ?></select></div>
                    <div class="form-group phil-field-select"><label for="phil-language">Language</label><select id="phil-language" class="form-control" name="language" required><option>English</option><option>Filipino</option></select></div>
                    <div class="form-group phil-field-select"><label for="phil-period">Assessment period</label><select id="phil-period" class="form-control" name="assessment_period" required><option>Pre-test</option><option>Post-test</option></select></div>
                    <?php foreach (array('learners_assessed'=>'Learners assessed','independent'=>'Independent','instructional'=>'Instructional','frustration'=>'Frustration','non_reader'=>'Non-reader') as $field=>$label): ?><div class="form-group phil-field-count"><label for="phil-<?= $field; ?>"><?= $label; ?></label><input id="phil-<?= $field; ?>" class="form-control" type="number" min="0" step="1" inputmode="numeric" name="<?= $field; ?>" required value="0"></div><?php endforeach; ?>
                </div>
                <div class="phil-entry-footer"><small><i class="mdi mdi-information-outline mr-1"></i>Reading-level counts should not exceed the number of learners assessed.</small><button class="btn btn-primary" type="submit"><i class="mdi mdi-content-save-outline mr-1"></i>Save Results</button></div>
            </form>
        </div>
    </section>

    <section class="phil-card">
        <div class="phil-card-head"><span class="phil-head-icon"><i class="mdi mdi-table-check"></i></span><div><h2>Saved results</h2><p>Showing Phil-IRI entries for School Year <?= html_escape($school_year); ?>.</p></div></div>
        <div class="phil-table-wrap"><table class="table phil-table"><thead><tr><th>Grade</th><th>Language</th><th>Assessment</th><th>Learners assessed</th><th>Independent</th><th>Instructional</th><th>Frustration</th><th>Non-reader</th></tr></thead><tbody>
            <?php if (empty($records)): ?><tr><td colspan="8" class="phil-empty"><i class="mdi mdi-clipboard-text-outline"></i><strong>No results for this school year yet</strong>Enter the school’s first grade-level summary above.</td></tr><?php else: foreach ($records as $row): ?><tr><td><span class="grade-chip">Grade <?= (int)$row->grade_level; ?></span></td><td><span class="phil-tag"><?= html_escape($row->language); ?></span></td><td><?= html_escape($row->assessment_period); ?></td><td class="phil-num"><?= number_format((int)$row->learners_assessed); ?></td><td class="phil-num"><?= number_format((int)$row->independent); ?></td><td class="phil-num"><?= number_format((int)$row->instructional); ?></td><td class="phil-num"><?= number_format((int)$row->frustration); ?></td><td class="phil-num"><?= number_format((int)$row->non_reader); ?></td></tr><?php endforeach; endif; ?>
        </tbody></table></div>
    </section>
</div>
