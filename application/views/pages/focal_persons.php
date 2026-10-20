<link rel="stylesheet" href="<?= base_url('assets/css/focal-persons.css'); ?>">
<section class="focal-directory">
    <header class="focal-heading"><div><span class="focal-eyebrow">CLMD DIRECTORY</span><h1>Focal Persons</h1><p><?= $can_manage ? 'Manage focal persons across the divisions in your region.' : ($division_scoped ? 'View focal persons for your learning area in your assigned division.' : 'View focal persons for your learning area across all divisions in your region.'); ?></p></div>
    <?php if ($can_manage): ?><a class="btn btn-primary" href="<?= base_url('focal-persons/new'); ?>">+ Add Focal Person</a><?php else: ?><span class="badge badge-light p-2">View only</span><?php endif; ?></header>
    <?php if ($this->session->flashdata('success')): ?><div class="alert alert-success" role="status"><?= html_escape($this->session->flashdata('success')); ?></div><?php endif; ?>
    <?php if ($can_manage): ?>
    <form method="get" action="<?= base_url('focal-persons'); ?>" class="card card-body mb-3">
        <div class="row align-items-end">
            <div class="col-md-5 form-group"><label for="directory-division">Division</label><select id="directory-division" name="division_id" class="form-control"><option value="0">All divisions</option><?php foreach ($divisions as $division): ?><option value="<?= (int) $division->id; ?>" <?= $selected_division === (int) $division->id ? 'selected' : ''; ?>><?= html_escape($division->description); ?></option><?php endforeach; ?></select></div>
            <div class="col-md-5 form-group"><label for="directory-area">Learning Area</label>
            <?php if ($can_manage): ?><select id="directory-area" name="learning_area" class="form-control"><option value="">All learning areas</option><?php foreach ($areas as $slug => $area): ?><option value="<?= html_escape($slug); ?>" <?= $selected_area === $slug ? 'selected' : ''; ?>><?= html_escape($area['name']); ?></option><?php endforeach; ?></select>
            <?php else: ?><input id="directory-area" class="form-control" value="<?= html_escape($areas[$selected_area]['name']); ?>" readonly><?php endif; ?></div>
            <div class="col-md-2 form-group"><button type="submit" class="btn btn-primary">Apply filters</button></div>
        </div>
        <?php if ($can_manage): ?><small class="text-muted">Existing focal persons without a learning area remain visible under All learning areas. Edit their records to assign a subject before learning-area users can view them.</small><?php endif; ?>
    </form>
    <?php endif; ?>
    <p class="text-muted"><?= number_format(count($people)); ?> focal person<?= count($people) === 1 ? '' : 's'; ?></p>
    <?php if (!$people): ?><div class="focal-empty"><h2>No matching focal persons</h2><p><?= $can_manage ? 'Add a focal person or adjust the selected filters.' : 'Focal persons will appear once regional users assign them to your learning area.'; ?></p></div><?php endif; ?>
    <div class="focal-grid">
    <?php foreach ($people as $person): ?><article class="focal-card">
        <?php if ($person->picture): ?><img class="focal-avatar" src="<?= html_escape($person->picture); ?>" alt="Profile picture of <?= html_escape($person->name); ?>" loading="lazy"><?php else: ?><div class="focal-avatar focal-placeholder" aria-hidden="true"><i class="mdi mdi-account-outline"></i></div><?php endif; ?>
        <h2><?= html_escape($person->name); ?></h2><p class="focal-position"><?= html_escape($person->position); ?></p><p class="focal-position"><?= html_escape(isset($areas[$person->learning_area]) ? $areas[$person->learning_area]['name'] : 'Learning area not assigned'); ?></p><p class="focal-division"><?= html_escape($person->division_name); ?></p>
        <?php if ($can_manage): ?><div class="focal-actions"><a class="btn btn-outline-primary btn-sm" href="<?= base_url('focal-persons/' . $person->id . '/edit'); ?>" aria-label="Edit <?= html_escape($person->name); ?>">Edit</a>
        <?= form_open('focal-persons/' . $person->id . '/delete', array('onsubmit' => "return confirm('Delete this focal person? This cannot be undone.');")); ?><button class="btn btn-outline-danger btn-sm" type="submit" aria-label="Delete <?= html_escape($person->name); ?>">Delete</button><?= form_close(); ?></div><?php endif; ?>
    </article><?php endforeach; ?>
    </div>
</section>
