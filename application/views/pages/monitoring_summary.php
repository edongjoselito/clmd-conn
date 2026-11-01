<?php
/**
 * Regional monitoring roll-up: the 8-domain graph per division and the
 * one-SDO summary that downloads as a Word file.
 */
$bands = array('Needs Improvement' => 'low', 'Fairly Meets Expectation' => 'fair', 'Satisfactorily Meets Expectation' => 'mid', 'Very Satisfactory' => 'good', 'Outstanding' => 'top');
$score_cell = function ($rating) use ($bands) {
    if ($rating['percent'] === null) { return '<span class="ms-unrated" title="Not yet rated">—</span>'; }
    $band = isset($bands[$rating['description']]) ? ' ms-band-' . $bands[$rating['description']] : '';
    return '<span class="ms-score' . $band . '" title="' . html_escape($rating['description']) . '">' . number_format($rating['percent'], 1) . '%</span>';
};
$tool_name = $tool ? $tool->name : 'All monitoring tools';
$summary_url = function ($division_id) use ($selected_sy, $tool) {
    return base_url('monitoring/summary') . '?' . html_escape(http_build_query(array('sy' => $selected_sy, 'tool_id' => $tool ? (int) $tool->id : '', 'division_id' => (int) $division_id)));
};
$has_ratings = $selected && $selected['assessed'] > 0;
?>
<link rel="stylesheet" href="<?= base_url('assets/css/monitoring-indicators.css'); ?>?v=<?= filemtime(FCPATH . 'assets/css/monitoring-indicators.css'); ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/monitoring-tool.css'); ?>?v=<?= filemtime(FCPATH . 'assets/css/monitoring-tool.css'); ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/monitoring-summary.css'); ?>?v=<?= filemtime(FCPATH . 'assets/css/monitoring-summary.css'); ?>">
<div class="mi-page mt-tool ms-page">
    <header class="mi-header">
        <div><small>MONITORING / SUMMARY</small><h1>Monitoring Summary</h1><p>See results across the 8 domains for each division, then download a Schools Division Office summary as a Word file.</p></div>
        <a href="<?= base_url('monitoring/tool'); ?>" class="btn btn-light"><i class="mdi mdi-clipboard-text-outline" aria-hidden="true"></i> Monitoring tools</a>
    </header>
    <section class="card mt-filter-card"><div class="card-body">
        <form action="<?= base_url('monitoring/summary'); ?>" method="get" id="ms-filters">
            <div class="form-row">
                <div class="form-group col-md-4 mb-md-0">
                    <label for="ms-sy">School year (SY)</label>
                    <select class="form-control" id="ms-sy" name="sy" <?= $school_years ? '' : 'disabled'; ?>>
                        <?php if (!$school_years): ?><option value="">No saved assessments</option><?php endif; ?>
                        <?php foreach ($school_years as $sy): ?><option value="<?= html_escape($sy); ?>" <?= $selected_sy === $sy ? 'selected' : ''; ?>><?= html_escape($sy); ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group col-md-4 mb-md-0">
                    <label for="ms-tool">Monitoring tool</label>
                    <select class="form-control" id="ms-tool" name="tool_id">
                        <option value="">All monitoring tools</option>
                        <?php foreach ($tools as $option): ?><option value="<?= (int) $option->id; ?>" <?= $tool && (int) $tool->id === (int) $option->id ? 'selected' : ''; ?>><?= html_escape($option->name); ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group col-md-4 mb-0">
                    <label for="ms-division">Schools Division Office</label>
                    <select class="form-control" id="ms-division" name="division_id">
                        <?php foreach ($summary as $row): ?><option value="<?= (int) $row['id']; ?>" <?= $selected && $selected['id'] === $row['id'] ? 'selected' : ''; ?>><?= html_escape($row['name']); ?></option><?php endforeach; ?>
                    </select>
                </div>
            </div>
            <noscript><button type="submit" class="btn btn-primary mt-3">Update summary</button></noscript>
        </form>
    </div></section>

    <?php if (!$selected): ?>
        <section class="card"><div class="card-body"><div class="mt-empty"><i class="mdi mdi-chart-bar" aria-hidden="true"></i><h3>No assessments to summarize</h3><p>Results will appear here once assessments are saved for this selection.</p><?php if ($tool): ?><p><a href="<?= base_url('monitoring/summary'); ?>">View all monitoring tools</a></p><?php endif; ?></div></div></section>
    <?php else: ?>
        <div class="mt-stats" aria-label="<?= html_escape($selected['name']); ?> totals">
            <div><span class="mt-stat-icon mt-stat-complete"><i class="mdi mdi-check-circle-outline" aria-hidden="true"></i></span><div><strong><?= number_format($selected['assessed']); ?></strong><span>Fully assessed forms</span></div></div>
            <div><span class="mt-stat-icon"><i class="mdi mdi-pencil-outline" aria-hidden="true"></i></span><div><strong><?= number_format($selected['in_progress']); ?></strong><span>In progress</span></div></div>
            <div><span class="mt-stat-icon"><i class="mdi mdi-school" aria-hidden="true"></i></span><div><strong><?= number_format($selected['school_count']); ?></strong><span>Schools rated</span></div></div>
            <div><span class="mt-stat-icon"><i class="mdi mdi-chart-bar" aria-hidden="true"></i></span><div><strong><?= html_escape($selected['overall']['score']); ?></strong><span>Overall rating</span><?php if ($has_ratings): ?><small><?= html_escape($selected['overall']['description']); ?></small><?php endif; ?></div></div>
        </div>

        <section class="card" aria-labelledby="ms-graph-title"><div class="card-body">
            <div class="mt-section-heading"><div><span class="mt-eyebrow">SUMMARY GRAPH</span><h2 id="ms-graph-title">8 Domains — <?= html_escape($selected['name']); ?></h2><p>SY <?= html_escape($selected_sy); ?> · <?= html_escape($tool_name); ?></p></div></div>
            <?php if ($has_ratings): ?>
                <div class="ms-chart-wrap"><canvas id="ms-domain-chart" role="img" aria-label="Rating per domain for <?= html_escape($selected['name']); ?>. The table below lists the same values."></canvas></div>
                <div class="table-responsive"><table class="table ms-table"><thead><tr><th scope="col">Domain</th><th scope="col">Rating</th><th scope="col">Description</th><th scope="col">Indicators evident</th></tr></thead><tbody>
                    <?php foreach ($selected['areas'] as $area): ?><tr><td><?= html_escape($area['name']); ?></td><td><?= $score_cell($area); ?></td><td><?= html_escape($area['description']); ?></td><td class="ms-num"><?= number_format($area['evident']); ?> of <?= number_format($area['total']); ?></td></tr><?php endforeach; ?>
                </tbody></table></div>
                <p class="ms-note">Rating = Evident indicators ÷ all indicators × 100, using fully assessed forms only.<?php if ($selected['in_progress']): ?> <?= number_format($selected['in_progress']); ?> form<?= $selected['in_progress'] === 1 ? ' is' : 's are'; ?> still in progress and will count once complete.<?php endif; ?></p>
            <?php else: ?>
                <div class="mt-empty"><i class="mdi mdi-chart-bar" aria-hidden="true"></i><h3>No fully assessed forms yet</h3><p><?= $selected['in_progress'] ? 'This division has forms in progress. Ratings appear once every indicator on a form is assessed.' : 'No assessments were saved for this division in the selected school year and tool.'; ?></p></div>
            <?php endif; ?>
        </div></section>

        <?php if ($selected['forms']): $remark_forms = array_filter($selected['forms'], function ($form) { return $form['strengths'] !== '' || $form['technical_assistance'] !== '' || $form['recommendations'] !== ''; }); ?>
        <section class="card" aria-labelledby="ms-sdo-title"><div class="card-body">
            <div class="mt-section-heading">
                <div><span class="mt-eyebrow">SDO SUMMARY</span><h2 id="ms-sdo-title">Summary of Monitoring — <?= html_escape($selected['name']); ?></h2><p>Every saved form for this division, with its remarks and recommendations.</p></div>
                <div><button type="button" class="btn btn-primary" id="ms-download"><i class="mdi mdi-file-word-outline" aria-hidden="true"></i> Download Word file</button><span class="ms-download-status" id="ms-download-status" role="status"></span></div>
            </div>
            <div class="table-responsive"><table class="table ms-table"><thead><tr><th scope="col">School</th><th scope="col">District</th><th scope="col">Monitoring date</th><th scope="col">Status</th><th scope="col">Rating</th><th scope="col">Description</th></tr></thead><tbody>
                <?php foreach ($selected['forms'] as $form): ?><tr>
                    <td><a href="<?= base_url('Pages/monitoring_assessment/' . (int) $form['record_id']); ?>"><?= html_escape($form['school_name']); ?></a><br><small><?= html_escape($form['school_id']); ?></small></td>
                    <td><?= html_escape($form['district_name']); ?></td>
                    <td class="ms-num"><?= $form['monitor_date'] !== '' ? html_escape($form['monitor_date']) : '—'; ?></td>
                    <td><span class="mt-status <?= $form['complete'] ? 'is-complete' : ''; ?>"><?= $form['complete'] ? 'Fully assessed' : 'In progress'; ?></span></td>
                    <td><?= $form['complete'] ? $score_cell($form['overall']) : '<span class="ms-unrated">—</span>'; ?></td>
                    <td><?= $form['complete'] ? html_escape($form['overall']['description']) : html_escape($form['overall']['answered'] . ' of ' . $form['overall']['total'] . ' indicators assessed'); ?></td>
                </tr><?php endforeach; ?>
            </tbody></table></div>
            <?php if ($remark_forms): ?>
                <details class="ms-remarks"><summary>Remarks and recommendations (<?= number_format(count($remark_forms)); ?>)</summary>
                    <?php foreach ($remark_forms as $form): ?><div class="ms-remark">
                        <h3><?= html_escape($form['school_name']); ?> · <?= html_escape($form['district_name']); ?></h3>
                        <?php foreach (array('strengths' => 'Strengths / Best Practices', 'technical_assistance' => 'Technical Assistance Needed', 'recommendations' => 'Recommendations') as $key => $label): if ($form[$key] === '') continue; ?><p><strong><?= $label; ?>:</strong> <?= nl2br(html_escape($form[$key])); ?></p><?php endforeach; ?>
                    </div><?php endforeach; ?>
                </details>
            <?php endif; ?>
        </div></section>
        <?php endif; ?>

        <section class="card" aria-labelledby="ms-compare-title"><div class="card-body">
            <div class="mt-section-heading"><div><span class="mt-eyebrow">ALL DIVISIONS</span><h2 id="ms-compare-title">Domain Ratings per Division</h2><p>Select a division to see its graph and summary. A dash means no fully assessed forms yet.</p></div></div>
            <div class="table-responsive"><table class="table ms-table ms-compare"><thead><tr><th scope="col">Division</th>
                <?php foreach ($groups as $group): ?><th scope="col"><?= html_escape($group['name']); ?></th><?php endforeach; ?>
                <th scope="col">Overall</th><th scope="col">Forms</th></tr></thead><tbody>
                <?php foreach ($summary as $row): ?><tr <?= $selected['id'] === $row['id'] ? 'aria-current="true"' : ''; ?>>
                    <td><a href="<?= $summary_url($row['id']); ?>"><?= html_escape($row['name']); ?></a></td>
                    <?php foreach ($row['areas'] as $area): ?><td><?= $score_cell($area); ?></td><?php endforeach; ?>
                    <td><?= $score_cell($row['overall']); ?></td>
                    <td class="ms-num"><?= number_format($row['assessed']); ?><?php if ($row['in_progress']): ?><br><small>+<?= number_format($row['in_progress']); ?> in progress</small><?php endif; ?></td>
                </tr><?php endforeach; ?>
            </tbody></table></div>
        </div></section>
    <?php endif; ?>
</div>
<?php if ($selected): ?>
<script src="<?= base_url('assets/libs/chart-js/Chart.bundle.min.js'); ?>"></script>
<script src="<?= base_url('assets/libs/jszip/jszip.min.js'); ?>"></script>
<script src="<?= base_url('assets/libs/pdfmake/pdfmake.min.js'); ?>"></script>
<script src="<?= base_url('assets/libs/pdfmake/vfs_fonts.js'); ?>"></script>
<script src="<?= base_url('assets/js/chart-exports.js'); ?>?v=<?= filemtime(FCPATH . 'assets/js/chart-exports.js'); ?>"></script>
<?php endif; ?>
<script>
(function () {
    var filters = document.getElementById('ms-filters');
    filters.addEventListener('change', function () { filters.submit(); });
    var report = <?= json_encode($selected, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    if (!report) return;
    var sy = <?= json_encode($selected_sy, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    var toolName = <?= json_encode($tool_name, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    var chart = null;

    function wrapLabel(name) {
        var lines = [], line = '';
        name.split(' ').forEach(function (word) {
            if (line && (line + ' ' + word).length > 24) { lines.push(line); line = word; }
            else { line = line ? line + ' ' + word : word; }
        });
        lines.push(line);
        return lines;
    }

    var canvas = document.getElementById('ms-domain-chart');
    if (canvas) {
        var values = report.areas.map(function (area) { return area.percent; });
        Chart.defaults.global.defaultFontColor = '#78616a';
        Chart.defaults.global.defaultFontFamily = getComputedStyle(document.body).fontFamily;
        chart = new Chart(canvas.getContext('2d'), {
            type: 'horizontalBar',
            data: {
                labels: report.areas.map(function (area) { return wrapLabel(area.name); }),
                datasets: [{ label: 'Rating (%)', data: values, backgroundColor: 'rgba(128,0,32,.78)', hoverBackgroundColor: 'rgba(128,0,32,.95)', borderWidth: 0 }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { display: false },
                tooltips: { callbacks: {
                    title: function (items) { return report.areas[items[0].index].name; },
                    label: function (item) {
                        var area = report.areas[item.index];
                        return area.percent === null ? 'No rating' : area.score + ' — ' + area.description + ' (' + area.evident + ' of ' + area.total + ' indicators evident)';
                    }
                } },
                layout: { padding: { right: 12 } },
                scales: {
                    xAxes: [{ ticks: { beginAtZero: true, max: 100, stepSize: 20, callback: function (value) { return value + '%'; } }, gridLines: { color: '#f0e5e9', drawBorder: false, zeroLineColor: '#f0e5e9' } }],
                    yAxes: [{ gridLines: { display: false }, barPercentage: .72, categoryPercentage: .86 }]
                }
            },
            // Print each value on its bar so downloaded copies stay readable.
            plugins: [{ afterDatasetsDraw: function (instance) {
                var ctx = instance.ctx;
                ctx.save();
                ctx.font = '600 12px ' + Chart.defaults.global.defaultFontFamily;
                ctx.textBaseline = 'middle';
                instance.getDatasetMeta(0).data.forEach(function (bar, index) {
                    if (values[index] === null) return;
                    var inside = values[index] > 88;
                    ctx.textAlign = inside ? 'right' : 'left';
                    ctx.fillStyle = inside ? '#fff' : '#3b0718';
                    ctx.fillText(values[index].toFixed(1) + '%', bar._model.x + (inside ? -8 : 8), bar._model.y);
                });
                ctx.restore();
            } }]
        });
        addChartExports(chart, 'Monitoring Summary by Domain', report.name + ' SY ' + sy, [['Domain', 'Rating (%)', 'Description', 'Indicators evident']].concat(report.areas.map(function (area) {
            return [area.name, area.percent, area.description, area.evident + ' of ' + area.total];
        })));
    }

    function wordBlocks() {
        var blocks = [
            { title: 'Summary of Monitoring' },
            { label: 'Schools Division Office', text: report.name },
            { label: 'School Year', text: sy },
            { label: 'Monitoring Tool', text: toolName },
            { label: 'Date Generated', text: new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) },
            { heading: 'Overview' },
            { label: 'Fully assessed forms', text: report.assessed + ' (from ' + report.school_count + (report.school_count === 1 ? ' school)' : ' schools)') },
            { label: 'Forms in progress', text: report.in_progress + (report.in_progress ? ' (not included in the ratings)' : '') }
        ];
        if (report.assessed) {
            blocks.push({ label: 'Overall rating', text: report.overall.score + ' — ' + report.overall.description });
            blocks.push({ heading: 'Results by Domain' });
            if (chart) blocks.push({ chart: chart });
            blocks.push({ table: [['Domain', 'Rating', 'Description', 'Indicators evident']].concat(report.areas.map(function (area) {
                return [area.name, area.score, area.description, area.evident + ' of ' + area.total];
            })), widths: [42, 12, 28, 18] });
            blocks.push({ text: 'Rating = Evident indicators ÷ all indicators × 100, using fully assessed forms only.' });
        }
        blocks.push({ heading: 'Schools Monitored' });
        blocks.push({ table: [['School', 'District', 'Monitoring date', 'Rating', 'Description']].concat(report.forms.map(function (form) {
            return [form.school_name + '\n' + form.school_id, form.district_name, form.monitor_date || '—', form.complete ? form.overall.score : '—',
                form.complete ? form.overall.description : 'In progress (' + form.overall.answered + ' of ' + form.overall.total + ' indicators assessed)'];
        })), widths: [34, 18, 14, 10, 24] });
        var fields = [['strengths', 'Strengths / Best Practices'], ['technical_assistance', 'Technical Assistance Needed'], ['recommendations', 'Recommendations']];
        var remarks = report.forms.filter(function (form) {
            return fields.some(function (field) { return form[field[0]] !== ''; });
        });
        if (remarks.length) {
            blocks.push({ heading: 'Remarks and Recommendations' });
            remarks.forEach(function (form) {
                blocks.push({ subheading: form.school_name + ' · ' + form.district_name + (form.complete ? '' : ' (in progress)') });
                fields.forEach(function (field) {
                    if (form[field[0]] !== '') blocks.push({ label: field[1], text: form[field[0]] });
                });
            });
        }
        return blocks;
    }

    var download = document.getElementById('ms-download');
    if (download) {
        download.addEventListener('click', async function () {
            var status = document.getElementById('ms-download-status');
            download.disabled = true; status.textContent = 'Preparing Word file…';
            try {
                await downloadWordReport(('summary-of-monitoring-' + report.name + '-sy-' + sy).toLowerCase().replace(/[^a-z0-9]+/g, '-'), wordBlocks());
                status.textContent = 'Word file ready.';
            } catch (error) { status.textContent = 'Download failed. Please reload and try again.'; console.error(error); }
            finally { download.disabled = false; }
        });
    }
}());
</script>
