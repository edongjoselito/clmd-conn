<?php
$dashboard_method = strtolower((string) $this->router->fetch_method());
$is_dashboard = in_array($dashboard_method, array('view', 'region', 'division', 'district', 'school_dashboard', 'learning_area_dashboard'), true);
?>
<body<?= $is_dashboard ? ' class="dashboard-wide"' : ''; ?>>

    <!-- Begin page -->
    <div id="wrapper">


        <!-- Topbar Start -->
        <div class="navbar-custom">
            <ul class="list-unstyled topnav-menu float-right mb-0">

                <?php if ($this->session->position == 'division') : ?>
                    <?php
                    // Ensure table exists before querying
                    $this->db->query("CREATE TABLE IF NOT EXISTS `unlock_request` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `request_type` enum('ta','checklist') NOT NULL DEFAULT 'ta',
                      `ta_id` int(11) DEFAULT NULL,
                      `checklist_id` int(11) DEFAULT NULL,
                      `school_id` varchar(255) NOT NULL,
                      `division_id` int(11) NOT NULL,
                      `requested_by` varchar(255) NOT NULL,
                      `request_date` datetime NOT NULL,
                      `status` enum('pending','approved','cleared') DEFAULT 'pending',
                      `processed_date` datetime DEFAULT NULL,
                      `processed_by` varchar(255) DEFAULT NULL,
                      PRIMARY KEY (`id`),
                      KEY `request_type` (`request_type`),
                      KEY `ta_id` (`ta_id`),
                      KEY `checklist_id` (`checklist_id`),
                      KEY `school_id` (`school_id`),
                      KEY `division_id` (`division_id`),
                      KEY `status` (`status`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

                    $pending_requests = $this->db->where('division_id', $this->session->division)
                        ->where('status', 'pending')
                        ->order_by('request_date', 'DESC')
                        ->get('unlock_request')
                        ->result();
                    $request_count = count($pending_requests);
                    ?>
                    <li class="dropdown notification-list">
                        <a class="nav-link dropdown-toggle arrow-none waves-effect" data-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                            <i class="mdi mdi-bell-outline noti-icon"></i>
                            <?php if ($request_count > 0) : ?>
                                <span class="noti-icon-badge"><?= $request_count; ?></span>
                            <?php endif; ?>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right dropdown-menu-animated dropdown-lg">
                            <div class="dropdown-item noti-title">
                                <h5 class="m-0">
                                    <span class="float-right">
                                        <?= form_open('Pages/clear_unlock_requests', array('style' => 'display:inline;')); ?>
                                            <button type="submit" class="btn btn-link text-dark p-0"><small>Clear All</small></button>
                                        <?= form_close(); ?>
                                    </span>
                                    Unlock Requests
                                </h5>
                            </div>
                            <?php if ($request_count > 0) : ?>
                                <?php foreach ($pending_requests as $request) : ?>
                                    <?php
                                    $school = $this->Common->one_cond_row('schools', 'schoolID', $request->school_id);
                                    $school_name = $school ? $school->schoolName : 'Unknown School';
                                    $request_type_label = $request->request_type == 'ta' ? 'TA Report' : 'Checklist';
                                    ?>
                                    <a href="<?= base_url(); ?>Pages/unlock_request_view/<?= $request->id; ?>" class="dropdown-item notify-item active">
                                        <div class="notify-icon">
                                            <i class="mdi mdi-lock-open-variant-outline text-primary"></i>
                                        </div>
                                        <p class="notify-details">
                                            <strong><?= html_escape($school_name); ?></strong>
                                            <small class="text-muted">Requested <?= html_escape($request_type_label); ?> unlock</small>
                                            <small class="text-muted"><?= date('M d, H:i', strtotime($request->request_date)); ?></small>
                                        </p>
                                    </a>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <div class="dropdown-item notify-item">
                                    <div class="notify-icon">
                                        <i class="mdi mdi-check-circle-outline text-success"></i>
                                    </div>
                                    <p class="notify-details">
                                        <strong>No pending requests</strong>
                                        <small class="text-muted">All unlock requests have been processed</small>
                                    </p>
                                </div>
                            <?php endif; ?>
                            <a href="<?= base_url(); ?>Pages/unlock_requests" class="dropdown-item notify-item text-center text-primary">
                                View All Requests
                            </a>
                        </div>
                    </li>
                <?php endif; ?>

                <?php
                $selected_fiscal_year = isset($this->session->fy) ? (int) $this->session->fy : (int) date('Y');
                $last_fiscal_year = max((int) date('Y') + 1, $selected_fiscal_year);
                ?>
                <li class="topbar-fiscal-year">
                    <form action="<?= base_url('Pages/change_fy'); ?>" method="post">
                        <input type="hidden" name="<?= html_escape($this->security->get_csrf_token_name()); ?>" value="<?= html_escape($this->security->get_csrf_hash()); ?>">
                        <input type="hidden" name="return_to" value="<?= html_escape($this->uri->uri_string()); ?>">
                        <label for="globalFiscalYear">
                            <i class="mdi mdi-calendar-range" aria-hidden="true"></i>
                            <span>Fiscal Year</span>
                        </label>
                        <select id="globalFiscalYear" name="new_fy" aria-label="Change fiscal year" onchange="this.form.submit()">
                            <?php for ($year = 2023; $year <= $last_fiscal_year; $year++) : ?>
                                <option value="<?= $year; ?>" <?= $selected_fiscal_year === $year ? 'selected' : ''; ?>><?= $year; ?></option>
                            <?php endfor; ?>
                        </select>
                    </form>
                </li>

                <li class="dropdown notification-list">
                    <a class="nav-link dropdown-toggle nav-user mr-0 waves-effect" data-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                        <?php $image = $this->Common->one_cond_row_select('users','username,image','username',$this->session->username); if (empty($image->image ?? null)) {?>
                            <img src="<?= base_url(); ?>assets/images/users/avatar-1.jpg" alt="user-image" class="rounded-circle">
                        <?php }else{ ?>
                            <img src="<?= base_url(); ?>uploads/<?= rawurlencode(basename((string) $image->image)); ?>" alt="user-image" class="rounded-circle">
                        <?php } ?>
                        <span class="pro-user-name ml-1">
                            <?= html_escape($this->session->user); ?> <i class="mdi mdi-chevron-down"></i>
                        </span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right profile-dropdown ">
                        <!-- item-->
                        <div class="dropdown-header noti-title">
                            <h6 class="text-overflow m-0">Welcome !</h6>
                        </div>

                        <!-- item-->
                        <?php if (strpos((string) $this->session->position, 'la_') !== 0): ?>
                        <a href="<?= base_url('Pages/edit_profile'); ?>" class="dropdown-item notify-item">
                            <i class="mdi mdi-account-outline"></i>
                            <span>Profile</span>
                        </a>

                        <!-- item-->
                        <button type="button" class="dropdown-item notify-item" style="border:0;background:transparent;width:100%;text-align:left;" data-toggle="modal" data-target="#ivankylecrodua">
                            <i class="mdi mdi-lock-outline"></i>
                            <span>Change Profile Image</span>
                        </button>

                        <?php endif; ?>
                        <div class="dropdown-divider"></div>

                        <!-- item-->
                        <?= form_open('logout', array('style' => 'margin:0;')); ?>
                            <button type="submit" class="dropdown-item notify-item" style="border:0;background:transparent;width:100%;text-align:left;">
                                <i class="mdi mdi-logout-variant"></i>
                                <span>Logout</span>
                            </button>
                        <?= form_close(); ?>

                    </div>
                </li>




            </ul>

            <!-- LOGO -->
            <div class="logo-box">
                <a href="<?= base_url(); ?>" class="logo text-center logo-dark" aria-label="CLMD home">
                    <span class="logo-lg"><span class="clmd-wordmark">CLMD</span></span>
                    <span class="logo-sm"><span class="clmd-wordmark">CLMD</span></span>
                </a>

                <a href="<?= base_url(); ?>" class="logo text-center logo-light" aria-label="CLMD home">
                    <span class="logo-lg"><span class="clmd-wordmark">CLMD</span></span>
                    <span class="logo-sm"><span class="clmd-wordmark">CLMD</span></span>
                </a>
            </div>

            <!-- LOGO -->


            <ul class="list-unstyled topnav-menu topnav-menu-left m-0">
                <li>
                    <button class="button-menu-mobile waves-effect" type="button" aria-label="Toggle navigation menu" title="Toggle navigation menu">
                        <i class="mdi mdi-menu"></i>
                    </button>
                </li>
                <li>
                    <a class="global-back-button waves-effect"
                       href="<?= base_url(); ?>"
                       data-app-base="<?= base_url(); ?>"
                       aria-label="Go back to the previous page"
                       title="Go back">
                        <i class="mdi mdi-arrow-left" aria-hidden="true"></i>
                        <span>Back</span>
                    </a>
                </li>


            </ul>

            <script>
                (function () {
                    var backButton = document.querySelector('.global-back-button');

                    if (!backButton) {
                        return;
                    }

                    backButton.addEventListener('click', function (event) {
                        var referrer = document.referrer;

                        if (!referrer) {
                            return;
                        }

                        try {
                            var previousUrl = new URL(referrer);
                            var currentUrl = new URL(window.location.href);
                            var appBaseUrl = new URL(backButton.getAttribute('data-app-base'));
                            var cameFromThisApp = previousUrl.origin === appBaseUrl.origin
                                && previousUrl.pathname.indexOf(appBaseUrl.pathname) === 0;
                            var isDifferentPage = previousUrl.href !== currentUrl.href;

                            if (cameFromThisApp && isDifferentPage) {
                                event.preventDefault();
                                window.history.back();
                            }
                        } catch (error) {
                            // Keep the link's dashboard fallback if a URL cannot be parsed.
                        }
                    });
                }());
            </script>
        </div>
        <!-- end Topbar --> <!-- ========== Left Sidebar Start ========== -->

        <div class="left-side-menu">

            <div class="slimscroll-menu">

                <!--- Sidemenu -->
                <div id="sidebar-menu">
                    <ul class="metismenu" id="side-menu">

                        <li class="menu-title">Navigation</li>

                        <li><a href="<?= base_url(); ?>" class="waves-effect"><i class="mdi mdi-view-dashboard"></i><span> Dashboard </span></a></li>
                        <?php if (strpos((string) $this->session->position, 'la_') === 0): ?>
                            <li><a href="<?= base_url('focal-persons'); ?>" class="waves-effect"><i class="mdi mdi-account-group-outline"></i><span> Focal Persons </span></a></li>
                        <?php endif; ?>
                        <?php if ($this->session->position == 'district') { ?>
                            <li><a href="<?= base_url(); ?>pages/schools_district/<?= $this->session->district; ?>" class="waves-effect"><i class="fas fa-school"></i><span>Schools</span></a></li>
                            <li><a href="<?= base_url(); ?>Pages/learning_gap" class="waves-effect"><i class="fas fa-chart-bar"></i><span> Learning Gap Overview</span></a></li>
                            <li><a href="<?= base_url(); ?>Pages/learning_gap_school_summary" class="waves-effect"><i class="fas fa-school"></i><span> Learning Gap Summary</span></a></li>
                            <li><a href="<?= base_url(); ?>Pages/learning_gap_records" class="waves-effect"><i class="fas fa-list"></i><span> Submitted Learning Gap</span></a></li>
                            <li><a href="<?= base_url(); ?>Pages/learning_gap_archives" class="waves-effect"><i class="mdi mdi-archive"></i><span> Archived Records</span></a></li>
                            <li><a href="<?= base_url(); ?>pages/userlist" class="waves-effect"><i class="mdi mdi-account-supervisor"></i><span> Manage Users </span></a></li>
                            <li><a href="#" class="waves-effect" data-toggle="modal" data-target="#renren"><i class="fas fa-lock"></i><span>Change Password</span></a></li>
                        <?php } ?>

                        <?php if ($this->session->position == 'school') { ?>

                            <li><a href="<?= base_url(); ?>Pages/learning_gap" class="waves-effect"><i class="fas fa-chart-bar"></i><span> Learning Gap Monitoring</span></a></li>
                            <li><a href="<?= base_url(); ?>Pages/phil_iri" class="waves-effect"><i class="mdi mdi-book-open-page-variant"></i><span> Phil-IRI Data Entry</span></a></li>
                            <li><a href="<?= base_url(); ?>Pages/learning_gap_records" class="waves-effect"><i class="fas fa-list"></i><span> My Learning Gap Records</span></a></li>
                            <li><a href="<?= base_url(); ?>Pages/learning_gap_archives" class="waves-effect"><i class="mdi mdi-archive"></i><span> Archived Records</span></a></li>

                            <li><a href="#" class="waves-effect" data-toggle="modal" data-target="#renren"><i class="fas fa-lock"></i><span>Change Password</span></a></li>
                        <?php } ?>

                        <?php if ($this->session->position == 'division') { ?>
                            <!-- <li><a href="<?= base_url(); ?>pages/schools_division/<?= $this->session->division; ?>" class="waves-effect"><i class="fas fa-school"></i><span>Schools</span></a></li> -->
                            <li>
                                <a href="<?= base_url(); ?>Pages/learning_gap" class="waves-effect"><i class="fas fa-chart-bar"></i><span> Learning Gap Overview</span></a>
                            </li>
                            <li><a href="<?= base_url(); ?>Pages/learning_gap_school_summary" class="waves-effect"><i class="fas fa-school"></i><span> Learning Gap Summary</span></a></li>
                            <li><a href="<?= base_url(); ?>Pages/learning_gap_records" class="waves-effect"><i class="fas fa-list"></i><span> Submitted Learning Gap</span></a></li>
                            <li><a href="<?= base_url(); ?>Pages/learning_gap_archives" class="waves-effect"><i class="mdi mdi-archive"></i><span> Archived Records</span></a></li>
                            <li><a href="<?= base_url(); ?>Pages/regional_phil_iri_report" class="waves-effect"><i class="mdi mdi-book-open-page-variant"></i><span> Phil-IRI KS2 Report</span></a></li>
                            <li><a href="<?= base_url(); ?>Pages/school_submission_monitoring" class="waves-effect"><i class="mdi mdi-clipboard-check-outline"></i><span> School Submissions</span></a></li>
                            <li>
                                <a href="javascript: void(0);" class="waves-effect">
                                    <i class="fas fa-cogs"></i>
                                    <span> Division Settings</span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <ul class="nav-second-level" aria-expanded="false">
                                    <li><a href="<?= base_url(); ?>pages/district_account/<?= $this->session->division; ?>">Districts</a></li>
                                    <li><a href="<?= base_url(); ?>pages/schools_division/<?= $this->session->division; ?>">Manage Schools</a></li>
                                    <li><a href="<?= base_url(); ?>Pages/division_setup">Division Setup</a></li>
                                </ul>
                            </li>
                            <li><a href="<?= base_url(); ?>pages/userlist_division" class="waves-effect"><i class="mdi mdi-account-supervisor"></i><span> Manage Users </span></a></li>
                            <li><a href="#" class="waves-effect" data-toggle="modal" data-target="#renren"><i class="fas fa-lock"></i><span>Change Password</span></a></li>

                        <?php } ?>

                        <?php if ($this->session->position == 'division_head') { ?>
                            <!-- <li><a href="<?= base_url(); ?>pages/schools_division/<?= $this->session->division; ?>" class="waves-effect"><i class="fas fa-school"></i><span>Schools</span></a></li> -->
                            
                            <li><a href="<?= base_url(); ?>Pages/learning_gap" class="waves-effect"><i class="fas fa-chart-bar"></i><span> Learning Gap Overview</span></a></li>
                            <li><a href="<?= base_url(); ?>Pages/learning_gap_school_summary" class="waves-effect"><i class="fas fa-school"></i><span> Learning Gap Summary</span></a></li>
                            <li><a href="<?= base_url(); ?>Pages/learning_gap_records" class="waves-effect"><i class="fas fa-list"></i><span> Submitted Learning Gap</span></a></li>
                            <li><a href="<?= base_url(); ?>Pages/learning_gap_archives" class="waves-effect"><i class="mdi mdi-archive"></i><span> Archived Records</span></a></li>
                            <li><a href="<?= base_url(); ?>Pages/regional_phil_iri_report" class="waves-effect"><i class="mdi mdi-book-open-page-variant"></i><span> Phil-IRI KS2 Report</span></a></li>
                            <li><a href="#" class="waves-effect" data-toggle="modal" data-target="#renren"><i class="fas fa-lock"></i><span>Change Password</span></a></li>

                        <?php } ?>

                        <?php if ($this->session->position == 'region') { ?>
                            <li><a href="<?= base_url('region/learning-areas'); ?>" class="waves-effect"><i class="mdi mdi-book-open-page-variant"></i><span> Learning Area Dashboards </span></a></li>
                            <li>
                                <a href="javascript: void(0);" class="waves-effect"><i class="mdi mdi-clipboard-check-outline"></i><span> Monitoring</span><span class="menu-arrow"></span></a>
                                <ul class="nav-second-level" aria-expanded="false">
                                    <li><a href="<?= base_url('monitoring/tool'); ?>">Tools</a></li>
                                    <li>
                                        <a href="javascript: void(0);">Settings <span class="menu-arrow"></span></a>
                                        <ul class="nav-third-level" aria-expanded="false">
                                            <li><a href="<?= base_url('monitoring-tool/settings/tools'); ?>">Monitoring Tools</a></li>
                                        </ul>
                                    </li>
                                </ul>
                            </li>
                        <?php } ?>

                        <?php if ($this->session->position == 'region') { ?>

                            <li><a href="<?= base_url(); ?>Pages/learning_gap" class="waves-effect"><i class="fas fa-chart-bar"></i><span> Learning Gap Overview</span></a></li>
                            <li><a href="<?= base_url(); ?>Pages/regional_phil_iri_report" class="waves-effect"><i class="mdi mdi-book-open-page-variant"></i><span> Phil-IRI Regional Report</span></a></li>
                            <li><a href="<?= base_url(); ?>Pages/learning_gap_school_summary" class="waves-effect"><i class="fas fa-school"></i><span> Learning Gap Summary</span></a></li>
                            <li><a href="<?= base_url(); ?>Pages/learning_gap_records" class="waves-effect"><i class="fas fa-list"></i><span> Submitted Learning Gap</span></a></li>
                            <li><a href="<?= base_url(); ?>Pages/learning_gap_archives" class="waves-effect"><i class="mdi mdi-archive"></i><span> Archived Records</span></a></li>
                            <li><a href="<?= base_url(); ?>Pages/learning_area_setup" class="waves-effect"><i class="mdi mdi-tune"></i><span> Learning Area Setup</span></a></li>
                            <li><a href="<?= base_url('focal-persons'); ?>" class="waves-effect"><i class="mdi mdi-account-group-outline"></i><span> Focal Persons </span></a></li>
                            <li><a href="<?= base_url('pages/userlist'); ?>" class="waves-effect"><i class="mdi mdi-account-supervisor"></i><span> Manage Users </span></a></li>
                            <li><a href="#" class="waves-effect" data-toggle="modal" data-target="#renren"><i class="fas fa-lock"></i><span>Change Password</span></a></li>

                        <?php } ?>

                        <?php if ($this->session->position == 'admin') { ?>
                            <li>
                                <a href="javascript: void(0);" class="waves-effect">
                                    <i class="fas fa-school"></i>
                                    <span> Schools</span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <ul class="nav-second-level" aria-expanded="false">
                                    <li><a href="<?= base_url(); ?>pages/school_by_district">Schools By Division</a></li>
                                    <li><a href="<?= base_url(); ?>pages/school_list">List of Schools</a></li>
                                    <li><a href="<?= base_url(); ?>pages/summative_cpl_access">Summative CPL Access</a></li>
                                </ul>
                            </li>
                            <li><a href="<?= base_url(); ?>pages/userlist" class="waves-effect"><i class="mdi mdi-account-supervisor"></i><span> Manage Users </span></a></li>
                        <?php } ?>

                        <?php if ($this->session->position == 'ict') { ?>
                            <li><a href="<?= base_url(); ?>pages/schools/<?= $this->session->division; ?>" class="waves-effect"><i class="fas fa-school"></i><span>Schools</span></a></li>
                            <li><a href="<?= base_url(); ?>Pages/regional_phil_iri_report" class="waves-effect"><i class="mdi mdi-book-open-page-variant"></i><span> Phil-IRI KS2 Report</span></a></li>
                            <li><a href="<?= base_url(); ?>pages/userlist_division" class="waves-effect"><i class="mdi mdi-account-supervisor"></i><span> Manage Users </span></a></li>
                        <?php } ?>
                    </ul>

                </div>
                <!-- End Sidebar -->

                <div class="clearfix"></div>

            </div>
            <!-- Sidebar -left -->

        </div>
        <!-- Left Sidebar End -->


        <!-- ============================================================== -->
        <!-- Start Page Content here -->
        <!-- ============================================================== -->

        <div class="content-page">
            <div class="content">

                <!-- Start Content-->
                <div class="container-fluid">
