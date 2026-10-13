<?php
require_once APPPATH . 'libraries/Monitoring_summary.php';
$by_area = array_fill(0, count($assessment['groups']), array());
foreach ($assessment['items'] as $item) { $by_area[$item['area']][] = $item; }
$detail = function ($key) use ($assessment) { return isset($assessment['details'][$key]) ? $assessment['details'][$key] : ''; };
$field = function ($name, $label, $type = 'text') use ($detail, $print_mode) {
    echo '<div class="ma-field"><label for="ma-' . $name . '">' . html_escape($label) . '</label>';
    if ($print_mode) { echo '<div class="ma-value">' . nl2br(html_escape($detail($name))) . '</div>'; }
    elseif ($type === 'textarea') { echo '<textarea class="form-control" id="ma-' . $name . '" name="' . $name . '" rows="3" maxlength="10000">' . html_escape($detail($name)) . '</textarea>'; }
    else { echo '<input class="form-control" id="ma-' . $name . '" name="' . $name . '" type="' . $type . '" maxlength="255" value="' . html_escape($detail($name)) . '">'; }
    echo '</div>';
};
?>
<?php if ($print_mode): ?>
<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Monitoring Form — <?= html_escape($assessment['school_name']); ?></title></head><body>
<?php endif; ?>
<link rel="stylesheet" href="<?= base_url('assets/css/monitoring-assessment.css'); ?>?v=<?= filemtime(FCPATH . 'assets/css/monitoring-assessment.css'); ?>">
<div class="ma-page <?= $print_mode ? 'ma-print' : 'ma-editor'; ?>">
    <div class="ma-toolbar">
        <a href="<?= base_url('monitoring/tool'); ?>">Back to Monitoring</a>
        <?php if ($print_mode): ?><button type="button" onclick="window.print()">Print / Save as PDF</button><a href="<?= base_url('Pages/monitoring_assessment/' . (int) $record->id); ?>">Edit assessment</a>
        <?php elseif ($record): ?><a target="_blank" rel="noopener" href="<?= base_url('Pages/monitoring_assessment_print/' . (int) $record->id); ?>">Print saved form</a><?php endif; ?>
    </div>
    <?php if ($print_mode): ?><h1><?= html_escape(isset($assessment['tool_name']) ? $assessment['tool_name'] : 'UNIFIED LEARNING AREA MONITORING TOOL'); ?></h1><?php else: ?>
    <?php $overall = Monitoring_summary::calculate($assessment['items']); ?>
    <header class="ma-hero"><div><span class="ma-eyebrow">MONITORING / <?= $record ? 'ASSESSMENT #' . (int) $record->id : 'NEW ASSESSMENT'; ?></span><h1><?= html_escape(isset($assessment['tool_name']) ? $assessment['tool_name'] : 'Learning Area Assessment'); ?></h1><p><?= html_escape($assessment['school_name']); ?></p><div class="ma-hero-tags"><span>SY <?= html_escape($assessment['sy']); ?></span><span><?= html_escape($assessment['district_name']); ?></span></div></div><a href="#assessment-summary" class="ma-hero-link">View summary &darr;</a></header>
    <div class="ma-overview"><div><span>Assessment progress</span><strong id="ma-live-progress"><?= $overall['answered']; ?> / <?= $overall['total']; ?></strong><small>indicators assessed</small></div><div><span>Overall rating</span><strong id="ma-live-score"><?= $overall['score']; ?></strong><small id="ma-live-description"><?= html_escape($overall['description']); ?></small></div><div class="ma-jump"><label for="ma-jump">Jump to a section</label><select id="ma-jump" class="form-control"><option value="assessment-details">School details</option><?php foreach ($assessment['groups'] as $area => $group): if (!$by_area[$area]) continue; ?><option value="assessment-area-<?= $area; ?>"><?= html_escape($group['name']); ?></option><?php endforeach; ?><option value="assessment-summary">Summary</option><option value="assessment-remarks">Remarks &amp; recommendations</option><option value="assessment-signatures">Signatures</option></select></div></div>
    <?php endif; ?>
    <?php if (!$print_mode): ?>
        <?php if ($error): ?><div class="alert alert-danger" role="alert"><?= html_escape($error); ?></div><?php endif; ?>
        <?php if ($message = $this->session->flashdata('success')): ?><div class="alert alert-success" role="status"><?= html_escape($message); ?></div><?php endif; ?>
        <p>Save your progress at any time. Unanswered indicators remain incomplete. Print uses the last saved assessment.</p>
        <?= form_open('Pages/monitoring_assessment_save', array('id' => 'assessment-form')); ?>
        <input type="hidden" name="id" value="<?= $record ? (int) $record->id : 0; ?>">
        <input type="hidden" name="tool_id" value="<?= isset($assessment['tool_id']) ? (int) $assessment['tool_id'] : 0; ?>">
        <input type="hidden" name="version" value="<?= $record ? (int) $record->version : 0; ?>">
        <?php foreach (array('sy', 'school_id', 'division_id', 'district_id') as $key): ?><input type="hidden" name="<?= $key; ?>" value="<?= html_escape((string) $assessment[$key]); ?>"><?php endforeach; ?>
    <?php endif; ?>
    <div class="ma-details ma-school-details" id="assessment-details">
        <div><strong>Name of School:</strong> <?= html_escape($assessment['school_name']); ?></div>
        <div><strong>School ID:</strong> <?= html_escape($assessment['school_id']); ?></div>
        <div><strong>SY:</strong> <?= html_escape($assessment['sy']); ?></div>
        <div><strong>Division:</strong> <?= html_escape($assessment['division_name']); ?></div>
        <div><strong>District:</strong> <?= html_escape($assessment['district_name']); ?></div>
        <div class="ma-field"><label for="ma-school-type">Type of School</label>
            <?php if ($print_mode): ?><div class="ma-value"><?= html_escape($detail('school_type')); ?></div>
            <?php else: ?><select class="form-control" name="school_type" id="ma-school-type"><option value="">Select type</option><?php foreach (array('Public', 'Private') as $type): ?><option <?= $detail('school_type') === $type ? 'selected' : ''; ?>><?= $type; ?></option><?php endforeach; ?></select><?php endif; ?>
        </div>
        <?php $field('school_head', 'School Head'); $field('contact', 'Contact Number'); $field('psds', 'PSDS'); $field('monitor_date', 'Monitoring Date', 'date'); ?>
    </div>
    <p class="ma-instruction"><strong>Instruction:</strong> Check whether it is “Evident” or “Not Evident” based on your observations, assessment, and evidences. Provide relevant remarks in each indicator and comments in each competency.</p>
    <?php foreach ($assessment['groups'] as $area => $group): ?>
        <?php if (!$by_area[$area]) { continue; } ?>
        <section id="assessment-area-<?= (int) $area; ?>" class="ma-area" data-area="<?= (int) $area; ?>">
            <?php if (!$print_mode): ?><div class="ma-area-heading"><span class="ma-area-number"><?= (int) $area + 1; ?></span><div><h2><?= html_escape($group['name']); ?></h2><p><?= count($by_area[$area]); ?> indicators · Select a response and add supporting remarks</p></div><span class="ma-area-progress" data-area-progress="<?= $area; ?>"></span></div><?php endif; ?>
            <div class="ma-table-wrap"><table class="ma-table">
                <colgroup><col style="width:52%"><col style="width:9%"><col style="width:10%"><col style="width:29%"></colgroup>
                <thead><tr><th scope="col"><?= html_escape($group['name']); ?></th><th scope="col">Evident</th><th scope="col">Not Evident</th><th scope="col">Remarks</th></tr></thead>
                <tbody>
                    <?php foreach ($by_area[$area] as $item): ?>
                    <tr>
                        <td><?php if ($item['subgroup']): ?><strong class="ma-subgroup"><?= html_escape($item['subgroup']); ?></strong><?php endif; ?><?= nl2br(html_escape($item['text'])); ?>
                        <?php if (!$print_mode): ?><button type="button" class="ma-clear" data-clear="<?= (int) $item['id']; ?>">Clear response</button><?php endif; ?></td>
                        <?php foreach (array('evident' => 'Evident', 'not_evident' => 'Not Evident') as $value => $label): ?>
                            <td class="ma-choice"><?php if ($print_mode): ?><?= $item['rating'] === $value ? '&#10003;' : ''; ?><?php else: ?><label class="ma-rating-option"><input type="radio" name="rating[<?= (int) $item['id']; ?>]" value="<?= $value; ?>" <?= $item['rating'] === $value ? 'checked' : ''; ?> aria-label="<?= html_escape($label . ': ' . $item['text']); ?>"><span><?= $label; ?></span></label><?php endif; ?></td>
                        <?php endforeach; ?>
                        <td><?php if ($print_mode): ?><?= nl2br(html_escape($item['remarks'])); ?><?php else: ?><textarea class="form-control" name="remarks[<?= (int) $item['id']; ?>]" rows="3" maxlength="10000" aria-label="<?= html_escape('Remarks: ' . $item['text']); ?>"><?= html_escape($item['remarks']); ?></textarea><?php endif; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table></div>
            <div class="ma-area-comment"><label for="comment-<?= $area; ?>">Comments — <?= html_escape($group['name']); ?></label>
                <?php $comment = isset($assessment['comments'][$area]) ? $assessment['comments'][$area] : ''; ?>
                <?php if ($print_mode): ?><div class="ma-value"><?= nl2br(html_escape($comment)); ?></div><?php else: ?><textarea class="form-control" id="comment-<?= $area; ?>" name="comments[<?= $area; ?>]" rows="2" maxlength="10000"><?= html_escape($comment); ?></textarea><?php endif; ?>
            </div>
        </section>
    <?php endforeach; ?>
    <section class="ma-summary" id="assessment-summary"><h2>Summary</h2>
        <table class="ma-table"><thead><tr><th>Competencies / Area of Concern</th><th>Rating</th><th>Description</th></tr></thead><tbody>
            <?php foreach ($assessment['groups'] as $area => $group): $summary = Monitoring_summary::calculate($by_area[$area]); ?>
            <tr data-summary-area="<?= (int) $area; ?>"><td><?= html_escape($group['name']); ?></td><td data-score><?= $summary['score']; ?></td><td data-description><?= html_escape($summary['description']); ?> (<?= $summary['answered']; ?>/<?= $summary['total']; ?> assessed)</td></tr>
            <?php endforeach; $summary = Monitoring_summary::calculate($assessment['items']); ?>
            <tr data-summary-total><th>Total</th><th data-score><?= $summary['score']; ?></th><th data-description><?= html_escape($summary['description']); ?> (<?= $summary['answered']; ?>/<?= $summary['total']; ?> assessed)</th></tr>
        </tbody></table>
        <p>Score = total Evident indicators ÷ total indicators × 100. Ratings are provisional until all indicators are assessed.<?php if (!$print_mode): ?> Ratings update with each selection. Save the assessment before printing to include your latest changes.<?php endif; ?></p>
        <table class="ma-table"><thead><tr><th>Percentage Range</th><th>Description</th></tr></thead><tbody>
            <?php foreach (array('Below 50' => 'Needs Improvement', '51–62%' => 'Fairly Meets Expectation', '63–75%' => 'Satisfactorily Meets Expectation', '76–88%' => 'Very Satisfactory', '89–100%' => 'Outstanding') as $range => $description): ?><tr><td><?= $range; ?></td><td><?= $description; ?></td></tr><?php endforeach; ?>
        </tbody></table>
        <p class="ma-scale-note">The source scale leaves 50% and gaps between ranges undefined. Such scores are flagged for review.</p>
    </section>
    <section class="ma-narrative" id="assessment-remarks"><h2>Special Curricular Program and other Programs</h2>
        <label><?php if ($print_mode): ?><?= $detail('special_programs') === '1' ? '☑' : '☐'; ?><?php else: ?><input type="checkbox" name="special_programs" value="1" <?= $detail('special_programs') === '1' ? 'checked' : ''; ?>><?php endif; ?> The School has implemented Special Curricular Programs and Inclusive Education.</label>
        <h2>Remarks</h2>
        <?php $field('strengths', 'Strengths / Best Practices', 'textarea'); $field('technical_assistance', 'Technical Assistance Needed', 'textarea'); $field('recommendations', 'Recommendations', 'textarea'); ?>
    </section>
    <section class="ma-signatures" id="assessment-signatures"><h2>Monitors</h2><div class="ma-details">
        <div class="ma-field">
            <label>Name and Signature of Monitor</label>
            <?php if (!empty($assessment['monitor_signature']) && preg_match('#^data:image/png;base64,[A-Za-z0-9+/=]+$#D', $assessment['monitor_signature'])): ?>
                <img class="ma-esignature" src="<?= html_escape($assessment['monitor_signature']); ?>" alt="Monitor e-signature">
            <?php endif; ?>
            <div class="ma-value"><?= html_escape($detail('monitor_one')); ?></div>
            <?php if (!$print_mode && empty($assessment['monitor_signature'])): ?>
                <?php if (isset($assessment['monitor_user_id']) && (int) $assessment['monitor_user_id'] === (int) $this->session->id): ?>
                    <p class="small mt-2">No e-signature yet. <a href="<?= base_url('Pages/edit_profile'); ?>#e-signature" target="_blank" rel="noopener">Upload an e-signature in your profile</a>, then save this assessment to include it.</p>
                <?php else: ?><p class="small mt-2">The monitor has not attached an e-signature.</p><?php endif; ?>
            <?php endif; ?>
        </div>
        <?php $field('monitor_two', 'Name and Signature of Monitor'); ?>
    </div><p>Date: <?= html_escape($detail('monitor_date')); ?></p>
    <h2>Conforme</h2><div class="ma-signature-line"><?= html_escape($detail('school_head')); ?></div><p>Name and Signature of School Head</p><?php $field('conforme_date', 'Date', 'date'); ?>
    </section>
    <?php if (!$print_mode): ?><div class="ma-save"><span id="ma-save-status" role="status">Changes are saved when you select Save assessment.</span><button type="submit" class="btn btn-primary">Save assessment</button><a href="<?= base_url('monitoring/tool'); ?>">Back to records</a></div><?= form_close(); ?><?php endif; ?>
</div>
<?php if (!$print_mode): ?>
<script src="<?= base_url('assets/js/monitoring-rating.js'); ?>?v=<?= filemtime(FCPATH . 'assets/js/monitoring-rating.js'); ?>"></script>
<script>
(function () {
    var form = document.getElementById('assessment-form'), dirty = false;
    document.getElementById('ma-jump').addEventListener('change', function () { document.getElementById(this.value).scrollIntoView({behavior: 'smooth', block: 'start'}); });
    function markDirty() { dirty = true; document.getElementById('ma-save-status').textContent = 'Unsaved changes'; }
    function summarize(section) {
        var total = section ? section.querySelectorAll('input[type="radio"][value="evident"]').length : 0;
        var answered = section ? section.querySelectorAll('input[type="radio"]:checked').length : 0;
        var evident = section ? section.querySelectorAll('input[type="radio"][value="evident"]:checked').length : 0;
        return window.MonitoringRating.calculate(total, answered, evident);
    }
    function updateRow(row, summary) {
        row.querySelector('[data-score]').textContent = summary.score;
        row.querySelector('[data-description]').textContent = summary.description + ' (' + summary.answered + '/' + summary.total + ' assessed)';
    }
    function refreshSummary() {
        form.querySelectorAll('[data-summary-area]').forEach(function (row) {
            var area = row.getAttribute('data-summary-area');
            var summary = summarize(form.querySelector('[data-area="' + area + '"]'));
            updateRow(row, summary);
            var progress = form.querySelector('[data-area-progress="' + area + '"]');
            if (progress) progress.textContent = summary.answered + ' / ' + summary.total + ' assessed';
        });
        var total = summarize(form);
        updateRow(form.querySelector('[data-summary-total]'), total);
        document.getElementById('ma-live-progress').textContent = total.answered + ' / ' + total.total;
        document.getElementById('ma-live-score').textContent = total.score;
        document.getElementById('ma-live-description').textContent = total.description;
    }
    form.addEventListener('change', function (event) {
        markDirty();
        if (event.target.type === 'radio') refreshSummary();
    });
    form.addEventListener('input', markDirty);
    form.addEventListener('click', function (event) {
        var id = event.target.getAttribute('data-clear');
        if (!id) return;
        form.querySelectorAll('input[name="rating[' + id + ']"]').forEach(function (input) { input.checked = false; });
        markDirty();
        refreshSummary();
    });
    refreshSummary();
    form.addEventListener('submit', function () { dirty = false; });
    window.addEventListener('beforeunload', function (event) { if (dirty) { event.preventDefault(); event.returnValue = ''; } });
}());
</script>
<?php else: ?></body></html><?php endif; ?>
