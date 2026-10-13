<?php
$school_name = mb_convert_case($data->schoolName, MB_CASE_TITLE, 'UTF-8');
$school_initials = '';
foreach (preg_split('/\s+/', trim($school_name)) as $word) {
    if ($word !== '') {
        $school_initials .= mb_substr($word, 0, 1, 'UTF-8');
    }
    if (mb_strlen($school_initials, 'UTF-8') >= 3) {
        break;
    }
}
$school_initials = mb_strtoupper($school_initials, 'UTF-8');

$head_name = trim(implode(' ', array_filter(array(
    $data->adminFName,
    $data->adminMName,
    $data->adminLName
))));
$head_name = $head_name !== '' ? mb_convert_case($head_name, MB_CASE_TITLE, 'UTF-8') : 'Not provided';

$division_name = $division ? mb_convert_case($division->description, MB_CASE_TITLE, 'UTF-8') : 'Not provided';
$district_name = $district ? mb_convert_case($district->description, MB_CASE_TITLE, 'UTF-8') : 'Not provided';
$location = implode(', ', array_filter(array(
    !empty($data->brgy) ? mb_convert_case($data->brgy, MB_CASE_TITLE, 'UTF-8') : null,
    !empty($data->city) ? mb_convert_case($data->city, MB_CASE_TITLE, 'UTF-8') : null,
    !empty($data->province) ? mb_convert_case($data->province, MB_CASE_TITLE, 'UTF-8') : null
)));
$location = $location !== '' ? $location : 'Not provided';

$category_labels = array(
    1 => 'Elementary',
    2 => 'Integrated (Elementary & JHS)',
    3 => 'Integrated (Elementary, JHS & SHS)',
    4 => 'Secondary (JHS only)',
    5 => 'Secondary (JHS & SHS)',
    6 => 'SHS – Stand Alone'
);
$offering_labels = array(
    1 => 'None',
    2 => 'School-Based ALS Program',
    3 => 'TLE-TVL Course Offerings',
    4 => 'School-Based ALS and TLE-TVL'
);
$sgc_labels = array(
    1 => 'Not Yet Organized',
    2 => 'Organized, Not Functional',
    3 => 'Functional'
);
?>

<style>
    .school-profile-page {
        --sp-navy: var(--llcm-navy, #3b0718);
        --sp-blue: var(--llcm-blue, #922c43);
        --sp-sky: var(--llcm-sky, #fbf4f5);
        --sp-line: var(--llcm-border, #ead7dc);
        --sp-ink: var(--llcm-ink, #233342);
        --sp-muted: #6b7f92;
        --sp-radius: 10px;
        --sp-shadow: 0 4px 16px rgba(20, 62, 94, .05);
        margin-bottom: 24px;
        color: var(--sp-ink);
    }

    .school-profile-page .alert {
        border: 0;
        border-radius: var(--sp-radius);
        box-shadow: var(--sp-shadow);
    }

    /* ---------- Page header ---------- */
    .sp-header {
        display: flex;
        flex-wrap: wrap;
        gap: 14px 18px;
        align-items: center;
        justify-content: space-between;
        margin: 16px 0 18px;
        padding: 18px 22px;
        border-radius: var(--sp-radius);
        color: #fff;
        background: linear-gradient(118deg, var(--sp-navy), var(--sp-blue));
    }

    .sp-identity {
        display: flex;
        align-items: center;
        gap: 16px;
        min-width: 0;
    }

    .sp-avatar {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 52px;
        height: 52px;
        flex: 0 0 52px;
        border-radius: 10px;
        color: var(--sp-navy);
        background: #fff;
        font-size: 17px;
        font-weight: 700;
    }

    .sp-eyebrow {
        display: block;
        margin-bottom: 3px;
        color: rgba(255, 255, 255, .75);
        font-size: .72rem;
        font-weight: 600;
        letter-spacing: .09em;
        text-transform: uppercase;
    }

    .sp-header h1 {
        margin: 0;
        color: #fff;
        font-size: 1.45rem;
        font-weight: 600;
        line-height: 1.25;
    }

    .sp-header-meta {
        margin: 6px 0 0;
        color: rgba(255, 255, 255, .85);
        font-size: .85rem;
    }

    .sp-header-meta span + span::before {
        content: "\00b7";
        margin: 0 .5rem;
        color: rgba(255, 255, 255, .5);
    }

    .sp-header-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .sp-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 13px;
        border: 1px solid transparent;
        border-radius: 8px;
        font-size: .83rem;
        font-weight: 600;
        text-decoration: none;
        white-space: nowrap;
        transition: background .15s ease, border-color .15s ease, color .15s ease;
    }

    .sp-btn-ghost {
        color: #fff;
        border-color: rgba(255, 255, 255, .45);
        background: transparent;
    }

    .sp-btn-ghost:hover {
        color: var(--sp-navy);
        background: #fff;
        border-color: #fff;
        text-decoration: none;
    }

    .sp-btn-solid {
        color: var(--sp-navy);
        background: #fff;
        border-color: #fff;
    }

    .sp-btn-solid:hover {
        color: var(--sp-navy);
        background: var(--sp-sky);
        border-color: var(--sp-sky);
        text-decoration: none;
    }

    /* ---------- Fact tiles ---------- */
    .sp-facts {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 18px;
    }

    .sp-fact {
        display: flex;
        gap: 12px;
        align-items: center;
        padding: 14px 16px;
        border: 1px solid var(--sp-line);
        border-radius: var(--sp-radius);
        background: #fff;
        box-shadow: var(--sp-shadow);
    }

    .sp-fact-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 32px;
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: var(--sp-sky);
        color: var(--sp-blue);
        font-size: 1rem;
    }

    .sp-fact small {
        display: block;
        margin-bottom: 2px;
        color: var(--sp-muted);
        font-size: .72rem;
        font-weight: 600;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .sp-fact strong {
        display: block;
        color: var(--sp-navy);
        font-size: .88rem;
        font-weight: 600;
        line-height: 1.4;
    }

    /* ---------- Details panel ---------- */
    .sp-panel {
        border: 1px solid var(--sp-line);
        border-radius: var(--sp-radius);
        background: #fff;
        box-shadow: var(--sp-shadow);
        overflow: hidden;
    }

    .sp-panel-head {
        padding: 15px 20px;
        border-bottom: 1px solid var(--sp-line);
    }

    .sp-panel-head h4 {
        margin: 0 0 2px;
        color: var(--sp-navy);
        font-size: 1.02rem;
        font-weight: 600;
    }

    .sp-panel-head p {
        margin: 0;
        color: var(--sp-muted);
        font-size: .84rem;
    }

    .sp-detail-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .sp-detail {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 14px 20px;
        border-top: 1px solid #edf3f8;
    }

    .sp-detail:nth-child(-n + 2) { border-top: 0; }
    .sp-detail:nth-child(odd) { border-right: 1px solid #edf3f8; }

    .sp-detail-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 30px;
        width: 30px;
        height: 30px;
        margin-top: 1px;
        border-radius: 8px;
        background: var(--sp-sky);
        color: var(--sp-blue);
        font-size: .95rem;
    }

    .sp-detail small {
        display: block;
        margin-bottom: 2px;
        color: var(--sp-muted);
        font-size: .7rem;
        font-weight: 600;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .sp-detail strong,
    .sp-detail a {
        color: var(--sp-ink);
        font-size: .86rem;
        font-weight: 600;
        word-break: break-word;
    }

    .sp-detail a { color: var(--sp-blue); }
    .sp-detail a:hover { color: var(--sp-navy); }

    @media (max-width: 767.98px) {
        .sp-header { padding: 16px 18px; }
        .sp-header h1 { font-size: 1.25rem; }
        .sp-header-actions { width: 100%; }
        .sp-header-actions .sp-btn { flex: 1 1 auto; justify-content: center; }
        .sp-facts { grid-template-columns: 1fr; gap: 10px; }
        .sp-detail-grid { grid-template-columns: 1fr; }
        .sp-detail:nth-child(odd) { border-right: 0; }
        .sp-detail:nth-child(-n + 2) { border-top: 1px solid #edf3f8; }
        .sp-detail:first-child { border-top: 0; }
    }
</style>

<div class="school-profile-page">
    <header class="sp-header">
        <div class="sp-identity">
            <span class="sp-avatar"><?= html_escape($school_initials); ?></span>
            <div>
                <span class="sp-eyebrow">School Profile</span>
                <h1><?= html_escape($school_name); ?></h1>
                <p class="sp-header-meta">
                    <span>School ID <?= html_escape($data->schoolID); ?></span>
                    <span><?= html_escape($district_name); ?></span>
                    <span><?= html_escape($division_name); ?></span>
                </p>
            </div>
        </div>
        <div class="sp-header-actions">
            <a href="javascript:history.back()" class="sp-btn sp-btn-ghost">
                <i class="mdi mdi-arrow-left"></i> Back
            </a>
            <a href="<?= base_url(); ?>Pages/school_update/<?= $data->recID; ?>" class="sp-btn sp-btn-solid">
                <i class="mdi mdi-pencil-outline"></i> Edit Profile
            </a>
        </div>
    </header>

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

    <div class="sp-facts">
        <div class="sp-fact">
            <span class="sp-fact-icon"><i class="mdi mdi-school-outline"></i></span>
            <div>
                <small>School Category</small>
                <strong><?= html_escape(isset($category_labels[(int) $data->category]) ? $category_labels[(int) $data->category] : 'Not provided'); ?></strong>
            </div>
        </div>
        <div class="sp-fact">
            <span class="sp-fact-icon"><i class="mdi mdi-book-open-page-variant"></i></span>
            <div>
                <small>Program Offerings</small>
                <strong><?= html_escape(isset($offering_labels[(int) $data->schoolType]) ? $offering_labels[(int) $data->schoolType] : 'Not provided'); ?></strong>
            </div>
        </div>
        <div class="sp-fact">
            <span class="sp-fact-icon"><i class="mdi mdi-account-group-outline"></i></span>
            <div>
                <small>School Governance Council</small>
                <strong><?= html_escape(isset($sgc_labels[(int) $data->sgc]) ? $sgc_labels[(int) $data->sgc] : 'Not provided'); ?></strong>
            </div>
        </div>
    </div>

    <section class="sp-panel">
        <div class="sp-panel-head">
            <h4>Official Information</h4>
            <p>School leadership and official contact details.</p>
        </div>
        <div class="sp-detail-grid">
            <div class="sp-detail">
                <span class="sp-detail-icon"><i class="mdi mdi-account-tie-outline"></i></span>
                <div>
                    <small>School Head</small>
                    <strong><?= html_escape($head_name); ?></strong>
                </div>
            </div>

            <div class="sp-detail">
                <span class="sp-detail-icon"><i class="mdi mdi-briefcase-outline"></i></span>
                <div>
                    <small>Designation</small>
                    <strong><?= html_escape(!empty($data->adminDesignation) ? mb_convert_case($data->adminDesignation, MB_CASE_TITLE, 'UTF-8') : 'Not provided'); ?></strong>
                </div>
            </div>

            <div class="sp-detail">
                <span class="sp-detail-icon"><i class="mdi mdi-email-outline"></i></span>
                <div>
                    <small>School Head Email</small>
                    <?php if (!empty($data->adminEmail)) { ?>
                        <a href="mailto:<?= html_escape($data->adminEmail); ?>"><?= html_escape($data->adminEmail); ?></a>
                    <?php } else { ?>
                        <strong>Not provided</strong>
                    <?php } ?>
                </div>
            </div>

            <div class="sp-detail">
                <span class="sp-detail-icon"><i class="mdi mdi-at"></i></span>
                <div>
                    <small>School Email</small>
                    <?php if (!empty($data->schoolEmail)) { ?>
                        <a href="mailto:<?= html_escape($data->schoolEmail); ?>"><?= html_escape($data->schoolEmail); ?></a>
                    <?php } else { ?>
                        <strong>Not provided</strong>
                    <?php } ?>
                </div>
            </div>

            <div class="sp-detail">
                <span class="sp-detail-icon"><i class="mdi mdi-phone-outline"></i></span>
                <div>
                    <small>Contact Number</small>
                    <?php if (!empty($data->adminMobile)) { ?>
                        <a href="tel:<?= html_escape($data->adminMobile); ?>"><?= html_escape($data->adminMobile); ?></a>
                    <?php } else { ?>
                        <strong>Not provided</strong>
                    <?php } ?>
                </div>
            </div>

            <div class="sp-detail">
                <span class="sp-detail-icon"><i class="mdi mdi-map-marker-radius"></i></span>
                <div>
                    <small>Location</small>
                    <strong><?= html_escape($location); ?></strong>
                </div>
            </div>
        </div>
    </section>
</div>
