<section class="card" id="e-signature"><div class="card-body">
    <h2 class="h4">E-signature</h2>
    <p class="text-muted">Upload a PNG or JPEG image, up to 1 MB. A transparent PNG works best. The signature is included when this user saves a monitoring assessment.</p>
    <?php if (!empty($signature) && preg_match('#^data:image/png;base64,[A-Za-z0-9+/=]+$#D', $signature)): ?>
        <div class="mb-3"><img src="<?= html_escape($signature); ?>" alt="Saved e-signature" style="max-width:280px;max-height:120px;object-fit:contain;background:#fff;border:1px solid #ead7dc;padding:10px"></div>
    <?php else: ?><p>No e-signature uploaded yet.</p><?php endif; ?>
    <?= form_open_multipart('Pages/user_signature_save'); ?>
        <input type="hidden" name="user_id" value="<?= (int) $profile_user->id; ?>">
        <div class="form-group"><label for="profile-signature">Signature image</label><input type="file" class="form-control-file" id="profile-signature" name="signature" accept="image/png,image/jpeg" required></div>
        <button type="submit" class="btn btn-primary"><?= empty($signature) ? 'Upload e-signature' : 'Replace e-signature'; ?></button>
    <?= form_close(); ?>
</div></section>
