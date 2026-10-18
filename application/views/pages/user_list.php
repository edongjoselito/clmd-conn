                        <?php
                            $themed_user_scope = !empty($division_scope) || !empty($district_user_scope);
                            $is_division_user_scope = !empty($division_scope) && empty($district_user_scope);
                            $district_name = !empty($district) ? mb_convert_case($district->description, MB_CASE_TITLE, 'UTF-8') : 'District';
                            $district_user_back_url = !empty($district_user_back_url) ? $district_user_back_url : base_url();
                        ?>

                        <style>
                            .user-list-page {
                                --ul-primary: var(--llcm-blue, #922c43);
                                --ul-primary-light: #c66b7c;
                                --ul-accent: #9dd7f2;
                                --ul-ink: #233342;
                                --ul-muted: #687386;
                                --ul-border: var(--llcm-border, #ead7dc);
                                --ul-surface: var(--llcm-sky, #fbf4f5);
                            }

                            .user-list-page .alert {
                                border-radius: 14px;
                            }

                            .user-list-hero {
                                position: relative;
                                display: flex;
                                align-items: stretch;
                                justify-content: space-between;
                                gap: 24px;
                                margin: 18px 0 22px;
                                padding: 32px;
                                border-radius: 24px;
                                color: #fff;
                                background:
                                    radial-gradient(circle at 100% 0%, rgba(255, 255, 255, .15), transparent 30%),
                                    radial-gradient(circle at 0% 100%, rgba(214, 168, 75, .18), transparent 24%),
                                    linear-gradient(135deg, #541117 0%, #7f1d1d 46%, #b83a4b 100%);
                                box-shadow: 0 22px 44px rgba(84, 17, 23, .20);
                                overflow: hidden;
                            }

                            .user-list-hero::after {
                                content: '';
                                position: absolute;
                                right: -42px;
                                bottom: -56px;
                                width: 210px;
                                height: 210px;
                                border-radius: 50%;
                                background: rgba(255, 255, 255, .08);
                            }

                            .user-list-page.division-scope .user-list-hero {
                                padding: 28px;
                                border-radius: 18px;
                                background:
                                    radial-gradient(circle at 90% 15%, rgba(255, 255, 255, .2), transparent 25%),
                                    linear-gradient(135deg, var(--llcm-navy, #3b0718) 0%, var(--ul-primary) 100%);
                                box-shadow: 0 14px 34px rgba(20, 62, 94, .16);
                            }

                            .user-list-page.division-scope .user-list-hero::after {
                                display: none;
                            }

                            .user-list-hero-copy,
                            .user-list-hero-side {
                                position: relative;
                                z-index: 1;
                            }

                            .user-list-hero-copy {
                                max-width: 760px;
                            }

                            .user-list-hero-side {
                                display: flex;
                                flex-direction: column;
                                align-items: flex-end;
                                justify-content: space-between;
                                gap: 16px;
                                min-width: 220px;
                            }

                            .hero-eyebrow {
                                display: inline-flex;
                                align-items: center;
                                gap: 8px;
                                margin-bottom: 14px;
                                padding: 8px 12px;
                                border-radius: 999px;
                                background: rgba(255, 255, 255, .12);
                                color: rgba(255, 255, 255, .92);
                                font-size: 11px;
                                font-weight: 700;
                                letter-spacing: .08em;
                                text-transform: uppercase;
                            }

                            .hero-eyebrow i {
                                font-size: 15px;
                            }

                            .user-list-hero h1 {
                                margin: 0;
                                color: #fff;
                                font-size: 30px;
                                font-weight: 800;
                                line-height: 1.1;
                                letter-spacing: -.03em;
                            }

                            .user-list-hero p {
                                max-width: 700px;
                                margin: 14px 0 18px;
                                color: rgba(255, 255, 255, .86);
                                font-size: 14px;
                                line-height: 1.75;
                            }

                            .hero-actions {
                                display: flex;
                                flex-wrap: wrap;
                                gap: 10px;
                                justify-content: flex-end;
                            }

                            .hero-button {
                                display: inline-flex;
                                align-items: center;
                                justify-content: center;
                                gap: 8px;
                                padding: 11px 16px;
                                border: 0;
                                border-radius: 12px;
                                color: #fff;
                                background: rgba(255, 255, 255, .10);
                                border: 1px solid rgba(255, 255, 255, .18);
                                font-size: 12px;
                                font-weight: 700;
                                text-decoration: none !important;
                                transition: transform .18s ease, box-shadow .18s ease, background .18s ease, color .18s ease;
                            }

                            .hero-button:hover {
                                transform: translateY(-2px);
                                background: rgba(255, 255, 255, .16);
                            }

                            .hero-button-primary {
                                color: var(--ul-primary);
                                background: #fff;
                                box-shadow: 0 10px 20px rgba(61, 12, 29, .18);
                            }

                            .hero-button-primary:hover {
                                color: var(--ul-primary);
                                background: #fff;
                            }

                            .workspace-panel {
                                margin-bottom: 22px;
                                border: 1px solid var(--ul-border);
                                border-radius: 18px;
                                background: #fff;
                                box-shadow: 0 10px 30px rgba(31, 45, 75, .07);
                                overflow: hidden;
                            }

                            .workspace-panel-header {
                                display: flex;
                                align-items: center;
                                justify-content: space-between;
                                gap: 16px;
                                padding: 20px 24px;
                                border-bottom: 1px solid var(--ul-border);
                            }

                            .workspace-panel-header h4 {
                                margin: 0 0 4px;
                                color: var(--ul-ink);
                                font-size: 18px;
                                font-weight: 700;
                            }

                            .workspace-panel-header p {
                                margin: 0;
                                color: var(--ul-muted);
                                font-size: 12px;
                                line-height: 1.6;
                            }

                            .workspace-panel-body {
                                padding: 22px 24px;
                            }

                            .user-list-table {
                                width: 100%;
                                border-collapse: separate;
                                border-spacing: 0;
                            }

                            .user-list-table thead th {
                                padding: 14px 16px;
                                border-bottom: 2px solid var(--ul-border);
                                color: var(--ul-ink);
                                font-size: 12px;
                                font-weight: 700;
                                letter-spacing: .06em;
                                text-transform: uppercase;
                                background: var(--ul-surface);
                            }

                            .user-list-table tbody td {
                                padding: 16px;
                                border-bottom: 1px solid var(--ul-border);
                                color: var(--ul-ink);
                                font-size: 13px;
                                vertical-align: middle;
                            }

                            .user-list-table tbody tr:last-child td {
                                border-bottom: 0;
                            }

                            .user-list-table tbody tr:hover td {
                                background: var(--ul-surface);
                            }

                            .account-cell {
                                display: flex;
                                align-items: center;
                                gap: 12px;
                                min-width: 190px;
                            }

                            .account-avatar {
                                display: inline-flex;
                                align-items: center;
                                justify-content: center;
                                width: 40px;
                                height: 40px;
                                flex: 0 0 40px;
                                border-radius: 12px;
                                color: #fff;
                                background: linear-gradient(135deg, var(--ul-primary), var(--ul-primary-light));
                                font-size: 14px;
                                font-weight: 700;
                            }

                            .account-name {
                                color: var(--ul-ink);
                                font-weight: 600;
                            }

                            .username-text {
                                color: var(--ul-muted);
                                font-family: Consolas, Monaco, monospace;
                                font-size: 13px;
                            }

                            .account-level {
                                display: inline-flex;
                                padding: 6px 10px;
                                border-radius: 999px;
                                color: var(--ul-primary);
                                background: #f5e9ec;
                                font-size: 11px;
                                font-weight: 700;
                                text-transform: capitalize;
                            }

                            .user-actions {
                                display: flex;
                                flex-wrap: wrap;
                                gap: 7px;
                            }

                            .user-actions form {
                                margin: 0;
                            }

                            .user-actions .btn {
                                display: inline-flex;
                                align-items: center;
                                gap: 5px;
                                min-height: 34px;
                                padding: 7px 10px;
                                border-radius: 8px;
                                font-size: 12px;
                                font-weight: 600;
                            }

                            .pagination-wrapper {
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                gap: 8px;
                                margin-top: 24px;
                                padding: 16px;
                                border-top: 1px solid var(--ul-border);
                            }

                            .pagination-wrapper span {
                                display: inline-flex;
                                align-items: center;
                                justify-content: center;
                                min-width: 40px;
                                height: 40px;
                                padding: 0 14px;
                                border-radius: 10px;
                                font-size: 13px;
                                font-weight: 600;
                                text-decoration: none;
                                transition: all .18s ease;
                            }

                            .pagination-wrapper span a {
                                color: var(--ul-ink);
                                text-decoration: none;
                            }

                            .pagination-wrapper span:hover a {
                                color: var(--ul-primary);
                            }

                            .pagination-wrapper span.active {
                                background: linear-gradient(135deg, var(--ul-primary), var(--ul-primary-light));
                                color: #fff;
                            }

                            .pagination-wrapper span.active a {
                                color: #fff;
                            }

                            @media (max-width: 767.98px) {
                                .user-list-hero {
                                    flex-direction: column;
                                    padding: 22px;
                                }

                                .user-list-hero-side {
                                    width: 100%;
                                    align-items: stretch;
                                }

                                .hero-actions {
                                    width: 100%;
                                }

                                .hero-button {
                                    flex: 1;
                                    justify-content: center;
                                }

                                .user-list-table thead th,
                                .user-list-table tbody td {
                                    padding: 12px 10px;
                                    font-size: 12px;
                                }

                                .user-actions .btn {
                                    justify-content: center;
                                }
                            }
                        </style>

                        <div class="user-list-page <?= $is_division_user_scope ? 'division-scope' : ''; ?>">
                            <!-- Hero Section -->
                            <div class="user-list-hero">
                                <div class="user-list-hero-copy">
                                    <span class="hero-eyebrow">
                                        <i class="mdi mdi-account-group-outline"></i>
                                        User Management
                                    </span>
                                    <h1><?= !empty($regional_scope) ? 'Regional User Accounts' : (!empty($district_user_scope) ? 'District User Accounts' : (!empty($division_scope) ? 'Division User Accounts' : 'User List')); ?></h1>
                                    <p><?= !empty($district_user_scope) ? 'Manage district user accounts assigned to ' . html_escape($district_name) . '.' : (!empty($division_scope) ? 'Create and maintain accounts assigned to your division.' : 'Manage system user accounts and access levels.'); ?></p>
                                </div>
                                <div class="user-list-hero-side">
                                    <div class="hero-actions">
                                        <?php if(in_array($this->session->position, array('admin', 'region', 'division', 'ict', 'district'), true)){ ?>
                                        <a href="<?= base_url(); ?>pages/user_new" class="hero-button hero-button-primary">
                                            <i class="mdi mdi-account-plus-outline"></i>
                                            Add User
                                        </a>
                                        <?php } ?>
                                        <?php if (!empty($district_user_scope)) { ?>
                                        <a href="<?= $district_user_back_url; ?>" class="hero-button">
                                            <i class="mdi mdi-arrow-left"></i>
                                            Back to Districts
                                        </a>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>

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
                                <?php endif;  ?>
                            </div>
                        </div>

                        <!-- Workspace Panel -->
                        <div class="workspace-panel">
                            <div class="workspace-panel-header">
                                <div>
                                    <h4><?= !empty($district_user_scope) ? html_escape($district_name) . ' District Accounts' : (!empty($division_scope) ? 'Division Accounts' : ($this->session->position === 'region' ? 'Regional Accounts' : ($this->session->position === 'district' ? 'District Accounts' : 'System Accounts'))); ?></h4>
                                    <p><?= !empty($district_user_scope) ? 'Review and manage district-level user access for this district.' : (!empty($division_scope) ? 'Manage account details, access levels, and passwords.' : 'Manage system user accounts and access levels.'); ?></p>
                                </div>
                                <span class="account-level">
                                    <i class="mdi mdi-account-multiple-outline"></i>
                                    <?= count($users); ?> <?= count($users) === 1 ? 'account' : 'accounts'; ?>
                                </span>
                            </div>
                            <div class="workspace-panel-body">
                                <div class="table-responsive">
                                    <table id="datatable" class="table table-bordered dt-responsive nowrap" style="width: 100%;">
                                        <thead>
                                            <tr>
                                                <th data-name="account">Account</th>
                                                <th data-name="username">Username</th>
                                                <th data-name="position">Position</th>
                                                <th>Manage</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($users as $row): ?>
                                            <tr>
                                                <td>
                                                    <div class="account-cell">
                                                        <span class="account-avatar"><?= strtoupper(substr($row->fname, 0, 1) . (!empty($row->lname) ? substr($row->lname, 0, 1) : '')); ?></span>
                                                        <span class="account-name"><?= mb_convert_case(trim((!empty($row->lname) ? $row->lname . ', ' : '') . $row->fname . (!empty($row->mname) ? ' ' . substr($row->mname, 0, 1) . '.' : '')), MB_CASE_TITLE, 'UTF-8'); ?></span>
                                                    </div>
                                                </td>
                                                <td><span class="username-text"><?= html_escape($row->username); ?></span></td>
                                                <td><span class="account-level"><?= html_escape($row->position); ?></span></td>
                                                <td>
                                                    <div class="user-actions">
                                                        <?php if(in_array($this->session->position, array('admin', 'region', 'division', 'ict', 'district'), true)){ ?>
                                                        <a class="btn btn-primary btn-sm" href="<?= base_url(); ?>pages/user_update/<?= $row->id; ?>"><i class="mdi mdi-pencil-outline"></i> Edit</a>
                                                        <?php } ?>
                                                        <?php if(in_array($this->session->position, array('admin', 'region'), true)){ ?>
                                                        <a href="#profile" class="open-AddBookDialog btn btn-primary btn-sm waves-effect waves-light" data-id="<?= $row->id; ?>" data-animation="slit" data-plugin="custommodal" data-overlayspeed="100" data-overlaycolor="#36404a">Change profile</a>
                                                        <?php } ?>
                                                        <form action="<?= base_url(); ?>pages/user_reset_password" method="post" style="display:inline;" onsubmit="return confirm('Reset this user\'s password?');">
                                                            <input type="hidden" name="<?= html_escape($this->security->get_csrf_token_name()); ?>" value="<?= html_escape($this->security->get_csrf_hash()); ?>">
                                                            <input type="hidden" name="id" value="<?= $row->id; ?>">
                                                            <button type="submit" class="btn btn-warning btn-sm waves-effect waves-light"><i class="mdi mdi-lock-reset"></i> Reset</button>
                                                        </form>
                                                        <?php if(in_array($this->session->position, array('admin', 'region', 'division', 'ict', 'district'), true)){ ?>
                                                        <?= form_open('pages/user_delete', array('style' => 'display:inline;', 'onsubmit' => "return confirm('Delete this user account?');")); ?>
                                                        <input type="hidden" name="id" value="<?= (int) $row->id; ?>">
                                                        <button type="submit" class="btn btn-danger btn-sm"><i class="mdi mdi-trash-can-outline"></i> Delete</button>
                                                        <?= form_close(); ?>
                                                        <?php } ?>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Modal -->
                        <div id="profile" class="modal-demo">
                            <button type="button" class="close" onclick="Custombox.modal.close();">
                                <span>&times;</span><span class="sr-only">Close</span>
                            </button>
                            <h4 class="custom-modal-title">Change Profile Picture</h4>
                            <div class="custom-modal-text">
                                
                            <div class="row">
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="card-body">
                                        <?php 
                                            $attributes = array('class' => 'parsley-examples');
                                            echo form_open_multipart('pages/profile', $attributes);
                                        ?>

                                            <div class="modal-body">
                                                <input type="hidden" name="id" id="id" value="">            
                                            </div> 
                                            
                                            <div class="form-group row">
                                                <label for="hori-pass1" class="col-md-4 col-form-label">Select Image<span class="text-danger">*</span></label>
                                                <div class="col-md-7">
                                                    <input id="myInput" type="file" placeholder="Password" name="file"  required class="form-control">
                                                </div>
                                            </div>

                                          
                                            
                                            <div class="form-group row mb-0">
                                                <div class="col-md-8 offset-md-4">
                                                    <button type="submit" class="btn btn-primary waves-effect waves-light mr-1">
                                                        Change Profile
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <!-- end card -->
                            </div>
                            <!-- end col -->
                            </div>
                            <!-- end row -->
                            </div>
                        </div>



                        <script>
                        function myFunction() {
                        var x = document.getElementById("myInput");
                        if (x.type === "password") {
                            x.type = "text";
                        } else {
                            x.type = "password";
                        }
                        var x = document.getElementById("myInput2");
                        if (x.type === "password") {
                            x.type = "text";
                        } else {
                            x.type = "password";
                        }
                        }

                        $(document).ready(function() {
                            console.log('Initializing DataTables with client-side processing');
                            var table = $('#datatable').DataTable({
                                pageLength: 20,
                                lengthMenu: [[10, 20, 50, 100], [10, 20, 50, 100]],
                                order: [[0, 'asc']],
                                responsive: true,
                                columns: [
                                    {
                                        data: 'account',
                                        render: function(data, type, row) {
                                            return '<div class="account-cell">' +
                                                '<span class="account-avatar">' + row.initials + '</span>' +
                                                '<span class="account-name">' + data + '</span>' +
                                                '</div>';
                                        }
                                    },
                                    { data: 'username', render: function(data) { return '<span class="username-text">' + data + '</span>'; } },
                                    { data: 'position', render: function(data) { return '<span class="account-level">' + data + '</span>'; } },
                                    {
                                        data: 'id',
                                        render: function(data, type, row) {
                                            var actions = '';
                                            <?php if(in_array($this->session->position, array('admin', 'region', 'division', 'ict', 'district'), true)){ ?>
                                            actions += '<a class="btn btn-primary btn-sm" href="<?= base_url(); ?>pages/user_update/' + data + '"><i class="mdi mdi-pencil-outline"></i> Edit</a> ';
                                            <?php } ?>
                                            <?php if(in_array($this->session->position, array('admin', 'region'), true)){ ?>
                                            actions += '<a href="#profile" class="open-AddBookDialog btn btn-primary btn-sm waves-effect waves-light" data-id="' + data + '" data-animation="slit" data-plugin="custommodal" data-overlayspeed="100" data-overlaycolor="#36404a">Change profile</a> ';
                                            <?php } ?>
                                            actions += '<form action="<?= base_url(); ?>pages/user_reset_password" method="post" style="display:inline;" onsubmit="return confirm(\'Reset this user\\\'s password?\');">' +
                                                '<input type="hidden" name="<?= html_escape($this->security->get_csrf_token_name()); ?>" value="<?= html_escape($this->security->get_csrf_hash()); ?>">' +
                                                '<input type="hidden" name="id" value="' + data + '">' +
                                                '<button type="submit" class="btn btn-warning btn-sm waves-effect waves-light"><i class="mdi mdi-lock-reset"></i> Reset</button>' +
                                                '</form> ';
                                            <?php if(in_array($this->session->position, array('admin', 'region', 'division', 'ict', 'district'), true)){ ?>
                                            actions += '<form action="<?= base_url(); ?>pages/user_delete" method="post" style="display:inline" onsubmit="return confirm(\'Delete this user account?\')">' +
                                                '<input type="hidden" name="<?= html_escape($this->security->get_csrf_token_name()); ?>" value="<?= html_escape($this->security->get_csrf_hash()); ?>">' +
                                                '<input type="hidden" name="id" value="' + parseInt(data, 10) + '">' +
                                                '<button type="submit" class="btn btn-danger btn-sm"><i class="mdi mdi-trash-can-outline"></i> Delete</button></form>';
                                            <?php } ?>
                                            return '<div class="user-actions">' + actions + '</div>';
                                        }
                                    }
                                ],
                                language: {
                                    search: "_INPUT_",
                                    searchPlaceholder: "Search users..."
                                },
                                initComplete: function() {
                                    console.log('DataTables initialized');
                                    // Update account count from API response
                                    table.on('xhr.dt', function(e, settings, json, xhr) {
                                        console.log('XHR response:', json);
                                        if (json && json.recordsTotal !== undefined) {
                                            $('.account-level').html('<i class="mdi mdi-account-multiple-outline"></i> ' + json.recordsTotal + ' account' + (json.recordsTotal === 1 ? '' : 's'));
                                        }
                                    });
                                }
                            });
                        });
                        </script>
                        </div>
