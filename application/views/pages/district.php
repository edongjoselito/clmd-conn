<style>
    .district-list-page {
        --district-primary: #8b1e3f;
        --district-primary-dark: #64142d;
        --district-border: #e8ecf4;
        --district-muted: #6b7280;
    }

    .district-list-hero {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        margin: 18px 0 22px;
        padding: 26px 28px;
        border-radius: 18px;
        color: #fff;
        background: linear-gradient(135deg, #64142d 0%, #a83255 100%);
        box-shadow: 0 14px 34px rgba(139, 30, 63, .22);
    }

    .district-list-hero h2 {
        margin: 0 0 6px;
        color: #fff;
        font-size: 25px;
        font-weight: 700;
    }

    .district-list-hero p {
        margin: 0;
        color: rgba(255, 255, 255, .82);
    }

    .district-total {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        flex: 0 0 auto;
        padding: 10px 15px;
        border: 1px solid rgba(255, 255, 255, .24);
        border-radius: 999px;
        color: #fff;
        background: rgba(255, 255, 255, .14);
        font-size: 13px;
        font-weight: 700;
        backdrop-filter: blur(5px);
    }

    .district-list-card {
        border: 1px solid var(--district-border);
        border-radius: 16px;
        box-shadow: 0 8px 28px rgba(31, 45, 75, .07);
        overflow: hidden;
    }

    .district-list-card .card-body {
        padding: 0;
    }

    .district-list-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 20px 24px;
        border-bottom: 1px solid var(--district-border);
    }

    .district-list-toolbar h4 {
        margin: 0 0 3px;
        color: #27324a;
        font-size: 17px;
        font-weight: 700;
    }

    .district-table-wrap {
        padding: 10px 24px 24px;
    }

    .district-table {
        margin: 0;
        border-collapse: separate;
        border-spacing: 0 8px;
    }

    .district-table thead th {
        padding: 11px 14px;
        border: 0;
        color: #687086;
        background: transparent;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .district-table tbody td {
        padding: 14px;
        border-top: 1px solid var(--district-border);
        border-bottom: 1px solid var(--district-border);
        vertical-align: middle;
        background: #fff;
    }

    .district-table tbody td:first-child {
        border-left: 1px solid var(--district-border);
        border-radius: 11px 0 0 11px;
    }

    .district-table tbody td:last-child {
        border-right: 1px solid var(--district-border);
        border-radius: 0 11px 11px 0;
    }

    .district-table tbody tr:hover td {
        background: #fff7f9;
    }

    .district-number {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border-radius: 10px;
        color: var(--district-primary-dark);
        background: #f9e9ee;
        font-size: 12px;
        font-weight: 700;
    }

    .district-name {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 210px;
    }

    .district-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        flex: 0 0 40px;
        border-radius: 12px;
        color: #fff;
        background: linear-gradient(135deg, #8b1e3f, #c65a77);
        font-size: 19px;
    }

    .district-name strong {
        color: #27324a;
        font-weight: 600;
    }

    .submission-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        min-width: 58px;
        padding: 7px 11px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        transition: transform .15s ease, box-shadow .15s ease;
    }

    .submission-link:hover {
        transform: translateY(-1px);
        box-shadow: 0 5px 12px rgba(31, 45, 75, .12);
    }

    .submission-action-plan {
        color: #147a50;
        background: #e6f7ef;
    }

    .submission-assessment {
        color: #176f8c;
        background: #e6f6fb;
    }

    .submission-ta {
        color: #8b1e3f;
        background: #f9e9ee;
    }

    .district-list-page .alert {
        border: 0;
        border-radius: 12px;
        box-shadow: 0 6px 18px rgba(31, 45, 75, .07);
    }

    @media (max-width: 767.98px) {
        .district-list-hero {
            align-items: flex-start;
            flex-direction: column;
            padding: 22px;
            border-radius: 14px;
        }

        .district-list-toolbar {
            align-items: flex-start;
            flex-direction: column;
            padding: 18px;
        }

        .district-table-wrap {
            padding: 8px 14px 18px;
        }

        .district-table thead {
            display: none;
        }

        .district-table,
        .district-table tbody,
        .district-table tr,
        .district-table td {
            display: block;
            width: 100%;
        }

        .district-table {
            border-spacing: 0;
        }

        .district-table tbody tr {
            margin-bottom: 14px;
            padding: 8px 14px;
            border: 1px solid var(--district-border);
            border-radius: 12px;
            background: #fff;
        }

        .district-table tbody td,
        .district-table tbody td:first-child,
        .district-table tbody td:last-child {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            padding: 10px 0;
            border: 0;
            border-bottom: 1px solid #f0f2f7;
            border-radius: 0;
        }

        .district-table tbody td:last-child {
            border-bottom: 0;
        }

        .district-table tbody td::before {
            content: attr(data-label);
            color: var(--district-muted);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .district-table tbody td.district-name-cell {
            align-items: flex-start;
            flex-direction: column;
        }

        .district-name {
            min-width: 0;
        }
    }
</style>

<div class="district-list-page">
    <div class="row">
        <div class="col-12">
            <div class="district-list-hero">
                <div>
                    <h2><i class="mdi mdi-map-marker-multiple mr-2"></i>District List</h2>
                    <p>Review school submissions and technical assistance records by district.</p>
                </div>
                <span class="district-total">
                    <i class="mdi mdi-map-marker-radius"></i>
                    <?= count($data); ?> <?= count($data) === 1 ? 'district' : 'districts'; ?>
                </span>
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
            <div class="card district-list-card">
                <div class="card-body">
                    <div class="district-list-toolbar">
                        <div>
                            <h4>District Submission Overview</h4>
                            <small class="text-muted">Select a submission count to view the participating schools.</small>
                        </div>
                        <small class="text-muted">Fiscal Year <?= html_escape($this->session->fy); ?></small>
                    </div>

                    <div class="district-table-wrap table-responsive">
                        <table class="table district-table">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>District</th>
                                    <th class="text-center">Action Plan</th>
                                    <th class="text-center">Self-Assessment</th>
                                    <th class="text-center">TA Form</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $counter = 1;
                                $submission_types = array(
                                    'sgod_action_plan' => array(
                                        'label' => 'Action Plan',
                                        'class' => 'submission-action-plan',
                                        'icon' => 'mdi-clipboard-check-outline'
                                    ),
                                    'sbm' => array(
                                        'label' => 'Self-Assessment',
                                        'class' => 'submission-assessment',
                                        'icon' => 'mdi-format-list-checks'
                                    ),
                                    'sbm_ta' => array(
                                        'label' => 'TA Form',
                                        'class' => 'submission-ta',
                                        'icon' => 'mdi-lifebuoy'
                                    )
                                );

                                foreach ($data as $row) :
                                ?>
                                    <tr>
                                        <td data-label="No.">
                                            <span class="district-number"><?= $counter++; ?></span>
                                        </td>
                                        <td class="district-name-cell" data-label="District">
                                            <div class="district-name">
                                                <span class="district-icon"><i class="mdi mdi-map-marker-outline"></i></span>
                                                <strong><?= html_escape(mb_convert_case($row->description, MB_CASE_TITLE, 'UTF-8')); ?></strong>
                                            </div>
                                        </td>

                                        <?php foreach ($submission_types as $table => $submission) :
                                            $count = isset($submission_counts[$table][(string) $row->id])
                                                ? $submission_counts[$table][(string) $row->id]
                                                : 0;
                                        ?>
                                            <td class="text-center" data-label="<?= html_escape($submission['label']); ?>">
                                                <a
                                                    class="submission-link <?= $submission['class']; ?>"
                                                    href="<?= base_url(); ?>Pages/school_list_division/<?= $row->id; ?>/<?= $table; ?>"
                                                    title="View <?= html_escape($submission['label']); ?> schools"
                                                >
                                                    <i class="mdi <?= $submission['icon']; ?>"></i>
                                                    <?= $count; ?>
                                                </a>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
