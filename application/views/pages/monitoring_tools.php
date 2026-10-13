<?php
$list_path = isset($list_path) ? $list_path : 'monitoring-tool/settings/tools';
$input = $edit ? (array) $edit : array('id' => 0, 'name' => '');
$previous = $this->session->flashdata('monitoring_tool_input');
if (is_array($previous)) { $input = array_merge($input, $previous); }
$open_editor = $edit || is_array($previous);
?>
<link rel="stylesheet" href="<?= base_url('assets/css/monitoring-indicators.css'); ?>?v=<?= filemtime(FCPATH . 'assets/css/monitoring-indicators.css'); ?>">
<div class="mi-page">
    <header class="mi-header"><div><small><?= $list_path === 'monitoring/tool' ? 'MONITORING' : 'MONITORING / SETTINGS'; ?></small><h1>Monitoring Tools</h1><p>Create a tool, then manage the indicators associated with it.</p></div><button type="button" class="btn btn-light" data-toggle="modal" data-target="#tool-editor" data-tool-id="0" data-tool-name="">Add tool</button></header>
    <?php foreach (array('success', 'danger') as $type): ?>
        <?php if ($message = $this->session->flashdata($type)): ?><div class="alert alert-<?= $type; ?>" role="alert"><?= html_escape($message); ?></div><?php endif; ?>
    <?php endforeach; ?>
    <section class="card"><div class="card-body"><h2>Available tools</h2>
            <div class="table-responsive"><table class="table"><thead><tr><th>Tool name</th><th>Actions</th></tr></thead><tbody>
            <?php foreach ($tools as $tool): ?><tr>
                <td><a href="<?= base_url(($list_path === 'monitoring/tool' ? 'monitoring/tool?tool_id=' : 'monitoring-tool/settings/indicators?tool_id=') . (int) $tool->id); ?>"><?= html_escape($tool->name); ?></a></td>
                <td><div class="d-flex flex-wrap" style="gap: 8px">
                    <a class="btn btn-sm btn-primary" href="<?= base_url('monitoring-tool/settings/indicators?tool_id=' . (int) $tool->id); ?>">Indicators</a>
                    <a class="btn btn-sm btn-outline-primary" href="<?= base_url('monitoring/tool?tool_id=' . (int) $tool->id); ?>">View tool</a>
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-toggle="modal" data-target="#tool-editor" data-tool-id="<?= (int) $tool->id; ?>" data-tool-name="<?= html_escape($tool->name); ?>">Edit</button>
                    <?= form_open('Pages/monitoring_tool_delete', array('onsubmit' => "return confirm('Remove this monitoring tool and its indicators from use? Saved assessments will remain available.');")); ?>
                    <input type="hidden" name="return_to" value="<?= html_escape($list_path); ?>">
                    <input type="hidden" name="id" value="<?= (int) $tool->id; ?>"><button class="btn btn-sm btn-outline-danger" type="submit">Delete</button><?= form_close(); ?>
                </div></td>
            </tr><?php endforeach; ?>
            <?php if (!$tools): ?><tr><td colspan="2">No monitoring tools yet. Add a tool to get started.</td></tr><?php endif; ?>
            </tbody></table></div>
        </div></section>
</div>
<div class="modal fade" id="tool-editor" tabindex="-1" role="dialog" aria-labelledby="tool-editor-title" aria-hidden="true" data-open-editor="<?= $open_editor ? '1' : '0'; ?>">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <?= form_open('Pages/monitoring_tool_save', array('id' => 'tool-editor-form')); ?>
                <div class="modal-header">
                    <h5 class="modal-title" id="tool-editor-title"><?= (int) $input['id'] ? 'Edit tool' : 'Add tool'; ?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <?php if (is_array($previous)): ?><div class="alert alert-danger" role="alert" id="tool-editor-error">Please enter a tool name of up to 255 characters.</div><?php endif; ?>
                    <input type="hidden" name="return_to" value="<?= html_escape($list_path); ?>">
                    <input type="hidden" name="id" id="tool-editor-id" value="<?= (int) $input['id']; ?>">
                    <div class="form-group mb-0"><label for="tool-name">Tool name <span class="text-danger" aria-hidden="true">*</span></label><input id="tool-name" name="name" class="form-control" maxlength="255" value="<?= html_escape($input['name']); ?>" placeholder="Enter monitoring tool name" required></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="tool-editor-save"><?= (int) $input['id'] ? 'Save changes' : 'Create tool'; ?></button>
                </div>
            <?= form_close(); ?>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var modal = document.getElementById('tool-editor');
    var name = document.getElementById('tool-name');
    var id = document.getElementById('tool-editor-id');
    jQuery(modal).on('show.bs.modal', function (event) {
        var trigger = event.relatedTarget;
        if (!trigger) return;
        id.value = trigger.getAttribute('data-tool-id');
        name.value = trigger.getAttribute('data-tool-name');
        name.setCustomValidity('');
        var editing = id.value !== '0';
        document.getElementById('tool-editor-title').textContent = editing ? 'Edit tool' : 'Add tool';
        document.getElementById('tool-editor-save').textContent = editing ? 'Save changes' : 'Create tool';
        var error = document.getElementById('tool-editor-error');
        if (error) error.hidden = true;
    }).on('shown.bs.modal', function () { name.focus(); });
    name.addEventListener('input', function () { name.setCustomValidity(''); });
    document.getElementById('tool-editor-form').addEventListener('submit', function (event) {
        if (!name.value.trim()) {
            event.preventDefault();
            name.setCustomValidity('Enter a tool name.');
            name.reportValidity();
        }
    });
    if (modal.getAttribute('data-open-editor') === '1') jQuery(modal).modal('show');
});
</script>
