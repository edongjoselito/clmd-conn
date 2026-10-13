<?php
$is_view_only = !empty($view_only);
$terms = array('Term 1', 'Term 2', 'Term 3');
$loose_term = 'All Terms';

// Entries encoded before term-based filtering sit under "All Terms"; they are
// still usable, so they are listed last rather than hidden.
$grouped = array();
foreach ($competencies as $competency) {
    $term = trim((string) $competency->term);
    if ($term === '') {
        $term = $loose_term;
    }
    $grouped[$term][] = $competency;
}
$ordered_terms = array();
foreach (array_merge($terms, array($loose_term)) as $term) {
    if (!empty($grouped[$term])) {
        $ordered_terms[] = $term;
    }
}
foreach (array_keys($grouped) as $term) {
    if (!in_array($term, $ordered_terms, true)) {
        $ordered_terms[] = $term;
    }
}

// A large share of the catalog was encoded with a leading bullet glyph. The
// list supplies its own structure, so strip it to avoid a doubled marker.
$display_text = function ($text) {
    return trim(preg_replace('/^[\s\x{2022}\x{00B7}\x{25AA}\x{2013}\x{2014}\-\*]+/u', '', (string) $text));
};

$areas_url = base_url('Pages/learning_area_setup?division_id=' . (int) $selected_division_id);
?>
<link rel="stylesheet" href="<?= base_url('assets/css/learning-competencies.css'); ?>?v=<?= filemtime(FCPATH . 'assets/css/learning-competencies.css'); ?>">

<div class="lc-page">
    <header class="lc-header">
        <div>
            <span class="lc-eyebrow">Regional catalog</span>
            <h1>Learning Competencies</h1>
            <p class="lc-header-meta">
                <span><?= html_escape($area->grade_level); ?></span>
                <span><?= html_escape($area->learning_area); ?></span>
                <span>Available to all divisions in this region</span>
            </p>
        </div>
        <a class="btn btn-light" href="<?= $areas_url; ?>"><i class="mdi mdi-arrow-left" aria-hidden="true"></i> Learning areas</a>
    </header>

    <?php if ($this->session->flashdata('success')): ?>
        <div class="lc-flash lc-flash-success" role="status"><i class="mdi mdi-check-circle-outline" aria-hidden="true"></i><span><?= $this->session->flashdata('success'); ?></span></div>
    <?php endif; ?>
    <?php if ($this->session->flashdata('danger')): ?>
        <div class="lc-flash lc-flash-danger" role="alert"><i class="mdi mdi-alert-circle-outline" aria-hidden="true"></i><span><?= $this->session->flashdata('danger'); ?></span></div>
    <?php endif; ?>

    <?php if (!$is_view_only): ?>
        <section class="lc-card" aria-labelledby="lc-add-title">
            <div class="lc-card-head">
                <h2 id="lc-add-title">Add a competency</h2>
                <p>Encoded competencies become selectable by every school in this region for the term you assign.</p>
            </div>
            <?= form_open('Pages/learning_competency_setup_save/' . (int) $area->id, array('class' => 'lc-form')); ?>
                <input type="hidden" name="division_id" value="<?= (int) $selected_division_id; ?>">
                <div class="lc-form-grid">
                    <div class="lc-field">
                        <label for="term">Term</label>
                        <select id="term" name="term" class="custom-select" required>
                            <option value="">Select term</option>
                            <?php foreach ($terms as $term): ?><option value="<?= $term; ?>"><?= $term; ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="lc-field">
                        <label for="competency">Learning competency</label>
                        <textarea id="competency" name="competency" class="form-control" rows="3" maxlength="1000" required placeholder="Type the full competency statement, e.g. Nasusuri ang kalagayang heograpikal ng mga sinaunang kabihasnan sa Asya"></textarea>
                        <span class="lc-hint">Up to 1,000 characters. Enter one competency at a time.</span>
                    </div>
                    <div class="lc-form-actions">
                        <button class="btn btn-primary" type="submit"><i class="mdi mdi-plus" aria-hidden="true"></i> Add competency</button>
                    </div>
                </div>
            <?= form_close(); ?>
        </section>
    <?php endif; ?>

    <section class="lc-card" aria-labelledby="lc-list-title">
        <div class="lc-card-head">
            <h2 id="lc-list-title">Configured competencies</h2>
            <span class="lc-count"><?= number_format(count($competencies)); ?></span>
            <p>
                <?php if (empty($competencies)): ?>
                    Nothing encoded yet for this grade level and learning area.
                <?php else: ?>
                    Grouped by term<?= $is_view_only ? '.' : ' · change a term or remove an entry from its row.'; ?>
                <?php endif; ?>
            </p>
        </div>

        <?php if (empty($competencies)): ?>
            <div class="lc-empty">
                <i class="mdi mdi-playlist-plus" aria-hidden="true"></i>
                <h3>No competencies yet</h3>
                <p><?= $is_view_only
                    ? 'Once competencies are encoded for this learning area, they will be listed here by term.'
                    : 'Add the first competency above. Schools in this region will then be able to select it when they encode learning gaps.'; ?></p>
            </div>
        <?php else: foreach ($ordered_terms as $term): $is_loose = ($term === $loose_term); ?>
            <div class="lc-group">
                <div class="lc-group-head">
                    <h3><?= html_escape($term); ?></h3>
                    <span class="lc-group-count"><?= count($grouped[$term]); ?> <?= count($grouped[$term]) === 1 ? 'competency' : 'competencies'; ?></span>
                    <?php if ($is_loose && !$is_view_only): ?><span class="lc-group-note"><i class="mdi mdi-information-outline" aria-hidden="true"></i> Selectable in every term until a specific term is assigned</span><?php endif; ?>
                </div>
                <ul class="lc-items">
                    <?php foreach ($grouped[$term] as $index => $competency): $text = $display_text($competency->competency); ?>
                        <li class="lc-item">
                            <span class="lc-item-no" aria-hidden="true"><?= $index + 1; ?></span>
                            <p class="lc-item-text"><?= html_escape($text); ?></p>
                            <?php if (!$is_view_only): ?>
                                <div class="lc-item-actions">
                                    <?= form_open('Pages/learning_competency_setup_update_term/' . (int) $area->id . '/' . (int) $competency->id, array('class' => 'lc-term-form')); ?>
                                        <input type="hidden" name="division_id" value="<?= (int) $selected_division_id; ?>">
                                        <select name="term" class="custom-select lc-term-select" onchange="this.form.submit()" aria-label="Move this competency to another term">
                                            <option value="" selected disabled>Move to&hellip;</option>
                                            <?php foreach ($terms as $option): if ($option === $term) continue; ?><option value="<?= $option; ?>"><?= $option; ?></option><?php endforeach; ?>
                                        </select>
                                        <noscript><button type="submit" class="btn btn-sm btn-outline-primary">Save</button></noscript>
                                    <?= form_close(); ?>
                                    <?= form_open('Pages/learning_competency_setup_delete/' . (int) $area->id, array('class' => 'lc-term-form', 'onsubmit' => "return confirm('Remove this learning competency?');")); ?>
                                        <input type="hidden" name="id" value="<?= (int) $competency->id; ?>">
                                        <input type="hidden" name="division_id" value="<?= (int) $selected_division_id; ?>">
                                        <button type="submit" class="lc-remove" title="Remove competency"><i class="mdi mdi-close" aria-hidden="true"></i><span class="sr-only">Remove competency: <?= html_escape($text); ?></span></button>
                                    <?= form_close(); ?>
                                </div>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endforeach; endif; ?>
    </section>
</div>
