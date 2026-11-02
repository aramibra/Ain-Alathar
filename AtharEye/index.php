<?php
require_once __DIR__ . '/includes/helpers.php';
if (is_logged_in()) redirect('dashboard.php');
$title=t('nav.home'); $bodyClass='public-body';
$mapData=site_map_data('WHERE status="active"');
$siteCount=count($mapData); $highCount=count(array_filter($mapData,fn($s)=>(int)$s['risk']>=70));
include __DIR__.'/includes/header.php';
?>
<section class="hero">
  <div class="hero-copy">
    <span class="eyebrow"><?= h(t('home.eyebrow')) ?></span>
    <h1><?= h(t('home.title')) ?></h1>
    <p><?= h(t('home.desc')) ?></p>
    <div class="actions"><a class="btn btn-primary" href="login.php"><i class="ti ti-login-2"></i><?= h(t('home.enter')) ?></a><a class="btn btn-light" href="about.php"><i class="ti ti-info-circle"></i><?= h(t('home.about')) ?></a></div>
  </div>
  <div class="hero-map-shell" aria-label="<?= h(t('nav.map')) ?>">
    <div id="heritageMap" class="hero-map"></div>
    <div class="hero-map-badge hero-map-badge-top"><i class="ti ti-alert-circle"></i><?= $highCount ?> <?= h(t('reports.high')) ?></div>
    <div class="hero-map-badge hero-map-badge-bottom"><i class="ti ti-map-pin"></i><?= $siteCount ?> <?= h(t('reports.sites')) ?></div>
  </div>
</section>
<section class="public-section steps-section">
  <div class="section-heading"><span class="eyebrow"><?= h(t('home.workflow_label')) ?></span><h2><?= h(t('home.workflow_title')) ?></h2><p><?= h(t('home.workflow_desc')) ?></p></div>
  <div class="grid-4">
    <?php $steps=[['ti-building-monument','home.step1','home.step1_desc'],['ti-message-report','home.step2','home.step2_desc'],['ti-clipboard-search','home.step3','home.step3_desc'],['ti-map-2','home.step4','home.step4_desc']]; foreach($steps as $i=>$step): ?>
    <div class="stat-card step-card"><span class="step-icon"><i class="ti <?= h($step[0]) ?>"></i></span><div class="stat-num"><?= $i+1 ?></div><b><?= h(t($step[1])) ?></b><p class="muted"><?= h(t($step[2])) ?></p></div>
    <?php endforeach; ?>
  </div>
</section>
<section class="public-section features-section"><div class="section-card"><div class="section-heading compact"><span class="eyebrow"><?= h(t('home.features_label')) ?></span><h2><?= h(t('home.features_title')) ?></h2></div><div class="grid-3">
  <div class="feature-card"><span class="feature-icon"><i class="ti ti-map-2"></i></span><h3><?= h(t('home.feature_map')) ?></h3><p class="muted"><?= h(t('home.feature_map_desc')) ?></p></div>
  <div class="feature-card"><span class="feature-icon"><i class="ti ti-clipboard-check"></i></span><h3><?= h(t('home.feature_reports')) ?></h3><p class="muted"><?= h(t('home.feature_reports_desc')) ?></p></div>
  <div class="feature-card"><span class="feature-icon"><i class="ti ti-shield-check"></i></span><h3><?= h(t('home.feature_roles')) ?></h3><p class="muted"><?= h(t('home.feature_roles_desc')) ?></p></div>
</div></div></section>
<script>window.ATHAR_SITES=<?= json_encode($mapData,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) ?>;window.ATHAR_DEFAULT_MODE='hybrid';window.ATHAR_PUBLIC_PREVIEW=true;window.ATHAR_MAP_OPTIONS={scrollWheelZoom:false,zoomControl:true,dragging:true};</script>
<script src="assets/js/map.js?v=14.0"></script>
<?php include __DIR__.'/includes/footer.php'; ?>
