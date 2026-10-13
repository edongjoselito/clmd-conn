<?php
$result_count = count($records);

// Calculate summaries per district
$district_summary = array();
foreach ($records as $record) {
    $district_name = !empty($record->district_name) ? $record->district_name : 'Unassigned';
    if (!isset($district_summary[$district_name])) {
        $district_summary[$district_name] = 0;
    }
    $district_summary[$district_name]++;
}

// School category labels from signup
$category_labels = array(
    1 => 'Elementary',
    2 => 'Integrated (Elem & JHS)',
    3 => 'Integrated (Elem, JHS, & SHS)',
    4 => 'Secondary (JHS only)',
    5 => 'Secondary (JHS & SHS)',
    6 => 'SHS - Stand Alone'
);

// Calculate summaries per school category
$category_summary = array();
foreach ($records as $record) {
    $category_id = !empty($record->category) ? (int) $record->category : 0;
    $category_name = isset($category_labels[$category_id]) ? $category_labels[$category_id] : 'Uncategorized';
    if (!isset($category_summary[$category_name])) {
        $category_summary[$category_name] = 0;
    }
    $category_summary[$category_name]++;
}
?>

<style>
    .division-detail-page {
        --detail-primary: #8b1e3f;
        --detail-primary-dark: #64142d;
        --detail-border: #e8ecf4;
        --detail-muted: #6b7280;
    }

    .division-detail-hero {
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

    .division-detail-hero h2 {
        margin: 0 0 7px;
        color: #fff;
        font-size: 25px;
        font-weight: 700;
    }

    .division-detail-hero p {
        margin: 0;
        color: rgba(255, 255, 255, .82);
    }

    .division-detail-actions {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }

    .division-detail-pill,
    .division-detail-back {
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

    .division-detail-back:hover {
        color: var(--detail-primary-dark);
        background: #fff;
    }

    .division-detail-card {
        border: 1px solid var(--detail-border);
        border-radius: 16px;
        box-shadow: 0 8px 28px rgba(31, 45, 75, .07);
        overflow: hidden;
    }

    .division-detail-card .card-body {
        padding: 0;
    }

    .division-detail-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 20px 24px;
        border-bottom: 1px solid var(--detail-border);
    }

    .division-detail-toolbar h4 {
        margin: 0 0 3px;
        color: #27324a;
        font-size: 17px;
        font-weight: 700;
    }

    .division-detail-toolbar p {
        margin: 0;
        color: var(--detail-muted);
        font-size: 12px;
    }

    .detail-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 11px;
        border-radius: 999px;
        color: var(--detail-primary-dark);
        background: #f9e9ee;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
    }

    .division-detail-table-wrap {
        padding: 8px 24px 22px;
    }

    .division-detail-page .dataTables_wrapper .row:first-child {
        align-items: center;
        padding: 12px 0 6px;
    }

    .division-detail-page .dataTables_filter input,
    .division-detail-page .dataTables_length select {
        min-height: 38px;
        border: 1px solid #dce2ee;
        border-radius: 9px;
        box-shadow: none;
    }

    .division-detail-page table.dataTable {
        margin-top: 12px !important;
        border-collapse: separate !important;
        border-spacing: 0 8px !important;
    }

    .division-detail-page table.dataTable thead th {
        padding: 11px 14px;
        border: 0;
        color: #687086;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .division-detail-page table.dataTable tbody td {
        padding: 14px;
        border-top: 1px solid var(--detail-border);
        border-bottom: 1px solid var(--detail-border);
        vertical-align: middle;
        background: #fff;
    }

    .division-detail-page table.dataTable tbody td:first-child {
        border-left: 1px solid var(--detail-border);
        border-radius: 11px 0 0 11px;
    }

    .division-detail-page table.dataTable tbody td:last-child {
        border-right: 1px solid var(--detail-border);
        border-radius: 0 11px 11px 0;
    }

    .division-detail-page table.dataTable tbody tr:hover td {
        background: #fff7f9;
    }

    .detail-school-cell {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 240px;
    }

    .detail-school-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        flex: 0 0 42px;
        border-radius: 12px;
        color: #fff;
        background: linear-gradient(135deg, #8b1e3f, #c65a77);
        font-size: 19px;
    }

    .detail-school-name {
        color: #27324a;
        font-weight: 600;
    }

    .detail-school-id {
        color: #596277;
        font-family: Consolas, Monaco, monospace;
        font-size: 12px;
    }

    .detail-status {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 7px 10px;
        border-radius: 999px;
        color: var(--detail-primary-dark);
        background: #f9e9ee;
        font-size: 11px;
        font-weight: 700;
    }

    .detail-actions {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
        min-width: 220px;
    }

    .detail-empty-state {
        padding: 46px 20px;
        text-align: center;
        color: var(--detail-muted);
    }

    .detail-empty-state i {
        display: block;
        margin-bottom: 8px;
        color: #aab2c3;
        font-size: 34px;
    }

    .summary-section {
        margin-top: 24px;
        padding: 24px;
        border-radius: 16px;
        background: #f8f9ff;
        border: 1px solid var(--detail-border);
    }

    .summary-title {
        margin: 0 0 16px;
        color: #27324a;
        font-size: 16px;
        font-weight: 700;
    }

    .summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 20px;
    }

    .summary-card {
        padding: 16px;
        border-radius: 12px;
        background: #fff;
        border: 1px solid var(--detail-border);
    }

    .summary-card-title {
        margin: 0 0 12px;
        color: var(--detail-muted);
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .06em;
    }

    .summary-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px solid #f0f2f6;
    }

    .summary-item:last-child {
        border-bottom: 0;
    }

    .summary-item-label {
        color: #596277;
        font-size: 13px;
    }

    .summary-item-count {
        color: #27324a;
        font-size: 14px;
        font-weight: 700;
    }

    .summary-view-btn {
        padding: 4px 10px;
        border: 1px solid var(--detail-border);
        border-radius: 6px;
        color: var(--detail-primary);
        background: #fff;
        font-size: 11px;
        font-weight: 600;
        cursor: pointer;
        transition: all .18s ease;
    }

    .summary-view-btn:hover {
        color: #fff;
        background: var(--detail-primary);
        border-color: var(--detail-primary);
    }

    @media (max-width: 767.98px) {
        .division-detail-hero {
            align-items: flex-start;
            flex-direction: column;
            padding: 22px;
            border-radius: 14px;
        }

        .division-detail-actions {
            width: 100%;
        }

        .division-detail-pill,
        .division-detail-back {
            justify-content: center;
            flex: 1 1 auto;
        }

        .division-detail-toolbar {
            align-items: flex-start;
            flex-direction: column;
            padding: 18px;
        }

        .division-detail-table-wrap {
            padding: 6px 14px 18px;
        }
    }
</style>

<div class="division-detail-page">
    <div class="division-detail-hero">
        <div>
            <h2>School Governance Council - <?= html_escape($hero_title); ?></h2>
            <p><?= html_escape($hero_description); ?></p>
        </div>
        <div class="division-detail-actions">
            <span class="division-detail-pill">
                <i class="mdi mdi-format-list-bulleted"></i>
                <?= $result_count; ?> result<?= $result_count === 1 ? '' : 's'; ?>
            </span>
            <a href="<?= $back_url; ?>" class="division-detail-back">
                <i class="mdi mdi-arrow-left"></i>
                Back to Dashboard
            </a>
        </div>
    </div>

    <div class="card division-detail-card">
        <div class="card-body">
            <div class="division-detail-toolbar">
                <div>
                    <h4><?= html_escape($title); ?></h4>
                    <p>Browse the schools counted in this dashboard metric.</p>
                </div>
                <span class="detail-badge">
                    <i class="mdi mdi-filter-outline"></i>
                    <?= html_escape($detail_badge); ?>
                </span>
            </div>

            <div class="division-detail-table-wrap">
                <?php if (!empty($records)) { ?>
                    <table id="datatable" class="table dt-responsive nowrap" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>School</th>
                                <th>District</th>
                                <th><?= html_escape($detail_label); ?></th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($records as $index => $record) { ?>
                                <tr>
                                    <td><?= $index + 1; ?></td>
                                    <td>
                                        <div class="detail-school-cell">
                                            <span class="detail-school-icon"><i class="mdi mdi-school-outline"></i></span>
                                            <span>
                                                <span class="detail-school-name"><?= html_escape($record->schoolName); ?></span><br>
                                                <span class="detail-school-id"><?= html_escape($record->schoolID); ?></span>
                                            </span>
                                        </div>
                                    </td>
                                    <td><?= html_escape($record->district_name); ?></td>
                                    <td>
                                        <span class="detail-status">
                                            <i class="mdi mdi-check-decagram-outline"></i>
                                            <?= html_escape($record->detail_status); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="detail-actions">
                                            <a href="<?= base_url(); ?>school/<?= rawurlencode($record->schoolID); ?>" class="btn btn-info btn-sm">
                                                <i class="mdi mdi-eye-outline"></i> School
                                            </a>
                                            <a href="<?= base_url(); ?>Pages/checklist_district/<?= rawurlencode($record->schoolID); ?>" class="btn btn-success btn-sm">
                                                <i class="mdi mdi-clipboard-text-outline"></i> Checklist
                                            </a>
                                            <a href="<?= base_url(); ?>Pages/school_update/<?= $record->recID; ?>" class="btn btn-primary btn-sm">
                                                <i class="mdi mdi-pencil-outline"></i> Edit
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                <?php } else { ?>
                    <div class="detail-empty-state">
                        <i class="mdi mdi-database-search-outline"></i>
                        No records matched this dashboard count.
                    </div>
                <?php } ?>
            </div>

            <?php if (!empty($records)) { ?>
            <div class="summary-section">
                <h3 class="summary-title">Summary Breakdown</h3>
                <div class="summary-grid">
                    <div class="summary-card">
                        <h4 class="summary-card-title">By District</h4>
                        <?php foreach ($district_summary as $district => $count) { ?>
                            <div class="summary-item">
                                <span class="summary-item-label"><?= html_escape($district); ?></span>
                                <div>
                                    <span class="summary-item-count"><?= $count; ?></span>
                                    <button class="summary-view-btn ml-2" onclick="filterByDistrict('<?= html_escape($district); ?>')">View</button>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="summary-card">
                        <h4 class="summary-card-title">By School Category</h4>
                        <?php foreach ($category_summary as $category => $count) { ?>
                            <div class="summary-item">
                                <span class="summary-item-label"><?= html_escape($category); ?></span>
                                <div>
                                    <span class="summary-item-count"><?= $count; ?></span>
                                    <a href="<?= base_url(); ?>Pages/division_sgc_category_printable/<?= $this->uri->segment(3); ?>/<?= array_search($category, $category_labels); ?>" class="summary-view-btn ml-2">View</a>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
                <div class="mt-3">
                    <button class="summary-view-btn" onclick="resetFilter()">Show All Schools</button>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>
</div>

<script>
// Store records data for filtering
const recordsData = <?php echo json_encode($records); ?>;

// Category name to ID mapping for filtering
const categoryToId = {
    'Elementary': 1,
    'Integrated (Elem & JHS)': 2,
    'Integrated (Elem, JHS, & SHS)': 3,
    'Secondary (JHS only)': 4,
    'Secondary (JHS & SHS)': 5,
    'SHS - Stand Alone': 6
};

function filterByDistrict(districtName) {
    const table = document.getElementById('datatable');
    const tbody = table.querySelector('tbody');
    const rows = tbody.querySelectorAll('tr');

    rows.forEach(row => {
        const districtCell = row.cells[2];
        if (districtCell) {
            const rowDistrict = districtCell.textContent.trim();
            if (rowDistrict === districtName) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        }
    });

    // Scroll to table
    table.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function filterByCategory(categoryName) {
    const categoryId = categoryToId[categoryName];
    if (!categoryId) return;

    const table = document.getElementById('datatable');
    const tbody = table.querySelector('tbody');
    const rows = tbody.querySelectorAll('tr');

    rows.forEach((row, index) => {
        const record = recordsData[index];
        if (record && record.category == categoryId) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });

    // Scroll to table
    table.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function resetFilter() {
    const table = document.getElementById('datatable');
    const tbody = table.querySelector('tbody');
    const rows = tbody.querySelectorAll('tr');

    rows.forEach(row => {
        row.style.display = '';
    });
}
</script>
