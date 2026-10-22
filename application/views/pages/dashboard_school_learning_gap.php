<?php
$school = $this->Common->one_cond_row('schools', 'schoolID', $this->session->username);
$scope = array('type' => 'school', 'id' => (string) $this->session->username);
$summary = $this->Page_model->learning_gap_summary($scope);
$recent = array_slice($this->Page_model->learning_gap_records($scope), 0, 5);
$school_name = $school && trim((string) $school->schoolName) !== '' ? $school->schoolName : $this->session->user;
$phil_iri_url = base_url('Pages/phil_iri');
?>
<style>
    .lg-home { --navy:var(--llcm-navy, #3b0718); --blue:var(--llcm-blue, #922c43); --soft:var(--llcm-sky, #fbf4f5); --line:var(--llcm-border, #ead7dc); }.lg-home .hero{margin:18px 0 22px;padding:32px;border-radius:18px;color:#fff;background:linear-gradient(130deg,var(--navy),var(--blue));}.lg-home .hero h1{color:#fff;margin:0 0 7px;font-size:27px}.lg-home .hero p{margin:0;color:#f4e6e9}.lg-home .metric{height:calc(100% - 20px);margin-bottom:20px;padding:20px;border:1px solid var(--line);border-radius:14px;background:#fff;box-shadow:0 5px 18px rgba(59,7,24,.06)}.lg-home .metric small{display:block;text-transform:uppercase;letter-spacing:.05em;color:#6b7d8b;font-weight:700}.lg-home .metric strong{display:block;margin-top:7px;font-size:29px;color:var(--navy)}.lg-home .card{border:1px solid var(--line);border-radius:14px;box-shadow:0 5px 18px rgba(59,7,24,.06)}.lg-home .card-header{background:var(--soft);border-bottom:1px solid var(--line);color:var(--navy)}
    .lg-home .term-chart-wrap{position:relative;height:320px}.lg-home .term-chart-empty{display:flex;align-items:center;justify-content:center;height:100%;color:#786971;text-align:center}
</style>
<div class="lg-home">
    <div class="hero">
        <h1>Welcome, <?= html_escape($school_name); ?></h1>
        <p>Learning Gap Monitoring dashboard for Fiscal Year <?= (int) $this->session->fy; ?> — encode competency gaps, interventions, and progress for your school.</p>
    </div>
    <div class="row">
        <div class="col-md-6"><div class="metric"><small>Encoded Records</small><strong><?= (int) $summary->record_count; ?></strong></div></div>
        <div class="col-md-6"><div class="metric"><small>Recorded Assessed Count</small><strong><?= number_format((int) $summary->learners_assessed); ?></strong></div></div>
    </div>
    <section class="card mb-4" aria-labelledby="school-term-performance-title">
        <div class="card-header"><h4 class="mb-1" id="school-term-performance-title">Proficiency and Gap Rate by Term</h4><small>Recorded gap rate and class proficiency level for <?= (int) $this->session->fy; ?>.</small></div>
        <div class="card-body"><div class="term-chart-wrap"><canvas id="schoolTermPerformanceChart" role="img" aria-label="Recorded gap rate and class proficiency level by term"></canvas><div id="schoolTermPerformanceEmpty" class="term-chart-empty">No per-term assessment data has been submitted yet.</div></div></div>
    </section>
    <div class="card mb-4"><div class="card-body d-flex justify-content-between align-items-center"><div><h4 class="mb-1">Phil-IRI Reading Results</h4><p class="text-muted mb-0">Enter school reading results by grade level for this school year.</p></div><a class="btn btn-primary" href="<?= $phil_iri_url; ?>">Open Phil-IRI Data Entry <i class="mdi mdi-arrow-right"></i></a></div></div>
    <?php if ($school && in_array((int)$school->category, array(1, 2, 3), true)): ?><div class="card mb-4"><div class="card-body d-flex justify-content-between align-items-center"><div><h4 class="mb-1">Comprehensive Rapid Literacy Assessment</h4><p class="text-muted mb-0">Record CRLA results for Grades 1–3 by language and assessment period.</p></div><a class="btn btn-primary" href="<?= base_url('Pages/crla'); ?>">Open CRLA Data Entry <i class="mdi mdi-arrow-right"></i></a></div></div><?php endif; ?>
    <div class="card"><div class="card-header"><h4 class="mb-0">Recent Encoded Learning Gaps</h4></div><div class="table-responsive"><table class="table mb-0"><thead><tr><th>Grade</th><th>Learning Area</th><th>Trimester</th><th>Least Learned Competency</th><th>Learners with Gap</th><th>Status</th></tr></thead><tbody><?php if (empty($recent)): ?><tr><td colspan="6" class="p-4 text-center text-muted">No records yet. Start by encoding the results from your assessment.</td></tr><?php else: foreach ($recent as $row): ?><tr><td><?= html_escape($row->grade_level); ?></td><td><?= html_escape($row->learning_area); ?></td><td><?= html_escape($row->term); ?></td><td><?= html_escape($row->least_learned_competency); ?></td><td><?= (int) $row->learners_with_gap; ?></td><td><?= html_escape($row->intervention_status ?: 'Not set'); ?></td></tr><?php endforeach; endif; ?></tbody></table></div><div class="card-body"><a href="<?= base_url('Pages/learning_gap'); ?>">Open Learning Gap Monitoring <i class="mdi mdi-arrow-right"></i></a></div></div>
</div>
<script src="<?= base_url('assets/libs/chart-js/Chart.bundle.min.js'); ?>"></script>
<script>
(function () {
    var rows = <?= json_encode(array_map(function ($row) {
        $assessed = (int) $row->learners_assessed;
        return array(
            'term' => (string) $row->term,
            'gap_rate' => $assessed > 0 ? round(((int) $row->learners_with_gap / $assessed) * 100, 2) : null,
            'proficiency' => $row->class_proficiency_level === null ? null : (float) $row->class_proficiency_level,
            'assessed' => $assessed,
        );
    }, $term_performance), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    var canvas = document.getElementById('schoolTermPerformanceChart');
    var empty = document.getElementById('schoolTermPerformanceEmpty');
    var hasData = rows.some(function (row) { return row.gap_rate !== null || row.proficiency !== null; });
    if (!hasData || typeof Chart === 'undefined' || !canvas) {
        if (canvas) canvas.style.display = 'none';
        if (empty) empty.style.display = 'flex';
        return;
    }
    empty.style.display = 'none';
    new Chart(canvas.getContext('2d'), {
        type: 'bar',
        data: {
            labels: rows.map(function (row) { return row.term; }),
            datasets: [
                { label: 'Recorded gap rate', data: rows.map(function (row) { return row.gap_rate; }), backgroundColor: 'rgba(128,0,32,.78)', borderColor: '#800020', borderWidth: 0 },
                { type: 'line', label: 'Class proficiency level', data: rows.map(function (row) { return row.proficiency; }), borderColor: '#1d6b3c', backgroundColor: 'rgba(29,107,60,.08)', pointBackgroundColor: '#1d6b3c', pointBorderColor: '#fff', pointBorderWidth: 2, pointRadius: 5, borderWidth: 2, fill: false, spanGaps: true, lineTension: .25 }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            legend: { position: 'bottom', labels: { usePointStyle: true, padding: 18, boxWidth: 8 } },
            tooltips: { mode: 'index', intersect: false, callbacks: { label: function (item, data) { var label = data.datasets[item.datasetIndex].label; return label + ': ' + (item.yLabel === null ? 'No data' : Number(item.yLabel).toFixed(1) + '%'); } } },
            scales: {
                xAxes: [{ gridLines: { display: false }, barPercentage: .58, categoryPercentage: .68 }],
                yAxes: [{ ticks: { beginAtZero: true, max: 100, stepSize: 20, callback: function (value) { return value + '%'; } }, scaleLabel: { display: true, labelString: 'Percentage' }, gridLines: { color: 'rgba(59,7,24,.08)' } }]
            }
        }
    });
}());
</script>
