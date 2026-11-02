<?php
require_once __DIR__.'/../includes/helpers.php';
require_role('user');
$title=t('nav.report_new');

$selectedSite=(int)($_GET['site_id']??($_POST['site_id']??0));
$oldTitle=trim($_POST['title']??'');
$oldDescription=trim($_POST['description']??'');
$oldPriority=in_array($_POST['priority']??'',['low','medium','high'],true)?$_POST['priority']:'medium';
$openReportModal=false;

if($_SERVER['REQUEST_METHOD']==='POST'){
  verify_csrf();
  $site=(int)($_POST['site_id']??0);
  $titleIn=$oldTitle;
  $desc=$oldDescription;
  $priority=$oldPriority;

  if($site&&$titleIn&&$desc){
    query('INSERT INTO ae_incident_reports(site_id,user_id,title,description,priority,status) VALUES(?,?,?,?,?,"new")',[$site,current_user()['id'],$titleIn,$desc,$priority]);
    $rid=db()->insert_id;
    foreach(upload_files('images','reports') as $p){
      query('INSERT INTO ae_report_attachments(report_id,image_path) VALUES(?,?)',[$rid,$p]);
    }
    flash('success',localized('تم إرسال البلاغ بنجاح، ويمكنك متابعة حالته من القائمة.','Your report was submitted successfully. You can track it from the list.'));
    redirect('user/report.php');
  }

  $openReportModal=true;
  flash('warning',localized('يرجى تعبئة الموقع والعنوان ووصف المشكلة.','Please complete the site, title, and problem description.'));
}

if($selectedSite>0 && $_SERVER['REQUEST_METHOD']!=='POST'){
  $openReportModal=true;
}

$sites=site_list('WHERE status="active"');
$rows=fetch_all('SELECT r.*,s.name site_name,s.city,(SELECT COUNT(*) FROM ae_report_attachments a WHERE a.report_id=r.id) attachment_count FROM ae_incident_reports r JOIN ae_heritage_sites s ON s.id=r.site_id WHERE r.user_id=? ORDER BY r.id DESC',[current_user()['id']]);
$counts=['all'=>count($rows),'new'=>0,'assigned'=>0,'under_review'=>0,'closed'=>0];
foreach($rows as $r){if(isset($counts[$r['status']]))$counts[$r['status']]++;}

include __DIR__.'/../includes/header.php';
?>
<section class="page-hero report-page-hero report-center-hero">
  <div>
    <span class="eyebrow"><?= h(localized('مركز البلاغات','Report Center')) ?></span>
    <h1><?= h(localized('بلاغاتي','My Reports')) ?></h1>
    <p><?= h(localized('تابع جميع البلاغات التي قدمتها وحالتها الحالية، وأنشئ بلاغًا جديدًا عند ملاحظة أي خطر في موقع تراثي.','Track all submitted reports and their current status, and create a new report whenever you notice a risk at a heritage site.')) ?></p>
  </div>
  <button class="btn btn-primary report-primary-action" type="button" data-modal-template="#reportFormTemplate" data-modal-wide="true" data-open-report-modal>
    <i class="ti ti-plus"></i><?= h(localized('إنشاء بلاغ جديد','Create New Report')) ?>
  </button>
</section>

<section class="report-overview-card">
  <div class="report-overview-copy">
    <span class="report-overview-icon"><i class="ti ti-message-report"></i></span>
    <div>
      <h2><?= h(localized('أرسل البلاغ عند الحاجة فقط','Submit a Report When Needed')) ?></h2>
      <p><?= h(localized('اضغط على زر إنشاء بلاغ لفتح نموذج منبثق مرتب، ثم تابع حالة البلاغات السابقة في الأسفل.','Use the Create Report button to open a clean modal form, then track previous reports below.')) ?></p>
    </div>
  </div>
  <div class="report-overview-stats">
    <div><b><?= $counts['all'] ?></b><span><?= h(localized('إجمالي البلاغات','Total Reports')) ?></span></div>
    <div><b><?= $counts['new']+$counts['assigned']+$counts['under_review'] ?></b><span><?= h(localized('قيد المتابعة','In Progress')) ?></span></div>
    <div><b><?= $counts['closed'] ?></b><span><?= h(localized('مغلقة','Closed')) ?></span></div>
  </div>
</section>

<section class="report-history-section">
  <div class="section-heading report-history-heading">
    <div>
      <span class="eyebrow"><?= h(localized('البلاغات المقدمة','Submitted Reports')) ?></span>
      <h2><?= h(localized('متابعة حالة بلاغاتك','Track Your Reports')) ?></h2>
      <p><?= h(localized('تعرض هذه القائمة جميع البلاغات التي قدمتها وآخر مرحلة وصلت إليها.','This list shows every report you submitted and its latest stage.')) ?></p>
    </div>
    <span class="report-total"><b><?= $counts['all'] ?></b><?= h(localized('إجمالي البلاغات','Total Reports')) ?></span>
  </div>

  <div class="report-status-summary">
    <div><i class="ti ti-sparkles"></i><b><?= $counts['new'] ?></b><span><?= h(status_label('new')) ?></span></div>
    <div><i class="ti ti-user-check"></i><b><?= $counts['assigned'] ?></b><span><?= h(status_label('assigned')) ?></span></div>
    <div><i class="ti ti-clipboard-search"></i><b><?= $counts['under_review'] ?></b><span><?= h(status_label('under_review')) ?></span></div>
    <div><i class="ti ti-circle-check"></i><b><?= $counts['closed'] ?></b><span><?= h(status_label('closed')) ?></span></div>
  </div>

  <?php if(!$rows): ?>
    <div class="report-empty-state">
      <i class="ti ti-message-report"></i>
      <h3><?= h(localized('لم ترسل أي بلاغ بعد','No reports submitted yet')) ?></h3>
      <p><?= h(localized('ابدأ بإنشاء أول بلاغ عند ملاحظة مشكلة في أحد المواقع التراثية.','Create your first report when you notice an issue at a heritage site.')) ?></p>
      <button class="btn btn-primary" type="button" data-modal-template="#reportFormTemplate" data-modal-wide="true">
        <i class="ti ti-plus"></i><?= h(localized('إنشاء بلاغ','Create Report')) ?>
      </button>
    </div>
  <?php else: ?>
    <div class="submitted-report-list">
      <?php foreach($rows as $r): ?>
      <article class="submitted-report-card">
        <div class="submitted-report-main">
          <div class="submitted-report-top"><span class="badge badge-neutral"><?= h(status_label($r['status'])) ?></span><span class="priority-pill priority-<?= h($r['priority']) ?>"><?= h(priority_label($r['priority'])) ?></span></div>
          <h3><?= h($r['title']) ?></h3>
          <p><?= h(excerpt($r['description'],150)) ?></p>
          <div class="submitted-report-meta">
            <span><i class="ti ti-building-monument"></i><?= h($r['site_name']) ?></span>
            <span><i class="ti ti-map-pin"></i><?= h($r['city']) ?></span>
            <span><i class="ti ti-calendar"></i><?= h(substr($r['created_at'],0,10)) ?></span>
            <?php if((int)$r['attachment_count']): ?><span><i class="ti ti-photo"></i><?= (int)$r['attachment_count'] ?> <?= h(localized('صور','photos')) ?></span><?php endif; ?>
          </div>
        </div>
        <div class="report-progress" aria-label="<?= h(status_label($r['status'])) ?>">
          <?php $steps=['new','assigned','under_review','closed'];$idx=array_search($r['status'],$steps,true);if($idx===false)$idx=0;foreach($steps as $i=>$st): ?>
            <span class="<?= $i<=$idx?'done':'' ?>"><i class="ti <?= $i<$idx?'ti-check':'ti-circle' ?>"></i><small><?= h(status_label($st)) ?></small></span>
          <?php endforeach; ?>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<template id="reportFormTemplate">
  <div class="report-modal-shell">
    <div class="modal-head report-modal-head">
      <span class="report-form-icon"><i class="ti ti-message-report"></i></span>
      <div>
        <span class="eyebrow"><?= h(localized('بلاغ جديد','New Report')) ?></span>
        <h2><?= h(localized('أبلغ عن ملاحظة في موقع تراثي','Report an Issue at a Heritage Site')) ?></h2>
        <p><?= h(localized('اختر الموقع وحدد الأولوية واكتب وصفًا دقيقًا. تساعد الصور الواضحة المختصين على تقييم الحالة بشكل أسرع.','Choose the site and priority, then provide an accurate description. Clear photos help specialists assess the condition faster.')) ?></p>
      </div>
    </div>

    <form class="report-form report-modal-form" method="post" enctype="multipart/form-data">
      <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
      <div class="form-grid">
        <div class="form-group">
          <label class="form-label"><?= h(t('reports.site')) ?></label>
          <select class="form-select" name="site_id" required>
            <option value=""><?= h(localized('اختر الموقع التراثي','Select a heritage site')) ?></option>
            <?php foreach($sites as $s): ?><option value="<?= (int)$s['id'] ?>" <?= $selectedSite===(int)$s['id']?'selected':'' ?>><?= h($s['name']) ?> — <?= h($s['city']) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label"><?= h(localized('الأولوية','Priority')) ?></label>
          <select class="form-select" name="priority">
            <option value="medium" <?= $oldPriority==='medium'?'selected':'' ?>><?= h(t('priority.medium')) ?></option>
            <option value="high" <?= $oldPriority==='high'?'selected':'' ?>><?= h(t('priority.high')) ?></option>
            <option value="low" <?= $oldPriority==='low'?'selected':'' ?>><?= h(t('priority.low')) ?></option>
          </select>
        </div>
        <div class="form-group full">
          <label class="form-label"><?= h(localized('عنوان البلاغ','Report Title')) ?></label>
          <input class="form-control" name="title" value="<?= h($oldTitle) ?>" placeholder="<?= h(localized('مثال: تشققات واضحة في الجدار الشمالي','Example: Visible cracks in the northern wall')) ?>" required>
        </div>
        <div class="form-group full">
          <label class="form-label"><?= h(localized('وصف المشكلة','Problem Description')) ?></label>
          <textarea name="description" rows="5" placeholder="<?= h(localized('صف ما شاهدته، ومكان الضرر، ومدى انتشاره إن أمكن...','Describe what you observed, the damage location, and its extent if possible...')) ?>" required><?= h($oldDescription) ?></textarea>
          <div class="field-hint"><i class="ti ti-bulb"></i><?= h(localized('لا تحدد نسبة الخطورة بنفسك؛ العالم الأثري يقيّمها بعد الزيارة الميدانية.','Do not assign a risk percentage yourself; the archaeologist calculates it after the field visit.')) ?></div>
        </div>
        <div class="form-group full">
          <label class="form-label"><?= h(localized('صور البلاغ','Report Photos')) ?></label>
          <label class="upload-dropzone" data-file-dropzone>
            <i class="ti ti-cloud-upload"></i>
            <b><?= h(localized('اضغط لاختيار الصور','Choose Photos')) ?></b>
            <span data-file-summary><?= h(localized('يمكن رفع أكثر من صورة بصيغة JPG أو PNG.','You may upload multiple JPG or PNG images.')) ?></span>
            <input type="file" name="images[]" multiple accept="image/*" data-file-input>
          </label>
        </div>
      </div>
      <div class="report-submit-row">
        <div class="privacy-note"><i class="ti ti-shield-check"></i><?= h(localized('يظهر البلاغ للجهات المختصة فقط حتى تتم مراجعته.','The report is visible only to authorized staff while under review.')) ?></div>
        <div class="actions report-modal-actions">
          <button class="btn btn-light" type="button" data-modal-close><?= h(t('common.cancel')) ?></button>
          <button class="btn btn-primary" type="submit"><i class="ti ti-send"></i><?= h(localized('إرسال البلاغ','Submit Report')) ?></button>
        </div>
      </div>
    </form>
  </div>
</template>

<?php if($openReportModal): ?>
<script>document.addEventListener('DOMContentLoaded',function(){setTimeout(function(){document.querySelector('[data-open-report-modal]')?.click();},50)});</script>
<?php endif; ?>

<?php include __DIR__.'/../includes/footer.php'; ?>
