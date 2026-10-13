<?php
$division_name = !empty($division) ? $division->description : 'Division';
$district_total = count($district);
$encoded_school_total = isset($encoded_total_schools) ? (int) $encoded_total_schools : 0;
$signup_rate = isset($signup_percentage) ? (float) $signup_percentage : 0;
?>

<style>
    .district-accounts-page {
        --accounts-primary: var(--llcm-blue, #922c43);
        --accounts-primary-dark: var(--llcm-navy, #3b0718);
        --accounts-border: var(--llcm-border, #ead7dc);
        --accounts-muted: #6b7280;
    }

    .district-accounts-hero {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 24px;
        margin: 18px 0 22px;
        padding: 28px;
        border-radius: 18px;
        color: #fff;
        background:
            radial-gradient(circle at 90% 15%, rgba(255, 255, 255, .2), transparent 25%),
            linear-gradient(135deg, var(--accounts-primary-dark) 0%, var(--accounts-primary) 100%);
        box-shadow: 0 14px 34px rgba(20, 62, 94, .16);
    }

    .district-accounts-hero h2 {
        margin: 0 0 6px;
        color: #fff;
        font-size: 25px;
        font-weight: 700;
    }

    .district-accounts-hero p {
        margin: 0;
        color: rgba(255, 255, 255, .82);
    }

    .district-accounts-summary {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }

    .summary-pill {
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

    .district-account-card {
        margin-bottom: 13px;
        border: 1px solid var(--accounts-border);
        border-radius: 15px;
        box-shadow: 0 7px 22px rgba(31, 45, 75, .06);
        overflow: hidden;
    }

    .district-account-card:last-child {
        margin-bottom: 0;
    }

    .district-account-header {
        padding: 0;
        border: 0;
        background: #fff;
    }

    .district-account-toggle {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        width: 100%;
        padding: 18px 20px;
        color: #233342;
    }

    .district-account-toggle:hover {
        color: var(--accounts-primary);
        background: #fbf4f5;
    }

    .district-account-title {
        display: flex;
        align-items: center;
        gap: 13px;
        min-width: 0;
    }

    .district-account-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        flex: 0 0 42px;
        border-radius: 12px;
        color: #fff;
        background: linear-gradient(135deg, var(--accounts-primary-dark), var(--accounts-primary));
        font-size: 19px;
    }

    .district-account-name {
        display: block;
        font-size: 15px;
        font-weight: 700;
    }

    .district-account-meta {
        display: block;
        margin-top: 2px;
        color: var(--accounts-muted);
        font-size: 11px;
        font-weight: 400;
    }

    .district-account-header-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex: 0 0 auto;
    }

    .district-user-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 6px 10px;
        border-radius: 999px;
        color: #147a50;
        background: #e6f7ef;
        font-size: 11px;
        font-weight: 700;
    }

    .district-chevron {
        color: #8b93a7;
        transition: transform .2s ease;
    }

    .district-account-toggle[aria-expanded="true"] .district-chevron {
        transform: rotate(180deg);
    }

    .district-account-body {
        padding: 8px 20px 20px;
        border-top: 1px solid var(--accounts-border);
        background: #fbf4f5;
    }

    .school-account-table {
        margin: 0;
        border-collapse: separate;
        border-spacing: 0 7px;
    }

    .school-account-table thead th {
        padding: 10px 12px;
        border: 0;
        color: #687086;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .05em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .school-account-table tbody td {
        padding: 12px;
        border-top: 1px solid var(--accounts-border);
        border-bottom: 1px solid var(--accounts-border);
        vertical-align: middle;
        background: #fff;
    }

    .school-account-table tbody td:first-child {
        border-left: 1px solid var(--accounts-border);
        border-radius: 10px 0 0 10px;
    }

    .school-account-table tbody td:last-child {
        border-right: 1px solid var(--accounts-border);
        border-radius: 0 10px 10px 0;
    }

    .school-account-table tbody tr:hover td {
        background: #fbf4f5;
    }

    .school-sequence {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 9px;
        color: var(--accounts-primary-dark);
        background: #f5e9ec;
        font-size: 11px;
        font-weight: 700;
    }

    .school-id-text {
        color: #596277;
        font-family: Consolas, Monaco, monospace;
        font-size: 12px;
    }

    .school-account-name {
        min-width: 210px;
        color: #233342;
        font-weight: 600;
    }

    .account-state {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 6px 9px;
        border-radius: 999px;
        font-size: 10px;
        font-weight: 700;
    }

    .account-state.active {
        color: #147a50;
        background: #e6f7ef;
    }

    .account-state.missing {
        color: #8b6a12;
        background: #fff4d6;
    }

    .school-account-actions {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 6px;
        min-width: 310px;
    }

    .school-account-actions form {
        margin: 0;
    }

    .school-account-actions .btn {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 6px 9px;
        border-radius: 7px;
        font-size: 11px;
        font-weight: 600;
    }

    .district-empty-state {
        padding: 34px 20px;
        color: var(--accounts-muted);
        text-align: center;
    }

    .district-empty-state i {
        display: block;
        margin-bottom: 8px;
        color: #aab2c3;
        font-size: 32px;
    }

    .district-accounts-page .alert {
        border: 0;
        border-radius: 12px;
        box-shadow: 0 6px 18px rgba(31, 45, 75, .07);
    }

    @media (max-width: 767.98px) {
        .district-accounts-hero {
            align-items: flex-start;
            flex-direction: column;
            padding: 22px;
            border-radius: 14px;
        }

        .district-accounts-summary {
            width: 100%;
        }

        .summary-pill {
            justify-content: center;
            flex: 1 1 auto;
        }

        .district-account-toggle {
            align-items: flex-start;
            padding: 15px;
        }

        .district-account-header-actions {
            align-items: flex-end;
            flex-direction: column;
        }

        .district-account-body {
            padding: 8px 12px 14px;
        }

        .school-account-actions {
            min-width: 260px;
        }
    }
</style>

<div class="district-accounts-page">
    <div class="row">
        <div class="col-12">
            <div class="district-accounts-hero">
                <div>
                    <h2><i class="mdi mdi-account-network-outline mr-2"></i>District &amp; School Accounts</h2>
                    <p>Manage account access for schools under <?= html_escape(mb_convert_case($division_name, MB_CASE_TITLE, 'UTF-8')); ?>.</p>
                </div>
                <div class="district-accounts-summary">
                    <?php if($this->session->position == 'division' || $this->session->position == 'Admin'){ ?>
                    <a href="<?= base_url(); ?>Pages/district_new" class="summary-pill" style="text-decoration: none; cursor: pointer;">
                        <i class="mdi mdi-plus"></i>
                        Add District
                    </a>
                    <?php } ?>
                </div>
            </div>

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
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div id="districtAccountsAccordion">
                <?php foreach ($district as $index => $district_row) :
                    $district_id = (string) $district_row->id;
                    $schools = isset($schools_by_district[$district_id])
                        ? $schools_by_district[$district_id]
                        : array();
                    $district_accounts = isset($district_user_counts[$district_id])
                        ? $district_user_counts[$district_id]
                        : 0;
                    $panel_number = $index + 1;
                ?>
                    <div class="district-account-card">
                        <div class="district-account-header" id="districtHeading<?= $panel_number; ?>">
                            <a
                                href="#districtCollapse<?= $panel_number; ?>"
                                class="district-account-toggle"
                                data-toggle="collapse"
                                aria-expanded="<?= $panel_number === 1 ? 'true' : 'false'; ?>"
                                aria-controls="districtCollapse<?= $panel_number; ?>"
                            >
                                <span class="district-account-title">
                                    <span class="district-account-icon"><i class="mdi mdi-map-marker-outline"></i></span>
                                    <span>
                                        <span class="district-account-name"><?= html_escape(mb_convert_case($district_row->description, MB_CASE_TITLE, 'UTF-8')); ?></span>
                                        <span class="district-account-meta"><?= count($schools); ?> <?= count($schools) === 1 ? 'school' : 'schools'; ?></span>
                                    </span>
                                </span>
                                <span class="district-account-header-actions">
                                    <span class="district-user-badge">
                                        <i class="mdi mdi-account-key-outline"></i>
                                        <?= $district_accounts; ?> district <?= $district_accounts === 1 ? 'account' : 'accounts'; ?>
                                    </span>
                                    <i class="mdi mdi-chevron-down district-chevron"></i>
                                </span>
                            </a>
                        </div>

                        <div
                            id="districtCollapse<?= $panel_number; ?>"
                            class="collapse <?= $panel_number === 1 ? 'show' : ''; ?>"
                            aria-labelledby="districtHeading<?= $panel_number; ?>"
                            data-parent="#districtAccountsAccordion"
                        >
                            <div class="district-account-body">
                                <div class="mb-2 text-right">
                                    <a
                                        href="<?= base_url(); ?>Pages/district_userlist_by_division/<?= $district_row->id; ?>"
                                        class="btn btn-outline-primary btn-sm"
                                    >
                                        <i class="mdi mdi-account-supervisor-outline"></i> District Accounts
                                    </a>
                                    <?php if($this->session->position == 'division' || $this->session->position == 'Admin'){ ?>
                                    <a
                                        href="<?= base_url(); ?>Pages/district_update/<?= $district_row->id; ?>"
                                        class="btn btn-outline-warning btn-sm"
                                    >
                                        <i class="mdi mdi-pencil-outline"></i> Edit District
                                    </a>
                                    <?php } ?>
                                    <?php if($this->session->position == 'division' || $this->session->position == 'Admin'){ ?>
                                    <?= form_open('Pages/district_delete', array('style' => 'display:inline;', 'onsubmit' => "return confirm('Delete this district and all its schools?');")); ?>
                                    <input type="hidden" name="id" value="<?= (int) $district_row->id; ?>">
                                    <button type="submit" class="btn btn-outline-danger btn-sm">
                                        <i class="mdi mdi-trash-can-outline"></i> Delete District
                                    </button>
                                    <?= form_close(); ?>
                                    <?php } ?>
                                </div>

                                <?php if (!empty($schools)) { ?>
                                    <div class="table-responsive">
                                        <table class="table school-account-table">
                                            <thead>
                                                <tr>
                                                    <th>No.</th>
                                                    <th>School ID</th>
                                                    <th>School Name</th>
                                                    <th>Account</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($schools as $school_index => $school) :
                                                    $school_id = (string) $school->schoolID;
                                                    $has_account = !empty($school_usernames[$school_id]);
                                                    $account_id = isset($school_account_ids[$school_id])
                                                        ? $school_account_ids[$school_id]
                                                        : null;
                                                ?>
                                                    <tr>
                                                        <td><span class="school-sequence"><?= $school_index + 1; ?></span></td>
                                                        <td><span class="school-id-text"><?= html_escape($school_id); ?></span></td>
                                                        <td class="school-account-name"><?= html_escape(mb_convert_case($school->schoolName, MB_CASE_TITLE, 'UTF-8')); ?></td>
                                                        <td>
                                                            <span class="account-state <?= $has_account ? 'active' : 'missing'; ?>">
                                                                <i class="mdi <?= $has_account ? 'mdi-check-circle-outline' : 'mdi-alert-circle-outline'; ?>"></i>
                                                                <?= $has_account ? 'Active' : 'Not created'; ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <div class="school-account-actions">
                                                                <?php if (!$has_account) { ?>
                                                                    <?= form_open('Pages/add_school_user', array(
                                                                        'style' => 'display:inline;',
                                                                        'onsubmit' => "return confirm('Create an account for this school?');"
                                                                    )); ?>
                                                                        <input type="hidden" name="school_id" value="<?= html_escape($school_id); ?>">
                                                                    <button type="submit" class="btn btn-success btn-sm">
                                                                        <i class="mdi mdi-account-plus-outline"></i> Add Account
                                                                    </button>
                                                                    <?= form_close(); ?>
                                                                <?php } else { ?>
                                                                    <?= form_open(
                                                                        'pages/user_reset_password',
                                                                        array(
                                                                            'style' => 'display:inline;',
                                                                            'onsubmit' => "return confirm('Reset this school account password?');"
                                                                        )
                                                                    ); ?>
                                                                        <input type="hidden" name="id" value="<?= (int) $account_id; ?>">
                                                                        <input type="hidden" name="return_to" value="district_account">
                                                                        <button type="submit" class="btn btn-warning btn-sm">
                                                                            <i class="mdi mdi-lock-reset"></i> Reset Password
                                                                        </button>
                                                                    </form>
                                                                <?php } ?>

                                                                <a href="<?= base_url(); ?>school/<?= rawurlencode($school_id); ?>" class="btn btn-info btn-sm">
                                                                    <i class="mdi mdi-eye-outline"></i> View
                                                                </a>
                                                                <a href="<?= base_url(); ?>Pages/school_update/<?= $school->recID; ?>" class="btn btn-primary btn-sm">
                                                                    <i class="mdi mdi-pencil-outline"></i> Edit
                                                                </a>
                                                                <?= form_open('Pages/school_delete', array('style' => 'display:inline;', 'onsubmit' => "return confirm('Delete this school and its account?');")); ?>
                                                                <input type="hidden" name="id" value="<?= (int) $school->recID; ?>">
                                                                <button type="submit" class="btn btn-danger btn-sm">
                                                                    <i class="mdi mdi-trash-can-outline"></i> Delete
                                                                </button>
                                                                <?= form_close(); ?>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php } else { ?>
                                    <div class="district-empty-state">
                                        <i class="mdi mdi-school"></i>
                                        No schools found in this district.
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
