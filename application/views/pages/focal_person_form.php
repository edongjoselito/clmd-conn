<link rel="stylesheet" href="<?= base_url('assets/css/focal-persons.css'); ?>">
<section class="focal-directory">
<header class="focal-heading"><div><span class="focal-eyebrow">CLMD DIRECTORY</span><h1><?= $person ? 'Edit' : 'Add'; ?> Focal Person</h1><p>Keep your division contacts and profile pictures up to date.</p></div><a class="btn btn-outline-secondary" href="<?= base_url('focal-persons'); ?>">Back to directory</a></header>
<div class="card"><div class="card-body">
<?= validation_errors('<div class="alert alert-danger" role="alert">', '</div>'); ?>
<?php if ($error): ?><div class="alert alert-danger" role="alert"><?= html_escape($error); ?></div><?php endif; ?>
<?= form_open_multipart($person ? 'focal-persons/' . $person->id . '/edit' : 'focal-persons/new'); ?>
<div class="row">
<div class="col-md-6 form-group"><label for="focal-name">Focal Person <span class="text-danger">*</span></label><input id="focal-name" class="form-control" name="name" value="<?= set_value('name', $person ? $person->name : ''); ?>" maxlength="150" required autocomplete="name"></div>
<div class="col-md-6 form-group"><label for="focal-position">Position <span class="text-danger">*</span></label><input id="focal-position" class="form-control" name="position" value="<?= set_value('position', $person ? $person->position : ''); ?>" maxlength="150" required></div>
<div class="col-md-6 form-group"><label for="focal-division">Division <span class="text-danger">*</span></label><select id="focal-division" class="form-control" name="division_id" required><option value="">Select a division</option><?php foreach ($divisions as $division): ?><option value="<?= (int) $division->id; ?>" <?= set_select('division_id', (string) $division->id, $person && (int) $person->division_id === (int) $division->id); ?>><?= html_escape($division->description); ?></option><?php endforeach; ?></select></div>
<div class="col-md-6 form-group"><label for="focal-area">Learning Area <span class="text-danger">*</span></label><select id="focal-area" class="form-control" name="learning_area" required><option value="">Select a learning area</option><?php foreach ($areas as $slug => $area): ?><option value="<?= html_escape($slug); ?>" <?= set_select('learning_area', $slug, $person && $person->learning_area === $slug); ?>><?= html_escape($area['name']); ?></option><?php endforeach; ?></select></div>
<div class="col-md-6 form-group"><label for="focal-picture">Profile Picture</label><input id="focal-picture" class="form-control-file" name="picture" type="file" accept="image/jpeg,image/png" aria-describedby="picture-help"><small id="picture-help" class="form-text text-muted">Optional PNG or JPEG, up to 2 MB, 4000 pixels per side and 8 megapixels. Leave blank to keep the existing picture.</small></div>
</div>
<?php if ($person && $person->picture): ?><div class="form-group"><img class="focal-avatar" src="<?= html_escape($person->picture); ?>" alt="Current profile picture"><label class="d-block mt-2"><input type="checkbox" name="remove_picture" value="1" <?= set_checkbox('remove_picture', '1'); ?>> Remove current picture</label></div><?php endif; ?>
<?php if (!$divisions): ?><div class="alert alert-warning">No divisions are configured for your region.</div><?php endif; ?>
<button type="submit" class="btn btn-primary" <?= !$divisions ? 'disabled' : ''; ?>><?= $person ? 'Save Changes' : 'Add Focal Person'; ?></button> <a href="<?= base_url('focal-persons'); ?>" class="btn btn-light">Cancel</a>
<?= form_close(); ?>
</div></div></section>
