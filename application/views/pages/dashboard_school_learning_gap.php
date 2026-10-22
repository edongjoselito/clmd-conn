<?php
$school = $this->Common->one_cond_row('schools', 'schoolID', $this->session->username);
$scope = array('type' => 'school', 'id' => (string) $this->session->username);
$summary = $this->Page_model->learning_gap_summary($scope);
$recent = array_slice($this->Page_model->learning_gap_records($scope), 0, 5);
$school_name = $school && trim((string) $school->schoolName) !== '' ? $school->schoolName : $this->session->user;
$phil_iri_url = base_url('Pages/phil_iri');
?>
<style>
    .lg-home { --navy:var(--llcm-navy, #3b0718); --blue:var(--llcm-blue, #922c43); --soft:var(--llcm-sky, #fbf4f5); --line:var(--llcm-border, #ead7dc); }.lg-home .hero{margin:18px 0 22px;padding:32px;border-radius:18px;color:#fff;background:linear-gradient(130deg,var(--navy),var(--blue));}.lg-home .hero h1{color:#fff;margin:0 0 7px;font-size:27px}.lg-home .hero p{margin:0;color:#f4e6e9}.lg-home .metric{height:calc(100% - 20px);margin-bottom:20px;padding:20px;border:1px solid var(--line);border-radius:14px;background:#fff;box-shadow:0 5px 18px rgba(59,7,24,.06)}.lg-home .metric small{display:block;text-transform:uppercase;letter-spacing:.05em;color:#6b7d8b;font-weight:700}.lg-home .metric strong{display:block;margin-top:7px;font-size:29px;color:var(--navy)}.lg-home .card{border:1px solid var(--line);border-radius:14px;box-shadow:0 5px 18px rgba(59,7,24,.06)}.lg-home .card-header{background:var(--soft);border-bottom:1px solid var(--line);color:var(--navy)}
</style>
<div class="lg-home">
    <div class="hero">
        <h1>Welcome, <?= html_escape($school_name); ?></h1>
        <p>Learning Gap Monitoring dashboard for Fiscal Year <?= (int) $this->session->fy; ?> — encode competency gaps, interventions, and progress for your school.</p>
    </div>
    <div class="row">
        <div class="col-md-6"><div class="metric"><small>Encoded Records</small><strong><?= (int) $summary->record_count; ?></strong></div></div>
        <div class="col-md-6"><div class="metric"><small>Recorded Assessed Count</small><strong><?= number_format((int) $summary->learners_assessed); ?></strong></div></div>
    </div>
    <div class="card mb-4"><div class="card-body d-flex justify-content-between align-items-center"><div><h4 class="mb-1">Phil-IRI Reading Results</h4><p class="text-muted mb-0">Enter school reading results by grade level for this school year.</p></div><a class="btn btn-primary" href="<?= $phil_iri_url; ?>">Open Phil-IRI Data Entry <i class="mdi mdi-arrow-right"></i></a></div></div>
    <div class="card"><div class="card-header"><h4 class="mb-0">Recent Encoded Learning Gaps</h4></div><div class="table-responsive"><table class="table mb-0"><thead><tr><th>Grade</th><th>Learning Area</th><th>Trimester</th><th>Least Learned Competency</th><th>Learners with Gap</th><th>Status</th></tr></thead><tbody><?php if (empty($recent)): ?><tr><td colspan="6" class="p-4 text-center text-muted">No records yet. Start by encoding the results from your assessment.</td></tr><?php else: foreach ($recent as $row): ?><tr><td><?= html_escape($row->grade_level); ?></td><td><?= html_escape($row->learning_area); ?></td><td><?= html_escape($row->term); ?></td><td><?= html_escape($row->least_learned_competency); ?></td><td><?= (int) $row->learners_with_gap; ?></td><td><?= html_escape($row->intervention_status ?: 'Not set'); ?></td></tr><?php endforeach; endif; ?></tbody></table></div><div class="card-body"><a href="<?= base_url('Pages/learning_gap'); ?>">Open Learning Gap Monitoring <i class="mdi mdi-arrow-right"></i></a></div></div>
</div>
