<?php
$is_division_scope = in_array($this->session->position, array('division', 'ict'), true);
$user_list_url = base_url() . ($is_division_scope ? 'pages/userlist_division' : 'pages/userlist');
$selected_division = set_value('division_id', $is_division_scope ? $this->session->division : '');
$selected_district = set_value('d_id');
$selected_position = set_value('position');
$selected_gender = set_value('gender');
$division_count = !empty($division) ? count($division) : 0;
$district_count = !empty($districts) ? count($districts) : 0;
$position_count = !empty($pos) ? count($pos) : 0;
?>

<style>
    .user-create-page {
        --user-create-primary: #8b1e3f;
        --user-create-primary-dark: #64142d;
        --user-create-border: #e8ecf4;
        --user-create-muted: #6b7280;
        --user-create-surface: #ffffff;
        --user-create-soft: #fbfcff;
    }

    .user-create-page .alert {
        border: 0;
        border-radius: 12px;
        box-shadow: 0 6px 18px rgba(31, 45, 75, .07);
    }

    .user-create-hero {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 24px;
        margin: 18px 0 22px;
        padding: 30px;
        border-radius: 20px;
        color: #fff;
        background:
            radial-gradient(circle at 88% 18%, rgba(255, 255, 255, .22), transparent 25%),
            linear-gradient(135deg, #64142d 0%, #a83255 100%);
        box-shadow: 0 14px 34px rgba(139, 30, 63, .22);
    }

    .user-create-hero h2 {
        margin: 0 0 6px;
        color: #fff;
        font-size: 27px;
        font-weight: 700;
    }

    .user-create-hero p {
        margin: 0;
        color: rgba(255, 255, 255, .84);
        max-width: 620px;
    }

    .user-create-hero-actions {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }

    .user-create-pill,
    .user-create-back {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 10px 14px;
        border: 1px solid rgba(255, 255, 255, .24);
        border-radius: 999px;
        color: #fff;
        background: rgba(255, 255, 255, .14);
        font-size: 12px;
        font-weight: 700;
        backdrop-filter: blur(5px);
    }

    .user-create-back:hover {
        color: var(--user-create-primary-dark);
        background: #fff;
        text-decoration: none;
    }

    .user-create-card,
    .user-create-sidecard {
        border: 1px solid var(--user-create-border);
        border-radius: 18px;
        background: var(--user-create-surface);
        box-shadow: 0 10px 30px rgba(31, 45, 75, .07);
        overflow: hidden;
    }

    .user-create-card .card-body,
    .user-create-sidecard .card-body {
        padding: 0;
    }

    .user-create-card-header,
    .user-create-sidecard-header {
        padding: 22px 24px;
        border-bottom: 1px solid var(--user-create-border);
    }

    .user-create-card-header h4,
    .user-create-sidecard-header h4 {
        margin: 0 0 4px;
        color: #27324a;
        font-size: 18px;
        font-weight: 700;
    }

    .user-create-card-header p,
    .user-create-sidecard-header p {
        margin: 0;
        color: var(--user-create-muted);
        font-size: 12px;
    }

    .user-create-form {
        padding: 24px;
    }

    .user-create-section {
        margin-bottom: 22px;
        padding: 20px;
        border: 1px solid var(--user-create-border);
        border-radius: 16px;
        background: var(--user-create-soft);
    }

    .user-create-section:last-of-type {
        margin-bottom: 0;
    }

    .user-create-section-title {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 0 0 18px;
        color: #27324a;
        font-size: 15px;
        font-weight: 700;
    }

    .user-create-section-title i {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border-radius: 10px;
        color: #fff;
        background: linear-gradient(135deg, #8b1e3f, #c65a77);
        font-size: 16px;
    }

    .user-create-form .form-group {
        margin-bottom: 18px;
    }

    .user-create-form label {
        margin-bottom: 8px;
        color: #495166;
        font-weight: 700;
    }

    .user-create-form .form-control,
    .user-create-form .custom-file-label {
        min-height: 46px;
        border: 1px solid #dce2ee;
        border-radius: 12px;
        box-shadow: none;
    }

    .user-create-form .form-control:focus {
        border-color: rgba(139, 30, 63, .35);
        box-shadow: 0 0 0 .18rem rgba(139, 30, 63, .1);
    }

    .user-create-help {
        margin-top: 6px;
        color: var(--user-create-muted);
        font-size: 12px;
    }

    .user-create-password-toggle {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #495166;
        font-size: 13px;
        font-weight: 600;
    }

    .user-create-radio-group {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 18px;
        min-height: 46px;
    }

    .user-create-radio-group .custom-control-label {
        font-weight: 600;
    }

    .user-create-actions {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 24px;
    }

    .user-create-actions .btn {
        min-width: 148px;
        min-height: 44px;
        border-radius: 10px;
        font-weight: 700;
    }

    .user-create-sidecard-body {
        padding: 24px;
    }

    .user-create-stat-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 18px;
    }

    .user-create-stat {
        padding: 16px;
        border: 1px solid var(--user-create-border);
        border-radius: 14px;
        background: var(--user-create-soft);
    }

    .user-create-stat span {
        display: block;
        color: var(--user-create-muted);
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .05em;
        text-transform: uppercase;
    }

    .user-create-stat strong {
        display: block;
        margin-top: 9px;
        color: #27324a;
        font-size: 25px;
        line-height: 1;
    }

    .user-create-checklist {
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .user-create-checklist li {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        margin-bottom: 12px;
        color: #495166;
        font-size: 13px;
    }

    .user-create-checklist li:last-child {
        margin-bottom: 0;
    }

    .user-create-checklist i {
        margin-top: 2px;
        color: var(--user-create-primary);
        font-size: 15px;
    }

    .user-create-scope {
        margin-top: 18px;
        padding: 16px;
        border: 1px solid #f1d8df;
        border-radius: 14px;
        color: #7b3b4c;
        background: #fff7f9;
        font-size: 13px;
    }

    @media (max-width: 991.98px) {
        .user-create-hero {
            flex-direction: column;
            align-items: flex-start;
        }

        .user-create-hero-actions {
            width: 100%;
        }

        .user-create-pill,
        .user-create-back {
            justify-content: center;
            flex: 1 1 auto;
        }
    }

    @media (max-width: 767.98px) {
        .user-create-hero {
            padding: 24px;
            border-radius: 16px;
        }

        .user-create-form,
        .user-create-sidecard-body {
            padding: 18px;
        }

        .user-create-section {
            padding: 16px;
        }

        .user-create-stat-grid {
            grid-template-columns: 1fr;
        }

        .user-create-actions .btn {
            width: 100%;
        }
    }
</style>

<div class="user-create-page">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <?php if($this->session->flashdata('success')) : ?>
                    <?= '<div class="alert alert-success alert-dismissible fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>'
                            .$this->session->flashdata('success'). 
                        '</div>'; 
                    ?>
                <?php endif; ?>

                <?php if($this->session->flashdata('danger')) : ?>
                    <?= '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>'
                            .$this->session->flashdata('danger'). 
                        '</div>'; 
                    ?>
                <?php endif; ?>

                <?= validation_errors(); ?>
                <div class="clearfix"></div>
            </div>
        </div>
    </div>

    <div class="user-create-hero">
        <div>
            <h2><i class="mdi mdi-account-plus-outline mr-2"></i><?= html_escape($title); ?></h2>
            <p>Create a new user account, assign the right access level, and set its scope before saving.</p>
        </div>
        <div class="user-create-hero-actions">
            <span class="user-create-pill">
                <i class="mdi mdi-shield-account-outline"></i>
                <?= $position_count; ?> available roles
            </span>
            <span class="user-create-pill">
                <i class="mdi mdi-map-marker-multiple-outline"></i>
                <?= $is_division_scope ? $district_count . ' districts in scope' : $division_count . ' divisions available'; ?>
            </span>
            <a href="<?= $user_list_url; ?>" class="user-create-back">
                <i class="mdi mdi-arrow-left"></i>
                Back to Users
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card user-create-card">
                <div class="card-body">
                    <div class="user-create-card-header">
                        <h4>Account Setup Form</h4>
                        <p>Fill in the required details below to register a new user account.</p>
                    </div>

                    <?php 
                        $attributes = array(
                            'class' => 'parsley-examples user-create-form',
                            'id' => 'user-create-form'
                        );
                        echo form_open_multipart('pages/user_new', $attributes);
                    ?>
                        <div class="user-create-section">
                            <div class="user-create-section-title">
                                <i class="mdi mdi-lock-outline"></i>
                                <span>Login Credentials</span>
                            </div>

                            <div class="form-group">
                                <label for="email">Email Address <span class="text-danger">*</span></label>
                                <input
                                    type="email"
                                    name="email"
                                    id="email"
                                    class="form-control"
                                    autocomplete="email"
                                    maxlength="254"
                                    required
                                    value="<?= html_escape(set_value('email')); ?>"
                                >
                                <small class="form-text text-muted">Your email address will also be your username.</small>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="password">Password <span class="text-danger">*</span></label>
                                        <input
                                            type="password"
                                            name="password"
                                            id="password"
                                            minlength="8"
                                            maxlength="128"
                                            aria-describedby="password-help"
                                            class="form-control"
                                            required
                                        >
                                        <small id="password-help" class="form-text text-muted">Use at least 8 characters.</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="confirm-password">Confirm Password <span class="text-danger">*</span></label>
                                        <input
                                            type="password"
                                            name="cpassword"
                                            id="confirm-password"
                                            class="form-control"
                                            required
                                            data-parsley-equalto="#password"
                                        >
                                    </div>
                                </div>
                            </div>

                            <label class="user-create-password-toggle" for="toggle-passwords">
                                <input id="toggle-passwords" type="checkbox">
                                <span>Show password fields</span>
                            </label>
                        </div>

                        <div class="user-create-section">
                            <div class="user-create-section-title">
                                <i class="mdi mdi-account-card-details-outline"></i>
                                <span>Profile Details</span>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="fname">First Name <span class="text-danger">*</span></label>
                                        <input
                                            type="text"
                                            name="fname"
                                            id="fname"
                                            class="form-control"
                                            required
                                            value="<?= html_escape(set_value('fname')); ?>"
                                            oninput="this.value = this.value.toUpperCase()"
                                        >
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="lname">Last Name <span class="text-danger">*</span></label>
                                        <input
                                            type="text"
                                            name="lname"
                                            id="lname"
                                            class="form-control"
                                            required
                                            value="<?= html_escape(set_value('lname')); ?>"
                                            oninput="this.value = this.value.toUpperCase()"
                                        >
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="mname">Middle Name</label>
                                <input
                                    type="text"
                                    name="mname"
                                    id="mname"
                                    class="form-control"
                                    value="<?= html_escape(set_value('mname')); ?>"
                                    oninput="this.value = this.value.toUpperCase()"
                                >
                            </div>

                            <div class="form-group">
                                <label>Gender <span class="text-danger">*</span></label>
                                <div class="user-create-radio-group">
                                    <div class="custom-control custom-radio">
                                        <input type="radio" id="gender-male" name="gender" value="0" class="custom-control-input" <?= (string) $selected_gender === '0' ? 'checked' : ''; ?> required>
                                        <label class="custom-control-label" for="gender-male">Male</label>
                                    </div>
                                    <div class="custom-control custom-radio">
                                        <input type="radio" id="gender-female" name="gender" value="1" class="custom-control-input" <?= (string) $selected_gender === '1' ? 'checked' : ''; ?> required>
                                        <label class="custom-control-label" for="gender-female">Female</label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-0">
                                <label for="profile-picture">Profile Picture</label>
                                <input type="file" name="file" id="profile-picture" class="form-control">
                                <div class="user-create-help">Accepted file types: JPG and PNG.</div>
                            </div>
                        </div>

                        <div class="user-create-section">
                            <div class="user-create-section-title">
                                <i class="mdi mdi-office-building-outline"></i>
                                <span>Access Scope</span>
                            </div>

                            <div class="form-group">
                                <label for="position">Position <span class="text-danger">*</span></label>
                                <select name="position" id="position" class="form-control" required>
                                    <option value="">Select Position</option>
                                    <?php foreach ($pos as $row){ ?>
                                        <option value="<?= html_escape($row->pos); ?>" <?= $selected_position === $row->pos ? 'selected' : ''; ?>>
                                            <?= html_escape($row->description); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>

                            <?php if ($this->session->position === 'district') { ?>
                                <input type="hidden" name="division_id" value="<?= (int) $this->session->division; ?>">
                                <input type="hidden" name="d_id" value="<?= (int) $this->session->district; ?>">
                            <?php } elseif($is_division_scope){ ?>
                                <input type="hidden" name="division_id" value="<?= html_escape($this->session->division); ?>">

                                <div class="user-create-scope">
                                    This account will be created under your assigned division. You may optionally assign a district when the user should be district-scoped.
                                </div>

                                <div class="form-group mt-3 mb-0">
                                    <label for="district-inline">District</label>
                                    <select name="d_id" id="district-inline" class="form-control">
                                        <option value="">Select District</option>
                                        <?php foreach($districts as $row){ ?>
                                            <option value="<?= html_escape($row->id); ?>" <?= (string) $selected_district === (string) $row->id ? 'selected' : ''; ?>>
                                                <?= html_escape($row->description); ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            <?php } else { ?>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="division">Division</label>
                                            <select name="division_id" id="division" class="form-control">
                                                <option value="">Select Division</option>
                                                <?php foreach($division as $row){ ?>
                                                    <option value="<?= html_escape($row->id); ?>" <?= (string) $selected_division === (string) $row->id ? 'selected' : ''; ?>>
                                                        <?= html_escape($row->description); ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-0">
                                            <label for="district">District</label>
                                            <select name="d_id" id="district" class="form-control">
                                                <option value="">Select District</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>

                        <div class="user-create-actions">
                            <button type="submit" class="btn btn-primary waves-effect waves-light">
                                <i class="mdi mdi-content-save-outline mr-1"></i> Register User
                            </button>
                            <a href="<?= $user_list_url; ?>" class="btn btn-light waves-effect">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mt-4 mt-lg-0">
            <div class="card user-create-sidecard">
                <div class="card-body">
                    <div class="user-create-sidecard-header">
                        <h4>Quick Guide</h4>
                        <p>Use this panel to keep account setup consistent and complete.</p>
                    </div>

                    <div class="user-create-sidecard-body">
                        <div class="user-create-stat-grid">
                            <div class="user-create-stat">
                                <span>Roles</span>
                                <strong><?= $position_count; ?></strong>
                            </div>
                            <div class="user-create-stat">
                                <span>Districts</span>
                                <strong><?= $is_division_scope ? $district_count : $division_count; ?></strong>
                            </div>
                        </div>

                        <ul class="user-create-checklist">
                            <li>
                                <i class="mdi mdi-check-circle-outline"></i>
                                <span>Choose the account position first so the scope matches the intended user access.</span>
                            </li>
                            <li>
                                <i class="mdi mdi-check-circle-outline"></i>
                                <span>Use a unique username to avoid duplicate account entries.</span>
                            </li>
                            <li>
                                <i class="mdi mdi-check-circle-outline"></i>
                                <span>Assign a district when the new account should manage district-level records.</span>
                            </li>
                            <li>
                                <i class="mdi mdi-check-circle-outline"></i>
                                <span>Upload an optional profile image if you want the account to show a custom avatar immediately.</span>
                            </li>
                        </ul>

                        <div class="user-create-scope">
                            <?= $is_division_scope
                                ? 'Division and ICT managers can only create accounts within their assigned division.'
                                : 'Administrators can create accounts across available divisions and positions.'; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    var togglePasswords = document.getElementById('toggle-passwords');
    var password = document.getElementById('password');
    var confirmPassword = document.getElementById('confirm-password');

    if (togglePasswords && password && confirmPassword) {
        togglePasswords.addEventListener('change', function () {
            var type = this.checked ? 'text' : 'password';
            password.type = type;
            confirmPassword.type = type;
        });
    }

    var divisionSelect = document.getElementById('division');
    var districtSelect = document.getElementById('district');
    var selectedDistrict = '<?= html_escape($selected_district); ?>';

    function populateDistricts(divisionId, initialLoad) {
        if (!divisionSelect || !districtSelect) {
            return;
        }

        if (!divisionId) {
            districtSelect.innerHTML = '<option value="">Select District</option>';
            return;
        }

        districtSelect.innerHTML = '<option value="">Loading...</option>';

        fetch('<?= base_url("Pages/get_district_by_division"); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
            },
            body: 'division_id=' + encodeURIComponent(divisionId)
        })
        .then(function (response) {
            return response.json();
        })
        .then(function (response) {
                districtSelect.innerHTML = '<option value="">Select District</option>';
                response.forEach(function (item) {
                    var selected = ((selectedDistrict && selectedDistrict.toString() === item.id.toString()) ? ' selected' : '');
                    districtSelect.innerHTML += '<option value="' + item.id + '"' + selected + '>' + item.description + '</option>';
                });

                if (!initialLoad) {
                    selectedDistrict = '';
                }
        })
        .catch(function () {
            districtSelect.innerHTML = '<option value="">Select District</option>';
        });
    }

    if (divisionSelect && districtSelect) {
        divisionSelect.addEventListener('change', function () {
            selectedDistrict = '';
            populateDistricts(this.value, false);
        });

        if (divisionSelect.value) {
            populateDistricts(divisionSelect.value, true);
        }
    }
})();
</script>
