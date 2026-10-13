<style>
    .print-container {
        width: 100%;
        margin: 0 auto;
    }

    .print-header {
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

    .print-header h1 {
        margin: 0 0 7px;
        color: #fff;
        font-size: 25px;
        font-weight: 700;
    }

    .print-header h2 {
        margin: 0;
        color: rgba(255, 255, 255, .82);
        font-size: 16px;
        font-weight: 500;
    }

    .print-header-info {
        text-align: right;
    }

    .print-header-info p {
        margin: 5px 0;
        color: rgba(255, 255, 255, .82);
        font-size: 13px;
    }

    .print-card {
        border: 1px solid #e8ecf4;
        border-radius: 16px;
        box-shadow: 0 8px 28px rgba(31, 45, 75, .07);
        overflow: hidden;
        background: #fff;
    }

    .print-card-body {
        padding: 24px;
    }

    .print-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }

    .print-table thead {
        background: #f8f9ff;
    }

    .print-table th {
        padding: 12px 14px;
        border: 1px solid #e8ecf4;
        color: #687086;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
    }

    .print-table td {
        padding: 14px;
        border: 1px solid #e8ecf4;
        color: #27324a;
        font-size: 13px;
        vertical-align: middle;
    }

    .print-table tbody tr:hover td {
        background: #fff7f9;
    }

    .print-table .no {
        width: 60px;
        text-align: center;
        font-weight: 700;
    }

    .print-table .school-id {
        width: 120px;
        font-family: 'Courier New', monospace;
        font-size: 12px;
        color: #596277;
    }

    .print-actions {
        text-align: center;
        margin-top: 24px;
    }

    .print-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 24px;
        border: 0;
        border-radius: 999px;
        color: #fff;
        background: linear-gradient(135deg, #8b1e3f, #be5c3c);
        box-shadow: 0 14px 28px rgba(139, 30, 63, .2);
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        transition: all .18s ease;
    }

    .print-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 18px 32px rgba(139, 30, 63, .28);
    }

    @media print {
        .print-header {
            box-shadow: none;
        }

        .print-card {
            box-shadow: none;
            border: 1px solid #e8ecf4;
        }

        .print-actions {
            display: none;
        }

        .print-table tbody tr:hover td {
            background: #fff;
        }
    }
</style>

<div class="print-container">
    <div class="print-header">
        <div>
            <h1>School Governance Council</h1>
            <h2>School-Based Management System</h2>
        </div>
        <div class="print-header-info">
            <p><strong>SGC Status:</strong> <?= html_escape($status_label); ?></p>
            <p><strong>School Category:</strong> <?= html_escape($category_label); ?></p>
            <p><strong>Total Schools:</strong> <?= count($records); ?></p>
            <p><strong>Date:</strong> <?= date('F j, Y'); ?></p>
        </div>
    </div>

    <div class="print-card">
        <div class="print-card-body">
            <table class="print-table">
                <thead>
                    <tr>
                        <th class="no">No.</th>
                        <th>School ID</th>
                        <th>School Name</th>
                        <th>District</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($records)) : ?>
                        <?php foreach ($records as $index => $record) : ?>
                            <tr>
                                <td class="no"><?= $index + 1; ?></td>
                                <td class="school-id"><?= html_escape($record->schoolID); ?></td>
                                <td><?= function_exists('mb_strtoupper') ? mb_strtoupper(html_escape($record->schoolName), 'UTF-8') : strtoupper(html_escape($record->schoolName)); ?></td>
                                <td><?= html_escape($record->district_name); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="4" style="text-align: center; color: #6b7280;">No schools found for this category.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="print-actions">
        <button onclick="window.print()" class="print-btn">
            <i class="mdi mdi-printer"></i>
            Print This Report
        </button>
    </div>
</div>
