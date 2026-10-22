<?php
$profiles = array(
    'low_emerging' => array('Low Emerging', '#c94c4c'),
    'high_emerging' => array('High Emerging', '#e89535'),
    'developing' => array('Developing', '#d0b43c'),
    'transitioning' => array('Transitioning', '#3988b5'),
    'at_grade_level' => array('At Grade Level', '#348455'),
);
$format_number = function ($value) { return number_format((int)$value); };
$chart_labels = array();
$chart_data = array_fill_keys(array_keys($profiles), array());
foreach ($groups as $group) {
    $chart_labels[] = 'Grade ' . $group['grade_level'] . ' · ' . $group['language'] . ' · ' . $group['assessment_period'];
    foreach ($profiles as $field => $profile) {
        $chart_data[$field][] = $group['learners_assessed'] > 0 ? round(($group[$field] / $group['learners_assessed']) * 100, 1) : 0;
    }
}
?>
<style>
.crla-report{--ink:#3b0718;--accent:#922c43;--line:#ead7dc;--soft:#fbf4f5;color:#34313a}.crla-report .report-hero{padding:28px 32px;margin:18px 0 22px;border-radius:16px;background:linear-gradient(125deg,#3b0718,#922c43);color:#fff}.crla-report .report-hero h1{color:#fff;margin:0 0 6px;font-size:25px}.crla-report .report-hero p{margin:0;color:#f5e8eb}.crla-report .scope-tag{display:inline-block;margin-bottom:8px;padding:4px 10px;border:1px solid rgba(255,255,255,.35);border-radius:20px;font-size:12px}.crla-report .report-card{margin-bottom:20px;background:#fff;border:1px solid var(--line);border-radius:14px;box-shadow:0 5px 18px rgba(59,7,24,.05);overflow:hidden}.crla-report .card-heading{padding:18px 20px;background:var(--soft);border-bottom:1px solid var(--line)}.crla-report .card-heading h2{font-size:17px;color:var(--ink);margin:0 0 4px}.crla-report .card-heading p{margin:0;color:#82757a;font-size:13px}.crla-report .report-metric{height:calc(100% - 20px);margin-bottom:20px;padding:18px;border:1px solid var(--line);border-radius:13px;background:#fff}.crla-report .report-metric small{display:block;color:#776a70;text-transform:uppercase;letter-spacing:.04em;font-weight:700}.crla-report .report-metric strong{display:block;margin-top:7px;color:var(--ink);font-size:25px}.crla-report .report-table{margin:0}.crla-report .report-table thead th{background:var(--soft);color:var(--ink);font-size:11px;text-transform:uppercase;white-space:nowrap;border-color:var(--line);vertical-align:middle}.crla-report .report-table td{vertical-align:middle;border-color:#f0e5e8}.crla-report .num{text-align:center;font-variant-numeric:tabular-nums}.crla-report .chart-wrap{height:370px;padding:20px}.crla-report .filter-row{display:flex;gap:12px;align-items:end;flex-wrap:wrap;padding:18px 20px}.crla-report .filter-row .form-group{margin:0;min-width:150px}.crla-report .filter-row label{font-size:12px;font-weight:700;color:#66575d}.crla-report .school-name{min-width:220px}.crla-report .empty{padding:32px!important;text-align:center;color:#81777a}@media(max-width:767px){.crla-report .report-hero{padding:23px}.crla-report .chart-wrap{height:320px}.crla-report .report-metric strong{font-size:22px}}
</style>
<div class="crla-report container-fluid">
    <header class="report-hero"><span class="scope-tag"><?= html_escape($scope_label); ?></span><h1><?= html_escape($scope_heading); ?></h1><p>CRLA school submissions for School Year <?= html_escape($school_year_label); ?>.</p></header>
    <section class="report-card">
        <form class="filter-row" method="get" action="<?= base_url('Pages/regional_crla_report'); ?>">
            <div class="form-group"><label for="crla-sy">School Year</label><select id="crla-sy" class="form-control" name="sy"><?php for ($year=(int)date('Y')+2; $year>=2023; $year--): $label=($year-1).'-'.$year; ?><option value="<?= $year; ?>" <?= $school_year===$year?'selected':''; ?>><?= html_escape($label); ?></option><?php endfor; ?></select></div>
            <div class="form-group"><label for="crla-period">Assessment Period</label><select id="crla-period" class="form-control" name="period"><option value="BOSY" <?= $period==='BOSY'?'selected':''; ?>>BOSY</option><option value="EOSY" <?= $period==='EOSY'?'selected':''; ?>>EOSY</option><option value="all" <?= $period==='all'?'selected':''; ?>>All periods</option></select></div>
            <div class="form-group"><label for="crla-grade">Grade</label><select id="crla-grade" class="form-control" name="grade"><option value="all">All grades</option><?php foreach (array(1,2,3) as $level): ?><option value="<?= $level; ?>" <?= (string)$grade_filter===(string)$level?'selected':''; ?>>Grade <?= $level; ?></option><?php endforeach; ?></select></div>
            <div class="form-group"><label for="crla-language">Language</label><select id="crla-language" class="form-control" name="language"><option value="all">All languages</option><?php foreach (array('Mother Tongue (L1)','Filipino','English') as $lang): ?><option value="<?= html_escape($lang); ?>" <?= $language_filter===$lang?'selected':''; ?>><?= html_escape($lang); ?></option><?php endforeach; ?></select></div>
            <button class="btn btn-primary" type="submit"><i class="mdi mdi-filter-outline"></i> Apply Filters</button><button class="btn btn-light" type="button" onclick="window.print()"><i class="mdi mdi-printer-outline"></i> Print</button>
        </form>
    </section>
    <div class="row">
        <div class="col-md-3 col-sm-6"><div class="report-metric"><small>Eligible Schools</small><strong><?= $format_number($eligible_school_count); ?></strong></div></div>
        <div class="col-md-3 col-sm-6"><div class="report-metric"><small>Schools With Submissions</small><strong><?= $format_number($reported_school_count); ?></strong></div></div>
        <div class="col-md-3 col-sm-6"><div class="report-metric"><small>Learners Assessed</small><strong><?= $format_number($totals['learners_assessed']); ?></strong></div></div>
        <div class="col-md-3 col-sm-6"><div class="report-metric"><small>Low Emerging</small><strong><?= $format_number($totals['low_emerging']); ?></strong></div></div>
    </div>
    <?php if ($show_division_summary): ?>
    <section class="report-card"><div class="card-heading"><h2>Summary by Division</h2><p>Combined school submissions matching the selected filters.</p></div><div class="table-responsive"><table class="table report-table"><thead><tr><th>Division</th><th class="num">Schools Submitted</th><th class="num">Learners Assessed</th><?php foreach ($profiles as $profile): ?><th class="num"><?= html_escape($profile[0]); ?></th><?php endforeach; ?></tr></thead><tbody>
    <?php if (empty($divisions)): ?><tr><td class="empty" colspan="8">No CRLA submissions match these filters.</td></tr><?php else: foreach ($divisions as $division): ?><tr><td><?= html_escape($division['name']); ?></td><td class="num"><?= $format_number($division['school_count']); ?></td><td class="num"><?= $format_number($division['learners_assessed']); ?></td><?php foreach ($profiles as $field=>$profile): ?><td class="num"><?= $format_number($division[$field]); ?></td><?php endforeach; ?></tr><?php endforeach; endif; ?>
    </tbody></table></div></section>
    <?php endif; ?>
    <section class="report-card"><div class="card-heading"><h2>CRLA Reading Profiles by Grade and Language</h2><p>Each bar group shows the percentage of assessed learners in each reading profile.</p></div><div class="chart-wrap"><?php if (empty($groups)): ?><div class="empty">No CRLA submissions match these filters.</div><?php else: ?><canvas id="crlaProfileChart" aria-label="CRLA reading profile percentages by grade and language"></canvas><?php endif; ?></div></section>
    <section class="report-card"><div class="card-heading"><h2>Results by Grade, Language, and Assessment Period</h2><p>Aggregate counts from submitted school results.</p></div><div class="table-responsive"><table class="table report-table"><thead><tr><th>Grade</th><th>Language</th><th>Assessment</th><th class="num">Schools</th><th class="num">Learners Assessed</th><?php foreach ($profiles as $profile): ?><th class="num"><?= html_escape($profile[0]); ?></th><?php endforeach; ?></tr></thead><tbody>
    <?php if (empty($groups)): ?><tr><td class="empty" colspan="10">No CRLA submissions match these filters.</td></tr><?php else: foreach ($groups as $group): ?><tr><td>Grade <?= (int)$group['grade_level']; ?></td><td><?= html_escape($group['language']); ?></td><td><?= html_escape($group['assessment_period']); ?></td><td class="num"><?= $format_number($group['school_count']); ?></td><td class="num"><?= $format_number($group['learners_assessed']); ?></td><?php foreach ($profiles as $field=>$profile): ?><td class="num"><?= $format_number($group[$field]); ?></td><?php endforeach; ?></tr><?php endforeach; endif; ?>
    </tbody></table></div></section>
    <section class="report-card"><div class="card-heading"><h2>School Submissions</h2><p>School-level entries received for the selected school year and filters.</p></div><div class="table-responsive"><table class="table report-table"><thead><tr><th>Division</th><th>School / ID</th><th>Grade</th><th>Language</th><th>Assessment</th><th class="num">Learners Assessed</th><?php foreach ($profiles as $profile): ?><th class="num"><?= html_escape($profile[0]); ?></th><?php endforeach; ?></tr></thead><tbody>
    <?php if (empty($records)): ?><tr><td class="empty" colspan="11">No CRLA school submissions found for these filters.</td></tr><?php else: foreach ($records as $record): ?><tr><td><?= html_escape($record->division_name); ?></td><td class="school-name"><strong><?= html_escape($record->schoolName); ?></strong><br><small><?= html_escape($record->school_code); ?></small></td><td>Grade <?= (int)$record->grade_level; ?></td><td><?= html_escape($record->language); ?></td><td><?= html_escape($record->assessment_period); ?></td><td class="num"><?= $format_number($record->learners_assessed); ?></td><?php foreach ($profiles as $field=>$profile): ?><td class="num"><?= $format_number($record->$field); ?></td><?php endforeach; ?></tr><?php endforeach; endif; ?>
    </tbody></table></div></section>
</div>
<?php if (!empty($groups)): ?>
<script src="<?= base_url('assets/libs/chart-js/Chart.bundle.min.js'); ?>"></script>
<script>
(function(){
 var canvas=document.getElementById('crlaProfileChart');if(!canvas||typeof Chart==='undefined')return;
 var labels=<?= json_encode($chart_labels, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT); ?>;
 var profiles=<?= json_encode($profiles, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT); ?>;
 var values=<?= json_encode($chart_data, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT); ?>;
 var colors={};Object.keys(profiles).forEach(function(key){colors[key]=profiles[key][1];});
 var datasets=Object.keys(profiles).map(function(key){return{label:profiles[key][0],data:values[key],backgroundColor:colors[key],borderWidth:0};});
 new Chart(canvas.getContext('2d'),{type:'bar',data:{labels:labels,datasets:datasets},options:{responsive:true,maintainAspectRatio:false,legend:{position:'bottom'},tooltips:{mode:'index',intersect:false,callbacks:{label:function(item,data){return data.datasets[item.datasetIndex].label+': '+Number(item.yLabel).toFixed(1)+'%';}}},scales:{xAxes:[{stacked:true,gridLines:{display:false}}],yAxes:[{stacked:true,ticks:{beginAtZero:true,max:100,stepSize:20,callback:function(v){return v+'%';}},scaleLabel:{display:true,labelString:'Learners'},gridLines:{color:'rgba(59,7,24,.08)'}}]}}});
}());
</script>
<?php endif; ?>
