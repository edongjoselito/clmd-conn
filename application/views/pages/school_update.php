<style>
    .school-update-page {
        --school-primary: var(--llcm-blue, #922c43);
        --school-primary-light: var(--llcm-blue, #922c43);
        --school-navy: var(--llcm-navy, #3b0718);
        --school-sky: var(--llcm-sky, #fbf4f5);
        --school-ink: var(--llcm-ink, #233342);
        --school-muted: #6b7f92;
        --school-border: var(--llcm-border, #ead7dc);
        --school-surface: #f7fafc;
        padding-bottom: 2rem;
    }

    .school-update-page .school-hero {
        display: flex;
        flex-wrap: wrap;
        gap: 14px 18px;
        align-items: center;
        justify-content: space-between;
        margin: 1rem 0 1.25rem;
        padding: 1.15rem 1.4rem;
        border-radius: 10px;
        color: #fff;
        background: linear-gradient(118deg, var(--school-navy), var(--school-primary));
        box-shadow: 0 4px 16px rgba(20, 62, 94, .05);
    }

    .school-update-page .hero-content {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1.5rem;
        width: 100%;
    }

    .school-update-page .hero-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        margin-bottom: .3rem;
        font-size: .72rem;
        font-weight: 600;
        letter-spacing: .09em;
        text-transform: uppercase;
        color: rgba(255, 255, 255, .75);
    }

    .school-update-page .school-hero h2 {
        margin: 0;
        color: #fff;
        font-size: 1.45rem;
        font-weight: 600;
        line-height: 1.25;
    }

    .school-update-page .school-hero p {
        max-width: 650px;
        margin: .3rem 0 0;
        color: rgba(255, 255, 255, .85);
        font-size: .85rem;
    }

    .school-update-page .school-id-badge {
        flex: 0 0 auto;
        padding: .5rem .95rem;
        border: 1px solid rgba(255, 255, 255, .3);
        border-radius: 8px;
        background: rgba(255, 255, 255, .1);
        text-align: center;
    }

    .school-update-page .school-id-badge small {
        display: block;
        margin-bottom: .1rem;
        font-size: .64rem;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: rgba(255, 255, 255, .7);
    }

    .school-update-page .school-id-badge strong {
        font-size: .95rem;
        color: #fff;
    }

    .school-update-page .update-card {
        overflow: hidden;
        border: 1px solid var(--school-border);
        border-radius: 10px;
        background: #fff;
        box-shadow: 0 4px 16px rgba(20, 62, 94, .05);
    }

    .school-update-page .form-section {
        padding: 1.3rem 1.4rem;
        border-bottom: 1px solid var(--school-border);
    }

    .school-update-page .section-heading {
        display: flex;
        align-items: center;
        gap: .7rem;
        margin-bottom: 1.1rem;
    }

    .school-update-page .section-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        flex: 0 0 32px;
        border-radius: 8px;
        color: var(--school-primary);
        background: var(--school-sky);
        font-size: 1rem;
    }

    .school-update-page .section-heading h4 {
        margin: 0 0 .1rem;
        color: var(--school-navy);
        font-size: .98rem;
        font-weight: 600;
    }

    .school-update-page .section-heading p {
        margin: 0;
        color: var(--school-muted);
        font-size: .8rem;
    }

    .school-update-page .form-group {
        margin-bottom: 1rem;
    }

    .school-update-page label {
        margin-bottom: .4rem;
        color: var(--school-navy);
        font-size: .78rem;
        font-weight: 600;
    }

    .school-update-page .form-control {
        min-height: 38px;
        border: 1px solid var(--school-border);
        border-radius: 8px;
        color: var(--school-ink);
        background-color: #fbfdff;
        box-shadow: none;
        font-size: .87rem;
        transition: border-color .15s ease, box-shadow .15s ease, background-color .15s ease;
    }

    .school-update-page .form-control:focus {
        border-color: var(--school-primary);
        background-color: #fff;
        box-shadow: 0 0 0 .15rem rgba(146, 44, 67, .16);
    }

    .school-update-page select.form-control {
        cursor: pointer;
    }

    .school-update-page .field-note {
        display: block;
        margin-top: .3rem;
        color: var(--school-muted);
        font-size: .72rem;
    }

    .school-update-page .form-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 1.4rem;
        background: var(--school-surface);
    }

    .school-update-page .action-note {
        margin: 0;
        color: var(--school-muted);
        font-size: .78rem;
    }

    .school-update-page .btn-school-secondary,
    .school-update-page .btn-school-primary {
        min-width: 110px;
        padding: .5rem 1rem;
        border-radius: 8px;
        font-size: .85rem;
        font-weight: 600;
        transition: background .15s ease, border-color .15s ease, color .15s ease;
    }

    .school-update-page .btn-school-secondary {
        border: 1px solid var(--school-border);
        color: #475467;
        background: #fff;
    }

    .school-update-page .btn-school-secondary:hover {
        border-color: var(--school-primary);
        color: var(--school-navy);
    }

    .school-update-page .btn-school-primary {
        border: 1px solid var(--school-primary);
        color: #fff;
        background: var(--school-primary);
    }

    .school-update-page .btn-school-primary:hover {
        border-color: var(--school-navy);
        color: #fff;
        background: var(--school-navy);
    }

    .school-update-page .alert {
        border: 0;
        border-radius: 10px;
        box-shadow: 0 4px 16px rgba(20, 62, 94, .05);
    }

    @media (max-width: 767.98px) {
        .school-update-page .school-hero {
            padding: 1rem 1.1rem;
        }

        .school-update-page .school-hero h2 {
            font-size: 1.25rem;
        }

        .school-update-page .hero-content {
            align-items: flex-start;
            flex-direction: column;
        }

        .school-update-page .school-id-badge {
            text-align: left;
        }

        .school-update-page .form-section {
            padding: 1.1rem 1rem;
        }

        .school-update-page .form-actions {
            align-items: stretch;
            flex-direction: column;
            padding: 1rem;
        }

        .school-update-page .action-buttons {
            display: flex;
            gap: .65rem;
        }

        .school-update-page .action-buttons .btn {
            min-width: 0;
            flex: 1;
        }
    }
</style>

<div class="school-update-page">
    <div class="school-hero">
        <div class="hero-content">
            <div>
                <div class="hero-eyebrow">
                    <i class="mdi mdi-school-outline"></i>
                    School profile
                </div>
                <h2>Edit School Information</h2>
                <p>Keep the school's identity, leadership, location, and governance details accurate and up to date.</p>
            </div>
            <div class="school-id-badge">
                <small>School ID</small>
                <strong><?= html_escape($data->schoolID); ?></strong>
            </div>
        </div>
    </div>

    <?php if ($this->session->flashdata('success')) : ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <i class="mdi mdi-check-circle-outline mr-1"></i>
            <?= $this->session->flashdata('success'); ?>
        </div>
    <?php endif; ?>

    <?php if ($this->session->flashdata('danger')) : ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <i class="mdi mdi-alert-circle-outline mr-1"></i>
            <?= $this->session->flashdata('danger'); ?>
        </div>
    <?php endif; ?>

    <?= validation_errors(); ?>

    <div class="update-card">
        <?php $att = array('class' => 'parsley-examples', 'id' => 'school-update-form'); ?>
        <?= form_open('Pages/school_update', $att); ?>
            <input type="hidden" name="recID" value="<?= html_escape($data->recID); ?>">
            <input type="hidden" name="old_schoolID" value="<?= html_escape($data->schoolID); ?>">

            <section class="form-section">
                <div class="section-heading">
                    <span class="section-icon"><i class="mdi mdi-domain"></i></span>
                    <div>
                        <h4>School Identity</h4>
                        <p>Basic information and administrative assignment</p>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-lg-6">
                        <label for="schoolID">School ID</label>
                        <input type="text" class="form-control" id="schoolID" name="schoolID"
                               value="<?= html_escape($data->schoolID); ?>" required>
                        <small class="field-note">Changing the School ID will update it in the users table as well.</small>
                    </div>
                    <div class="form-group col-lg-6">
                        <label for="schoolName">School Name</label>
                        <input type="text" class="form-control" id="schoolName" name="schoolName"
                               value="<?= html_escape($data->schoolName); ?>" required>
                    </div>
                    <div class="form-group col-md-6 col-lg-3">
                        <label for="division">Division</label>
                        <select name="division_id" id="division" class="form-control">
                            <option value="">Select Division</option>
                            <?php foreach ($division as $row) : ?>
                                <option value="<?= html_escape($row->id); ?>" <?= $data->division_id == $row->id ? 'selected' : ''; ?>>
                                    <?= html_escape($row->description); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-md-6 col-lg-3">
                        <label for="district">District / Cluster</label>
                        <select name="d_id" id="district" class="form-control">
                            <option value="">Select District / Cluster</option>
                            <?php if (isset($districts)) : ?>
                                <?php foreach ($districts as $row) : ?>
                                    <option value="<?= html_escape($row->id); ?>" <?= $data->district_id == $row->id ? 'selected' : ''; ?>>
                                        <?= html_escape($row->description); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
            </section>

            <section class="form-section">
                <div class="section-heading">
                    <span class="section-icon"><i class="mdi mdi-account-tie"></i></span>
                    <div>
                        <h4>School Head</h4>
                        <p>Leadership and primary contact information</p>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="adminFName">First Name</label>
                        <input type="text" class="form-control" id="adminFName" name="adminFName"
                               value="<?= html_escape($data->adminFName); ?>" placeholder="First name">
                    </div>
                    <div class="form-group col-md-4">
                        <label for="adminMName">Middle Name</label>
                        <input type="text" class="form-control" id="adminMName" name="adminMName"
                               value="<?= html_escape($data->adminMName); ?>" placeholder="Middle name">
                    </div>
                    <div class="form-group col-md-4">
                        <label for="adminLName">Last Name</label>
                        <input type="text" class="form-control" id="adminLName" name="adminLName"
                               value="<?= html_escape($data->adminLName); ?>" placeholder="Last name">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6 col-lg-3">
                        <label for="adminDesignation">Designation</label>
                        <input type="text" class="form-control" id="adminDesignation" name="adminDesignation"
                               value="<?= html_escape($data->adminDesignation); ?>">
                    </div>
                    <div class="form-group col-md-6 col-lg-3">
                        <label for="adminEmail">School Head Email</label>
                        <input type="email" class="form-control" id="adminEmail" name="adminEmail"
                               value="<?= html_escape($data->adminEmail); ?>" placeholder="name@example.com">
                    </div>
                    <div class="form-group col-md-6 col-lg-3">
                        <label for="schoolEmail">School Email</label>
                        <input type="email" class="form-control" id="schoolEmail" name="schoolEmail"
                               value="<?= html_escape($data->schoolEmail); ?>" placeholder="school@example.com">
                    </div>
                    <div class="form-group col-md-6 col-lg-3">
                        <label for="adminMobile">Contact Number(s)</label>
                        <input type="text" class="form-control" id="adminMobile" name="adminMobile"
                               value="<?= html_escape($data->adminMobile); ?>" placeholder="e.g. 09XX XXX XXXX">
                    </div>
                </div>
            </section>

            <section class="form-section">
                <div class="section-heading">
                    <span class="section-icon"><i class="mdi mdi-map-marker-outline"></i></span>
                    <div>
                        <h4>School Location</h4>
                        <p>Complete physical address of the school</p>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6 col-lg-3">
                        <label for="province">Province</label>
                        <input type="text" class="form-control" id="province" name="province"
                               value="<?= html_escape($data->province); ?>">
                    </div>
                    <div class="form-group col-md-6 col-lg-3">
                        <label for="city">City / Municipality</label>
                        <input type="text" class="form-control" id="city" name="city"
                               value="<?= html_escape($data->city); ?>">
                    </div>
                    <div class="form-group col-md-6 col-lg-3">
                        <label for="brgy">Barangay</label>
                        <input type="text" class="form-control" id="brgy" name="brgy"
                               value="<?= html_escape($data->brgy); ?>">
                    </div>
                    <div class="form-group col-md-6 col-lg-3">
                        <label for="sitio">Sitio</label>
                        <input type="text" class="form-control" id="sitio" name="sitio"
                               value="<?= html_escape($data->sitio); ?>">
                    </div>
                </div>
            </section>

            <section class="form-section">
                <div class="section-heading">
                    <span class="section-icon"><i class="mdi mdi-shield-check-outline"></i></span>
                    <div>
                        <h4>Governance & Offerings</h4>
                        <p>School classification, programs, and governance status</p>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-lg-4">
                        <label for="sgc">School Governance Council (SGC)</label>
                        <select name="sgc" id="sgc" required class="form-control">
                            <option value="" disabled>Select SGC status</option>
                            <option value="1" <?= $data->sgc == 1 ? 'selected' : ''; ?>>Not Yet Organized</option>
                            <option value="2" <?= $data->sgc == 2 ? 'selected' : ''; ?>>Organized but not Functional</option>
                            <option value="3" <?= $data->sgc == 3 ? 'selected' : ''; ?>>Functional</option>
                        </select>
                    </div>

                    <div class="form-group col-lg-4">
                        <label for="category">Category</label>
                        <select class="form-control" required name="category" id="category">
                            <option value="" disabled>Choose category</option>
                            <?php
                            $schoolTypes = array(
                                'Elementary' => 1,
                                'Integrated (Elem & JHS)' => 2,
                                'Integrated (Elem, JHS, & SHS)' => 3,
                                'Secondary (JHS only)' => 4,
                                'Secondary (JHS & SHS)' => 5,
                                'SHS - Stand Alone' => 6
                            );
                            foreach ($schoolTypes as $label => $value) :
                            ?>
                                <option value="<?= $value; ?>" <?= $data->category == $value ? 'selected' : ''; ?>>
                                    <?= html_escape($label); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group col-lg-4">
                        <label for="school_type">Offerings</label>
                        <select name="schoolType" id="school_type" class="form-control">
                            <option value="" disabled>Select offerings</option>
                            <option value="1" <?= $data->schoolType == 1 ? 'selected' : ''; ?>>None</option>
                            <option value="2" <?= $data->schoolType == 2 ? 'selected' : ''; ?>>School-Based ALS Program</option>
                            <option value="3" <?= $data->schoolType == 3 ? 'selected' : ''; ?>>TLE-TVL Course Offerings</option>
                            <option value="4" <?= $data->schoolType == 4 ? 'selected' : ''; ?>>School-Based ALS and TLE-TVL</option>
                        </select>
                    </div>
                </div>
            </section>

            <div class="form-actions">
                <p class="action-note">
                    <i class="mdi mdi-information-outline mr-1"></i>
                    Review the information before saving your changes.
                </p>
                <div class="action-buttons">
                    <button type="button" class="btn btn-school-secondary" onclick="window.history.back();">
                        <i class="mdi mdi-arrow-left mr-1"></i> Cancel
                    </button>
                    <button type="submit" name="submit" class="btn btn-school-primary">
                        <i class="mdi mdi-content-save-outline mr-1"></i> Save Changes
                    </button>
                </div>
            </div>
        <?= form_close(); ?>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {
        $('#division').on('change', function () {
            var divisionID = $(this).val();
            var $district = $('#district');

            $district.html('<option value="">Loading districts...</option>').prop('disabled', true);

            if (divisionID !== '') {
                $.ajax({
                    url: '<?= base_url("Pages/get_district_by_division"); ?>',
                    method: 'POST',
                    data: {division_id: divisionID},
                    dataType: 'json',
                    success: function (response) {
                        $district.html('<option value="">Select District / Cluster</option>');
                        $.each(response, function (index, item) {
                            $district.append($('<option>', {
                                value: item.id,
                                text: item.description
                            }));
                        });
                    },
                    error: function () {
                        $district.html('<option value="">Unable to load districts</option>');
                    },
                    complete: function () {
                        $district.prop('disabled', false);
                    }
                });
            } else {
                $district.html('<option value="">Select District / Cluster</option>').prop('disabled', false);
            }
        });
    });
</script>
