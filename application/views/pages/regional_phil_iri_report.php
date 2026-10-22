<style>
    .rphi{--wine:#3b0718;--red:#922c43;--line:#ead7dc;--muted:#786971;color:#38252b;padding:10px 0 32px}.rphi-hero{display:flex;justify-content:space-between;align-items:center;gap:20px;margin:8px 0 20px;padding:25px 28px;border-radius:16px;color:#fff;background:linear-gradient(120deg,var(--wine),#68182f 60%,var(--red));box-shadow:0 10px 25px rgba(59,7,24,.14)}.rphi-hero h1{margin:0 0 6px;color:#fff;font-size:24px}.rphi-hero p{margin:0;color:#f1dce1;font-size:13px}.rphi-hero .rphi-label{font-size:10px;text-transform:uppercase;letter-spacing:.08em;font-weight:800;color:#f1dce1}.rphi-hero .rphi-year{font-size:21px;font-weight:800}.rphi-actions{display:flex;gap:8px;align-items:center}.rphi-actions .btn{border-radius:8px;font-weight:700}.rphi-filter{display:flex;align-items:end;gap:10px;margin:0 0 18px;padding:15px 18px;border:1px solid var(--line);border-radius:13px;background:#fff;box-shadow:0 4px 15px rgba(59,7,24,.04)}.rphi-filter label{display:block;margin-bottom:5px;color:#59454c;font-size:11px;font-weight:700}.rphi-filter select{height:38px;min-width:170px;border:1px solid #dfcbd1;border-radius:7px}.rphi-filter button{height:38px;border-radius:7px}.rphi-metrics{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:13px;margin-bottom:18px}.rphi-metric{padding:16px 18px;border:1px solid var(--line);border-radius:13px;background:#fff;box-shadow:0 4px 15px rgba(59,7,24,.04)}.rphi-metric span{display:block;color:var(--muted);font-size:10px;font-weight:800;letter-spacing:.05em;text-transform:uppercase}.rphi-metric strong{display:block;margin-top:7px;color:var(--wine);font-size:25px;line-height:1}.rphi-metric small{display:block;margin-top:7px;color:var(--muted);font-size:11px}.rphi-card{overflow:hidden;margin-bottom:18px;border:1px solid var(--line);border-radius:14px;background:#fff;box-shadow:0 5px 17px rgba(59,7,24,.045)}.rphi-card-head{padding:15px 18px;border-bottom:1px solid #f0e4e7;background:#fdf9fa}.rphi-card-head h2{margin:0;color:var(--wine);font-size:15px;font-weight:800}.rphi-card-head p{margin:4px 0 0;color:var(--muted);font-size:11px}.rphi-table-wrap{overflow:auto}.rphi-table{min-width:780px;margin:0}.rphi-table th{padding:10px 12px;border-top:0;background:#fbf4f5;color:#70545d;font-size:10px;text-transform:uppercase;letter-spacing:.04em;white-space:nowrap}.rphi-table td{padding:11px 12px;border-color:#f1e8ea;font-size:12px;vertical-align:middle}.rphi-table tr:hover{background:#fdf9fa}.rphi-pill{display:inline-block;padding:4px 7px;border-radius:20px;background:#f8eef0;color:#713449;font-size:10px;font-weight:700}.rphi-num{text-align:center;font-weight:700;font-variant-numeric:tabular-nums}.rphi-empty{padding:24px!important;color:var(--muted);text-align:center}.rphi-sr-only{position:absolute;left:-9999px}
    @media(max-width:767px){.rphi-hero{align-items:flex-start;flex-direction:column;padding:21px}.rphi-hero h1{font-size:20px}.rphi-actions{width:100%}.rphi-actions .btn{flex:1}.rphi-filter{align-items:stretch;flex-direction:column}.rphi-filter select{width:100%}.rphi-metrics{grid-template-columns:repeat(2,minmax(0,1fr));gap:9px}.rphi-metric{padding:13px}.rphi-metric strong{font-size:22px}}
    @media print{body{background:#fff!important}.rphi{padding:0}.rphi-hero{padding:14px 18px;box-shadow:none;-webkit-print-color-adjust:exact;print-color-adjust:exact}.rphi-actions,.rphi-filter,.no-print{display:none!important}.rphi-card,.rphi-metric{break-inside:avoid;box-shadow:none}.rphi-card{margin-bottom:10px}.rphi-table{min-width:0;font-size:9px}.rphi-table th,.rphi-table td{padding:6px}.rphi-table-wrap{overflow:visible}.rphi-table{width:100%;table-layout:auto}}
</style>
<?php
$phil_iri_charts = array('Filipino'=>array(), 'English'=>array());
$phil_iri_chart_totals = array('Filipino'=>array('learners_assessed'=>0,'independent'=>0,'instructional'=>0,'frustration'=>0,'non_reader'=>0), 'English'=>array('learners_assessed'=>0,'independent'=>0,'instructional'=>0,'frustration'=>0,'non_reader'=>0));
foreach ($groups as $group) {
    if (!isset($phil_iri_charts[$group['language']])) continue;
    foreach ($phil_iri_chart_totals[$group['language']] as $level => $unused) $phil_iri_chart_totals[$group['language']][$level] += (int)$group[$level];
    $grade = (int)$group['grade_level'];
    if (!isset($phil_iri_charts[$group['language']][$grade])) {
        $phil_iri_charts[$group['language']][$grade] = array('label'=>'Grade ' . $grade, 'learners_assessed'=>0, 'independent'=>0, 'instructional'=>0, 'frustration'=>0, 'non_reader'=>0);
    }
    foreach (array('learners_assessed','independent','instructional','frustration','non_reader') as $level) $phil_iri_charts[$group['language']][$grade][$level] += (int)$group[$level];
}
foreach ($phil_iri_charts as $language => $grade_rows) {
    ksort($grade_rows);
    $phil_iri_charts[$language] = array_values($grade_rows);
}
$school_submissions = array();
foreach ($records as $record) {
    $school_key = (string)$record->school_id;
    if (!isset($school_submissions[$school_key])) {
        $school_submissions[$school_key] = array('school_id'=>$school_key, 'schoolName'=>$record->schoolName, 'division_name'=>$record->division_name,
            'Filipino'=>array('learners_assessed'=>0,'independent'=>0,'instructional'=>0,'frustration'=>0,'non_reader'=>0),
            'English'=>array('learners_assessed'=>0,'independent'=>0,'instructional'=>0,'frustration'=>0,'non_reader'=>0));
    }
    if (!isset($school_submissions[$school_key][$record->language])) continue;
    foreach ($school_submissions[$school_key][$record->language] as $level => $unused) $school_submissions[$school_key][$record->language][$level] += (int)$record->$level;
}
usort($school_submissions, function($a, $b) { return array($a['division_name'], $a['schoolName']) <=> array($b['division_name'], $b['schoolName']); });
?>
<style>
    .rphi-charts{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px;padding:18px}.rphi-chart-card{min-width:0;padding:17px;border:1px solid #f0e4e7;border-radius:12px;background:#fff}.rphi-chart-head{display:flex;align-items:center;gap:10px;margin-bottom:13px}.rphi-chart-icon{display:grid;place-items:center;width:37px;height:37px;border-radius:10px;background:#f8eef0;color:#922c43;font-size:18px}.rphi-chart-card.english .rphi-chart-icon{background:#edf3fc;color:#2458a0}.rphi-chart-head h3{margin:0;color:#3b0718;font-size:15px;font-weight:800}.rphi-chart-head p{margin:3px 0 0;color:#786971;font-size:11px}.rphi-level-legend{display:flex;flex-wrap:wrap;gap:9px 15px;margin:0 0 13px}.rphi-level-item{display:flex;align-items:center;gap:6px}.rphi-level-dot{flex:0 0 11px;width:11px;height:11px}.rphi-level-name{color:#59454c;font-size:11px;font-weight:700}.rphi-level-total{color:#3b0718;font-size:11px;font-weight:800;font-variant-numeric:tabular-nums}.rphi-chart-frame{position:relative;height:330px;padding:8px 10px;border:1px solid #d9d9d9}.rphi-chart-empty{display:flex;align-items:center;justify-content:center;height:100%;padding:18px;color:#786971;text-align:center;font-size:13px}.rphi-school-submissions .rphi-num{text-align:center}
    @media(max-width:767px){.rphi-charts{grid-template-columns:1fr;padding:12px}.rphi-chart-frame{height:290px}}
    @media print{.rphi-charts{grid-template-columns:repeat(2,minmax(0,1fr));gap:8px;padding:10px}.rphi-chart-card{padding:10px}.rphi-chart-frame{height:220px}}
</style>
<div class="container-fluid rphi">
    <header class="rphi-hero"><div><div class="rphi-label"><i class="mdi mdi-map-marker-radius-outline mr-1"></i> <?= html_escape($scope_label); ?> · Submission dashboard</div><h1><?= html_escape($scope_heading); ?></h1><p>Consolidated school submissions for <?= html_escape($grade_bands[$grade_band]['label']); ?>.</p></div><div class="rphi-actions"><div><div class="rphi-label">School Year</div><div class="rphi-year"><?= html_escape($school_year_label); ?></div></div><button type="button" class="btn btn-light" onclick="window.print()"><i class="mdi mdi-printer-outline mr-1"></i>Print / Save PDF</button></div></header>

    <form method="get" action="<?= base_url('Pages/regional_phil_iri_report'); ?>" class="rphi-filter no-print"><div><label for="rphi-sy">School Year</label><select id="rphi-sy" name="sy" class="form-control"><?php for ($year=(int)date('Y')+2; $year>=2023; $year--): ?><option value="<?= ($year-1).'-'.$year; ?>" <?= $year === (int)$school_year ? 'selected' : ''; ?>><?= ($year-1).'-'.$year; ?></option><?php endfor; ?></select></div><div><label for="rphi-grade-band">Grade range</label><select id="rphi-grade-band" name="grade_band" class="form-control"><?php foreach ($grade_bands as $key=>$band): ?><option value="<?= html_escape($key); ?>" <?= $key === $grade_band ? 'selected' : ''; ?>><?= html_escape($band['label']); ?></option><?php endforeach; ?></select></div><button class="btn btn-primary" type="submit"><i class="mdi mdi-filter-outline mr-1"></i>Generate Report</button></form>

    <section class="rphi-metrics" aria-label="Regional Phil-IRI summary">
        <div class="rphi-metric"><span>Schools submitted</span><strong><?= number_format($reported_school_count); ?></strong><small>of <?= number_format($regional_school_count); ?> eligible schools in scope</small></div>
        <div class="rphi-metric"><span>Learner assessment entries</span><strong><?= number_format($totals['learners_assessed']); ?></strong><small>Counts may repeat across grades, languages, or test periods</small></div>
        <div class="rphi-metric"><span>Independent readers</span><strong><?= number_format($totals['independent']); ?></strong><small><?= $totals['learners_assessed'] > 0 ? number_format(($totals['independent']/$totals['learners_assessed'])*100,1).'%' : '0.0%'; ?> of reported learner assessment entries</small></div>
        <div class="rphi-metric"><span>Needs reading support</span><strong><?= number_format($totals['instructional']+$totals['frustration']+$totals['non_reader']); ?></strong><small>Instructional, frustration, and non-reader totals</small></div>
    </section>

    <section class="rphi-card"><div class="rphi-card-head"><h2>Results by Grade, Language, and Assessment Period</h2><p>Counts are summed from school submissions for <?= html_escape($school_year_label); ?>.</p></div>
        <div class="rphi-charts">
            <?php $grade_list = $grade_bands[$grade_band]['grades']; $grade_title = count($grade_list) > 1 ? 'Grades ' . min($grade_list) . ' to ' . max($grade_list) : 'Grade ' . reset($grade_list); foreach (array('Filipino'=>'filipino','English'=>'english') as $language=>$chart_id): ?><article class="rphi-chart-card <?= $chart_id; ?>"><div class="rphi-chart-head"><span class="rphi-chart-icon"><i class="mdi mdi-chart-bar"></i></span><div><h3>Percentage of <?= html_escape($grade_title); ?> Learners in <?= html_escape($language); ?></h3><p>Reading-level distribution within each grade for <?= html_escape($school_year_label); ?>.</p></div></div><?php if (empty($phil_iri_charts[$language])): ?><div class="rphi-chart-frame"><div class="rphi-chart-empty">No <?= html_escape($language); ?> submissions for the selected year and grade range.</div></div><?php else: ?><div class="rphi-chart-frame"><canvas id="phil-iri-chart-<?= $chart_id; ?>" aria-label="<?= html_escape($language); ?> percentage of learners grouped by grade and reading level"></canvas></div><?php endif; ?></article><?php endforeach; ?>
        </div>
        <div class="rphi-table-wrap"><table class="table rphi-table"><thead><tr><th>Grade</th><th>Language</th><th>Assessment</th><th>Schools</th><th>Learners assessed</th><th>Independent</th><th>Instructional</th><th>Frustration</th><th>Non-reader</th></tr></thead><tbody><?php if (empty($groups)): ?><tr><td colspan="9" class="rphi-empty">No school submissions matched the selected year and grade range.</td></tr><?php else: foreach ($groups as $group): ?><tr><td><strong>Grade <?= (int)$group['grade_level']; ?></strong></td><td><span class="rphi-pill"><?= html_escape($group['language']); ?></span></td><td><?= html_escape($group['assessment_period']); ?></td><td class="rphi-num"><?= number_format((int)$group['school_count']); ?></td><td class="rphi-num"><?= number_format((int)$group['learners_assessed']); ?></td><td class="rphi-num"><?= number_format((int)$group['independent']); ?></td><td class="rphi-num"><?= number_format((int)$group['instructional']); ?></td><td class="rphi-num"><?= number_format((int)$group['frustration']); ?></td><td class="rphi-num"><?= number_format((int)$group['non_reader']); ?></td></tr><?php endforeach; endif; ?></tbody></table></div>
    </section>

    <?php if (!empty($show_division_summary)): ?><section class="rphi-card"><div class="rphi-card-head"><h2>Summary by Division</h2><p>Divisions and schools in <?= html_escape($scope_label); ?>.</p></div><div class="rphi-table-wrap"><table class="table rphi-table"><thead><tr><th>Division</th><th>Schools submitted</th><th>Learners assessed</th><th>Independent</th><th>Instructional</th><th>Frustration</th><th>Non-reader</th></tr></thead><tbody><?php if (empty($divisions)): ?><tr><td colspan="7" class="rphi-empty">Division summaries will appear after schools submit results.</td></tr><?php else: foreach ($divisions as $division): ?><tr><td><strong><?= html_escape($division['name']); ?></strong></td><td class="rphi-num"><?= number_format((int)$division['school_count']); ?></td><td class="rphi-num"><?= number_format((int)$division['learners_assessed']); ?></td><td class="rphi-num"><?= number_format((int)$division['independent']); ?></td><td class="rphi-num"><?= number_format((int)$division['instructional']); ?></td><td class="rphi-num"><?= number_format((int)$division['frustration']); ?></td><td class="rphi-num"><?= number_format((int)$division['non_reader']); ?></td></tr><?php endforeach; endif; ?></tbody></table></div></section><?php endif; ?>

    <section class="rphi-card"><div class="rphi-card-head"><h2>School Submissions</h2><p>One row per school; Filipino and English counts are combined across the selected grades and assessment periods.</p></div><div class="rphi-table-wrap"><table class="table rphi-table rphi-school-submissions"><thead><tr><th rowspan="2">Division</th><th rowspan="2">School / ID</th><th colspan="5">Filipino</th><th colspan="5">English</th></tr><tr><th>Learners Assessed</th><th>Independent</th><th>Instructional</th><th>Frustration</th><th>Non-Reader</th><th>Learners Assessed</th><th>Independent</th><th>Instructional</th><th>Frustration</th><th>Non-Reader</th></tr></thead><tbody><?php if (empty($school_submissions)): ?><tr><td colspan="12" class="rphi-empty">No school submissions matched the selected year and grade range.</td></tr><?php else: foreach ($school_submissions as $school_submission): ?><tr><td><?= html_escape($school_submission['division_name']); ?></td><td><strong><?= html_escape($school_submission['schoolName']); ?></strong><br><small><?= html_escape($school_submission['school_id']); ?></small></td><?php foreach (array('Filipino','English') as $language): foreach (array('learners_assessed','independent','instructional','frustration','non_reader') as $level): ?><td class="rphi-num"><?= number_format($school_submission[$language][$level]); ?></td><?php endforeach; endforeach; ?></tr><?php endforeach; endif; ?></tbody></table></div></section>
</div>
<?php if (!empty($phil_iri_charts['Filipino']) || !empty($phil_iri_charts['English'])): ?>
<script src="<?= base_url('assets/libs/chart-js/Chart.bundle.min.js'); ?>"></script>
<script>
(function () {
    var charts = <?= json_encode($phil_iri_charts, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;
    var colors = [
        {label:'Independent', key:'independent', color:'#ff8f00'},
        {label:'Instructional', key:'instructional', color:'#08b4c3'},
        {label:'Frustration', key:'frustration', color:'#388e3c'},
        {label:'Non-Reader', key:'non_reader', color:'#c84c58'}
    ];
    function draw(language, id) {
        var canvas = document.getElementById(id);
        if (!canvas || typeof Chart === 'undefined') return;
        var rows = charts[language] || [];
        var labels = rows.map(function(row){return row.label;});
        new Chart(canvas.getContext('2d'), {
            type:'bar',
            data:{labels:labels,datasets:colors.map(function(level){return {label:level.label,data:rows.map(function(row){return row.learners_assessed ? (row[level.key] || 0) * 100 / row.learners_assessed : 0;}),backgroundColor:level.color,borderColor:level.color,borderWidth:1,maxBarThickness:42,barPercentage:.86,categoryPercentage:.78};})},
            options:{responsive:true,maintainAspectRatio:false,legend:{display:true,position:'top',labels:{fontColor:'#222',boxWidth:18,padding:12}},
                tooltips:{mode:'index',intersect:false,backgroundColor:'#3b0718',titleFontColor:'#fff',bodyFontColor:'#fff',cornerRadius:8,callbacks:{label:function(item,dataset){return dataset.label+': '+item.yLabel.toFixed(2)+'%';}}},
                scales:{xAxes:[{ticks:{fontColor:'#222',fontSize:11},scaleLabel:{display:true,labelString:'Grade Level',fontColor:'#222'},gridLines:{display:false}}],yAxes:[{ticks:{beginAtZero:true,max:100,stepSize:20,callback:function(value){return value+'%';},fontColor:'#222'},gridLines:{color:'rgba(0,0,0,.09)'},scaleLabel:{display:false}}]},
                animation:{duration:650}
            },
            plugins:[{afterDatasetsDraw:function(chart){var ctx=chart.chart.ctx;chart.data.datasets.forEach(function(dataset,datasetIndex){var meta=chart.getDatasetMeta(datasetIndex);meta.data.forEach(function(bar,index){var value=dataset.data[index];if(value<=0)return;var model=bar._model;var barHeight=model.base-model.y;ctx.save();ctx.fillStyle=barHeight<25?'#38252b':'#fff';ctx.font='bold 10px Arial';ctx.textAlign='center';ctx.textBaseline='middle';ctx.fillText(value.toFixed(1)+'%',model.x,barHeight<25?model.y-8:model.y+Math.min(12,barHeight/2));ctx.restore();});});}}]
        });
    }
    draw('Filipino','phil-iri-chart-filipino');
    draw('English','phil-iri-chart-english');
}());
</script>
<?php endif; ?>
