<?php
require_once __DIR__ . '/includes/helpers.php';
require_login();
$title=t('profile.title');
$user=fetch_one('SELECT id,name,email,phone,role,status,preferred_language,created_at,updated_at FROM ae_users WHERE id=?',[current_user()['id']]);
if(!$user){ session_destroy(); redirect('login.php'); }
if($_SERVER['REQUEST_METHOD']==='POST'){
  verify_csrf();
  $name=trim($_POST['name']??''); $email=trim($_POST['email']??''); $phone=trim($_POST['phone']??''); $pref=in_array($_POST['preferred_language']??'',['ar','en'],true)?$_POST['preferred_language']:'ar';
  $password=$_POST['new_password']??''; $confirm=$_POST['confirm_password']??'';
  $exists=(int)scalar('SELECT COUNT(*) FROM ae_users WHERE email=? AND id<>?',[$email,current_user()['id']]);
  if(!$name || !$email){ flash('warning',localized('يرجى تعبئة الاسم والبريد الإلكتروني.','Please enter your name and email address.')); }
  elseif($exists){ flash('danger',t('profile.email_exists')); }
  elseif($password!=='' && $password!==$confirm){ flash('danger',t('profile.password_mismatch')); }
  else{
    if($password!==''){
      $hash=password_hash($password,PASSWORD_DEFAULT);
      query('UPDATE ae_users SET name=?,email=?,phone=?,preferred_language=?,password_hash=?,updated_at=NOW() WHERE id=?',[$name,$email,$phone,$pref,$hash,current_user()['id']]);
    } else {
      query('UPDATE ae_users SET name=?,email=?,phone=?,preferred_language=?,updated_at=NOW() WHERE id=?',[$name,$email,$phone,$pref,current_user()['id']]);
    }
    $_SESSION['user']['name']=$name; $_SESSION['user']['email']=$email; $_SESSION['user']['preferred_language']=$pref; $_SESSION['lang']=$pref;
    flash('success',t('profile.updated')); redirect('profile.php');
  }
}
$user=fetch_one('SELECT id,name,email,phone,role,status,preferred_language,created_at,updated_at FROM ae_users WHERE id=?',[current_user()['id']]);
include __DIR__.'/includes/header.php';
?>
<section class="page-hero"><span class="eyebrow"><?= h(t('profile.label')) ?></span><h1><?= h(t('profile.title')) ?></h1><p><?= h(t('profile.desc')) ?></p></section>
<div class="profile-layout">
  <aside class="card profile-summary">
    <div class="profile-cover"><div class="profile-avatar"><?= h(first_char($user['name'])) ?></div></div>
    <div class="profile-meta"><h2><?= h($user['name']) ?></h2><span class="badge badge-success"><?= h(role_label($user['role'])) ?></span></div>
    <div class="profile-detail-list">
      <div class="profile-detail"><span><?= h(t('profile.email')) ?></span><b class="ltr"><?= h($user['email']) ?></b></div>
      <div class="profile-detail"><span><?= h(t('profile.phone')) ?></span><b class="ltr"><?= h($user['phone']?:'—') ?></b></div>
      <div class="profile-detail"><span><?= h(t('profile.joined')) ?></span><b><?= h(substr($user['created_at'],0,10)) ?></b></div>
      <div class="profile-detail"><span><?= h(t('common.status')) ?></span><b><?= h(status_label($user['status'])) ?></b></div>
    </div>
    <h3 style="color:var(--green);margin-top:24px"><?= h(t('profile.permissions')) ?></h3>
    <ul class="permission-list"><?php foreach(user_permissions($user['role']) as $permission): ?><li><i class="ti ti-circle-check"></i><span><?= h($permission) ?></span></li><?php endforeach; ?></ul>
  </aside>
  <section class="card">
    <div class="section-heading compact"><span class="eyebrow"><?= h(t('profile.label')) ?></span><h2><?= h(t('profile.desc')) ?></h2></div>
    <form method="post"><input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
      <div class="form-grid">
        <div class="form-group"><label class="form-label"><?= h(t('profile.name')) ?></label><input class="form-control" name="name" value="<?= h($user['name']) ?>" required></div>
        <div class="form-group"><label class="form-label"><?= h(t('profile.email')) ?></label><input class="form-control ltr" type="email" name="email" value="<?= h($user['email']) ?>" required></div>
        <div class="form-group"><label class="form-label"><?= h(t('profile.phone')) ?></label><input class="form-control ltr" name="phone" value="<?= h($user['phone']) ?>" placeholder="+966..."></div>
        <div class="form-group"><label class="form-label"><?= h(t('profile.language')) ?></label><select class="form-select" name="preferred_language"><option value="ar" <?= $user['preferred_language']==='ar'?'selected':'' ?>>العربية</option><option value="en" <?= $user['preferred_language']==='en'?'selected':'' ?>>English</option></select></div>
        <div class="form-section-title"><?= h(localized('تغيير كلمة المرور اختياري','Optional password change')) ?></div>
        <div class="form-group"><label class="form-label"><?= h(t('profile.new_password')) ?></label><input class="form-control ltr" type="password" name="new_password" minlength="6"></div>
        <div class="form-group"><label class="form-label"><?= h(t('profile.confirm_password')) ?></label><input class="form-control ltr" type="password" name="confirm_password" minlength="6"></div>
      </div>
      <div class="actions"><button class="btn btn-primary" type="submit"><i class="ti ti-device-floppy"></i><?= h(t('profile.save')) ?></button></div>
    </form>
  </section>
</div>
<?php include __DIR__.'/includes/footer.php'; ?>
