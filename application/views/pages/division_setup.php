<?php
$region_name = !empty($region) ? $region->description : 'Not assigned';
$encoded_total = isset($division->total_schools) ? $division->total_schools : '';
$signup_rate = !empty($encoded_total) ? ((int) $actual_school_count / (int) $encoded_total) * 100 : 0;
?>

<style>
    .division-setup-page {
        --setup-primary: var(--llcm-blue, #922c43);
        --setup-primary-dark: var(--llcm-navy, #3b0718);
        --setup-border: var(--llcm-border, #ead7dc);
        --setup-muted: #6b7280;
    }

    .division-setup-page .page-title-box {
        min-height: 0;
        margin-bottom: 8px;
        padding: 0;
    }

    .division-setup-hero {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 24px;
        margin: 8px 0 22px;
        padding: 28px;
        border-radius: 18px;
        color: #fff;
        background:
            radial-gradient(circle at 88% 18%, rgba(255, 255, 255, .22), transparent 25%),
            linear-gradient(135deg, var(--setup-primary-dark) 0%, var(--setup-primary) 100%);
        box-shadow: 0 14px 34px rgba(20, 62, 94, .16);
    }

    .division-setup-hero h2 {
        margin: 0 0 6px;
        color: #fff;
        font-size: 25px;
        font-weight: 700;
    }

    .division-setup-hero p {
        margin: 0;
        color: rgba(255, 255, 255, .84);
        max-width: 650px;
    }

    .division-setup-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 11px 15px;
        border: 1px solid rgba(255, 255, 255, .24);
        border-radius: 999px;
        color: #fff;
        background: rgba(255, 255, 255, .14);
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
        backdrop-filter: blur(5px);
    }

    .division-setup-card {
        margin-bottom: 22px;
        border: 1px solid var(--setup-border);
        border-radius: 18px;
        box-shadow: 0 10px 26px rgba(31, 45, 75, .06);
        overflow: hidden;
    }

    .division-setup-card .card-body {
        padding: 26px;
    }

    .division-setup-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .division-setup-stat {
        padding: 18px;
        border: 1px solid var(--setup-border);
        border-radius: 16px;
        background: #fbfcff;
    }

    .division-setup-stat span {
        display: block;
        color: var(--setup-muted);
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .05em;
    }

    .division-setup-stat strong {
        display: block;
        margin-top: 10px;
        color: #233342;
        font-size: 28px;
        line-height: 1;
    }

    .division-setup-form label {
        color: #233342;
        font-weight: 700;
    }

    .division-setup-form .form-control {
        min-height: 46px;
        border-color: var(--setup-border);
        border-radius: 12px;
        box-shadow: none;
    }

    .division-setup-help {
        margin-top: 8px;
        color: var(--setup-muted);
        font-size: 12px;
    }

    .division-setup-actions {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-top: 24px;
    }

    .division-setup-note {
        padding: 16px 18px;
        border: 1px solid var(--setup-border);
        border-radius: 16px;
        color: var(--setup-primary);
        background: #f5faff;
        font-size: 13px;
    }

    @media (max-width: 991.98px) {
        .division-setup-hero {
            flex-direction: column;
            align-items: flex-start;
        }

        .division-setup-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="division-setup-page">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <?php if ($this->session->flashdata('success')) : ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <?= $this->session->flashdata('success'); ?>
                    </div>
                <?php endif; ?>

                <?php if ($this->session->flashdata('danger')) : ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <?= $this->session->flashdata('danger'); ?>
                    </div>
                <?php endif; ?>

                <?= validation_errors(); ?>
                <?php if (!empty($upload_error)) : ?>
                    <div class="alert alert-danger" role="alert">
                        <?= html_escape($upload_error); ?>
                    </div>
                <?php endif; ?>
                <div class="clearfix"></div>
            </div>
        </div>
    </div>

    <div class="division-setup-hero">
        <div>
            <h2><?= html_escape($division->description); ?></h2>
            <p>Maintain the core setup for your division, including the official total number of schools for planning and monitoring.</p>
        </div>
        <div class="division-setup-badge">
            <i class="mdi mdi-office-building"></i>
            <?= html_escape($region_name); ?>
        </div>
    </div>

    <div class="card division-setup-card">
        <div class="card-body">
            <div class="division-setup-grid">
                <div class="division-setup-stat">
                    <span>Division ID</span>
                    <strong><?= (int) $division->id; ?></strong>
                </div>
                <div class="division-setup-stat">
                    <span>Districts</span>
                    <strong><?= (int) $district_count; ?></strong>
                </div>
                <div class="division-setup-stat">
                    <span>Registered Schools</span>
                    <strong><?= (int) $actual_school_count; ?></strong>
                </div>
                <div class="division-setup-stat">
                    <span>Signup Coverage</span>
                    <strong><?= number_format($signup_rate, 1); ?>%</strong>
                </div>
            </div>

            <div class="division-setup-note">
                Use the encoded total below when your official division total is different from the currently registered school accounts in the system.
            </div>

            <?php echo form_open_multipart('pages/division_setup', array('class' => 'parsley-examples division-setup-form')); ?>
                <div class="form-group mt-4">
                    <label for="division-name">Division Name <span class="text-danger">*</span></label>
                    <input
                        type="text"
                        id="division-name"
                        name="description"
                        class="form-control"
                        value="<?= html_escape(set_value('description', $division->description)); ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="total-schools">Total Number of Schools <span class="text-danger">*</span></label>
                    <input
                        type="number"
                        min="0"
                        step="1"
                        id="total-schools"
                        name="total_schools"
                        class="form-control"
                        value="<?= html_escape(set_value('total_schools', $encoded_total)); ?>"
                    >
                    <div class="division-setup-help">
                        Current registered schools in the system: <strong><?= (int) $actual_school_count; ?></strong>
                    </div>
                </div>

                <div class="form-group">
                    <label for="division-region">Region</label>
                    <input
                        type="text"
                        id="division-region"
                        class="form-control"
                        value="<?= html_escape($region_name); ?>"
                        readonly
                    >
                </div>

                <div class="form-group">
                    <label for="homepage-logo">Official Division Logo</label>
                    <div class="d-flex align-items-center flex-wrap" style="gap: 16px;">
                        <div style="width: 88px; height: 88px; border: 1px solid #dce6ef; border-radius: 16px; display: grid; place-items: center; overflow: hidden; background: #f8fafc;">
                            <?php if (!empty($division->homepage_logo)) : ?>
                                <img src="<?= html_escape(base_url($division->homepage_logo)); ?>" alt="<?= html_escape($division->description); ?> logo" style="width: 100%; height: 100%; object-fit: contain; padding: 7px;">
                            <?php else : ?>
                                <span style="color: var(--setup-primary); font-size: 26px; font-weight: 800;"><?= html_escape(strtoupper(substr(trim($division->description), 0, 1))); ?></span>
                            <?php endif; ?>
                        </div>
                        <div style="min-width: min(100%, 320px); flex: 1;">
                            <input
                                type="file"
                                id="homepage-logo"
                                name="homepage_logo"
                                class="form-control"
                                accept="image/png,image/jpeg"
                            >
                            <div class="division-setup-help">
                                PNG or JPG, up to 2 MB. Once saved, this logo appears automatically on the public homepage.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="division-setup-actions">
                    <button type="submit" class="btn btn-primary waves-effect waves-light">
                        Save Division Setup
                    </button>
                    <a href="<?= base_url(); ?>" class="btn btn-light waves-effect">
                        Back to Dashboard
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
