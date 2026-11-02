<?php
require_once __DIR__ . '/includes/helpers.php';
$title=t('about.title'); $bodyClass=is_logged_in()?'':'public-body'; include __DIR__.'/includes/header.php';
?>
<?php if(is_logged_in()): ?><section class="page-hero"><span class="eyebrow"><?= h(t('about.label')) ?></span><h1><?= h(t('about.title')) ?></h1><p><?= h(t('about.desc')) ?></p></section><?php endif; ?>
<div class="<?= is_logged_in()?'':'public-section' ?>">
  <div class="about-intro">
    <section class="identity-panel"><img class="identity-mark" src="assets/images/mark.svg" alt="<?= h(t('app.name')) ?>"><span class="eyebrow" style="background:rgba(255,255,255,.12);color:#fff;margin-top:18px"><?= h(t('about.label')) ?></span><h2><?= h(t('about.title')) ?></h2><p><?= h(t('about.desc')) ?></p></section>
    <div class="about-card-grid">
      <article class="about-card"><span class="about-card-icon"><i class="ti ti-eye"></i></span><h3><?= h(t('about.vision')) ?></h3><p class="muted"><?= h(t('about.vision_desc')) ?></p></article>
      <article class="about-card"><span class="about-card-icon"><i class="ti ti-target-arrow"></i></span><h3><?= h(t('about.mission')) ?></h3><p class="muted"><?= h(t('about.mission_desc')) ?></p></article>
    </div>
  </div>
  <section class="section-card" style="margin-top:24px"><div class="section-heading compact"><span class="eyebrow"><?= h(t('about.objectives')) ?></span><h2><?= h(t('about.objectives')) ?></h2></div><div class="grid-3">
    <article class="feature-card"><span class="feature-icon"><i class="ti ti-shield-check"></i></span><p><?= h(t('about.objective1')) ?></p></article>
    <article class="feature-card"><span class="feature-icon"><i class="ti ti-database"></i></span><p><?= h(t('about.objective2')) ?></p></article>
    <article class="feature-card"><span class="feature-icon"><i class="ti ti-chart-infographic"></i></span><p><?= h(t('about.objective3')) ?></p></article>
  </div></section>
  <section class="section-card" style="margin-top:24px"><div class="section-heading compact"><span class="eyebrow"><?= h(t('home.workflow_label')) ?></span><h2><?= h(t('home.workflow_title')) ?></h2></div><div class="workflow-line">
    <?php foreach([[1,'home.step1'],[2,'home.step2'],[3,'home.step3'],[4,'home.step4']] as $w): ?><div class="workflow-node"><span class="node-num"><?= $w[0] ?></span><b><?= h(t($w[1])) ?></b></div><?php endforeach; ?>
  </div></section>
  <section class="section-card" style="margin-top:24px"><div class="section-heading compact"><span class="eyebrow"><?= h(t('about.policies')) ?></span><h2><?= h(t('about.policies')) ?></h2></div><div class="about-card-grid">
    <?php $policies=['about.policy1','about.policy2','about.policy3','about.policy4']; foreach($policies as $i=>$p): ?><article class="about-card"><span class="about-card-icon"><i class="ti <?= ['ti-certificate','ti-lock-access','ti-calculator','ti-folders'][$i] ?>"></i></span><p><?= h(t($p)) ?></p></article><?php endforeach; ?>
  </div></section>
</div>
<?php include __DIR__.'/includes/footer.php'; ?>
