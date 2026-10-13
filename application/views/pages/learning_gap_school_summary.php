<link rel="stylesheet" href="<?= base_url('assets/css/learning-gap-records.css'); ?>?v=<?= filemtime(FCPATH . 'assets/css/learning-gap-records.css'); ?>">
<div class="lg-records">
    <header class="lg-records-header"><div><span class="records-eyebrow"><?= $is_region ? 'Regional' : (!empty($is_district) ? 'District' : 'Division'); ?> school summary</span><h1>Learning Gap Summary</h1><p>Learning competencies with gaps and the number of schools that submitted each one.</p></div></header>
    <section class="lg-records-card records-filter-card" aria-label="Grade level filter">
        <form method="get" action="<?= base_url('Pages/learning_gap_school_summary'); ?>" class="record-filter">
            <div class="record-filter-field"><label for="summary-grade">Grade level</label><select name="grade_level" id="summary-grade" class="custom-select"><option value="">All grade levels</option>
            <?php if ($grade_filter !== '' && !in_array($grade_filter, $grade_options, true)) $grade_options[] = $grade_filter; ?>
            <?php foreach ($grade_options as $grade): ?><option value="<?= html_escape($grade); ?>" <?= $grade_filter === $grade ? 'selected' : ''; ?>><?= html_escape($grade); ?></option><?php endforeach; ?>
            </select></div>
            <div class="record-filter-actions"><button class="btn btn-primary" type="submit">Apply filter</button><?php if ($grade_filter !== ''): ?><a class="btn btn-outline-secondary" href="<?= base_url('Pages/learning_gap_school_summary'); ?>">Clear filter</a><?php endif; ?></div>
        </form>
    </section>
    <section class="lg-records-card" aria-labelledby="school-summary-title">
        <div class="lg-records-tools"><h2 id="school-summary-title">Competencies with Learning Gap</h2><p><?= number_format(count($competency_rows)); ?> competencies · <?= number_format($reporting_school_count); ?> submitting schools · <?= $grade_filter !== '' ? html_escape($grade_filter) : 'All grade levels'; ?></p><p>Each school is counted once per competency, grade, and learning area, across all trimesters.</p></div>
        <div class="table-responsive" tabindex="0" role="region" aria-label="Competencies and submitting school counts">
            <table class="table mb-0"><thead><tr><th scope="col">Competencies with Learning Gap</th><th scope="col" class="text-right">Number of Schools Submitted</th><th scope="col">Action</th></tr></thead><tbody>
            <?php if (empty($competency_rows)): ?><tr><td colspan="3" class="records-empty">No competencies found<?= $grade_filter !== '' ? ' for the selected grade level' : ' within your assigned scope'; ?>.</td></tr><?php endif; ?>
            <?php foreach ($competency_rows as $competency):
                $details_url = base_url('Pages/learning_gap_records') . '?' . http_build_query(array(
                    'grade_level' => $competency['grade'],
                    'learning_area' => $competency['area'],
                    'competency' => $competency['text']
                ), '', '&', PHP_QUERY_RFC3986);
            ?><tr><th scope="row"><?= html_escape($competency['text']); ?><small class="d-block text-muted"><?= html_escape($competency['grade'] . ' · ' . $competency['area']); ?></small></th><td class="text-right"><strong><?= number_format($competency['school_count']); ?></strong></td><td><a class="btn btn-sm btn-outline-primary" href="<?= html_escape($details_url); ?>">View details<span class="sr-only"> for <?= html_escape($competency['text']); ?></span></a></td></tr><?php endforeach; ?>
            </tbody></table>
        </div>
    </section>
</div>
