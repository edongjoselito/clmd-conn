<style>
    .school-list-page {
        --list-primary: #8b1e3f;
        --list-primary-dark: #64142d;
        --list-border: #e8ecf4;
        --list-muted: #6b7280;
    }

    .school-list-hero {
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

    .school-list-hero h2 {
        margin: 0 0 7px;
        color: #fff;
        font-size: 25px;
        font-weight: 700;
    }

    .school-list-hero p {
        margin: 0;
        color: rgba(255, 255, 255, .82);
    }

    .school-list-card {
        border: 1px solid var(--list-border);
        border-radius: 16px;
        box-shadow: 0 8px 28px rgba(31, 45, 75, .07);
        overflow: hidden;
    }

    .school-list-card .card-body {
        padding: 0;
    }

    .school-list-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 20px 24px;
        border-bottom: 1px solid var(--list-border);
    }

    .school-list-toolbar h4 {
        margin: 0 0 3px;
        color: #27324a;
        font-size: 17px;
        font-weight: 700;
    }

    .school-list-toolbar p {
        margin: 0;
        color: var(--list-muted);
        font-size: 12px;
    }

    .school-list-pill {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 10px 14px;
        border: 1px solid var(--list-border);
        border-radius: 999px;
        color: var(--list-primary);
        background: #fff7f9;
        font-size: 12px;
        font-weight: 700;
    }

    .school-list-table-wrap {
        padding: 6px 14px 18px;
    }

    #datatable thead th {
        background: #f8f9ff;
        color: #687086;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
        border-bottom: 2px solid var(--list-border);
    }

    #datatable tbody td {
        color: #27324a;
        font-size: 13px;
        vertical-align: middle;
    }

    #datatable tbody tr:hover td {
        background: #fff7f9;
    }
</style>

<div class="school-list-page">
    <div class="school-list-hero">
        <div>
            <h2>School List</h2>
            <p>Complete list of all registered schools in the system</p>
        </div>
        <div>
            <span class="school-list-pill">
                <i class="mdi mdi-school"></i>
                <?= count($data); ?> Schools
            </span>
        </div>
    </div>

    <div class="card school-list-card">
        <div class="card-body">
            <div class="school-list-toolbar">
                <div>
                    <h4>All Schools</h4>
                    <p>Browse all schools that have signed up in the system</p>
                </div>
            </div>

            <div class="school-list-table-wrap">
                <table id="datatable" class="table table-hover table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                    <thead>
                        <tr>
                            <th>School Name</th>
                            <th>School ID</th>
                            <th>Division</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($data)) : ?>
                            <?php foreach ($data as $row) : ?>
                                <tr>
                                    <td><?= !empty($row->schoolName) ? mb_convert_case($row->schoolName, MB_CASE_TITLE, 'UTF-8') : ''; ?></td>
                                    <td><?= html_escape($row->schoolID); ?></td>
                                    <td><?= html_escape($row->division_name); ?></td>
                                    <td>
                                        <a href="<?= base_url(); ?>Pages/school_profile_region/<?= html_escape($row->schoolID); ?>" class="btn btn-sm btn-primary">
                                            <i class="mdi mdi-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="4" style="text-align: center;">No schools found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#datatable').DataTable({
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        order: [[0, 'asc']],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search schools..."
        }
    });
});
</script>
