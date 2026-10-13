<?php
$division_total = count($data);
$dashboard_url = base_url();
?>

<style>
    .report-page {
        --report-primary: #8b1e3f;
        --report-primary-dark: #64142d;
        --report-border: #e8ecf4;
        --report-muted: #6b7280;
    }

    .report-hero {
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

    .report-hero h2 {
        margin: 0 0 7px;
        color: #fff;
        font-size: 25px;
        font-weight: 700;
    }

    .report-hero p {
        max-width: 720px;
        margin: 0;
        color: rgba(255, 255, 255, .82);
    }

    .report-actions {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }

    .report-pill {
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

    .chart-section {
        margin-top: 32px;
        padding: 24px;
        background: #fff;
        border-radius: 16px;
        border: 1px solid var(--report-border);
        box-shadow: 0 8px 28px rgba(31, 45, 75, .07);
    }

    .chart-section h4 {
        margin: 0 0 20px;
        color: #27324a;
        font-size: 17px;
        font-weight: 700;
    }

    .chart-container {
        position: relative;
        height: 500px;
        width: 100%;
    }

    .report-empty {
        padding: 48px 24px;
        color: var(--report-muted);
        text-align: center;
    }

    .report-empty i {
        display: block;
        margin-bottom: 10px;
        color: #aab2c3;
        font-size: 38px;
    }
</style>

<div class="report-page">
    <div class="row">
        <div class="col-12">
            <div class="report-hero">
                <div>
                    <h2><i class="mdi mdi-chart-bar mr-2"></i>Overall Accomplishments Comparison</h2>
                    <p>Visual comparison of overall accomplishments across all divisions including Signup, Action Plan, Self-Assessment, and TNA completion rates.</p>
                </div>
                <div class="report-actions">
                    <span class="report-pill">
                        <i class="mdi mdi-office-building"></i>
                        <?= $division_total; ?> <?= $division_total === 1 ? 'division' : 'divisions'; ?>
                    </span>
                    <span class="report-pill">
                        <i class="mdi mdi-calendar"></i>
                        Fiscal Year <?= html_escape($this->session->fy); ?>
                    </span>
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

    <?php if (!empty($data)) { ?>
    <div class="row">
        <div class="col-12">
            <div class="chart-section">
                <h4><i class="mdi mdi-chart-bar mr-2"></i>Division Accomplishments on Self-Assessment Checklist</h4>
                <div class="chart-container">
                    <canvas id="divisionChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="chart-section">
                <h4><i class="mdi mdi-chart-bar mr-2"></i>Division Accomplishments on TNA</h4>
                <div class="chart-container">
                    <canvas id="tnaChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <?php } else { ?>
    <div class="row">
        <div class="col-12">
            <div class="report-empty">
                <i class="mdi mdi-office-building-remove-outline"></i>
                No divisions are available for the active region.
            </div>
        </div>
    </div>
    <?php } ?>
</div>

<script>
// Wait for jQuery to be loaded
(function() {
    var checkJQuery = setInterval(function() {
        if (typeof jQuery !== 'undefined') {
            clearInterval(checkJQuery);
            initChart();
        }
    }, 100);

    // Timeout after 5 seconds
    setTimeout(function() {
        clearInterval(checkJQuery);
        if (typeof jQuery === 'undefined') {
            console.error('jQuery failed to load');
        }
    }, 5000);
})();

function initChart() {
    console.log('jQuery loaded, initializing chart');
    console.log('Chart.js available:', typeof Chart !== 'undefined');

    <?php if (!empty($data)) { ?>
    // Prepare chart data
    var divisionNames = [
        <?php foreach ($data as $row) { ?>
            '<?= html_escape($row->description); ?>',
        <?php } ?>
    ];

    var selfAssessmentData = [
        <?php foreach ($data as $row) { ?>
            <?= $row->sbm_percentage; ?>,
        <?php } ?>
    ];

    var tnaData = [
        <?php foreach ($data as $row) { ?>
            <?= $row->sbm_ta_percentage; ?>,
        <?php } ?>
    ];

    console.log('Division names:', divisionNames);
    console.log('Self-Assessment data:', selfAssessmentData);
    console.log('TNA data:', tnaData);

    // Create Self-Assessment chart
    var ctx = document.getElementById('divisionChart');
    console.log('Canvas element:', ctx);

    if (ctx && typeof Chart !== 'undefined') {
        var chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: divisionNames,
                datasets: [
                    {
                        label: 'Self-Assessment %',
                        data: selfAssessmentData,
                        backgroundColor: 'rgba(23, 162, 184, 0.7)',
                        borderColor: 'rgba(23, 162, 184, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        },
                        title: {
                            display: true,
                            text: 'Self-Assessment Completion %',
                            font: {
                                size: 14,
                                weight: 'bold'
                            }
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Division',
                            font: {
                                size: 14,
                                weight: 'bold'
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: {
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.parsed.y + '%';
                            }
                        }
                    }
                },
                animation: {
                    onComplete: function() {
                        var chartInstance = this.chart;
                        var ctx = chartInstance.ctx;
                        ctx.font = Chart.helpers.fontString(Chart.defaults.global.defaultFontSize, 'normal', Chart.defaults.global.defaultFontFamily);
                        ctx.fillStyle = '#000';
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'bottom';

                        this.data.datasets.forEach(function(dataset, i) {
                            var meta = chartInstance.controller.getDatasetMeta(i);
                            meta.data.forEach(function(bar, index) {
                                var data = dataset.data[index];
                                ctx.fillText(data + '%', bar._model.x, bar._model.y - 5);
                            });
                        });
                    }
                }
            }
        });
        console.log('Self-Assessment chart created successfully');
    } else {
        console.error('Chart.js not loaded or canvas not found');
    }

    // Create TNA chart
    var tnaCtx = document.getElementById('tnaChart');
    console.log('TNA Canvas element:', tnaCtx);

    if (tnaCtx && typeof Chart !== 'undefined') {
        var tnaChart = new Chart(tnaCtx, {
            type: 'bar',
            data: {
                labels: divisionNames,
                datasets: [
                    {
                        label: 'TNA %',
                        data: tnaData,
                        backgroundColor: 'rgba(0, 123, 255, 0.7)',
                        borderColor: 'rgba(0, 123, 255, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        },
                        title: {
                            display: true,
                            text: 'TNA Completion %',
                            font: {
                                size: 14,
                                weight: 'bold'
                            }
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Division',
                            font: {
                                size: 14,
                                weight: 'bold'
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: {
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.parsed.y + '%';
                            }
                        }
                    }
                },
                animation: {
                    onComplete: function() {
                        var chartInstance = this.chart;
                        var ctx = chartInstance.ctx;
                        ctx.font = Chart.helpers.fontString(Chart.defaults.global.defaultFontSize, 'normal', Chart.defaults.global.defaultFontFamily);
                        ctx.fillStyle = '#000';
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'bottom';

                        this.data.datasets.forEach(function(dataset, i) {
                            var meta = chartInstance.controller.getDatasetMeta(i);
                            meta.data.forEach(function(bar, index) {
                                var data = dataset.data[index];
                                ctx.fillText(data + '%', bar._model.x, bar._model.y - 5);
                            });
                        });
                    }
                }
            }
        });
        console.log('TNA chart created successfully');
    } else {
        console.error('Chart.js not loaded or TNA canvas not found');
    }
    <?php } else { ?>
    console.log('No data available for chart');
    <?php } ?>
}
</script>
