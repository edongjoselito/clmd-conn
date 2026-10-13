<?php
$archive_count = 0;
$total_records = 0;
$total_assessed = 0;
$total_with_gap = 0;
foreach ($archive_years as $year_row) {
    if ((int) $year_row->fiscal_year < $current_year) {
        $archive_count++;
    }
    $total_records += (int) $year_row->record_count;
    $total_assessed += (int) $year_row->learners_assessed;
    $total_with_gap += (int) $year_row->learners_with_gap;
}
$overall_gap_rate = $total_assessed > 0 ? ($total_with_gap / $total_assessed) * 100 : 0;
?>
<link rel="stylesheet" href="<?= base_url('assets/css/learning-gap-archives.css'); ?>?v=<?= filemtime(FCPATH . 'assets/css/learning-gap-archives.css'); ?>">

<main class="archive-page">
    <header class="archive-hero">
        <div class="archive-hero-copy">
            <span class="archive-eyebrow">Learning evidence · Yearly history</span>
            <h1>Archived Learning Gap Records</h1>
            <p>Review records from earlier fiscal years without changing or mixing them with the active-year workspace.</p>
        </div>
        <a class="btn btn-light" href="<?= base_url('Pages/learning_gap_records'); ?>">
            <i class="mdi mdi-format-list-bulleted" aria-hidden="true"></i> Current records
        </a>
    </header>

    <section class="archive-metrics" aria-label="Archive summary">
        <article><span>Archived years</span><strong><?= number_format($archive_count); ?></strong><small>Read-only yearly collections</small></article>
        <article><span>Total records</span><strong><?= number_format($total_records); ?></strong><small>Across all available years</small></article>
        <article><span>Learners assessed</span><strong><?= number_format($total_assessed); ?></strong><small>Recorded assessment count</small></article>
        <article class="archive-metric-focus"><span>Overall gap rate</span><strong><?= $total_assessed > 0 ? number_format($overall_gap_rate, 1) . '%' : '—'; ?></strong><small><?= number_format($total_with_gap); ?> learners with gaps</small></article>
    </section>

    <section class="archive-panel" aria-labelledby="archive-years-title">
        <div class="archive-panel-head">
            <div>
                <h2 id="archive-years-title">Records by fiscal year</h2>
                <p>Each year opens as a separate record directory within your assigned <?= $is_school ? 'school' : 'reporting'; ?> scope.</p>
            </div>
            <span class="archive-scope"><i class="mdi mdi-shield-account-outline" aria-hidden="true"></i> Scope protected</span>
        </div>

        <?php if (empty($archive_years)) : ?>
            <div class="archive-empty">
                <i class="mdi mdi-archive-search-outline" aria-hidden="true"></i>
                <h3>No yearly records available yet</h3>
                <p>Fiscal-year archives will appear here after learning-gap records have been encoded.</p>
            </div>
        <?php else : ?>
            <div class="archive-grid">
                <?php foreach ($archive_years as $year_row) : ?>
                    <?php
                    $year = (int) $year_row->fiscal_year;
                    $is_current = $year === $current_year;
                    $is_upcoming = $year > $current_year;
                    $assessed = (int) $year_row->learners_assessed;
                    $with_gap = (int) $year_row->learners_with_gap;
                    $gap_rate = $assessed > 0 ? ($with_gap / $assessed) * 100 : 0;
                    ?>
                    <article class="archive-year-card<?= $is_current ? ' is-current' : ''; ?>">
                        <div class="archive-year-top">
                            <span class="archive-year-icon"><i class="mdi <?= $is_current ? 'mdi-calendar-check-outline' : 'mdi-archive-outline'; ?>" aria-hidden="true"></i></span>
                            <span class="archive-status"><?= $is_current ? 'Active year' : ($is_upcoming ? 'Upcoming year' : 'Archived · Read-only'); ?></span>
                        </div>
                        <h3>Fiscal Year <?= $year; ?></h3>
                        <div class="archive-year-stats<?= $is_school ? ' is-school' : ''; ?>">
                            <div><strong><?= number_format((int) $year_row->record_count); ?></strong><span>Records</span></div>
                            <?php if (!$is_school) : ?>
                                <div><strong><?= number_format((int) $year_row->school_count); ?></strong><span>Schools</span></div>
                            <?php endif; ?>
                            <div><strong><?= number_format($assessed); ?></strong><span>Assessed</span></div>
                            <div><strong><?= $assessed > 0 ? number_format($gap_rate, 1) . '%' : '—'; ?></strong><span>Gap rate</span></div>
                        </div>
                        <div class="archive-year-foot">
                            <small>Latest entry <?= !empty($year_row->latest_submission) ? html_escape(date('M j, Y', strtotime($year_row->latest_submission))) : 'not available'; ?></small>
                            <a href="<?= base_url('Pages/learning_gap_records?year=' . $year); ?>">
                                View records <i class="mdi mdi-arrow-right" aria-hidden="true"></i>
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>
