<link rel="stylesheet" href="<?= base_url('assets/css/learning-area-themes.css'); ?>?v=<?= filemtime(FCPATH . 'assets/css/learning-area-themes.css'); ?>">
<?php $is_filipino = isset($area_slug) && $area_slug === 'filipino'; $assessed = (int) $summary->learners_assessed; $gap = (int) $summary->learners_with_gap; ?>
<main style="--area-primary: <?= html_escape($area['theme']['primary']); ?>; --area-dark: <?= html_escape($area['theme']['dark']); ?>; --area-soft: <?= html_escape($area['theme']['soft']); ?>;" class="area-dashboard<?= $is_filipino ? ' area-dashboard-filipino' : ''; ?>" aria-labelledby="area-title">
    <?php if (!empty($regional_dashboard_origin)) : ?><p><a href="<?= base_url('region/learning-areas'); ?>">&larr; Learning Area Dashboards</a></p><?php endif; ?>
    <section class="area-hero">
        <div class="area-hero-copy">
            <span class="area-eyebrow"><?= !empty($regional_dashboard) ? 'CLMD · REGIONAL LEARNING AREA MONITORING' : 'CLMD · DIVISION LEARNING AREA MONITORING'; ?></span>
            <div class="area-brand">
                <img class="area-logo" src="<?= base_url($is_filipino ? 'assets/images/learning-areas/filipino-data-hub-emblem-transparent.png' : $area['theme']['logo']); ?>" alt="<?= html_escape($area['name']); ?> logo" width="96" height="96">
                <div><h1 id="area-title"><?= html_escape($area['name']); ?></h1>
                <p class="area-tagline"><?= html_escape($area['theme']['tagline']); ?></p></div>
            </div>
            <p><?= $is_filipino ? 'Subaybayan ang pagkatuto sa Filipino at ang mga hakbang upang mapaunlad ang kasanayan ng bawat mag-aaral.' : (!empty($regional_dashboard) ? 'Learning gaps, school progress, and interventions across all divisions in your region.' : 'Learning gaps, school progress, and interventions. Your subject at a glance.'); ?></p>
            <div class="area-hero-meta"><span><i class="mdi mdi-calendar-outline" aria-hidden="true"></i> Fiscal Year <?= (int) $this->session->fy; ?></span><span><i class="mdi mdi-map-marker-outline" aria-hidden="true"></i> <?= html_escape($scope_label); ?></span></div>
        </div>
        <?php if ($is_filipino): ?>
        <div class="filipino-hero-art" aria-hidden="true">
            <span class="filipino-art-orbit"></span>
            <img src="<?= base_url('assets/images/learning-areas/filipino-data-hub-emblem-transparent.png'); ?>" alt="" width="160" height="160">
            <span class="filipino-art-caption">WIKA · DIWA · KULTURA</span>
        </div>
        <?php endif; ?>
    </section>
    <?php if ($is_filipino): ?>
    <aside class="filipino-focus" aria-label="Gabay sa pagsusuri">
        <div><span class="filipino-kicker">GABAY SA PAGSUSURI</span><h2>Mula sa datos tungo sa pagkatuto</h2></div>
        <p><strong>Basahin.</strong> Suriin ang mga resulta.</p>
        <p><strong>Unawain.</strong> Tukuyin ang kasanayang nangangailangan ng suporta.</p>
        <p><strong>Tugunan.</strong> Iugnay ang interbensiyon sa pangangailangan.</p>
    </aside>
    <?php endif; ?>
    <div class="area-section-heading"><div><h2><?= $is_filipino ? 'Pangkalahatang Kalagayan' : 'Overview'; ?></h2><p><?= $is_filipino ? 'Batay sa mga ulat ng paaralan para sa napiling taon.' : 'Based on school submissions for the selected year.'; ?></p></div><span class="area-status"><?= $records ? 'Submitted data' : 'Awaiting submissions'; ?></span></div>
    <div class="row">
    <?php $metric_index = 0; $metric_icons = array('file-document-outline', 'school', 'account-group-outline', 'chart-line'); foreach (array(($is_filipino ? 'Mga naipasang ulat' : 'Submitted records') => $summary->record_count, ($is_filipino ? 'Mga paaralang nag-ulat' : 'Reporting schools') => $summary->school_count, ($is_filipino ? 'Mga pagtataya' : 'Learner assessments') => $assessed, ($is_filipino ? 'Mga pagtatayang may puwang' : 'Assessments with gaps') => $gap) as $label => $value): ?>
        <div class="col-sm-6 col-xl-3"><div class="card area-metric"><div class="card-body"><span class="area-metric-icon" aria-hidden="true"><i class="mdi mdi-<?= $metric_icons[$metric_index++]; ?>"></i></span><p><?= html_escape($label); ?></p><strong><?= number_format($value); ?></strong><span class="area-metric-caption">Selected fiscal year</span></div></div></div>
    <?php endforeach; ?>
    </div>
    <p class="area-gap-note"><i class="mdi mdi-information-outline" aria-hidden="true"></i> Recorded gap rate: <strong><?= $assessed ? number_format($gap / $assessed * 100, 1) . '%' : 'No data'; ?></strong>. Assessment totals may include the same learner in multiple records.</p>
    <div class="card"><div class="card-body"><div class="area-section-heading"><div><h2><?= $is_filipino ? 'Pagganap sa Bawat Markahan' : 'Term Performance'; ?></h2><p><?= $is_filipino ? 'Paghambingin ang resulta ng pagtataya at antas ng kasanayan sa bawat markahan.' : 'Compare assessment results and class proficiency across terms.'; ?></p></div></div><div class="table-responsive"><table class="table table-striped">
        <thead><tr><th>Term</th><th>Records</th><th>Assessed</th><th>With gaps</th><th>Gap rate</th><th>Class proficiency level</th></tr></thead><tbody>
        <?php foreach ($terms as $term): ?><tr>
            <td><?= html_escape($term->term); ?></td><td><?= number_format($term->record_count); ?></td><td><?= number_format($term->learners_assessed); ?></td><td><?= number_format($term->learners_with_gap); ?></td>
            <td><?= $term->learners_assessed ? number_format($term->learners_with_gap / $term->learners_assessed * 100, 1) . '%' : '—'; ?></td>
            <td><?= $term->class_proficiency_level !== null ? number_format($term->class_proficiency_level, 1) . '%' : '—'; ?></td>
        </tr><?php endforeach; ?>
        </tbody></table></div></div></div>
    <section id="school-submissions" class="card"><div class="card-body"><div class="area-section-heading"><div><h2><?= $is_filipino ? 'Mga Ulat at Interbensiyon' : 'School Submissions and Interventions'; ?></h2><p><?= $is_filipino ? 'Mga kasanayang nangangailangan ng suporta at mga hakbang ng paaralan.' : 'Competencies that need attention and the actions schools are taking.'; ?></p></div><span class="area-count"><?= number_format(count($records)); ?> records</span></div>
    <?php if (!$records): ?><div class="area-empty"><span class="area-empty-icon" aria-hidden="true"><i class="mdi mdi-clipboard-text-outline"></i></span><h3><?= $is_filipino ? 'Wala pang naipapasang ulat' : 'No submissions yet'; ?></h3><p>No submissions for this learning area in <?= (int) $this->session->fy; ?>. Matching school records will appear here as they are submitted.</p><small>To review another year, use the fiscal year selector above.</small></div>
    <?php else: ?><div class="table-responsive"><table class="table table-hover"><thead><tr><th>School / Division</th><th>Grade / Term</th><th>Least learned competency</th><th>Intervention / Action</th><th>Status</th></tr></thead><tbody>
    <?php foreach ($records as $record): ?><tr>
        <td><?= html_escape($record->schoolName ?: $record->school_id); ?><br><small class="text-muted"><?= html_escape($record->division_name); ?></small></td>
        <td><?= html_escape($record->grade_level); ?><br><?= html_escape($record->term); ?></td>
        <td><?= nl2br(html_escape($record->least_learned_competency)); ?></td><td><?= nl2br(html_escape($record->intervention_action ?? '')); ?></td><td><?= html_escape($record->intervention_status ?: 'Not specified'); ?></td>
    </tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
    </div></section>
    <?php if (strpos((string) $this->session->position, 'la_') === 0) : ?><details class="card area-account"<?= ($this->session->flashdata('success') || $this->session->flashdata('danger')) ? ' open' : ''; ?>><summary>Account security <span>Change your password</span></summary><div class="card-body"><h4>Change Account Password</h4>
        <?php if ($this->session->flashdata('success')): ?><p class="alert alert-success"><?= html_escape($this->session->flashdata('success')); ?></p><?php endif; ?>
        <?php if ($this->session->flashdata('danger')): ?><p class="alert alert-danger"><?= html_escape($this->session->flashdata('danger')); ?></p><?php endif; ?>
        <?= form_open('Pages/change_password_user'); ?>
        <div class="row">
        <div class="col-md-4 form-group"><label for="areaCurrentPassword">Current password</label><input class="form-control" type="password" id="areaCurrentPassword" name="current_password" autocomplete="current-password" required></div>
        <div class="col-md-4 form-group"><label for="areaNewPassword">New password</label><input class="form-control" type="password" id="areaNewPassword" name="password" autocomplete="new-password" minlength="8" maxlength="128" required></div>
        <div class="col-md-4 form-group"><label for="areaConfirmPassword">Confirm password</label><input class="form-control" type="password" id="areaConfirmPassword" name="password_confirm" autocomplete="new-password" minlength="8" maxlength="128" required></div>
        </div><button type="submit" class="btn btn-primary">Change password</button><?= form_close(); ?>
    </div></details><?php endif; ?>
</main>
