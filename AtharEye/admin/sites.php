<?php
require_once __DIR__.'/../includes/helpers.php';
require_role('admin');
$title=t('sites.manage_title');
if($_SERVER['REQUEST_METHOD']==='POST'){
  verify_csrf(); $action=$_POST['action']??'';
  if($action==='delete'){
    $id=(int)($_POST['id']??0);
    if($id){ query('DELETE FROM ae_heritage_sites WHERE id=?',[$id]); flash('success',t('sites.deleted')); }
    redirect('admin/sites.php');
  }
  if($action==='save'){
    $id=(int)($_POST['id']??0);
    $name=trim($_POST['name']??''); $city=trim($_POST['city']??''); $region=trim($_POST['region']??''); $type=trim($_POST['site_type']??''); $desc=trim($_POST['description']??'');
    $lat=(float)($_POST['latitude']??0); $lng=(float)($_POST['longitude']??0); $risk=max(0,min(100,(int)($_POST['risk_percentage']??0))); $level=risk_level_from_percentage($risk);
    if(!$name||!$city||!$type||!$lat||!$lng){ flash('warning',localized('يرجى تعبئة جميع البيانات الأساسية والإحداثيات.','Please complete the required data and coordinates.')); redirect('admin/sites.php'); }
    if($id){
      query('UPDATE ae_heritage_sites SET name=?,city=?,region=?,site_type=?,description=?,latitude=?,longitude=?,risk_percentage=?,risk_level=? WHERE id=?',[$name,$city,$region?:'تبوك',$type,$desc,$lat,$lng,$risk,$level,$id]);
    }else{
      query('INSERT INTO ae_heritage_sites(name,city,region,site_type,description,latitude,longitude,risk_percentage,risk_level,status,created_by) VALUES(?,?,?,?,?,?,?,?,?,"active",?)',[$name,$city,$region?:'تبوك',$type,$desc,$lat,$lng,$risk,$level,current_user()['id']]); $id=db()->insert_id;
    }
    foreach(upload_files('images','sites') as $img) query('INSERT INTO ae_site_images(site_id,image_path) VALUES(?,?)',[$id,$img]);
    flash('success',t('sites.saved')); redirect('admin/sites.php');
  }
}
$q=trim($_GET['q']??''); $where=''; $params=[];
if($q!==''){ $where='WHERE name LIKE ? OR city LIKE ? OR site_type LIKE ?'; $like="%$q%"; $params=[$like,$like,$like]; }
$sites=site_list($where,$params);
include __DIR__.'/../includes/header.php';
?>
<section class="page-hero"><span class="eyebrow"><?= h(t('sites.manage_label')) ?></span><h1><?= h(t('sites.manage_title')) ?></h1><p><?= h(t('sites.manage_desc')) ?></p></section>
<div class="admin-site-toolbar">
  <div class="top-search-wrap" style="max-width:460px;flex:1"><i class="ti ti-search"></i><input class="form-control" data-live-filter="#adminSiteList" placeholder="<?= h(localized('ابحث باسم الموقع أو المدينة أو النوع...','Search by site, city, or type...')) ?>"></div>
  <button class="btn btn-primary" type="button" data-add-site data-title="<?= h(t('sites.add')) ?>"><i class="ti ti-plus"></i><?= h(t('sites.add')) ?></button>
</div>
<div class="admin-site-list" id="adminSiteList">
<?php foreach($sites as $site): $payload=['id'=>(int)$site['id'],'name'=>$site['name'],'city'=>$site['city'],'region'=>$site['region'],'site_type'=>$site['site_type'],'latitude'=>$site['latitude'],'longitude'=>$site['longitude'],'risk_percentage'=>$site['risk_percentage'],'description'=>$site['description']]; ?>
  <article class="admin-site-item" data-filter-row>
    <div class="admin-site-thumb"><i class="ti ti-building-fortress"></i></div>
    <div class="admin-site-info"><h3><?= h($site['name']) ?></h3><div class="site-meta"><span><i class="ti ti-map-pin"></i><?= h($site['city']) ?></span><span><i class="ti ti-building-monument"></i><?= h($site['site_type']) ?></span><span class="badge badge-<?= h(risk_class($site['risk_level'])) ?>"><?= h(risk_label($site['risk_level'])) ?> <?= (int)$site['risk_percentage'] ?>%</span></div><p class="mini"><?= h(excerpt($site['description'],120)) ?></p></div>
    <div class="admin-site-actions"><a class="btn btn-light btn-icon" title="<?= h(t('common.view')) ?>" href="site.php?id=<?= (int)$site['id'] ?>"><i class="ti ti-eye"></i></a><button class="btn btn-muted btn-icon" type="button" title="<?= h(t('common.edit')) ?>" data-edit-site data-title="<?= h(t('sites.edit')) ?>" data-site="<?= h(json_encode($payload,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)) ?>"><i class="ti ti-edit"></i></button><form method="post" data-confirm data-confirm-title="<?= h(t('sites.confirm_title')) ?>" data-confirm-text="<?= h(t('sites.confirm_text')) ?>" data-confirm-button="<?= h(t('sites.confirm_button')) ?>"><input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$site['id'] ?>"><button class="btn btn-danger btn-icon" type="submit" title="<?= h(t('common.delete')) ?>"><i class="ti ti-trash"></i></button></form></div>
  </article>
<?php endforeach; ?>
<?php if(!$sites): ?><div class="empty card"><?= h(t('sites.empty')) ?></div><?php endif; ?>
</div>
<template id="siteFormTemplate">
  <div class="modal-head"><span class="eyebrow"><?= h(t('sites.manage_label')) ?></span><h2 data-modal-title><?= h(t('sites.add')) ?></h2><p><?= h(t('sites.manage_desc')) ?></p></div>
  <form id="siteModalForm" method="post" enctype="multipart/form-data"><input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="">
    <div class="form-grid"><div class="form-section-title"><?= h(localized('البيانات الأساسية','Basic Information')) ?></div>
      <div class="form-group"><label class="form-label"><?= h(t('sites.name')) ?></label><input class="form-control" name="name" required></div>
      <div class="form-group"><label class="form-label"><?= h(t('sites.city')) ?></label><input class="form-control" name="city" required></div>
      <div class="form-group"><label class="form-label"><?= h(t('sites.region')) ?></label><input class="form-control" name="region" value="<?= h(localized('تبوك','Tabuk')) ?>"></div>
      <div class="form-group"><label class="form-label"><?= h(t('sites.type')) ?></label><input class="form-control" name="site_type" required></div>
      <div class="form-section-title"><?= h(localized('الموقع والخطورة','Location & Risk')) ?></div>
      <div class="form-group"><label class="form-label"><?= h(t('sites.latitude')) ?></label><input class="form-control ltr" type="number" step="0.000001" name="latitude" required></div>
      <div class="form-group"><label class="form-label"><?= h(t('sites.longitude')) ?></label><input class="form-control ltr" type="number" step="0.000001" name="longitude" required></div>
      <div class="form-group"><label class="form-label"><?= h(t('sites.risk')) ?></label><input class="form-control" type="number" min="0" max="100" name="risk_percentage" value="0" required></div>
      <div class="form-group"><label class="form-label"><?= h(t('sites.images')) ?></label><input class="form-control" type="file" name="images[]" multiple accept="image/*"></div>
      <div class="form-group full"><label class="form-label"><?= h(t('sites.description')) ?></label><textarea name="description" required></textarea></div>
    </div><div class="actions"><button class="btn btn-primary" type="submit"><i class="ti ti-device-floppy"></i><?= h(t('common.save')) ?></button><button class="btn btn-light" type="button" data-modal-close><?= h(t('common.cancel')) ?></button></div>
  </form>
</template>
<?php include __DIR__.'/../includes/footer.php'; ?>
