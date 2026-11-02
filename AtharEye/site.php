<?php
require_once __DIR__.'/includes/helpers.php'; require_login();
$id=(int)($_GET['id']??0); $site=fetch_one('SELECT * FROM ae_heritage_sites WHERE id=?',[$id]);
if(!$site){flash('warning',localized('الموقع غير موجود.','Site not found.'));redirect('sites.php');}
$title=$site['name'];
$assess=fetch_all("SELECT fa.*,u.name archaeologist FROM ae_field_assessments fa JOIN ae_users u ON u.id=fa.archaeologist_id WHERE fa.site_id=? AND fa.status='approved' ORDER BY fa.visit_date DESC",[$id]);
$images=fetch_all('SELECT * FROM ae_site_images WHERE site_id=? ORDER BY id DESC',[$id]);
$risks=fetch_all('SELECT rt.name,sr.severity FROM ae_site_risks sr JOIN ae_risk_types rt ON rt.id=sr.risk_type_id WHERE sr.site_id=? ORDER BY sr.severity DESC',[$id]);
$latest=$assess[0]??null;
include __DIR__.'/includes/header.php';
?>
<section class="site-detail-hero">
  <div class="site-detail-copy"><a class="back-link" href="sites.php"><i class="ti ti-arrow-right"></i><?= h(localized('العودة إلى المواقع','Back to Sites')) ?></a><span class="eyebrow"><?= h($site['site_type']) ?></span><h1><?= h($site['name']) ?></h1><p><?= h($site['description']) ?></p><div class="site-detail-tags"><span><i class="ti ti-map-pin"></i><?= h($site['city']) ?>، <?= h($site['region']) ?></span><span class="badge badge-<?= h(risk_class($site['risk_percentage'])) ?>"><?= h(risk_label($site['risk_percentage'])) ?> — <?= (int)$site['risk_percentage'] ?>%</span><span><i class="ti ti-file-check"></i><?= count($assess) ?> <?= h(localized('تقارير معتمدة','approved reports')) ?></span></div></div>
  <aside class="risk-summary-panel risk-<?= h(risk_class($site['risk_percentage'])) ?>"><span><?= h(localized('الحالة الحالية','Current Condition')) ?></span><strong><?= h(risk_label($site['risk_percentage'])) ?></strong><div class="risk-meter"><i style="width:<?= max(2,min(100,(int)$site['risk_percentage'])) ?>%"></i></div><b><?= (int)$site['risk_percentage'] ?>%</b><p><?= h($latest?localized('بناءً على آخر تقييم ميداني معتمد.','Based on the latest approved field assessment.'):localized('تقدير أولي حتى توفر تقييم ميداني معتمد.','Initial estimate until an approved field assessment is available.')) ?></p></aside>
</section>

<div class="site-detail-grid">
  <section class="site-main-column">
    <article class="detail-section-card"><div class="detail-section-head"><div><span class="eyebrow"><?= h(localized('الموقع الجغرافي','Geographic Location')) ?></span><h2><?= h(localized('موقع الأثر على الخريطة','Site Location on the Map')) ?></h2></div><span class="coordinate-chip ltr"><?= h($site['latitude']) ?>, <?= h($site['longitude']) ?></span></div><div id="heritageMap" class="map-wrap site-detail-map"></div></article>

    <article class="detail-section-card"><div class="detail-section-head"><div><span class="eyebrow"><?= h(localized('السجل المعتمد','Approved Record')) ?></span><h2><?= h(localized('التقييمات والتوصيات','Assessments and Recommendations')) ?></h2></div></div>
      <?php if(!$assess): ?><div class="detail-empty"><i class="ti ti-clipboard-off"></i><p><?= h(localized('لا توجد تقارير ميدانية معتمدة لهذا الموقع حتى الآن.','No approved field reports are available for this site yet.')) ?></p></div><?php else: ?>
      <div class="assessment-timeline"><?php foreach($assess as $a): ?><article class="assessment-entry"><div class="assessment-date"><i class="ti ti-calendar-event"></i><b><?= h($a['visit_date']) ?></b></div><div class="assessment-body"><div class="assessment-title-row"><h3><?= h($a['risk_type']) ?></h3><span class="badge badge-<?= h(risk_class($a['risk_percentage'])) ?>"><?= (int)$a['risk_percentage'] ?>%</span></div><div class="assessment-by"><i class="ti ti-user-search"></i><?= h($a['archaeologist']) ?></div><?php if($a['notes']): ?><p><?= h($a['notes']) ?></p><?php endif; ?><div class="recommendation-box"><i class="ti ti-bulb"></i><div><b><?= h(t('reports.recommendations')) ?></b><p><?= h($a['recommendations']?:localized('لا توجد توصيات إضافية.','No additional recommendations.')) ?></p></div></div></div></article><?php endforeach; ?></div>
      <?php endif; ?>
    </article>
  </section>

  <aside class="site-side-column">
    <article class="detail-section-card site-info-card"><div class="detail-section-head"><h2><?= h(localized('معلومات الموقع','Site Information')) ?></h2></div><dl class="site-facts"><div><dt><?= h(t('sites.type')) ?></dt><dd><?= h($site['site_type']) ?></dd></div><div><dt><?= h(t('sites.city')) ?></dt><dd><?= h($site['city']) ?></dd></div><div><dt><?= h(t('sites.region')) ?></dt><dd><?= h($site['region']) ?></dd></div><div><dt><?= h(localized('حالة السجل','Record Status')) ?></dt><dd><?= h(status_label($site['status'])) ?></dd></div></dl></article>
    <?php if($risks): ?><article class="detail-section-card"><div class="detail-section-head"><h2><?= h(localized('المخاطر المسجلة','Recorded Risks')) ?></h2></div><div class="risk-type-list"><?php foreach($risks as $r): ?><div><span><?= h($r['name']) ?></span><b><?= (int)$r['severity'] ?>/5</b></div><?php endforeach; ?></div></article><?php endif; ?>
    <?php if($images): ?><article class="detail-section-card"><div class="detail-section-head"><h2><?= h(localized('صور الموقع','Site Photos')) ?></h2></div><div class="site-gallery"><?php foreach($images as $img): ?><a href="<?= h($img['image_path']) ?>" target="_blank"><img src="<?= h($img['image_path']) ?>" alt="<?= h($img['caption']?:$site['name']) ?>"></a><?php endforeach; ?></div></article><?php endif; ?>
    <?php if(user_role()==='user'): ?><article class="site-report-cta"><i class="ti ti-message-report"></i><h3><?= h(localized('هل لاحظت مشكلة في الموقع؟','Did you notice an issue?')) ?></h3><p><?= h(localized('أرسل بلاغًا واضحًا وسيتولى المسؤول والعالم الأثري متابعة الحالة.','Submit a clear report and the administrator and archaeologist will follow up.')) ?></p><a class="btn btn-primary" href="user/report.php?site_id=<?= (int)$site['id'] ?>"><?= h(localized('إرسال بلاغ عن هذا الموقع','Report an Issue at This Site')) ?></a></article><?php endif; ?>
  </aside>
</div>
<script>window.ATHAR_SITES=[<?= json_encode(['id'=>(int)$site['id'],'name'=>$site['name'],'city'=>$site['city'],'lat'=>(float)$site['latitude'],'lng'=>(float)$site['longitude'],'risk'=>(int)$site['risk_percentage'],'level'=>$site['risk_level'],'type'=>$site['site_type']],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) ?>];window.ATHAR_LANG=<?= json_encode(current_lang()) ?>;window.ATHAR_DEFAULT_MODE='markers';</script><script src="assets/js/map.js?v=16.0"></script>
<?php include __DIR__.'/includes/footer.php'; ?>
