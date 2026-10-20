<?php
/**
 * Submitted Learning Gap Records. Schools see their own encoded records;
 * division and regional accounts see everything inside their scope.
 */
$competency_items = function ($competencies) {
    return array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) $competencies))));
};
$filters = array(
    'grade_level' => array('label' => 'Grade Level', 'value' => $grade_filter, 'all' => 'All grades'),
    'learning_area' => array('label' => 'Learning Area', 'value' => $learning_area_filter, 'all' => 'All learning areas'),
    'term' => array('label' => 'Trimester', 'value' => $term_filter, 'all' => 'All trimesters'),
);
$status_styles = array(
    'Planned' => 'planned',
    'Ongoing' => 'ongoing',
    'Completed' => 'completed',
    'For monitoring' => 'monitoring',
);
$records_path = base_url('Pages/learning_gap_records');
$records_url = $has_year_filter ? $records_path . '?year=' . (int) $record_year : $records_path;

/* Totals describe the rows actually on screen, so they stay consistent with
   whatever filters are applied. */
$record_count = count($records);
$learners_assessed = 0;
$learners_with_gap = 0;
$school_ids = array();
$area_pairs = array();
foreach ($records as $row) {
    $learners_assessed += (int) $row->learners_assessed;
    $learners_with_gap += (int) $row->learners_with_gap;
    $school_ids[(string) $row->school_id] = true;
    $area_pairs[$row->grade_level . '|' . $row->learning_area] = true;
}
$gap_rate = $learners_assessed > 0 ? ($learners_with_gap / $learners_assessed) * 100 : 0;
/* A school only ever sees its own rows, so a school count would always read 1.
   Its second tile counts the grade and learning area pairs it has covered. */
$breadth_count = $is_school ? count($area_pairs) : count($school_ids);
?>
<link rel="stylesheet" href="<?= base_url('assets/css/records-directory.css'); ?>?v=<?= filemtime(FCPATH . 'assets/css/records-directory.css'); ?>">

<div class="rec-page">
    <header class="rec-header">
        <div>
            <span class="rec-eyebrow">Learning evidence · Record directory</span>
            <h1><?= $is_school ? 'My Learning Gap Records' : 'Submitted Learning Gap Records'; ?></h1>
            <p class="rec-header-meta">
                <span>Review assessment results and the actions taken against them</span>
                <span class="rec-num">Fiscal Year <?= (int) $record_year; ?></span>
                <?php if (!$is_school) : ?><span class="rec-num"><?= number_format($breadth_count); ?> school<?= $breadth_count === 1 ? '' : 's'; ?></span><?php endif; ?>
            </p>
        </div>
        <div class="rec-header-actions">
            <a class="btn btn-outline-light" href="<?= base_url('Pages/learning_gap'); ?>"><i class="mdi mdi-chart-bar" aria-hidden="true"></i> Learning gap summary</a>
            <a class="btn btn-outline-light" href="<?= base_url('Pages/learning_gap_archives'); ?>"><i class="mdi mdi-archive-outline" aria-hidden="true"></i> Yearly archives</a>
            <?php if ($is_school && !$is_archive_year) : ?>
                <a class="btn btn-light" href="<?= base_url('Pages/learning_gap_entry'); ?>"><i class="mdi mdi-plus" aria-hidden="true"></i> Data entry</a>
            <?php endif; ?>
        </div>
    </header>

    <?php if ($is_archive_year) : ?>
        <div class="alert alert-info d-flex align-items-center" role="status">
            <i class="mdi mdi-lock-outline mr-2" aria-hidden="true"></i>
            <span>You are viewing the read-only archive for Fiscal Year <?= (int) $record_year; ?>.</span>
        </div>
    <?php endif; ?>

    <div class="rec-metrics">
        <div class="rec-metric">
            <span class="rec-metric-label">Records shown</span>
            <strong class="rec-metric-value rec-num"><?= number_format($record_count); ?></strong>
            <small class="rec-metric-note"><?= $has_record_filters ? 'Matching your filters' : 'All records in your scope'; ?></small>
        </div>
        <div class="rec-metric">
            <span class="rec-metric-label"><?= $is_school ? 'Grade and area pairs' : 'Reporting schools'; ?></span>
            <strong class="rec-metric-value rec-num"><?= number_format($breadth_count); ?></strong>
            <small class="rec-metric-note"><?= $is_school ? 'Distinct submissions on file' : 'Schools represented below'; ?></small>
        </div>
        <div class="rec-metric">
            <span class="rec-metric-label">Learners assessed</span>
            <strong class="rec-metric-value rec-num"><?= number_format($learners_assessed); ?></strong>
            <small class="rec-metric-note">Sum across the records shown</small>
        </div>
        <div class="rec-metric rec-metric-accent">
            <span class="rec-metric-label">Learners with gap</span>
            <strong class="rec-metric-value rec-num"><?= number_format($learners_with_gap); ?></strong>
            <small class="rec-metric-note"><?= $learners_assessed > 0 ? number_format($gap_rate, 1) . '% of those assessed' : 'No assessed learners recorded'; ?></small>
        </div>
    </div>

    <div class="rec-filter-toolbar mb-3">
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#recordFiltersModal"><i class="mdi mdi-filter-outline" aria-hidden="true"></i> Filters<?= $has_record_filters ? ' (Active)' : ''; ?></button>
        <?php if ($has_record_filters): ?><a class="btn btn-outline-secondary" href="<?= $records_url; ?>">Clear All Filters</a><?php endif; ?>
            <?php if ($has_record_filters) : ?>
                <div class="rec-active">
                    <span class="rec-active-label">Active filters</span>
                    <?php if ($division_filter > 0) : ?><span class="rec-active-chip">Division: <?= html_escape($division_options[$division_filter] ?? (string) $division_filter); ?></span><?php endif; ?>
                    <?php if ($district_filter > 0): ?><span class="rec-active-chip">District: <?= html_escape($district_options[$district_filter] ?? (string) $district_filter); ?></span><?php endif; ?>
                    <?php if ($proficiency_filter !== ''): ?><span class="rec-active-chip">Proficiency: <?= html_escape($proficiency_filter); ?></span><?php endif; ?>
                    <?php foreach ($filters as $filter) : ?>
                        <?php if ($filter['value'] !== '') : ?>
                            <span class="rec-active-chip"><?= html_escape($filter['label'] . ': ' . $filter['value']); ?></span>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <?php if ($competency_filter !== '') : ?>
                        <span class="rec-active-chip">Competency: <?= html_escape($competency_filter); ?></span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
    </div>

    <div class="modal fade" id="recordFiltersModal" tabindex="-1" role="dialog" aria-labelledby="recordFiltersTitle" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title h5" id="recordFiltersTitle">Filter Records</h2>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted">Filters apply within your assigned scope.</p>
                    <div class="mb-3" role="group" aria-label="Quick filter choices">
                        <button type="button" class="btn btn-sm btn-outline-primary mb-1" data-record-filter="all">All Filters</button>
                        <button type="button" class="btn btn-sm btn-outline-primary mb-1" data-record-filter="division">By Division</button>
                        <button type="button" class="btn btn-sm btn-outline-primary mb-1" data-record-filter="district">By Division and District</button>
                        <button type="button" class="btn btn-sm btn-outline-primary mb-1" data-record-filter="area">By Division and Learning Area</button>
                    </div>
                    <p id="record-filter-hint" class="text-muted" role="status">Choose any combination of filters, then select Apply Filters.</p>
                    <form id="recordFiltersForm" method="get" action="<?= $records_path; ?>">
                        <input type="hidden" name="filter_mode" id="record-filter-mode" value="all">
                        <?php if ($has_year_filter): ?><input type="hidden" name="year" value="<?= (int) $record_year; ?>"><?php endif; ?>
                        <div class="rec-modal-filter-grid">
                <div class="rec-field"><label for="proficiency-filter">Proficiency Level</label><select id="proficiency-filter" name="proficiency_level" class="custom-select"><option value="">All proficiency levels</option>
                    <?php foreach ($proficiency_options as $level): ?><option value="<?= html_escape($level); ?>" <?= $proficiency_filter === $level ? 'selected' : ''; ?>><?= html_escape($level); ?></option><?php endforeach; ?>
                </select></div>
                <?php foreach (array('division' => $division_options, 'district' => $district_options) as $kind => $options): $selected = $kind === 'division' ? $division_filter : $district_filter; ?>
                    <div class="rec-field"><label for="proficiency-<?= $kind; ?>"><?= ucfirst($kind); ?></label><select id="proficiency-<?= $kind; ?>" name="<?= $kind; ?>_id" class="custom-select"><option value="">All <?= $kind; ?>s in scope</option>
                    <?php foreach ($options as $id => $label): ?><option value="<?= (int) $id; ?>" <?= $selected === (int) $id ? 'selected' : ''; ?>><?= html_escape($label); ?></option><?php endforeach; ?>
                    </select></div>
                <?php endforeach; ?>
                <?php foreach ($filters as $field => $filter) : ?>
                    <?php
                    $options = isset($record_filter_options[$field]) ? $record_filter_options[$field] : array();
                    if ($filter['value'] !== '' && !in_array($filter['value'], $options, true)) {
                        $options[] = $filter['value'];
                    }
                    ?>
                    <div class="rec-field">
                        <label for="filter-<?= $field; ?>"><?= $filter['label']; ?></label>
                        <select id="filter-<?= $field; ?>" name="<?= $field; ?>" class="custom-select">
                            <option value=""><?= $filter['all']; ?></option>
                            <?php foreach ($options as $option) : ?>
                                <option value="<?= html_escape($option); ?>" <?= $filter['value'] === $option ? 'selected' : ''; ?>><?= html_escape($option); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endforeach; ?>
                <div class="rec-field">
                    <label for="filter-competency">Exact Competency</label>
                    <input id="filter-competency" name="competency" class="form-control" value="<?= html_escape($competency_filter); ?>" placeholder="Enter full competency text">
                </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <a class="btn btn-outline-secondary mr-auto" href="<?= $records_url; ?>">Clear All Filters</a>
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button type="submit" form="recordFiltersForm" class="btn btn-primary">Apply Filters</button>
                </div>
            </div>
        </div>
    </div>

    <section class="rec-card" aria-labelledby="rec-list-title">
        <div class="rec-card-head">
            <h2 id="rec-list-title"><?= $is_school ? 'My encoded records' : 'Submitted records'; ?></h2>
            <span class="rec-count rec-num"><?= number_format($record_count); ?></span>
            <p><?= $has_record_filters ? 'Showing records matching your filters.' : 'Showing all records within your assigned scope.'; ?> Expand a record to review its competencies and intervention details.</p>
        </div>

        <?php if (empty($records)) : ?>
            <div class="rec-empty">
                <i class="mdi mdi-file-search-outline" aria-hidden="true"></i>
                <h3><?= $has_record_filters ? 'No matching records' : 'No records yet'; ?></h3>
                <p><?= $has_record_filters ? 'Try another grade, learning area, or trimester, or clear your filters.' : 'Learning gap records will appear here once they have been encoded.'; ?></p>
                <?php if ($has_record_filters) : ?>
                    <a class="btn btn-outline-primary" href="<?= $records_url; ?>">Clear filters</a>
                <?php elseif ($is_school && !$is_archive_year) : ?>
                    <a class="btn btn-primary" href="<?= base_url('Pages/learning_gap_entry'); ?>">Create a record</a>
                <?php endif; ?>
            </div>
        <?php else : ?>
            <ul class="rec-list">
                <?php foreach ($records as $row) : ?>
                    <?php
                    $status = $row->intervention_status ?: 'Not set';
                    $status_class = isset($status_styles[$status]) ? $status_styles[$status] : 'unset';
                    $assessed = (int) $row->learners_assessed;
                    $with_gap = (int) $row->learners_with_gap;
                    $competencies = $competency_items($row->least_learned_competency);
                    $context = ($is_school ? '' : $row->schoolName . ', ') . $row->grade_level . ', ' . $row->learning_area . ', ' . $row->term;
                    ?>
                    <li class="rec-item">
                        <div class="rec-item-main">
                            <div class="rec-identity">
                                <?php if (!$is_school) : ?>
                                    <strong><?= html_escape($row->schoolName); ?></strong>
                                    <span><?= html_escape($row->division_name); ?> · <?= html_escape($row->grade_level); ?> · <?= html_escape($row->learning_area); ?> · <?= html_escape($row->term); ?></span>
                                <?php else : ?>
                                    <strong><?= html_escape($row->grade_level); ?> · <?= html_escape($row->learning_area); ?></strong>
                                    <span><?= html_escape($row->term); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="rec-stat">
                                <span class="rec-stat-label">Proficiency</span>
                                <span class="rec-stat-value rec-num"><?= isset($row->class_proficiency_level) ? number_format((float) $row->class_proficiency_level, 2) . '%' : '—'; ?></span>
                                <span class="rec-stat-note"><?= isset($row->proficiency_level) ? html_escape($row->proficiency_level) : 'Not set'; ?></span>
                                <?php if (isset($row->cpl_summative_1) || isset($row->cpl_summative_2)) : ?>
                                    <span class="rec-stat-note rec-num">S1 <?= isset($row->cpl_summative_1) ? number_format((float) $row->cpl_summative_1, 2) . '%' : '—'; ?> · S2 <?= isset($row->cpl_summative_2) ? number_format((float) $row->cpl_summative_2, 2) . '%' : '—'; ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="rec-stat">
                                <span class="rec-stat-label">Assessed</span>
                                <span class="rec-stat-value rec-num"><?= number_format($assessed); ?></span>
                            </div>
                            <div class="rec-stat rec-stat-gap">
                                <span class="rec-stat-label">With gap</span>
                                <span class="rec-stat-value rec-num"><?= number_format($with_gap); ?></span>
                                <span class="rec-stat-note rec-num"><?= number_format((float) $row->percent_not_meeting, 1); ?>% not meeting</span>
                            </div>
                            <span class="rec-status <?= $status_class; ?>"><?= html_escape($status); ?></span>
                        </div>

                        <details class="rec-detail">
                            <summary>Competencies &amp; intervention details<span class="sr-only"> for <?= html_escape($context); ?></span></summary>
                            <div class="rec-detail-grid">
                                <div class="rec-competencies">
                                    <h3>Least learned competencies</h3>
                                    <?php if (empty($competencies)) : ?>
                                        <p>—</p>
                                    <?php else : ?>
                                        <ul class="rec-competency-list">
                                            <?php foreach ($competencies as $competency) : ?>
                                                <li><?= html_escape($competency); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </div>
                                <?php
                                $summative_values = array();
                                if (isset($row->cpl_summative_1) && $row->cpl_summative_1 !== null) {
                                    $summative_values['Summative 1'] = (float) $row->cpl_summative_1;
                                }
                                if (isset($row->cpl_summative_2) && $row->cpl_summative_2 !== null) {
                                    $summative_values['Summative 2'] = (float) $row->cpl_summative_2;
                                }
                                ?>
                                <?php if (!empty($summative_values)) : ?>
                                    <div>
                                        <h3>Summative CPL</h3>
                                        <p>
                                            <?php foreach ($summative_values as $summative_label => $summative_value) : ?>
                                                <?= html_escape($summative_label); ?>: <strong><?= number_format($summative_value, 2); ?>%</strong><br>
                                            <?php endforeach; ?>
                                            Term average: <strong><?= number_format(array_sum($summative_values) / count($summative_values), 2); ?>%</strong>
                                        </p>
                                    </div>
                                <?php endif; ?>
                                <?php foreach (array('intervention_action' => 'Intervention / Action', 'remarks' => 'Remarks') as $field => $label) : ?>
                                    <div>
                                        <h3><?= $label; ?></h3>
                                        <p><?= trim((string) $row->$field) !== '' ? nl2br(html_escape($row->$field)) : '—'; ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <?php if ($is_school && !$is_archive_year) : ?>
                                <div class="rec-actions">
                                    <a class="btn btn-sm btn-outline-primary" href="<?= base_url('Pages/learning_gap_entry?edit=' . (int) $row->id); ?>"><i class="mdi mdi-pencil" aria-hidden="true"></i> Edit record</a>
                                    <?= form_open('Pages/learning_gap_delete', array('onsubmit' => "return confirm('Remove this record?');")); ?>
                                        <input type="hidden" name="id" value="<?= (int) $row->id; ?>">
                                        <button class="btn btn-sm btn-outline-danger" type="submit"><i class="mdi mdi-delete-outline" aria-hidden="true"></i> Delete</button>
                                    <?= form_close(); ?>
                                </div>
                            <?php endif; ?>
                        </details>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
</div>

<script>
(function () {
    var division = document.getElementById('proficiency-division');
    var district = document.getElementById('proficiency-district');
    var area = document.getElementById('filter-learning_area');
    var districtDivisions = <?= json_encode($district_divisions, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    var hint = document.getElementById('record-filter-hint');
    function updateDistricts() {
        Array.prototype.forEach.call(district.options, function (option) {
            var excluded = option.value !== '' && division.value !== '' && String(districtDivisions[option.value]) !== division.value;
            option.hidden = excluded;
            option.disabled = excluded;
            if (excluded && option.selected) district.value = '';
        });
    }
    division.addEventListener('change', updateDistricts);
    updateDistricts();
    var fieldsByMode = {
        all: ['division_id', 'district_id', 'learning_area', 'proficiency_level', 'grade_level', 'term', 'competency'],
        division: ['division_id'],
        district: ['division_id', 'district_id'],
        area: ['division_id', 'learning_area']
    };
    var buttons = document.querySelectorAll('[data-record-filter]');
    function setMode(mode, clearHidden) {
        if (!Object.prototype.hasOwnProperty.call(fieldsByMode, mode)) mode = 'all';
        document.getElementById('record-filter-mode').value = mode;
        Array.prototype.forEach.call(document.querySelectorAll('#recordFiltersForm .rec-field'), function (field) {
            var input = field.querySelector('select, input');
            var visible = fieldsByMode[mode].indexOf(input.name) !== -1;
            field.hidden = !visible;
            input.disabled = !visible;
            if (!visible && clearHidden) input.value = '';
        });
        Array.prototype.forEach.call(buttons, function (button) {
            var active = button.getAttribute('data-record-filter') === mode;
            button.classList.toggle('active', active);
            button.setAttribute('aria-pressed', active ? 'true' : 'false');
        });
        hint.textContent = {
            all: 'Choose any combination of filters, then select Apply Filters.',
            division: 'Select a Division, then select Apply Filters.',
            district: 'Select a Division and a District, then select Apply Filters.',
            area: 'Select a Division and Learning Area, then select Apply Filters.'
        }[mode];
        updateDistricts();
    }
    var initialMode = new URLSearchParams(window.location.search).get('filter_mode') || 'all';
    // Keep externally linked filters visible if they do not fit the requested mode.
    if (Object.prototype.hasOwnProperty.call(fieldsByMode, initialMode)) {
        Array.prototype.forEach.call(document.querySelectorAll('#recordFiltersForm .rec-field select, #recordFiltersForm .rec-field input'), function (input) {
            if (input.value && fieldsByMode[initialMode].indexOf(input.name) === -1) initialMode = 'all';
        });
    }
    setMode(initialMode, false);
    Array.prototype.forEach.call(buttons, function (button) {
        button.addEventListener('click', function () {
            setMode(button.getAttribute('data-record-filter'), true);
        });
    });
}());
</script>
