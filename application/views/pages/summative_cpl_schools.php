<?php
/**
 * Admin switchboard for the Summative CPL feature. Every school defaults to
 * OFF; toggling a school ON lets it encode CPL for Summative 1 and Summative 2
 * on each learning gap term record.
 */
$total_school_count = count($schools);
$enabled_rate = $total_school_count > 0 ? ($enabled_count / $total_school_count) * 100 : 0;
?>
<style>
    .cpl-toggle { --navy:var(--llcm-navy, #3b0718); --blue:var(--llcm-blue, #922c43); --line:var(--llcm-border, #ead7dc); --muted:#6d7e8e; --green:#21815c; }
    .cpl-toggle .ct-hero { display:flex; align-items:center; justify-content:space-between; gap:20px; margin:18px 0 22px; padding:29px; border-radius:17px; color:#fff; background:linear-gradient(125deg,var(--navy),var(--blue)); box-shadow:0 12px 27px rgba(18,63,99,.17); }
    .cpl-toggle .ct-hero h1 { margin:0 0 6px; color:#fff; font-size:27px; font-weight:700; }.cpl-toggle .ct-hero p { margin:0; color:#dceefa; }
    .cpl-toggle .ct-card { margin-bottom:22px; border:1px solid var(--line); border-radius:15px; background:#fff; box-shadow:0 6px 19px rgba(18,63,99,.06); overflow:hidden; }.cpl-toggle .ct-card-head { display:flex; align-items:flex-start; justify-content:space-between; gap:16px; padding:20px 22px; border-bottom:1px solid var(--line); background:#fbfdfe; }.cpl-toggle .ct-card-head h4 { margin:0 0 4px; color:var(--navy); font-size:16px; font-weight:700; }.cpl-toggle .ct-card-head p,.cpl-toggle .ct-card-head small { margin:0; color:var(--muted); font-size:12px; }
    .cpl-toggle .ct-summary { padding:21px 22px; }.cpl-toggle .ct-rate { color:var(--navy); font-size:32px; font-weight:700; line-height:1; }.cpl-toggle .ct-label { margin:7px 0 14px; color:var(--muted); font-size:13px; }.cpl-toggle .ct-progress { height:10px; border-radius:99px; background:#e9f0f4; overflow:hidden; }.cpl-toggle .ct-progress span { display:block; height:100%; border-radius:inherit; background:linear-gradient(90deg,#5b1026,#c66b7c); }.cpl-toggle .ct-counts { display:flex; justify-content:space-between; gap:12px; margin-top:11px; color:var(--muted); font-size:12px; }.cpl-toggle .ct-counts strong { color:var(--navy); }
    .cpl-toggle .ct-table { width:100%!important; margin:0; }.cpl-toggle .ct-table th { padding:12px 16px; border-top:0; color:var(--muted); font-size:10px; font-weight:700; letter-spacing:.05em; text-transform:uppercase; }.cpl-toggle .ct-table td { padding:14px 16px; color:#435467; vertical-align:middle; }.cpl-toggle .ct-table tbody tr:hover { background:#f8fcfe; }
    .cpl-toggle .ct-switch form { margin:0; }.cpl-toggle .custom-control-label { color:var(--muted); font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; cursor:pointer; }.cpl-toggle .custom-control-input:checked ~ .custom-control-label { color:var(--green); }
    .cpl-toggle .ct-pill { display:inline-block; padding:5px 9px; border-radius:999px; color:var(--green); background:#e9f7f0; font-size:10px; font-weight:700; }.cpl-toggle .ct-pill.off { color:#788692; background:#f0f3f5; }
    .cpl-toggle .empty { padding:28px; color:var(--muted); text-align:center; }
    .cpl-toggle .dataTables_wrapper { padding:18px 22px 20px; }.cpl-toggle .dataTables_wrapper .row:first-child { align-items:center; margin-bottom:14px; }.cpl-toggle .dataTables_filter input,.cpl-toggle .dataTables_length select { border:1px solid #cedce5; border-radius:6px; background:#fff; color:#435467; padding:5px 8px; }.cpl-toggle .dataTables_filter input { margin-left:7px; }.cpl-toggle .dataTables_info { color:var(--muted); font-size:12px; padding-top:14px!important; }.cpl-toggle .dataTables_paginate { padding-top:10px!important; }.cpl-toggle .dataTables_paginate .paginate_button { border-radius:6px!important; border:0!important; color:var(--navy)!important; }.cpl-toggle .dataTables_paginate .paginate_button.current { background:#f5e9ec!important; color:#5b1026!important; font-weight:700; }
    @media (max-width:767.98px) { .cpl-toggle .ct-hero { align-items:flex-start; flex-direction:column; padding:23px; }.cpl-toggle .ct-card-head { padding:18px; }.cpl-toggle .ct-summary { padding:18px; } }
</style>
<div class="cpl-toggle">
    <section class="ct-hero"><div><h1><i class="mdi mdi-toggle-switch-outline mr-2"></i>Summative CPL Access</h1><p>Choose which schools can encode CPL for Summative 1 and Summative 2 on each learning gap term record. All schools default to OFF.</p></div></section>

    <?php if ($this->session->flashdata('success')) : ?><div class="alert alert-success"><?= $this->session->flashdata('success'); ?></div><?php endif; ?>
    <?php if ($this->session->flashdata('danger')) : ?><div class="alert alert-danger"><?= $this->session->flashdata('danger'); ?></div><?php endif; ?>

    <section class="ct-card"><div class="ct-card-head"><div><h4>Feature coverage</h4><p>Enabled schools see two extra CPL fields per term in their data entry form.</p></div><small><?= number_format($enabled_count); ?> of <?= number_format($total_school_count); ?> schools</small></div><div class="ct-summary"><div class="ct-rate"><?= number_format($enabled_rate, 1); ?>%</div><p class="ct-label">of schools have Summative CPL encoding enabled</p><div class="ct-progress" aria-label="<?= number_format($enabled_rate, 1); ?> percent enabled"><span style="width:<?= min(100, max(0, $enabled_rate)); ?>%"></span></div><div class="ct-counts"><span><strong><?= number_format($enabled_count); ?></strong> enabled</span><span><strong><?= number_format(max(0, $total_school_count - $enabled_count)); ?></strong> disabled</span></div></div></section>

    <section class="ct-card"><div class="ct-card-head"><div><h4>Schools</h4><p>Search or filter by division, then flip the switch for any school.</p></div><small><?= number_format($total_school_count); ?> schools</small></div><table id="summativeCplTable" class="table ct-table mb-0"><thead><tr><th>School</th><th>School ID</th><th>Division</th><th>District</th><th>Status</th><th>Summative CPL</th></tr></thead><tbody><?php if (empty($schools)) : ?><tr><td colspan="6" class="empty">No schools are registered yet.</td></tr><?php else : foreach ($schools as $school) : $enabled = (int) $school->cpl_summative_enabled === 1; ?><tr><td><strong><?= html_escape($school->schoolName); ?></strong></td><td><?= html_escape($school->schoolID); ?></td><td><?= html_escape($school->division_name ?: '—'); ?></td><td><?= html_escape($school->district_name ?: '—'); ?></td><td><span class="ct-pill<?= $enabled ? '' : ' off'; ?>"><?= $enabled ? 'Enabled' : 'Disabled'; ?></span></td><td class="ct-switch"><?= form_open('Pages/summative_cpl_toggle'); ?><input type="hidden" name="school_id" value="<?= html_escape($school->schoolID); ?>"><input type="hidden" name="enabled" value="<?= $enabled ? 0 : 1; ?>"><div class="custom-control custom-switch"><input type="checkbox" class="custom-control-input" id="cpl-sw-<?= html_escape($school->schoolID); ?>" <?= $enabled ? 'checked' : ''; ?> onchange="this.form.submit()"><label class="custom-control-label" for="cpl-sw-<?= html_escape($school->schoolID); ?>"><?= $enabled ? 'On' : 'Off'; ?></label></div><?= form_close(); ?></td></tr><?php endforeach; endif; ?></tbody></table></section>
</div>
<?php if (!empty($schools)): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (!window.jQuery || !jQuery.fn.DataTable) return;
    jQuery('#summativeCplTable').DataTable({
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
        order: [[2, 'asc'], [0, 'asc']],
        responsive: true,
        columnDefs: [{ targets: [1, 4, 5], className: 'text-center' }, { targets: 5, orderable: false }],
        language: {
            search: '_INPUT_',
            searchPlaceholder: 'Search schools...'
        }
    });
});
</script>
<?php endif; ?>
