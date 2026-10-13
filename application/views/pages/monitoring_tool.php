<?php
$filtered_records = array(); $completed = 0;
foreach ($records as $saved) {
    $payload = json_decode($saved->payload, true);
    if ($tool && (isset($payload['tool_id']) ? (int) $payload['tool_id'] !== (int) $tool->id : $tool->seed_key !== 'legacy')) continue;
    if ($selected_sy && $payload['sy'] !== $selected_sy) continue;
    if ($selected_division && (int) $payload['division_id'] !== $selected_division) continue;
    if ($selected_district && (int) $payload['district_id'] !== $selected_district) continue;
    if ($selected_school && (string) $payload['school_id'] !== $selected_school) continue;
    $answered = 0;
    foreach ($payload['items'] as $item) { if ($item['rating'] !== '') $answered++; }
    $total = count($payload['items']);
    $complete = $total > 0 && $answered === $total;
    if ($complete) $completed++;
    $filtered_records[] = array('record' => $saved, 'payload' => $payload, 'answered' => $answered, 'total' => $total, 'complete' => $complete);
}
$ready = $selected_sy && $selected_school && $indicators;
?>
<link rel="stylesheet" href="<?= base_url('assets/css/monitoring-indicators.css'); ?>?v=<?= filemtime(FCPATH . 'assets/css/monitoring-indicators.css'); ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/monitoring-tool.css'); ?>?v=<?= filemtime(FCPATH . 'assets/css/monitoring-tool.css'); ?>">
<div class="mi-page mt-tool">
    <p><a href="<?= base_url('monitoring/tool'); ?>">&larr; All Monitoring Tools</a></p>
    <header class="mi-header">
        <div><small>MONITORING / TOOL</small><h1><?= $tool ? html_escape($tool->name) : 'Monitoring Tools'; ?></h1><p>Create school assessments, track progress, and prepare monitoring reports.</p><div class="mt-header-tags"><span><?= count($indicators); ?> active indicators</span><span><?= count($groups); ?> areas of concern</span></div></div>
        <a href="<?= base_url('monitoring-tool/settings/tools'); ?>" class="btn btn-light"><i class="mdi mdi-tune" aria-hidden="true"></i> Manage tools</a>
    </header>
    <div class="mt-stats" aria-label="Assessment counts for selected filters">
        <div><span class="mt-stat-icon"><i class="mdi mdi-clipboard-text-outline" aria-hidden="true"></i></span><div><strong><?= count($filtered_records); ?></strong><span>Assessments</span></div></div>
        <div><span class="mt-stat-icon"><i class="mdi mdi-pencil-outline" aria-hidden="true"></i></span><div><strong><?= count($filtered_records) - $completed; ?></strong><span>In progress</span></div></div>
        <div><span class="mt-stat-icon mt-stat-complete"><i class="mdi mdi-check-circle-outline" aria-hidden="true"></i></span><div><strong><?= $completed; ?></strong><span>Fully assessed</span></div></div>
    </div>
    <section class="card mt-filter-card">
        <div class="card-body">
            <div class="mt-section-heading"><div><span class="mt-eyebrow">START AN ASSESSMENT</span><h2>Select a school</h2><p>Choose the school year and location. Your selections also filter the records below.</p></div><a href="<?= base_url('monitoring/tool?tool_id=' . (int) $tool->id); ?>" class="mt-reset">Reset selection</a></div>
            <form action="<?= base_url('monitoring/tool'); ?>" method="get" id="monitoring-details">
                <input type="hidden" name="tool_id" value="<?= (int) $tool->id; ?>">
                <div class="form-row">
                    <div class="form-group col-md-6 col-xl-3">
                        <label for="monitoring-sy"><span class="mt-step">1</span> School year (SY)</label>
                        <select class="form-control" id="monitoring-sy" name="sy">
                            <option value="">Select school year</option>
                            <?php foreach ($school_years as $sy): ?><option value="<?= html_escape($sy); ?>" <?= $selected_sy === $sy ? 'selected' : ''; ?>><?= html_escape($sy); ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-md-6 col-xl-3">
                        <label for="monitoring-division"><span class="mt-step">2</span> Division</label>
                        <select class="form-control" id="monitoring-division" name="division_id">
                            <option value=""><?= $divisions ? 'Select division' : 'No divisions available'; ?></option>
                            <?php foreach ($divisions as $division): ?><option value="<?= (int) $division->id; ?>" <?= $selected_division === (int) $division->id ? 'selected' : ''; ?>><?= html_escape($division->description); ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-md-6 col-xl-3">
                        <label for="monitoring-district"><span class="mt-step">3</span> District</label>
                        <select class="form-control" id="monitoring-district" name="district_id" <?= !$selected_division || !$districts ? 'disabled' : ''; ?>>
                            <option value=""><?= !$selected_division ? 'Select division first' : ($districts ? 'Select district' : 'No districts available'); ?></option>
                            <?php foreach ($districts as $district): ?><option value="<?= (int) $district->id; ?>" <?= $selected_district === (int) $district->id ? 'selected' : ''; ?>><?= html_escape($district->description); ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-md-6 col-xl-3">
                        <label for="monitoring-school"><span class="mt-step">4</span> School</label>
                        <select class="form-control" id="monitoring-school" name="school_id" <?= !$selected_district || !$schools ? 'disabled' : ''; ?>>
                            <option value=""><?= !$selected_district ? 'Select district first' : ($schools ? 'Select school' : 'No schools available'); ?></option>
                            <?php foreach ($schools as $school): ?><option value="<?= html_escape($school->schoolID); ?>" <?= $selected_school === (string) $school->schoolID ? 'selected' : ''; ?>><?= html_escape($school->schoolName . ' (' . $school->schoolID . ')'); ?></option><?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <noscript><button type="submit" class="btn btn-primary">Update selection</button></noscript>
            </form>
    <div class="mt-create-bar">
        <?php if ($ready): ?><p><strong>Ready to begin</strong><span>Create an assessment for the selected school and school year.</span></p>
            <a class="btn btn-primary" href="<?= base_url('Pages/monitoring_assessment') . '?' . html_escape(http_build_query(array('tool_id' => (int) $tool->id, 'sy' => $selected_sy, 'division_id' => $selected_division, 'district_id' => $selected_district, 'school_id' => $selected_school))); ?>"><i class="mdi mdi-plus" aria-hidden="true"></i> Create assessment</a>
        <?php else: ?><p><strong><?= !$tool ? 'Select a monitoring tool' : (!$indicators ? 'No active indicators' : 'Choose your assessment details'); ?></strong><span><?= !$tool ? 'Choose a tool to use its associated indicators.' : (!$indicators ? 'Add or activate indicators in Settings to begin.' : 'Select all four fields above to create an assessment.'); ?></span></p><button class="btn btn-primary" disabled>Create assessment</button><?php endif; ?>
    </div>
        </div>
    </section>
    <?php foreach (array('success', 'danger') as $type): ?>
        <?php if ($message = $this->session->flashdata($type)): ?><div class="alert alert-<?= $type; ?>" role="status"><?= html_escape($message); ?></div><?php endif; ?>
    <?php endforeach; ?>
    <section class="card mt-records"><div class="card-body">
        <div class="mt-section-heading"><div><h2>Saved assessments <span class="mt-count"><?= count($filtered_records); ?></span></h2><p>Continue an assessment or print your saved monitoring form.</p></div>
        <div class="mt-search" hidden><label class="sr-only" for="assessment-search">Search saved assessments</label><i class="mdi mdi-magnify" aria-hidden="true"></i><input type="search" id="assessment-search" class="form-control" placeholder="Search school, division, or SY…"></div></div>
        <div class="table-responsive"><table class="table table-hover"><thead><tr><th>SY</th><th>Tool / School</th><th>Division / District</th><th>Progress</th><th>Updated</th><th>Actions</th></tr></thead><tbody>
        <?php foreach ($filtered_records as $entry):
            $saved = $entry['record']; $payload = $entry['payload']; $answered = $entry['answered']; $total = $entry['total'];
        ?>
            <tr data-assessment-row><td><span class="mt-year"><?= html_escape($saved->sy); ?></span></td><td><small><?= html_escape(isset($payload['tool_name']) ? $payload['tool_name'] : 'Unified Learning Area Monitoring Tool'); ?></small><br><strong class="mt-school-name"><?= html_escape($payload['school_name']); ?></strong><br><small><?= html_escape($saved->school_id); ?></small></td>
                <td><?= html_escape($payload['division_name']); ?><br><small><?= html_escape($payload['district_name']); ?></small></td>
                <td><span class="mt-status <?= $entry['complete'] ? 'is-complete' : ''; ?>"><?= $entry['complete'] ? 'Fully assessed' : 'In progress'; ?></span><div class="mt-progress" aria-hidden="true"><span style="width:<?= $total ? (int) round(100 * $answered / $total) : 0; ?>%"></span></div><small><?= $answered; ?> of <?= $total; ?> indicators</small></td><td><span class="mt-date"><?= html_escape(date('M j, Y', strtotime($saved->updated_at))); ?></span><small><?= html_escape(date('g:i A', strtotime($saved->updated_at))); ?></small></td>
                <td><div class="mt-actions">
                    <a class="btn btn-sm btn-outline-primary" href="<?= base_url('Pages/monitoring_assessment/' . (int) $saved->id); ?>">View / Edit</a>
                    <a class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener" href="<?= base_url('Pages/monitoring_assessment_print/' . (int) $saved->id); ?>">Print</a>
                    <?= form_open('Pages/monitoring_assessment_delete', array('onsubmit' => "return confirm('Delete this assessment permanently?');")); ?>
                    <input type="hidden" name="id" value="<?= (int) $saved->id; ?>"><input type="hidden" name="version" value="<?= (int) $saved->version; ?>"><button type="submit" class="btn btn-sm btn-outline-danger">Delete</button><?= form_close(); ?>
                </div></td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$filtered_records): ?><tr><td colspan="6"><div class="mt-empty"><i class="mdi mdi-clipboard-text-outline" aria-hidden="true"></i><h3>No assessments found</h3><p>Select a school above to create an assessment, or reset your filters to see more records.</p></div></td></tr><?php endif; ?>
        <tr id="assessment-search-empty" hidden><td colspan="6" class="text-center py-4">No assessments match your search.</td></tr>
        </tbody></table></div><div class="mt-records-foot" id="assessment-result-count" role="status"><?= count($filtered_records); ?> assessments in this selection</div>
    </div></section>
</div>

<script>
(function () {
    var form = document.getElementById('monitoring-details');
    var division = document.getElementById('monitoring-division');
    var district = document.getElementById('monitoring-district');
    var school = document.getElementById('monitoring-school');
    var search = document.getElementById('assessment-search');
    search.parentNode.hidden = false;
    var rows = Array.from(document.querySelectorAll('[data-assessment-row]'));
    search.addEventListener('input', function () {
        var query = search.value.trim().toLowerCase(), shown = 0;
        rows.forEach(function (row) {
            var match = row.textContent.toLowerCase().indexOf(query) !== -1;
            row.hidden = !match;
            if (match) shown++;
        });
        document.getElementById('assessment-search-empty').hidden = shown > 0 || rows.length === 0;
        document.getElementById('assessment-result-count').textContent = shown + ' of ' + rows.length + ' assessments shown';
    });
    form.addEventListener('change' , function (event) {
        if (event.target === division) { district.value = ''; school.value = ''; }
        if (event.target === district) { school.value = ''; }
        form.submit();
    });
}());
</script>
