<div class="mt-4 mb-4">
    <h1 class="h3">Edit Profile</h1>
    <?php foreach (array('success', 'danger') as $type): ?>
        <?php if ($message = $this->session->flashdata($type)): ?><div class="alert alert-<?= $type; ?>" role="status"><?= html_escape($message); ?></div><?php endif; ?>
    <?php endforeach; ?>
    <div class="card"><div class="card-body">
        <h2 class="h4">Personal details</h2>
        <p class="text-muted">Your name is used for Name and Signature of Monitor on monitoring assessments.</p>
        <?= form_open('Pages/edit_profile_save'); ?>
            <?php foreach (array('fname' => 'First name', 'mname' => 'Middle name', 'lname' => 'Last name') as $key => $label): ?>
                <div class="form-group"><label for="profile-<?= $key; ?>"><?= $label; ?></label><input class="form-control" id="profile-<?= $key; ?>" name="<?= $key; ?>" maxlength="100" value="<?= html_escape($profile_user->$key); ?>" <?= $key !== 'mname' ? 'required' : ''; ?>></div>
            <?php endforeach; ?>
            <button type="submit" class="btn btn-primary">Save profile</button>
        <?= form_close(); ?>
    </div></div>
    <?php $this->load->view('pages/_user_signature', array('profile_user' => $profile_user, 'signature' => $signature)); ?>
</div>
