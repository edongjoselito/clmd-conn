<?php
$edit = array('id' => 0, 'area_index' => 0, 'indicator' => '', 'subgroup' => '', 'sort_order' => 1, 'active' => 1);
$edit_id = (int) $this->input->get('edit');
$active_count = 0;
$by_area = array_fill(0, count($groups), array());
foreach ($indicators as $item) {
    $by_area[$item->area_index][] = $item;
    $active_count += (int) $item->active;
    if ((int) $item->id === $edit_id) { $edit = (array) $item; }
}
$previous = $this->session->flashdata('monitoring_input');
if (is_array($previous)) { $edit = array_merge($edit, $previous); }
?>
<link rel="stylesheet" href="<?= base_url('assets/css/monitoring-indicators.css'); ?>?v=<?= filemtime(FCPATH . 'assets/css/monitoring-indicators.css'); ?>">
<div class="mi-page">
    <p><a href="<?= base_url('monitoring-tool/settings/tools'); ?>">&larr; Monitoring Tools</a></p>
    <header class="mi-header">
        <div><small>MONITORING / SETTINGS</small><h1>Indicators</h1><p><?= html_escape($tool->name); ?></p></div>
        <a href="<?= base_url('monitoring-tool/settings/indicators?tool_id=' . (int) $tool->id); ?>#indicator-editor" class="btn btn-light">Add indicator</a>
    </header>
    <?php foreach (array('success', 'danger') as $type): ?>
        <?php if ($message = $this->session->flashdata($type)): ?><div class="alert alert-<?= $type; ?>" role="alert"><?= html_escape($message); ?></div><?php endif; ?>
    <?php endforeach; ?>
    <div class="mi-summary"><span><strong><?= count($groups); ?></strong> Areas of concern</span><span><strong><?= count($indicators); ?></strong> Indicators</span><span><strong><?= $active_count; ?></strong> Active</span></div>
    <p class="text-muted">Manage the indicators assessed as Evident or Not Evident, with remarks. Deactivate an indicator to retain it in settings without including it in the active list.</p>
    <?php if (!$indicators): ?><div class="alert alert-info">No indicators yet. Add the first indicator for this tool below.</div><?php endif; ?>
    <div class="row">
        <div class="col-xl-8">
            <?php foreach ($groups as $area => $group): ?>
            <section class="card mi-area">
                <div class="card-header"><h2><?= html_escape($group['name']); ?></h2><span><?= count($by_area[$area]); ?> indicators</span></div>
                <ol class="mi-indicators">
                    <?php foreach ($by_area[$area] as $item): ?>
                    <li class="<?= $item->active ? '' : 'mi-inactive'; ?>">
                        <div class="mi-indicator-copy">
                            <?php if ($item->subgroup !== ''): ?><small class="mi-subgroup"><?= html_escape($item->subgroup); ?></small><?php endif; ?>
                            <p><?= nl2br(html_escape($item->indicator)); ?></p>
                            <span class="mi-status"><?= $item->active ? 'Active' : 'Inactive'; ?></span>
                            <small class="text-muted">Order <?= (int) $item->sort_order; ?></small>
                        </div>
                        <a class="btn btn-sm btn-outline-primary" href="<?= base_url('monitoring-tool/settings/indicators?tool_id=' . (int) $tool->id . '&edit=' . (int) $item->id); ?>#indicator-editor" aria-label="Edit indicator <?= (int) $item->id; ?>">Edit</a>
                        <?= form_open('Pages/monitoring_indicator_delete', array('onsubmit' => "return confirm('Delete this indicator? Saved assessments will be retained.');")); ?>
                        <input type="hidden" name="tool_id" value="<?= (int) $tool->id; ?>"><input type="hidden" name="id" value="<?= (int) $item->id; ?>"><button class="btn btn-sm btn-outline-danger" type="submit">Delete</button><?= form_close(); ?>
                    </li>
                    <?php endforeach; ?>
                </ol>
            </section>
            <?php endforeach; ?>

        </div>
        <div class="col-xl-4">
            <section class="card mi-editor" id="indicator-editor">
                <div class="card-body">
                    <h2><?= (int) $edit['id'] ? 'Edit indicator' : 'Add indicator'; ?></h2>
                    <?= form_open('Pages/monitoring_indicator_save'); ?>
                        <input type="hidden" name="tool_id" value="<?= (int) $tool->id; ?>">
                        <input type="hidden" name="id" value="<?= (int) $edit['id']; ?>">
                        <div class="form-group"><label for="mi-area">Area of concern</label>
                            <select id="mi-area" name="area_index" class="form-control" required>
                            <?php foreach ($groups as $area => $group): ?><option value="<?= $area; ?>" <?= (int) $edit['area_index'] === $area ? 'selected' : ''; ?>><?= html_escape($group['name']); ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group"><label for="mi-subgroup">Group heading <span class="text-muted">(optional)</span></label><textarea id="mi-subgroup" name="subgroup" class="form-control" rows="2" maxlength="255"><?= html_escape($edit['subgroup']); ?></textarea></div>
                        <div class="form-group"><label for="mi-text">Indicator</label><textarea id="mi-text" name="indicator" class="form-control" rows="6" maxlength="5000" required><?= html_escape($edit['indicator']); ?></textarea></div>
                        <div class="form-group"><label for="mi-order">Display order within area</label><input id="mi-order" name="sort_order" class="form-control" type="number" min="1" max="9999" value="<?= (int) $edit['sort_order']; ?>" required></div>
                        <div class="form-group"><label for="mi-active">Status</label><select id="mi-active" name="active" class="form-control"><option value="1" <?= (int) $edit['active'] === 1 ? 'selected' : ''; ?>>Active</option><option value="0" <?= (int) $edit['active'] === 0 ? 'selected' : ''; ?>>Inactive</option></select></div>
                        <button type="submit" class="btn btn-primary">Save indicator</button>
                        <?php if ((int) $edit['id']): ?><a href="<?= base_url('monitoring-tool/settings/indicators?tool_id=' . (int) $tool->id); ?>#indicator-editor" class="btn btn-link">Cancel</a><?php endif; ?>
                    <?= form_close(); ?>
                </div>
            </section>
        </div>
    </div>
</div>
