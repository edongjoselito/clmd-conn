<link rel="stylesheet" href="<?= base_url('assets/css/region-dashboard.css'); ?>?v=<?= filemtime(FCPATH . 'assets/css/region-dashboard.css'); ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/learning-area-themes.css'); ?>?v=<?= filemtime(FCPATH . 'assets/css/learning-area-themes.css'); ?>">


<div class="rd-page">
    <header class="rd-header">
        <div>
            <span class="rd-eyebrow">CLMD Learning Portal · All Learning Areas</span>
            <h1>Learning Area Dashboards</h1>
        </div>
        <div class="rd-header-actions">
            <a class="btn btn-outline-light" href="<?= base_url('region'); ?>">Regional overview</a>
        </div>
    </header>

    <section class="rd-card rd-area-shortcuts" aria-labelledby="rd-area-shortcuts-title">
        <div class="rd-card-head">
            <h2 id="rd-area-shortcuts-title">Regional Dashboards by Learning Area</h2>
            <p>Review submitted records and performance for a specific learning area across all divisions in the region.</p>
        </div>
        <div class="rd-area-links">
            <?php foreach ($learning_area_dashboards as $slug => $area) : ?>
                <a style="--area-primary: <?= html_escape($area['theme']['primary']); ?>; --area-dark: <?= html_escape($area['theme']['dark']); ?>; --area-soft: <?= html_escape($area['theme']['soft']); ?>;" class="rd-area-link" href="<?= base_url('region/learning-area/' . rawurlencode($slug)); ?>">
                    <img class="area-card-logo" src="<?= base_url($area['theme']['logo']); ?>" alt="" width="64" height="64">
                    <span><strong><?= html_escape($area['name']); ?></strong><span class="area-card-tagline"><?= html_escape($area['theme']['tagline']); ?></span><small>View regional dashboard <i class="mdi mdi-arrow-right" aria-hidden="true"></i></small></span>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

</div>
