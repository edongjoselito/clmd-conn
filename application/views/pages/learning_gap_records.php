<?php
/**
 * Submitted Learning Gap Records. Schools see their own encoded records;
 * division and regional accounts see everything inside their scope.
 */
$competency_items = function ($competencies) {
    return array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) $competencies))));
};
$filters = array(
    'grade_level' => array('label' => 'Grade level', 'value' => $grade_filter, 'all' => 'All grades'),
    'learning_area' => array('label' => 'Learning area', 'value' => $learning_area_filter, 'all' => 'All learning areas'),
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

    <section class="rec-card" aria-labelledby="rec-filters-title">
        <div class="rec-card-head">
            <h2 id="rec-filters-title">Find records</h2>
            <p>Filters apply within your assigned scope.</p>
        </div>
        <form class="rec-filter" method="get" action="<?= $records_url; ?>">
            <?php if ($has_year_filter) : ?>
                <input type="hidden" name="year" value="<?= (int) $record_year; ?>">
            <?php endif; ?>
            <?php if ($division_filter > 0) : ?>
                <input type="hidden" name="division_id" value="<?= (int) $division_filter; ?>">
            <?php endif; ?>
            <div class="rec-filter-grid">
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
                    <label for="filter-competency">Exact competency</label>
                    <input id="filter-competency" name="competency" class="form-control" value="<?= html_escape($competency_filter); ?>" placeholder="Enter full competency text">
                </div>
                <div class="rec-filter-actions">
                    <button class="btn btn-primary" type="submit">Apply filters</button>
                    <?php if ($has_record_filters) : ?>
                        <a class="btn btn-outline-secondary" href="<?= $records_url; ?>">Clear all</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php if ($has_record_filters) : ?>
                <div class="rec-active">
                    <span class="rec-active-label">Active filters</span>
                    <?php if ($division_filter > 0) : ?><span class="rec-active-chip">Selected division</span><?php endif; ?>
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
        </form>
    </section>

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
