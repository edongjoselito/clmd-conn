<style>
    .school-profile-page {
        --profile-primary: #8b1e3f;
        --profile-primary-dark: #64142d;
        --profile-border: #e8ecf4;
        --profile-muted: #6b7280;
    }

    .profile-hero {
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
            linear-gradient(135deg, #64142d 0%, #a83255 100%);
        box-shadow: 0 14px 34px rgba(139, 30, 63, .22);
    }

    .profile-hero h2 {
        margin: 0 0 7px;
        color: #fff;
        font-size: 25px;
        font-weight: 700;
    }

    .profile-hero p {
        margin: 0;
        color: rgba(255, 255, 255, .82);
    }

    .profile-card {
        border: 1px solid var(--profile-border);
        border-radius: 16px;
        box-shadow: 0 8px 28px rgba(31, 45, 75, .07);
        overflow: hidden;
        margin-bottom: 24px;
    }

    .profile-card-header {
        padding: 20px 24px;
        border-bottom: 1px solid var(--profile-border);
        background: #f8f9ff;
    }

    .profile-card-header h4 {
        margin: 0 0 3px;
        color: #27324a;
        font-size: 17px;
        font-weight: 700;
    }

    .profile-card-header p {
        margin: 0;
        color: var(--profile-muted);
        font-size: 12px;
    }

    .profile-card-body {
        padding: 24px;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }

    .info-item {
        padding: 16px;
        border-radius: 12px;
        background: #f8f9ff;
        border: 1px solid var(--profile-border);
    }

    .info-label {
        display: block;
        margin-bottom: 8px;
        color: var(--profile-muted);
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .06em;
    }

    .info-value {
        color: #27324a;
        font-size: 14px;
        font-weight: 500;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
    }

    .status-badge.completed {
        background: #d4edda;
        color: #155724;
    }

    .status-badge.pending {
        background: #fff3cd;
        color: #856404;
    }

    .status-badge.not-started {
        background: #f8d7da;
        color: #721c24;
    }

    .back-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        border: 0;
        border-radius: 999px;
        color: #fff;
        background: rgba(255, 255, 255, .2);
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        transition: all .18s ease;
    }

    .back-btn:hover {
        background: #fff;
        color: var(--profile-primary-dark);
    }

    .view-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        border: 1px solid var(--profile-border);
        border-radius: 8px;
        color: var(--profile-primary);
        background: #fff;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        transition: all .18s ease;
    }

    .view-link:hover {
        background: var(--profile-primary);
        color: #fff;
        border-color: var(--profile-primary);
    }
</style>

<div class="school-profile-page">
    <div class="profile-hero">
        <div>
            <h2><?= !empty($school->schoolName) ? mb_convert_case($school->schoolName, MB_CASE_TITLE, 'UTF-8') : 'School Profile'; ?></h2>
            <p><?= html_escape($school->division->description ?? ''); ?> - <?= html_escape($school->district->description ?? ''); ?></p>
        </div>
        <div>
            <a href="<?= base_url(); ?>Pages/school_list_region" class="back-btn">
                <i class="mdi mdi-arrow-left"></i>
                Back to List
            </a>
        </div>
    </div>

    <!-- School Information -->
    <div class="profile-card">
        <div class="profile-card-header">
            <h4>School Information</h4>
            <p>Basic details about the school</p>
        </div>
        <div class="profile-card-body">
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">School ID</span>
                    <span class="info-value"><?= html_escape($school->schoolID); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">School Name</span>
                    <span class="info-value"><?= !empty($school->schoolName) ? mb_convert_case($school->schoolName, MB_CASE_TITLE, 'UTF-8') : ''; ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Division</span>
                    <span class="info-value"><?= html_escape($school->division->description ?? ''); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">District</span>
                    <span class="info-value"><?= html_escape($school->district->description ?? ''); ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- SBM Self-Assessment Checklist -->
    <div class="profile-card">
        <div class="profile-card-header">
            <h4>SBM Self-Assessment Checklist</h4>
            <p>School-Based Management self-assessment status</p>
        </div>
        <div class="profile-card-body">
            <?php if ($sbm) : ?>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Status</span>
                        <span class="status-badge <?= (int) $sbm->stat === 1 ? 'completed' : 'pending'; ?>">
                            <?= (int) $sbm->stat === 1 ? 'Completed' : 'In Progress'; ?>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Fiscal Year</span>
                        <span class="info-value"><?= html_escape($this->session->fy); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Last Updated</span>
                        <span class="info-value"><?= !empty($sbm->updated_at) ? date('F j, Y', strtotime($sbm->updated_at)) : 'N/A'; ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Action</span>
                        <a href="<?= base_url(); ?>Pages/sbm_checklist_pdf/<?= html_escape($school->schoolID); ?>" class="view-link" target="_blank">
                            <i class="mdi mdi-file-pdf"></i> View PDF
                        </a>
                    </div>
                </div>
            <?php else : ?>
                <div class="info-item">
                    <span class="status-badge not-started">Not Started</span>
                    <p style="margin-top: 10px; color: var(--profile-muted);">No SBM checklist data found for this school.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- TA Form -->
    <div class="profile-card">
        <div class="profile-card-header">
            <h4>Technical Assistance (TA) Form</h4>
            <p>Technical assistance form status</p>
        </div>
        <div class="profile-card-body">
            <?php if ($ta) : ?>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Status</span>
                        <span class="status-badge completed">Submitted</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Fiscal Year</span>
                        <span class="info-value"><?= html_escape($this->session->fy); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Last Updated</span>
                        <span class="info-value"><?= !empty($ta->updated_at) ? date('F j, Y', strtotime($ta->updated_at)) : 'N/A'; ?></span>
                    </div>
                </div>
            <?php else : ?>
                <div class="info-item">
                    <span class="status-badge not-started">Not Started</span>
                    <p style="margin-top: 10px; color: var(--profile-muted);">No TA form data found for this school.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- TANA Form -->
    <div class="profile-card">
        <div class="profile-card-header">
            <h4>TANA Form</h4>
            <p>Technical Assistance Needs Assessment status</p>
        </div>
        <div class="profile-card-body">
            <?php if ($tana) : ?>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Status</span>
                        <span class="status-badge completed">Submitted</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Fiscal Year</span>
                        <span class="info-value"><?= html_escape($this->session->fy); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Last Updated</span>
                        <span class="info-value"><?= !empty($tana->updated_at) ? date('F j, Y', strtotime($tana->updated_at)) : 'N/A'; ?></span>
                    </div>
                </div>
            <?php else : ?>
                <div class="info-item">
                    <span class="status-badge not-started">Not Started</span>
                    <p style="margin-top: 10px; color: var(--profile-muted);">No TANA form data found for this school.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Action Plan -->
    <div class="profile-card">
        <div class="profile-card-header">
            <h4>School Action Plan</h4>
            <p>School Governance and Operations Development action plan status</p>
        </div>
        <div class="profile-card-body">
            <?php if ($action_plan) : ?>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Status</span>
                        <span class="status-badge completed">Submitted</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Fiscal Year</span>
                        <span class="info-value"><?= html_escape($this->session->fy); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Last Updated</span>
                        <span class="info-value"><?= !empty($action_plan->updated_at) ? date('F j, Y', strtotime($action_plan->updated_at)) : 'N/A'; ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Action</span>
                        <a href="<?= base_url(); ?>Pages/sbm_action_plan_list/<?= html_escape($school->schoolID); ?>" class="view-link">
                            <i class="mdi mdi-file-document"></i> View Details
                        </a>
                    </div>
                </div>
            <?php else : ?>
                <div class="info-item">
                    <span class="status-badge not-started">Not Started</span>
                    <p style="margin-top: 10px; color: var(--profile-muted);">No action plan data found for this school.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
