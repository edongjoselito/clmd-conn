<?php
$district_name = !empty($district) ? trim((string) $district->description) : 'District';
$division_name = !empty($division) ? trim((string) $division->description) : 'Division';
?>

<style>
    .district-form-page {
        --district-primary: #8b1e3f;
        --district-primary-dark: #64142d;
        --district-primary-soft: #f9e9ee;
        --district-border: #e8ecf4;
        --district-text: #27324a;
        --district-muted: #6b7280;
        --district-surface: #ffffff;
        --district-surface-alt: #fbfcff;
        --district-shadow: 0 14px 34px rgba(139, 30, 63, .18);
    }

    .district-form-hero {
        position: relative;
        margin: 18px 0 22px;
        padding: 28px;
        border-radius: 20px;
        overflow: hidden;
        color: #fff;
        background:
            radial-gradient(circle at top right, rgba(255, 255, 255, .18), transparent 28%),
            linear-gradient(135deg, #64142d 0%, #a83255 55%, #d46d86 100%);
        box-shadow: var(--district-shadow);
    }

    .district-form-hero::after {
        content: "";
        position: absolute;
        inset: auto -60px -90px auto;
        width: 220px;
        height: 220px;
        border-radius: 50%;
        background: rgba(255, 255, 255, .08);
    }

    .district-form-hero > * {
        position: relative;
        z-index: 1;
    }

    .district-form-hero h2 {
        margin: 0 0 8px;
        color: #fff;
        font-size: 27px;
        font-weight: 700;
        line-height: 1.2;
    }

    .district-form-hero p {
        margin: 0;
        max-width: 760px;
        color: rgba(255, 255, 255, .84);
        font-size: 14px;
    }

    .district-form-card {
        border: 1px solid var(--district-border);
        border-radius: 18px;
        background: var(--district-surface);
        box-shadow: 0 10px 30px rgba(31, 45, 75, .08);
        overflow: hidden;
    }

    .district-form-card .card-body {
        padding: 0;
    }

    .district-form-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 22px 24px 16px;
        border-bottom: 1px solid var(--district-border);
        background: linear-gradient(180deg, #fff 0%, #fdfcff 100%);
    }

    .district-form-header h4 {
        margin: 0 0 5px;
        color: var(--district-text);
        font-size: 18px;
        font-weight: 700;
    }

    .district-form-header p {
        margin: 0;
        color: var(--district-muted);
        font-size: 13px;
    }

    .district-form-body {
        padding: 24px;
    }

    .district-form-group label {
        display: block;
        margin-bottom: 8px;
        color: var(--district-text);
        font-size: 13px;
        font-weight: 600;
    }

    .district-form-group .form-control {
        padding: 12px 16px;
        border: 1px solid var(--district-border);
        border-radius: 10px;
        font-size: 14px;
        transition: all 0.2s ease;
    }

    .district-form-group .form-control:focus {
        border-color: var(--district-primary);
        box-shadow: 0 0 0 3px rgba(139, 30, 63, .1);
    }

    .district-form-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 12px;
        margin-top: 24px;
        padding-top: 24px;
        border-top: 1px solid var(--district-border);
    }

    .district-form-actions .btn {
        padding: 10px 24px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 600;
    }

    .district-form-page .alert {
        border: 0;
        border-radius: 13px;
        box-shadow: 0 8px 20px rgba(31, 45, 75, .08);
    }
</style>

<div class="district-form-page">
    <div class="row">
        <div class="col-12">
            <div class="district-form-hero">
                <div>
                    <h2><i class="mdi mdi-pencil-outline mr-2"></i>Update District</h2>
                    <p>Modify district information for <?= html_escape(mb_convert_case($district_name, MB_CASE_TITLE, 'UTF-8')); ?> under <?= html_escape(mb_convert_case($division_name, MB_CASE_TITLE, 'UTF-8')); ?>.</p>
                </div>
            </div>

            <?php if($this->session->flashdata('success')) : ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <?= $this->session->flashdata('success'); ?>
                </div>
            <?php endif; ?>

            <?php if($this->session->flashdata('danger')) : ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <?= $this->session->flashdata('danger'); ?>
                </div>
            <?php endif; ?>

            <?= validation_errors(); ?>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card district-form-card">
                <div class="card-body">
                    <div class="district-form-header">
                        <div>
                            <h4>District Information</h4>
                            <p>Update the district name below.</p>
                        </div>
                    </div>

                    <div class="district-form-body">
                        <?php $att = array('class' => 'parsley-examples'); ?>
                        <?= form_open('pages/district_update', $att); ?>

                            <input type="hidden" name="id" value="<?= $district->id; ?>">
                            <input type="hidden" name="division_id" value="<?= $division->id; ?>">

                            <div class="district-form-group">
                                <label for="description">District Name</label>
                                <input type="text" id="description" required class="form-control" name="description" value="<?= html_escape($district->description); ?>" placeholder="Enter district name">
                            </div>

                            <div class="district-form-actions">
                                <a href="<?= base_url(); ?>pages/district_account/<?= $this->session->division; ?>" class="btn btn-secondary">
                                    <i class="mdi mdi-arrow-left mr-1"></i>
                                    Cancel
                                </a>
                                <button type="submit" class="btn btn-primary" style="background: var(--district-primary); border-color: var(--district-primary);">
                                    <i class="mdi mdi-content-save mr-1"></i>
                                    Update District
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
