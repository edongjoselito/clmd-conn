<?php
$submission_rate = $total_school_count > 0 ? ($submitted_school_count / $total_school_count) * 100 : 0;
$division_name = !empty($division->description) ? $division->description : 'Your Division';
?>
<style>
    .submission-monitor { --navy:var(--llcm-navy, #3b0718); --blue:var(--llcm-blue, #922c43); --line:var(--llcm-border, #ead7dc); --muted:#6d7e8e; --green:#21815c; }
    .submission-monitor .sm-hero { display:flex; align-items:center; justify-content:space-between; gap:20px; margin:18px 0 22px; padding:29px; border-radius:17px; color:#fff; background:linear-gradient(125deg,var(--navy),var(--blue)); box-shadow:0 12px 27px rgba(18,63,99,.17); }
    .submission-monitor .sm-hero h1 { margin:0 0 6px; color:#fff; font-size:27px; font-weight:700; }.submission-monitor .sm-hero p { margin:0; color:#dceefa; }.submission-monitor .sm-hero .btn { border-radius:999px; font-weight:700; white-space:nowrap; }
    .submission-monitor .sm-card { margin-bottom:22px; border:1px solid var(--line); border-radius:15px; background:#fff; box-shadow:0 6px 19px rgba(18,63,99,.06); overflow:hidden; }.submission-monitor .sm-card-head { display:flex; align-items:flex-start; justify-content:space-between; gap:16px; padding:20px 22px; border-bottom:1px solid var(--line); background:#fbfdfe; }.submission-monitor .sm-card-head h4 { margin:0 0 4px; color:var(--navy); font-size:16px; font-weight:700; }.submission-monitor .sm-card-head p,.submission-monitor .sm-card-head small { margin:0; color:var(--muted); font-size:12px; }
    .submission-monitor .sm-summary { padding:21px 22px; }.submission-monitor .sm-rate { color:var(--navy); font-size:32px; font-weight:700; line-height:1; }.submission-monitor .sm-label { margin:7px 0 14px; color:var(--muted); font-size:13px; }.submission-monitor .sm-progress { height:10px; border-radius:99px; background:#e9f0f4; overflow:hidden; }.submission-monitor .sm-progress span { display:block; height:100%; border-radius:inherit; background:linear-gradient(90deg,#5b1026,#c66b7c); }.submission-monitor .sm-counts { display:flex; justify-content:space-between; gap:12px; margin-top:11px; color:var(--muted); font-size:12px; }.submission-monitor .sm-counts strong { color:var(--navy); }
    .submission-monitor .sm-table { width:100%!important; margin:0; }.submission-monitor .sm-table th { padding:12px 16px; border-top:0; color:var(--muted); font-size:10px; font-weight:700; letter-spacing:.05em; text-transform:uppercase; }.submission-monitor .sm-table td { padding:14px 16px; color:#435467; vertical-align:middle; }.submission-monitor .sm-table tbody tr:hover { background:#f8fcfe; }.submission-monitor .sm-pill { display:inline-block; padding:5px 9px; border-radius:999px; color:var(--green); background:#e9f7f0; font-size:10px; font-weight:700; }.submission-monitor .sm-pill.pending { color:#788692; background:#f0f3f5; }.submission-monitor .empty { padding:28px; color:var(--muted); text-align:center; }.submission-monitor .dataTables_wrapper { padding:18px 22px 20px; }.submission-monitor .dataTables_wrapper .row:first-child { align-items:center; margin-bottom:14px; }.submission-monitor .dataTables_filter input,.submission-monitor .dataTables_length select { border:1px solid #cedce5; border-radius:6px; background:#fff; color:#435467; padding:5px 8px; }.submission-monitor .dataTables_filter input { margin-left:7px; }.submission-monitor .dataTables_info { color:var(--muted); font-size:12px; padding-top:14px!important; }.submission-monitor .dataTables_paginate { padding-top:10px!important; }.submission-monitor .dataTables_paginate .paginate_button { border-radius:6px!important; border:0!important; color:var(--navy)!important; }.submission-monitor .dataTables_paginate .paginate_button.current { background:#fbf4f5!important; color:#5b1026!important; font-weight:700; }
    @media (max-width:767.98px) { .submission-monitor .sm-hero { align-items:flex-start; flex-direction:column; padding:23px; }.submission-monitor .sm-hero .btn { width:100%; }.submission-monitor .sm-card-head { padding:18px; }.submission-monitor .sm-summary { padding:18px; } }
</style>
<div class="submission-monitor">
    <section class="sm-hero"><div><h1><i class="mdi mdi-clipboard-check-outline mr-2"></i>School Submission Monitoring</h1><p><?= html_escape($division_name); ?> — track which schools have submitted Learning Gap Monitoring data.</p></div><a href="<?= base_url('Pages/learning_gap_records'); ?>" class="btn btn-light"><i class="mdi mdi-format-list-bulleted mr-1"></i> View Submitted Records</a></section>
    <section class="sm-card"><div class="sm-card-head"><div><h4>Division submission coverage</h4><p>A school is counted as submitted after encoding at least one learning-gap record.</p></div><small><?= number_format($submitted_school_count); ?> of <?= number_format($total_school_count); ?> schools</small></div><div class="sm-summary"><div class="sm-rate"><?= number_format($submission_rate, 1); ?>%</div><p class="sm-label">of schools have submitted learning-gap data</p><div class="sm-progress" aria-label="<?= number_format($submission_rate, 1); ?> percent submitted"><span style="width:<?= min(100, max(0, $submission_rate)); ?>%"></span></div><div class="sm-counts"><span><strong><?= number_format($submitted_school_count); ?></strong> submitted</span><span><strong><?= number_format(max(0, $total_school_count - $submitted_school_count)); ?></strong> pending</span></div></div></section>
    <section class="sm-card"><div class="sm-card-head"><div><h4>School submission status</h4><p>Search, sort, and page through schools to follow up on pending submissions.</p></div><small><?= number_format($total_school_count); ?> schools</small></div><table id="schoolSubmissionsTable" class="table sm-table mb-0"><thead><tr><th>School</th><th>Status</th><th>Records submitted</th><th>Latest submission</th></tr></thead><tbody><?php if (empty($schools)) : ?><tr><td colspan="4" class="empty">No schools are assigned to this division.</td></tr><?php else : foreach ($schools as $school) : $submitted = (int) $school->record_count > 0; ?><tr><td><strong><?= html_escape($school->schoolName); ?></strong></td><td><span class="sm-pill<?= $submitted ? '' : ' pending'; ?>"><?= $submitted ? 'Submitted' : 'Pending'; ?></span></td><td><?= number_format((int) $school->record_count); ?></td><td data-order="<?= $school->latest_submission ? html_escape($school->latest_submission) : ''; ?>"><?= $school->latest_submission ? html_escape(date('M j, Y', strtotime($school->latest_submission))) : '—'; ?></td></tr><?php endforeach; endif; ?></tbody></table></section>
</div>
<?php if (!empty($schools)): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (!window.jQuery || !jQuery.fn.DataTable) return;
    jQuery('#schoolSubmissionsTable').DataTable({
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
        order: [[0, 'asc']],
        responsive: true,
        columnDefs: [{ targets: [1, 2, 3], className: 'text-center' }],
        language: {
            search: '_INPUT_',
            searchPlaceholder: 'Search schools...'
        }
    });
});
</script>
<?php endif; ?>
