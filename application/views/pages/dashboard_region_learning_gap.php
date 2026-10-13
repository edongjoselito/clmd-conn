<?php
/**
 * Regional Learning Gap Overview — served at /region.
 *
 * Every figure on this page is read back from submitted learning-gap records,
 * so a reader can trace any number here to the records list behind it.
 */
$region_id = (int) $this->session->region;
$scope = array('type' => 'region', 'id' => $region_id);

$summary = $this->Page_model->learning_gap_summary($scope);
$term_performance = $this->Page_model->learning_gap_term_performance($scope);
$thematic_analysis = $this->Page_model->learning_gap_thematic_analysis($scope);
$division_rows = $this->Page_model->learning_gap_division_summary($region_id);
$division_performance_rows = $this->Page_model->learning_gap_division_performance($region_id);

$performance_by_division = array();
foreach ($division_performance_rows as $performance_row) {
    $performance_by_division[(int) $performance_row->division_id] = $performance_row;
}

$records_url = base_url('Pages/learning_gap_records');
$overview_url = base_url('Pages/learning_gap');
$school_summary_url = base_url('Pages/learning_gap_school_summary');

$display_name = isset($this->session->user) && trim((string) $this->session->user) !== ''
    ? mb_convert_case((string) $this->session->user, MB_CASE_TITLE, 'UTF-8')
    : 'Regional User';

$record_count = (int) $summary->record_count;
$reporting_school_count = (int) $summary->school_count;
$learners_assessed = (int) $summary->learners_assessed;
$learners_with_gap = (int) $summary->learners_with_gap;
$gap_rate = $learners_assessed > 0 ? ($learners_with_gap / $learners_assessed) * 100 : 0;

$cpl_record_count = array_sum(array_map(function ($row) {
    return (int) $row->cpl_record_count;
}, $term_performance));
$cpl_coverage = $record_count > 0 ? ($cpl_record_count / $record_count) * 100 : 0;

$divisions_total = count($division_rows);
$divisions_reporting = 0;
foreach ($division_rows as $division_row) {
    if ((int) $division_row->record_count > 0) {
        $divisions_reporting++;
    }
}
$reporting_rate = $divisions_total > 0 ? ($divisions_reporting / $divisions_total) * 100 : 0;

/* The summary query already orders divisions by learners with gap, so the top
   of the list is the shortlist a regional officer follows up on first. */
$priority_rows = array_slice(array_values(array_filter($division_rows, function ($row) {
    return (int) $row->learners_with_gap > 0;
})), 0, 4);

$theme_max_count = !empty($thematic_analysis['themes'])
    ? max(array_map(function ($theme) { return (int) $theme->record_count; }, $thematic_analysis['themes']))
    : 0;

$network_districts = isset($district_count) ? (int) $district_count : 0;
$network_schools = isset($registered_school_count) ? (int) $registered_school_count : 0;
$school_coverage = $network_schools > 0 ? ($reporting_school_count / $network_schools) * 100 : 0;
?>
<link rel="stylesheet" href="<?= base_url('assets/css/region-dashboard.css'); ?>?v=<?= filemtime(FCPATH . 'assets/css/region-dashboard.css'); ?>">

<div class="rd-page">
    <header class="rd-header">
        <div>
            <span class="rd-eyebrow">CLMD Learning Portal · All Learning Areas</span>
            <h1>Regional Learning Gap Overview</h1>
            <p class="rd-header-meta">
                <span>Welcome, <?= html_escape($display_name); ?></span>
                <span class="rd-num">Fiscal Year <?= (int) $this->session->fy; ?></span>
                <span class="rd-num"><?= number_format($divisions_total); ?> division<?= $divisions_total === 1 ? '' : 's'; ?></span>
                <span class="rd-num"><?= number_format($network_districts); ?> district<?= $network_districts === 1 ? '' : 's'; ?></span>
                <span class="rd-num"><?= number_format($network_schools); ?> registered school<?= $network_schools === 1 ? '' : 's'; ?></span>
            </p>
        </div>
        <div class="rd-header-actions">
            <a class="btn btn-light" href="<?= $overview_url; ?>"><i class="mdi mdi-chart-bar" aria-hidden="true"></i> Learning gap summary</a>
            <a class="btn btn-outline-light" href="<?= $records_url; ?>"><i class="mdi mdi-format-list-bulleted" aria-hidden="true"></i> View records</a>
        </div>
    </header>

    <div class="rd-metrics">
        <a class="rd-metric" href="<?= $school_summary_url; ?>">
            <span class="rd-metric-top">
                <span class="rd-metric-label">Reporting schools</span>
                <span class="rd-metric-icon"><i class="mdi mdi-school-outline" aria-hidden="true"></i></span>
            </span>
            <strong class="rd-metric-value rd-num"><?= number_format($reporting_school_count); ?></strong>
            <small class="rd-metric-note">
                <?= $network_schools > 0
                    ? number_format($school_coverage, 1) . '% of ' . number_format($network_schools) . ' registered schools'
                    : 'Schools with at least one submission'; ?>
            </small>
        </a>
        <a class="rd-metric" href="<?= $records_url; ?>">
            <span class="rd-metric-top">
                <span class="rd-metric-label">Encoded records</span>
                <span class="rd-metric-icon"><i class="mdi mdi-file-document-outline" aria-hidden="true"></i></span>
            </span>
            <strong class="rd-metric-value rd-num"><?= number_format($record_count); ?></strong>
            <small class="rd-metric-note">Competency gap entries submitted region-wide</small>
        </a>
        <a class="rd-metric" href="<?= $records_url; ?>">
            <span class="rd-metric-top">
                <span class="rd-metric-label">Learners assessed</span>
                <span class="rd-metric-icon"><i class="mdi mdi-account-group-outline" aria-hidden="true"></i></span>
            </span>
            <strong class="rd-metric-value rd-num"><?= number_format($learners_assessed); ?></strong>
            <small class="rd-metric-note">Sum of the assessed counts on submitted records</small>
        </a>
        <a class="rd-metric rd-metric-focus" href="<?= $records_url; ?>">
            <span class="rd-metric-top">
                <span class="rd-metric-label">Recorded gap rate</span>
                <span class="rd-metric-icon"><i class="mdi mdi-alert-circle-outline" aria-hidden="true"></i></span>
            </span>
            <strong class="rd-metric-value rd-num"><?= $learners_assessed > 0 ? number_format($gap_rate, 1) . '%' : '—'; ?></strong>
            <small class="rd-metric-note">
                <?= $learners_assessed > 0
                    ? number_format($learners_with_gap) . ' of ' . number_format($learners_assessed) . ' learners'
                    : 'No assessed learners recorded yet'; ?>
            </small>
            <?php if ($learners_assessed > 0) : ?>
                <span class="rd-meter" aria-hidden="true"><span style="width:<?= min(100, max(0, $gap_rate)); ?>%"></span></span>
            <?php endif; ?>
        </a>
    </div>

    <div class="row">
        <div class="col-xl-8">
            <section class="rd-card" aria-labelledby="rd-term-title">
                <div class="rd-card-head">
                    <h2 id="rd-term-title">Proficiency and gap rate by term</h2>
                    <span class="rd-tag"><i class="mdi mdi-chart-timeline-variant" aria-hidden="true"></i> CPL on <?= number_format($cpl_record_count); ?>/<?= number_format($record_count); ?> records</span>
                    <p>Gap rate is the sum of recorded gap counts divided by the sum of recorded assessed counts. Class proficiency level is weighted by assessed count across the records that carry a CPL value.</p>
                </div>
                <div class="rd-card-body">
                    <div class="rd-chart">
                        <canvas id="rdTermChart" role="img" aria-label="Class proficiency level and recorded gap rate for each term"></canvas>
                        <div id="rdTermEmpty" class="rd-chart-empty">
                            <div><i class="mdi mdi-chart-line-variant" aria-hidden="true"></i>No per-term assessment data has been submitted yet.</div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
        <div class="col-xl-4">
            <section class="rd-card" aria-labelledby="rd-coverage-title">
                <div class="rd-card-head">
                    <h2 id="rd-coverage-title">Division reporting coverage</h2>
                    <span class="rd-tag rd-tag-quiet rd-num"><?= $divisions_reporting; ?> / <?= $divisions_total; ?></span>
                </div>
                <div class="rd-card-body">
                    <div class="rd-coverage-value rd-num"><?= number_format($reporting_rate, 1); ?>%</div>
                    <p class="rd-coverage-label">of divisions have at least one submitted learning-gap record</p>
                    <div class="rd-bar" aria-hidden="true"><span style="width:<?= min(100, max(0, $reporting_rate)); ?>%"></span></div>
                    <div class="rd-bar-legend">
                        <span><strong class="rd-num"><?= number_format($divisions_reporting); ?></strong> reporting</span>
                        <span><strong class="rd-num"><?= number_format(max(0, $divisions_total - $divisions_reporting)); ?></strong> without records</span>
                    </div>
                </div>
            </section>

            <section class="rd-card" aria-labelledby="rd-priority-title">
                <div class="rd-card-head">
                    <h2 id="rd-priority-title">Priority attention</h2>
                    <a class="rd-link" href="<?= $records_url; ?>">All records <i class="mdi mdi-arrow-right" aria-hidden="true"></i></a>
                    <p>Divisions carrying the largest recorded number of learners with gaps.</p>
                </div>
                <div class="rd-card-body">
                    <?php if (empty($priority_rows)) : ?>
                        <div class="rd-empty">
                            <i class="mdi mdi-check-circle-outline" aria-hidden="true"></i>
                            <p>No division has recorded learners with gaps yet.</p>
                        </div>
                    <?php else : ?>
                        <ol class="rd-priority">
                            <?php foreach ($priority_rows as $index => $row) : ?>
                                <li>
                                    <span class="rd-priority-rank"><?= $index + 1; ?></span>
                                    <span class="rd-priority-name">
                                        <a href="<?= $records_url; ?>?division_id=<?= (int) $row->division_id; ?>"><?= html_escape($row->division_name); ?></a>
                                        <small><?= number_format((int) $row->school_count); ?> reporting school<?= (int) $row->school_count === 1 ? '' : 's'; ?> · <?= number_format((int) $row->record_count); ?> record<?= (int) $row->record_count === 1 ? '' : 's'; ?></small>
                                    </span>
                                    <span class="rd-priority-value">
                                        <strong class="rd-num"><?= number_format((int) $row->learners_with_gap); ?></strong>
                                        <small>with gap</small>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        </ol>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </div>

    <section class="rd-card" aria-labelledby="rd-division-chart-title">
        <div class="rd-card-head">
            <h2 id="rd-division-chart-title">Proficiency and gap rate by division</h2>
            <span class="rd-tag rd-tag-quiet rd-num"><?= number_format($divisions_total); ?> division<?= $divisions_total === 1 ? '' : 's'; ?></span>
            <p>Both measures come from submitted records. Divisions without a CPL value are excluded from the proficiency bar and reported under coverage instead.</p>
        </div>
        <div class="rd-card-body">
            <div class="rd-chart rd-chart-tall">
                <canvas id="rdDivisionChart" role="img" aria-label="Comparison of class proficiency level and recorded gap rate for each division"></canvas>
                <div id="rdDivisionEmpty" class="rd-chart-empty">
                    <div><i class="mdi mdi-chart-bar" aria-hidden="true"></i>No division performance data is available yet.</div>
                </div>
            </div>
        </div>
    </section>

    <section class="rd-card" aria-labelledby="rd-theme-title">
        <div class="rd-card-head">
            <h2 id="rd-theme-title">Automated thematic analysis</h2>
            <span class="rd-tag"><i class="mdi mdi-auto-fix" aria-hidden="true"></i> Language-rule assisted</span>
            <p>Recurring themes detected in the intervention and remarks text that schools submitted with their records.</p>
        </div>
        <div class="rd-card-body">
            <?php if (empty($thematic_analysis['themes'])) : ?>
                <div class="rd-empty">
                    <i class="mdi mdi-file-search-outline" aria-hidden="true"></i>
                    <p>No intervention or remarks text has been submitted yet, so there is nothing to analyse.</p>
                </div>
            <?php else : ?>
                <?php
                $leading_theme = $thematic_analysis['themes'][0];
                $secondary_themes = array_slice($thematic_analysis['themes'], 1, 2);
                $unclassified = isset($thematic_analysis['unclassified_count']) ? (int) $thematic_analysis['unclassified_count'] : 0;
                ?>
                <div class="rd-theme-facts">
                    <span><strong class="rd-num"><?= number_format((int) $thematic_analysis['source_count']); ?></strong> records analysed</span>
                    <span><strong class="rd-num"><?= number_format((int) $thematic_analysis['school_count']); ?></strong> reporting schools</span>
                    <span><strong class="rd-num"><?= number_format(count($thematic_analysis['themes'])); ?></strong> leading themes</span>
                    <?php if ($unclassified > 0) : ?>
                        <span><strong class="rd-num"><?= number_format($unclassified); ?></strong> unmatched by any theme</span>
                    <?php endif; ?>
                </div>
                <p class="rd-insight">
                    <strong>Key finding:</strong> the leading theme is <?= html_escape($leading_theme->theme); ?>, appearing in <?= number_format((int) $leading_theme->record_count); ?> submitted record<?= (int) $leading_theme->record_count === 1 ? '' : 's'; ?> across <?= number_format((int) $leading_theme->school_count); ?> school<?= (int) $leading_theme->school_count === 1 ? '' : 's'; ?>.<?php if (!empty($secondary_themes)) : ?> Other recurring priorities include <?= html_escape(implode(' and ', array_map(function ($theme) { return $theme->theme; }, $secondary_themes))); ?>.<?php endif; ?>
                </p>
                <div class="rd-themes">
                    <?php foreach ($thematic_analysis['themes'] as $theme) : ?>
                        <?php $theme_width = $theme_max_count > 0 ? ((int) $theme->record_count / $theme_max_count) * 100 : 0; ?>
                        <article class="rd-theme">
                            <div class="rd-theme-head">
                                <h3><?= html_escape($theme->theme); ?></h3>
                                <span class="rd-theme-count rd-num"><?= number_format((int) $theme->record_count); ?></span>
                            </div>
                            <div class="rd-theme-bar" aria-hidden="true"><span style="width:<?= min(100, max(0, $theme_width)); ?>%"></span></div>
                            <div class="rd-theme-meta"><?= number_format((int) $theme->school_count); ?> school<?= (int) $theme->school_count === 1 ? '' : 's'; ?> · <?= number_format((int) $theme->intervention_count); ?> intervention mention<?= (int) $theme->intervention_count === 1 ? '' : 's'; ?> · <?= number_format((int) $theme->remarks_count); ?> remark mention<?= (int) $theme->remarks_count === 1 ? '' : 's'; ?></div>
                            <?php if (!empty($theme->examples)) : ?>
                                <p class="rd-theme-quote">“<?= html_escape($theme->examples[0]); ?>”</p>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
                <p class="rd-note">Themes are generated inside the system using transparent education-focused language rules. Treat them as decision-support indicators and validate them against the source records.</p>
            <?php endif; ?>
        </div>
    </section>

    <section class="rd-card" aria-labelledby="rd-table-title">
        <div class="rd-card-head">
            <h2 id="rd-table-title">Performance by division</h2>
            <div class="rd-filter">
                <i class="mdi mdi-magnify" aria-hidden="true"></i>
                <input type="search" id="rdDivisionFilter" placeholder="Filter divisions" aria-label="Filter divisions by name" autocomplete="off">
            </div>
            <p>Counts are direct sums from submitted records. Select a column heading to reorder the table.</p>
        </div>
        <div class="rd-card-body rd-card-body-flush">
            <?php if (empty($division_rows)) : ?>
                <div class="rd-empty">
                    <i class="mdi mdi-office-building" aria-hidden="true"></i>
                    <p>No divisions are configured for this regional account.</p>
                </div>
            <?php else : ?>
                <div class="rd-table-wrap">
                    <table class="rd-table" id="rdDivisionTable">
                        <thead>
                            <tr>
                                <th class="rd-sort" data-type="text" scope="col">Division</th>
                                <th class="rd-sort" data-type="number" scope="col">Status</th>
                                <th class="rd-sort" data-type="number" scope="col">School submissions</th>
                                <th class="rd-sort" data-type="number" scope="col">Records</th>
                                <th class="rd-sort" data-type="number" scope="col">Assessed</th>
                                <th class="rd-sort" data-type="number" scope="col">With gap</th>
                                <th class="rd-sort" data-type="number" scope="col">Weighted CPL</th>
                                <th class="rd-sort" data-type="number" data-dir="desc" scope="col">Gap rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($division_rows as $row) : ?>
                                <?php
                                $record_link = $records_url . '?division_id=' . (int) $row->division_id;
                                $row_assessed = (int) $row->learners_assessed;
                                $row_records = (int) $row->record_count;
                                $row_gap_rate = $row_assessed > 0 ? ((int) $row->learners_with_gap / $row_assessed) * 100 : null;
                                $row_school_total = (int) $row->total_school_count;
                                $row_submission_rate = $row_school_total > 0 ? ((int) $row->school_count / $row_school_total) * 100 : 0;
                                $row_performance = isset($performance_by_division[(int) $row->division_id])
                                    ? $performance_by_division[(int) $row->division_id]
                                    : null;
                                $row_cpl = $row_performance && $row_performance->class_proficiency_level !== null
                                    ? (float) $row_performance->class_proficiency_level
                                    : null;
                                ?>
                                <tr>
                                    <td data-sort="<?= html_escape(mb_strtolower((string) $row->division_name, 'UTF-8')); ?>">
                                        <span class="rd-division-name"><?= html_escape($row->division_name); ?></span>
                                    </td>
                                    <td data-sort="<?= $row_records > 0 ? 1 : 0; ?>">
                                        <span class="rd-pill<?= $row_records > 0 ? '' : ' rd-pill-muted'; ?>"><?= $row_records > 0 ? 'Reporting' : 'No records'; ?></span>
                                    </td>
                                    <td data-sort="<?= $row_submission_rate; ?>">
                                        <a class="rd-num" href="<?= $record_link; ?>"><?= number_format((int) $row->school_count); ?> / <?= number_format($row_school_total); ?></a>
                                        <span class="rd-sub rd-num"><?= number_format($row_submission_rate, 1); ?>% submitted</span>
                                    </td>
                                    <td class="rd-num" data-sort="<?= $row_records; ?>"><a href="<?= $record_link; ?>"><?= number_format($row_records); ?></a></td>
                                    <td class="rd-num" data-sort="<?= $row_assessed; ?>"><?= number_format($row_assessed); ?></td>
                                    <td class="rd-num" data-sort="<?= (int) $row->learners_with_gap; ?>"><?= number_format((int) $row->learners_with_gap); ?></td>
                                    <td data-sort="<?= $row_cpl === null ? -1 : $row_cpl; ?>">
                                        <?php if ($row_cpl === null) : ?>
                                            <span class="rd-sub">Not reported</span>
                                        <?php else : ?>
                                            <span class="rd-num"><?= number_format($row_cpl, 1); ?>%</span>
                                            <span class="rd-sub rd-num"><?= number_format((int) $row_performance->cpl_record_count); ?> of <?= number_format((int) $row_performance->record_count); ?> records</span>
                                        <?php endif; ?>
                                    </td>
                                    <td data-sort="<?= $row_gap_rate === null ? -1 : $row_gap_rate; ?>">
                                        <?php if ($row_gap_rate === null) : ?>
                                            <span class="rd-sub">—</span>
                                        <?php else : ?>
                                            <span class="rd-rate">
                                                <span class="rd-rate-bar" aria-hidden="true"><span style="width:<?= min(100, max(0, $row_gap_rate)); ?>%"></span></span>
                                                <span class="rd-rate-value rd-num"><?= number_format($row_gap_rate, 1); ?>%</span>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <p class="rd-table-foot" id="rdDivisionCount" data-total="<?= $divisions_total; ?>">Showing all <?= number_format($divisions_total); ?> division<?= $divisions_total === 1 ? '' : 's'; ?>. CPL coverage region-wide is <?= number_format($cpl_coverage, 1); ?>%.</p>
            <?php endif; ?>
        </div>
    </section>
</div>

<script src="<?= base_url('assets/libs/chart-js/Chart.bundle.min.js'); ?>"></script>
<script>
    (function () {
        var NAVY = '#3b0718';
        var BLUE = '#922c43';
        var MAROON = '#800020';
        var GREEN = '#1d6b3c';
        var GRID = 'rgba(59,7,24,.08)';

        function percentTick(value) { return value + '%'; }
        function percentLabel(value, label) {
            return label + ': ' + (value === null || value === undefined ? 'No data' : Number(value).toFixed(1) + '%');
        }

        /* ---------- Per-term chart ---------- */
        var termRows = <?= json_encode(array_map(function ($row) {
            $assessed = (int) $row->learners_assessed;
            return array(
                'term' => (string) $row->term,
                'cpl' => $row->class_proficiency_level === null ? null : round((float) $row->class_proficiency_level, 2),
                'gapPercentage' => $assessed > 0 ? round(((int) $row->learners_with_gap / $assessed) * 100, 2) : null,
                'assessed' => $assessed,
            );
        }, $term_performance), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

        var divisionRows = <?= json_encode(array_map(function ($row) {
            $assessed = (int) $row->learners_assessed;
            return array(
                'division' => (string) $row->division_name,
                'cpl' => $row->class_proficiency_level === null ? null : round((float) $row->class_proficiency_level, 2),
                'gapPercentage' => $assessed > 0 ? round(((int) $row->learners_with_gap / $assessed) * 100, 2) : null,
                'assessed' => $assessed,
            );
        }, $division_performance_rows), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

        function showEmpty(canvasId, emptyId) {
            var canvas = document.getElementById(canvasId);
            var empty = document.getElementById(emptyId);
            if (canvas) {
                canvas.style.display = 'none';
                if (canvas.parentNode) { canvas.parentNode.className += ' is-empty'; }
            }
            if (empty) { empty.style.display = 'flex'; }
        }

        function hasData(rows) {
            return rows.some(function (row) { return row.assessed > 0 || row.cpl !== null; });
        }

        if (typeof Chart !== 'undefined') {
            Chart.defaults.global.defaultFontColor = '#78616a';
            Chart.defaults.global.defaultFontFamily = getComputedStyle(document.body).fontFamily;
        }

        var termCanvas = document.getElementById('rdTermChart');
        if (typeof Chart !== 'undefined' && termCanvas && hasData(termRows)) {
            new Chart(termCanvas.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: termRows.map(function (row) { return row.term; }),
                    datasets: [
                        {
                            label: 'Recorded gap rate',
                            data: termRows.map(function (row) { return row.gapPercentage; }),
                            backgroundColor: 'rgba(128,0,32,.78)',
                            hoverBackgroundColor: 'rgba(128,0,32,.95)',
                            borderColor: MAROON,
                            borderWidth: 0,
                            borderSkipped: 'bottom'
                        },
                        {
                            type: 'line',
                            label: 'Class proficiency level',
                            data: termRows.map(function (row) { return row.cpl; }),
                            borderColor: GREEN,
                            backgroundColor: 'rgba(29,107,60,.08)',
                            pointBackgroundColor: GREEN,
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 5,
                            pointHoverRadius: 7,
                            borderWidth: 2,
                            fill: false,
                            spanGaps: true,
                            lineTension: .25
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    layout: { padding: { top: 6 } },
                    legend: { position: 'bottom', labels: { usePointStyle: true, padding: 18, boxWidth: 8 } },
                    tooltips: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: NAVY,
                        titleMarginBottom: 8,
                        xPadding: 12,
                        yPadding: 10,
                        cornerRadius: 6,
                        callbacks: {
                            label: function (item, data) {
                                return percentLabel(item.yLabel, data.datasets[item.datasetIndex].label);
                            }
                        }
                    },
                    scales: {
                        xAxes: [{ gridLines: { display: false }, barPercentage: .5, categoryPercentage: .6 }],
                        yAxes: [{
                            ticks: { beginAtZero: true, max: 100, stepSize: 20, callback: percentTick },
                            gridLines: { color: GRID, drawBorder: false, zeroLineColor: GRID }
                        }]
                    }
                }
            });
        } else {
            showEmpty('rdTermChart', 'rdTermEmpty');
        }

        /* ---------- Per-division chart ---------- */
        var divisionCanvas = document.getElementById('rdDivisionChart');
        if (typeof Chart !== 'undefined' && divisionCanvas && hasData(divisionRows)) {
            new Chart(divisionCanvas.getContext('2d'), {
                type: 'horizontalBar',
                data: {
                    labels: divisionRows.map(function (row) { return row.division; }),
                    datasets: [
                        {
                            label: 'Class proficiency level',
                            data: divisionRows.map(function (row) { return row.cpl; }),
                            backgroundColor: 'rgba(198,107,124,.8)',
                            hoverBackgroundColor: BLUE,
                            borderWidth: 0
                        },
                        {
                            label: 'Recorded gap rate',
                            data: divisionRows.map(function (row) { return row.gapPercentage; }),
                            backgroundColor: 'rgba(128,0,32,.78)',
                            hoverBackgroundColor: MAROON,
                            borderWidth: 0
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    legend: { position: 'bottom', labels: { usePointStyle: true, padding: 18, boxWidth: 8 } },
                    tooltips: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: NAVY,
                        titleMarginBottom: 8,
                        xPadding: 12,
                        yPadding: 10,
                        cornerRadius: 6,
                        callbacks: {
                            label: function (item, data) {
                                return percentLabel(item.xLabel, data.datasets[item.datasetIndex].label);
                            }
                        }
                    },
                    scales: {
                        xAxes: [{
                            ticks: { beginAtZero: true, max: 100, stepSize: 20, callback: percentTick },
                            gridLines: { color: GRID, drawBorder: false, zeroLineColor: GRID }
                        }],
                        yAxes: [{ gridLines: { display: false }, barPercentage: .78, categoryPercentage: .74 }]
                    }
                }
            });
        } else {
            showEmpty('rdDivisionChart', 'rdDivisionEmpty');
        }

        /* ---------- Division table: filter and sort ---------- */
        var table = document.getElementById('rdDivisionTable');
        if (!table) { return; }

        var body = table.tBodies[0];
        var countNote = document.getElementById('rdDivisionCount');
        var total = countNote ? parseInt(countNote.getAttribute('data-total'), 10) || 0 : 0;
        var allRows = Array.prototype.slice.call(body.rows);

        function updateCount(visible) {
            if (!countNote) { return; }
            countNote.textContent = visible === total
                ? 'Showing all ' + total + ' division' + (total === 1 ? '' : 's') + '.'
                : 'Showing ' + visible + ' of ' + total + ' division' + (total === 1 ? '' : 's') + '.';
        }

        var filter = document.getElementById('rdDivisionFilter');
        if (filter) {
            filter.addEventListener('input', function () {
                var needle = filter.value.trim().toLowerCase();
                var visible = 0;
                allRows.forEach(function (row) {
                    var name = row.cells[0].getAttribute('data-sort') || '';
                    var match = needle === '' || name.indexOf(needle) !== -1;
                    row.hidden = !match;
                    if (match) { visible++; }
                });
                updateCount(visible);
            });
        }

        function sortValue(row, index, type) {
            var raw = row.cells[index].getAttribute('data-sort');
            return type === 'number' ? parseFloat(raw) : (raw || '');
        }

        Array.prototype.forEach.call(table.tHead.rows[0].cells, function (header, index) {
            if (header.className.indexOf('rd-sort') === -1) { return; }
            header.setAttribute('tabindex', '0');
            header.setAttribute('role', 'button');

            function sort() {
                var type = header.getAttribute('data-type') || 'text';
                /* Numbers read best largest-first, names A to Z. */
                var current = header.getAttribute('data-dir');
                var dir = current === 'desc' ? 'asc' : (current === 'asc' ? 'desc' : (type === 'number' ? 'desc' : 'asc'));

                Array.prototype.forEach.call(table.tHead.rows[0].cells, function (other) {
                    other.removeAttribute('data-dir');
                });
                header.setAttribute('data-dir', dir);

                allRows.slice().sort(function (left, right) {
                    var a = sortValue(left, index, type);
                    var b = sortValue(right, index, type);
                    var result = type === 'number' ? a - b : String(a).localeCompare(String(b));
                    return dir === 'asc' ? result : -result;
                }).forEach(function (row) {
                    body.appendChild(row);
                });
            }

            header.addEventListener('click', sort);
            header.addEventListener('keydown', function (event) {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    sort();
                }
            });
        });
    }());
</script>
