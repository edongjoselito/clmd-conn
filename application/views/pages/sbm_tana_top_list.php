                        <style>
                            .tana-summary-page {
                                --tana-primary: #7f1d1d;
                                --tana-primary-light: #b83a4b;
                                --tana-accent: #d6a84b;
                                --tana-ink: #172033;
                                --tana-muted: #687386;
                                --tana-border: #e4e9f0;
                                --tana-surface: #f6f8fb;
                                --tana-success: #15803d;
                                --tana-warning: #c97a11;
                                --tana-info: #1d4ed8;
                            }

                            .tana-summary-page .alert {
                                border-radius: 14px;
                            }

                            .tana-summary-page .tana-hero {
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

                            .tana-summary-page .tana-hero::after {
                                content: '';
                                position: absolute;
                                right: -42px;
                                bottom: -56px;
                                width: 210px;
                                height: 210px;
                                border-radius: 50%;
                                background: rgba(255, 255, 255, .08);
                            }

                            .tana-summary-page .tana-hero-copy,
                            .tana-summary-page .tana-hero-side {
                                position: relative;
                                z-index: 1;
                            }

                            .tana-summary-page .tana-hero-copy {
                                max-width: 760px;
                            }

                            .tana-summary-page .tana-hero-side {
                                display: flex;
                                flex-direction: column;
                                align-items: flex-end;
                                justify-content: space-between;
                                gap: 16px;
                                min-width: 220px;
                            }

                            .tana-summary-page .hero-eyebrow {
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

                            .tana-summary-page .hero-eyebrow i {
                                font-size: 15px;
                            }

                            .tana-summary-page .tana-hero h1 {
                                margin: 0;
                                color: #fff;
                                font-size: 30px;
                                font-weight: 800;
                                line-height: 1.1;
                                letter-spacing: -.03em;
                            }

                            .tana-summary-page .tana-hero p {
                                max-width: 700px;
                                margin: 14px 0 18px;
                                color: rgba(255, 255, 255, .86);
                                font-size: 14px;
                                line-height: 1.75;
                            }

                            .tana-summary-page .hero-actions {
                                display: flex;
                                flex-wrap: wrap;
                                gap: 10px;
                                justify-content: flex-end;
                            }

                            .tana-summary-page .hero-button {
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

                            .tana-summary-page .hero-button:hover {
                                transform: translateY(-2px);
                                background: rgba(255, 255, 255, .16);
                            }

                            .tana-summary-page .hero-button-primary {
                                color: var(--tana-primary);
                                background: #fff;
                                box-shadow: 0 10px 20px rgba(61, 12, 29, .18);
                            }

                            .tana-summary-page .hero-button-primary:hover {
                                color: var(--tana-primary);
                                background: #fff;
                            }

                            .tana-summary-page .workspace-panel {
                                margin-bottom: 22px;
                                border: 1px solid var(--tana-border);
                                border-radius: 18px;
                                background: #fff;
                                box-shadow: 0 10px 30px rgba(31, 45, 75, .07);
                                overflow: hidden;
                            }

                            .tana-summary-page .workspace-panel-header {
                                display: flex;
                                align-items: center;
                                justify-content: space-between;
                                gap: 16px;
                                padding: 20px 24px;
                                border-bottom: 1px solid var(--tana-border);
                            }

                            .tana-summary-page .workspace-panel-header h4 {
                                margin: 0 0 4px;
                                color: var(--tana-ink);
                                font-size: 18px;
                                font-weight: 700;
                            }

                            .tana-summary-page .workspace-panel-header p {
                                margin: 0;
                                color: var(--tana-muted);
                                font-size: 12px;
                                line-height: 1.6;
                            }

                            .tana-summary-page .workspace-panel-body {
                                padding: 22px 24px;
                            }

                            .tana-summary-page .tana-table {
                                width: 100%;
                                border-collapse: separate;
                                border-spacing: 0;
                            }

                            .tana-summary-page .tana-table thead th {
                                padding: 14px 16px;
                                border-bottom: 2px solid var(--tana-border);
                                color: var(--tana-ink);
                                font-size: 12px;
                                font-weight: 700;
                                letter-spacing: .06em;
                                text-transform: uppercase;
                                background: #f8fafc;
                            }

                            .tana-summary-page .tana-table tbody td {
                                padding: 16px;
                                border-bottom: 1px solid var(--tana-border);
                                color: var(--tana-ink);
                                font-size: 13px;
                                vertical-align: middle;
                            }

                            .tana-summary-page .tana-table tbody tr:last-child td {
                                border-bottom: 0;
                            }

                            .tana-summary-page .tana-table tbody tr:hover td {
                                background: #f8fafc;
                            }

                            .tana-summary-page .tana-table .concern-text {
                                max-width: 400px;
                                line-height: 1.6;
                            }

                            .tana-summary-page .tana-table .priority-select {
                                min-width: 80px;
                                padding: 8px 12px;
                                border: 1px solid var(--tana-border);
                                border-radius: 10px;
                                background: #fff;
                                font-size: 13px;
                                font-weight: 600;
                            }

                            .tana-summary-page .tana-table .priority-select:focus {
                                outline: none;
                                border-color: var(--tana-primary);
                                box-shadow: 0 0 0 3px rgba(127, 29, 29, .10);
                            }

                            .tana-summary-page .tana-table .average-badge {
                                display: inline-flex;
                                align-items: center;
                                justify-content: center;
                                min-width: 50px;
                                padding: 6px 12px;
                                border-radius: 999px;
                                background: #fbeef1;
                                color: var(--tana-primary);
                                font-size: 14px;
                                font-weight: 700;
                            }

                            .tana-summary-page .tana-table .row-number {
                                display: inline-flex;
                                align-items: center;
                                justify-content: center;
                                width: 32px;
                                height: 32px;
                                border-radius: 10px;
                                background: #f4f7fb;
                                color: var(--tana-ink);
                                font-size: 13px;
                                font-weight: 700;
                            }

                            .tana-summary-page .action-bar {
                                display: flex;
                                align-items: center;
                                gap: 12px;
                                padding: 18px 24px;
                                border-top: 1px solid var(--tana-border);
                                background: #f8fafc;
                            }

                            .tana-summary-page .action-button {
                                display: inline-flex;
                                align-items: center;
                                justify-content: center;
                                gap: 8px;
                                padding: 11px 20px;
                                border: 0;
                                border-radius: 12px;
                                font-size: 13px;
                                font-weight: 700;
                                text-decoration: none !important;
                                transition: transform .18s ease, box-shadow .18s ease;
                            }

                            .tana-summary-page .action-button:hover {
                                transform: translateY(-2px);
                            }

                            .tana-summary-page .action-button-primary {
                                color: #fff;
                                background: linear-gradient(135deg, var(--tana-primary), var(--tana-primary-light));
                                box-shadow: 0 10px 20px rgba(127, 29, 29, .18);
                            }

                            .tana-summary-page .action-button-secondary {
                                color: var(--tana-primary);
                                background: #fbeef1;
                            }

                            .tana-summary-page .action-button-tertiary {
                                color: #fff;
                                background: linear-gradient(135deg, #5b1026, #922c43);
                                box-shadow: 0 10px 20px rgba(91, 16, 38, .18);
                            }

                            select option:disabled {
                                background-color: #ffcccc;
                                color: #555;
                                font-weight: bold;
                            }

                            @media (max-width: 767.98px) {
                                .tana-summary-page .tana-hero {
                                    flex-direction: column;
                                    padding: 22px;
                                }

                                .tana-summary-page .tana-hero-side {
                                    width: 100%;
                                    align-items: stretch;
                                }

                                .tana-summary-page .hero-actions {
                                    width: 100%;
                                }

                                .tana-summary-page .hero-button {
                                    flex: 1;
                                    justify-content: center;
                                }

                                .tana-summary-page .tana-table thead th,
                                .tana-summary-page .tana-table tbody td {
                                    padding: 12px 10px;
                                    font-size: 12px;
                                }

                                .tana-summary-page .tana-table .concern-text {
                                    max-width: 200px;
                                }

                                .tana-summary-page .action-bar {
                                    flex-direction: column;
                                    align-items: stretch;
                                }

                                .tana-summary-page .action-button {
                                    width: 100%;
                                }
                            }
                        </style>

                        <div class="tana-summary-page">
                            <!-- Hero Section -->
                            <div class="tana-hero">
                                <div class="tana-hero-copy">
                                    <span class="hero-eyebrow">
                                        <i class="mdi mdi-compass-outline"></i>
                                        TANA Priority Basis
                                    </span>
                                    <h1>Technical Assistance Needs Assessment</h1>
                                    <p>Review the top concerns from your TA provision report and assign priority rankings to focus technical assistance efforts on the most critical areas.</p>
                                </div>
                                <div class="tana-hero-side">
                                    <div class="hero-actions">
                                        <a href="<?= base_url(); ?>" class="hero-button">
                                            <i class="mdi mdi-view-dashboard-outline"></i>
                                            Dashboard
                                        </a>
                                        <a href="<?= base_url(); ?>Pages/tapr_form" class="hero-button hero-button-primary">
                                            <i class="mdi mdi-file-document-edit-outline"></i>
                                            TA Form
                                        </a>
                                    </div>
                                </div>
                            </div>
                         <?php 
                            $ts = $this->Common->two_cond_order_by('tana_summary','school_id', $this->session->username,'fy',$this->session->fy,'sequence','ASC');
                            $ta = $this->Common->two_cond_row('sbm_ta','school_id', $this->session->username,'fy',$this->session->fy);
                         ?>

                        

                        

                        

                            <!-- Flash Messages -->
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
                            <?php endif;  ?>

                            <?php $att = array('class' => 'parsley-examples'); ?>
                            <?php if(empty($ts)){?>
                                <?= form_open('Pages/tana_summary', $att); ?>
                            <?php }else{ ?>
                                <?= form_open('Pages/tana_summary_update', $att); ?>
                            <?php } ?>

                            <input type="hidden" name="id" value="">
                            <input type="hidden" name="district" value="<?= $this->session->district; ?>">

                            <!-- Workspace Panel -->
                            <div class="workspace-panel">
                                <div class="workspace-panel-header">
                                    <div>
                                        <h4>Priority Ranking</h4>
                                        <p>Assign priority numbers (1-20) to the top concerns. Lower numbers indicate higher priority.</p>
                                    </div>
                                </div>
                                <div class="workspace-panel-body">
                                    <div class="table-responsive">
                                        <table class="tana-table">
                                            <thead>
                                                <tr>
                                                    <th class="text-center">#</th>
                                                    <th>Concerns, Issues, Gaps, Problems and Bottlenecks</th>
                                                    <th class="text-center">Average</th>
                                                    <th class="text-center">Priority</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                    $ic = 1;

                                                    if(empty($ts)){

                                                    foreach ($averages as $index => $avg):
                                                        $col = 'q' . $index;

                                                        $text = ($ta && isset($ta->$col) && $ta->$col !== null) ? trim((string) $ta->$col) : '';

                                                        if ($text === '') {
                                                            continue;
                                                        }

                                                ?>
                                                <tr>
                                                    <td class="text-center">
                                                        <input type="hidden" value="<?= $index; ?>" name="concern_id[]" />
                                                        <input type="hidden" value="<?= number_format((float)$avg, 2); ?>" name="average[]" />
                                                        <span class="row-number"><?= $ic++; ?></span>
                                                    </td>
                                                    <td>
                                                        <div class="concern-text"><?= htmlspecialchars($text, ENT_QUOTES, 'UTF-8'); ?></div>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="average-badge"><?= number_format((float)$avg, 2); ?></span>
                                                    </td>
                                                    <td class="text-center">
                                                        <select class="priority-select seq-select" name="sequence[]">
                                                            <option value="">Select</option>
                                                            <?php for($i = 1; $i <= 20; $i++): ?>
                                                                <option value="<?= $i ?>"><?= $i ?></option>
                                                            <?php endfor; ?>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>

                                                <?php }else{ ?>
                                                    <?php 
                                                        foreach($ts as $row){
                                                        $col = 'q' . $row->concern_id;
                                                        $text = ($ta && isset($ta->$col) && $ta->$col !== null) ? trim((string) $ta->$col) : '';

                                                        if ($text === '') {
                                                            continue;
                                                        }
                                                        
                                                        ?>
                                                    <tr>
                                                        <td class="text-center">
                                                            <input type="hidden" value="<?= $row->concern_id; ?>" name="concern_id[]" />
                                                            <input type="hidden" value="<?= $row->average; ?>" name="average[]" />
                                                            <span class="row-number"><?= $ic++; ?></span>
                                                        </td>
                                                        <td>
                                                            <div class="concern-text"><?= htmlspecialchars($text, ENT_QUOTES, 'UTF-8'); ?></div>
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="average-badge"><?= $row->average; ?></span>
                                                        </td>
                                                        <td class="text-center">
                                                            <select class="priority-select seq-select" name="sequence[]">
                                                                <option value="">Select</option>
                                                                <?php for($i = 1; $i <= 20; $i++): ?>
                                                                    <option <?php if($row->stat != 0){echo ' disabled ';} ?> <?php if($i ==  $row->sequence){echo ' selected ';}?> value="<?= $i ?>"><?= $i ?></option>
                                                                <?php endfor; ?>
                                                            </select>
                                                        </td>
                                                    </tr>
                                                <?php } } ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Action Bar -->
                                    <div class="action-bar">
                                        <button type="submit" name="submit" class="action-button action-button-primary">
                                            <i class="mdi mdi-content-save-outline"></i>
                                            Save Rankings
                                        </button>
                                        <button type="submit" formaction="<?= base_url(); ?>Pages/update_tana_summary" onclick="return confirm('Are you sure? If you press OK, everything currently saved will be deleted.');" class="action-button action-button-secondary">
                                            <i class="mdi mdi-refresh"></i>
                                            Update / Reset
                                        </button>
                                    </div>
                                </div>
                            </div>
                            </form>
                        </div>

                    <script>
                    document.addEventListener("DOMContentLoaded", function () {
                        const selects = document.querySelectorAll('.seq-select');
                        const tableBody = document.querySelector('.tana-table tbody');

                        function updateDisabledOptions() {
                            const selected = Array.from(selects)
                                .map(sel => sel.value)
                                .filter(v => v !== "" && v !== "0");

                            selects.forEach(sel => {
                                for (let opt of sel.options) {
                                    if (opt.value !== "" && opt.value !== "0") {
                                        opt.disabled = false;
                                    }
                                }
                            });

                            selects.forEach(sel => {
                                const currentValue = sel.value;
                                if (currentValue === "" || currentValue === "0") return;

                                selects.forEach(other => {
                                    if (other === sel) return;
                                    for (let opt of other.options) {
                                        if (opt.value === currentValue) {
                                            opt.disabled = true;
                                        }
                                    }
                                });
                            });
                        }

                        function reorderRows() {
                            if (!tableBody) {
                                return;
                            }

                            const rows = Array.from(tableBody.querySelectorAll('tr'));
                            rows.sort((a, b) => {
                                const aValue = parseInt(a.querySelector('.priority-select')?.value, 10);
                                const bValue = parseInt(b.querySelector('.priority-select')?.value, 10);
                                const aValid = Number.isInteger(aValue) && aValue > 0;
                                const bValid = Number.isInteger(bValue) && bValue > 0;

                                if (aValid && bValid) {
                                    return aValue - bValue;
                                }

                                if (aValid) {
                                    return -1;
                                }

                                if (bValid) {
                                    return 1;
                                }

                                return 0;
                            });

                            rows.forEach((row, index) => {
                                const numberNode = row.querySelector('.row-number');
                                if (numberNode) {
                                    numberNode.textContent = index + 1;
                                }
                                tableBody.appendChild(row);
                            });
                        }

                        selects.forEach(sel => {
                            sel.addEventListener("change", function () {
                                updateDisabledOptions();
                                reorderRows();
                            });
                        });

                        updateDisabledOptions();
                        reorderRows();
                    });
                    </script>
