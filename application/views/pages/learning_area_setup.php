<?php
/**
 * Regional Learning Area Setup. Defines which learning areas a division's
 * schools can encode against, per grade level.
 */
$grades = array(
    'Kindergarten', 'Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5', 'Grade 6',
    'Grade 7', 'Grade 8', 'Grade 9', 'Grade 10', 'Grade 11', 'Grade 12',
);

/* The query orders grade_level alphabetically, which puts "Grade 10" ahead of
   "Grade 2". Group the rows and walk them in curriculum order instead. */
$areas_by_grade = array();
foreach ($areas as $area) {
    $areas_by_grade[(string) $area->grade_level][] = $area;
}
$ordered_grades = array();
foreach ($grades as $grade) {
    if (isset($areas_by_grade[$grade])) {
        $ordered_grades[] = $grade;
    }
}
foreach (array_keys($areas_by_grade) as $grade) {
    if (!in_array($grade, $ordered_grades, true)) {
        $ordered_grades[] = $grade;
    }
}

$area_total = count($areas);
$grade_total = count($ordered_grades);
$counts = isset($competency_counts) ? $competency_counts : array();
$areas_without_competencies = 0;
foreach ($areas as $area) {
    $key = $area->grade_level . '|' . $area->learning_area;
    if (empty($counts[$key]['competency_count'])) {
        $areas_without_competencies++;
    }
}

$selected_division_name = '';
foreach ($divisions as $division) {
    if ((int) $division->id === (int) $selected_division_id) {
        $selected_division_name = (string) $division->description;
        break;
    }
}
$setup_url = base_url('Pages/learning_area_setup');
?>
<link rel="stylesheet" href="<?= base_url('assets/css/learning-area-setup.css'); ?>?v=<?= filemtime(FCPATH . 'assets/css/learning-area-setup.css'); ?>">

<div class="la-page">
    <header class="la-header">
        <div>
            <span class="la-eyebrow">Regional configuration</span>
            <h1>Learning Area Setup</h1>
            <p class="la-header-meta">
                <?php if ($selected_division_name !== '') : ?><span><?= html_escape($selected_division_name); ?></span><?php endif; ?>
                <span><?= number_format($area_total); ?> learning area<?= $area_total === 1 ? '' : 's'; ?></span>
                <span><?= number_format($grade_total); ?> grade level<?= $grade_total === 1 ? '' : 's'; ?></span>
            </p>
        </div>
    </header>

    <form class="la-switcher" method="get" action="<?= $setup_url; ?>">
        <div class="la-switcher-field">
            <label for="division_id">Division</label>
            <select id="division_id" name="division_id" class="custom-select" onchange="this.form.submit()">
                <?php foreach ($divisions as $division) : ?>
                    <option value="<?= (int) $division->id; ?>" <?= (int) $selected_division_id === (int) $division->id ? 'selected' : ''; ?>><?= html_escape($division->description); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button class="btn btn-outline-primary" type="submit">View division</button>
        <p class="la-switcher-note">Learning areas are configured per division. Competencies encoded against an area are shared across the region.</p>
    </form>

    <?php if ($this->session->flashdata('success')) : ?>
        <div class="la-flash la-flash-success" role="status"><i class="mdi mdi-check-circle-outline" aria-hidden="true"></i><span><?= $this->session->flashdata('success'); ?></span></div>
    <?php endif; ?>
    <?php if ($this->session->flashdata('danger')) : ?>
        <div class="la-flash la-flash-danger" role="alert"><i class="mdi mdi-alert-circle-outline" aria-hidden="true"></i><span><?= $this->session->flashdata('danger'); ?></span></div>
    <?php endif; ?>

    <section class="la-card" aria-labelledby="la-add-title">
        <div class="la-card-head">
            <h2 id="la-add-title">Add a learning area</h2>
            <p>Schools in this division can only encode learning-gap records against the grade level and learning area combinations listed below.</p>
        </div>
        <?= form_open('Pages/learning_area_setup_save', array('class' => 'la-form')); ?>
            <input type="hidden" name="division_id" value="<?= (int) $selected_division_id; ?>">
            <div class="la-form-grid">
                <div class="la-field">
                    <label for="grade_level">Grade level</label>
                    <select id="grade_level" name="grade_level" class="custom-select" required>
                        <option value="">Select grade</option>
                        <?php foreach ($grades as $grade) : ?>
                            <option><?= $grade; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="la-field">
                    <label for="learning_area">Learning area / subject</label>
                    <input id="learning_area" name="learning_area" class="form-control" placeholder="e.g. Mathematics" maxlength="150" required>
                </div>
                <button class="btn btn-primary" type="submit"><i class="mdi mdi-plus" aria-hidden="true"></i> Add to setup</button>
            </div>
        <?= form_close(); ?>
    </section>

    <section class="la-card" aria-labelledby="la-list-title">
        <div class="la-card-head">
            <h2 id="la-list-title">Configured learning areas</h2>
            <span class="la-count"><?= number_format($area_total); ?> configured</span>
            <p>
                <?php if ($area_total === 0) : ?>
                    Nothing is configured for this division yet.
                <?php elseif ($areas_without_competencies > 0) : ?>
                    <?= number_format($areas_without_competencies); ?> of <?= number_format($area_total); ?> area<?= $area_total === 1 ? '' : 's'; ?> have no competencies encoded yet, so schools cannot select a competency for them.
                <?php else : ?>
                    Every configured area has competencies encoded and is ready for school data entry.
                <?php endif; ?>
            </p>
        </div>

        <?php if ($area_total === 0) : ?>
            <div class="la-empty">
                <i class="mdi mdi-book-open-outline" aria-hidden="true"></i>
                <h3>No learning areas set up yet</h3>
                <p>Add a grade level and learning area above to enable school data entry for this division.</p>
            </div>
        <?php else : ?>
            <?php foreach ($ordered_grades as $grade) : ?>
                <?php $grade_areas = $areas_by_grade[$grade]; ?>
                <div class="la-group">
                    <div class="la-group-head">
                        <h3><?= html_escape($grade); ?></h3>
                        <span class="la-group-count"><?= number_format(count($grade_areas)); ?> learning area<?= count($grade_areas) === 1 ? '' : 's'; ?></span>
                    </div>
                    <ul class="la-items">
                        <?php foreach ($grade_areas as $area) : ?>
                            <?php
                            $key = $area->grade_level . '|' . $area->learning_area;
                            $competency_count = isset($counts[$key]['competency_count']) ? (int) $counts[$key]['competency_count'] : 0;
                            $term_count = isset($counts[$key]['term_count']) ? (int) $counts[$key]['term_count'] : 0;
                            $query = '?division_id=' . (int) $selected_division_id;
                            ?>
                            <li class="la-item">
                                <div class="la-item-main">
                                    <span class="la-item-name"><?= html_escape($area->learning_area); ?></span>
                                    <span class="la-item-meta">
                                        <?php if ($competency_count > 0) : ?>
                                            <?= number_format($competency_count); ?> competenc<?= $competency_count === 1 ? 'y' : 'ies'; ?> across <?= number_format($term_count); ?> term<?= $term_count === 1 ? '' : 's'; ?>
                                        <?php else : ?>
                                            Schools cannot select a competency until one is encoded
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <div class="la-item-actions">
                                    <span class="la-chip<?= $competency_count > 0 ? '' : ' la-chip-empty'; ?>">
                                        <?= $competency_count > 0 ? number_format($competency_count) . ' encoded' : 'None encoded'; ?>
                                    </span>
                                    <a class="la-action" href="<?= base_url('Pages/learning_competencies_view/' . (int) $area->id . $query); ?>"><i class="mdi mdi-eye-outline" aria-hidden="true"></i> View</a>
                                    <a class="la-action la-action-primary" href="<?= base_url('Pages/learning_competency_setup/' . (int) $area->id . $query); ?>"><i class="mdi mdi-plus-box-outline" aria-hidden="true"></i> Competencies</a>
                                    <?= form_open('Pages/learning_area_setup_delete', array('class' => 'la-remove-form', 'onsubmit' => "return confirm('Remove " . html_escape($area->grade_level . ' — ' . $area->learning_area) . " from this division setup?');")); ?>
                                        <input type="hidden" name="id" value="<?= (int) $area->id; ?>">
                                        <input type="hidden" name="division_id" value="<?= (int) $selected_division_id; ?>">
                                        <button type="submit" class="la-remove" aria-label="Remove <?= html_escape($area->grade_level . ' ' . $area->learning_area); ?>"><i class="mdi mdi-delete-outline" aria-hidden="true"></i></button>
                                    <?= form_close(); ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
</div>
