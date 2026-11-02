<?php
require_once __DIR__.'/includes/helpers.php';
if(is_logged_in()) redirect('dashboard.php');
$title=t('nav.register'); $bodyClass='public-body auth-page register-page';
$values=['name'=>'','email'=>'','phone'=>''];
if($_SERVER['REQUEST_METHOD']==='POST'){
  verify_csrf();
  $values['name']=trim($_POST['name']??'');
  $values['email']=strtolower(trim($_POST['email']??''));
  $values['phone']=trim($_POST['phone']??'');
  $password=$_POST['password']??''; $confirm=$_POST['confirm_password']??'';
  $errors=[];
  if((function_exists('mb_strlen')?mb_strlen($values['name'],'UTF-8'):strlen($values['name']))<3) $errors[]=localized('يرجى كتابة الاسم الكامل.','Please enter your full name.');
  if(!filter_var($values['email'],FILTER_VALIDATE_EMAIL)) $errors[]=localized('البريد الإلكتروني غير صحيح.','Please enter a valid email address.');
  if(strlen($password)<8) $errors[]=localized('كلمة المرور يجب ألا تقل عن 8 أحرف.','Password must be at least 8 characters.');
  if($password!==$confirm) $errors[]=localized('كلمتا المرور غير متطابقتين.','Passwords do not match.');
  try{
    if(!$errors && fetch_one('SELECT id FROM ae_users WHERE email=?',[$values['email']])) $errors[]=localized('البريد الإلكتروني مستخدم بالفعل.','This email address is already registered.');
    if(!$errors){
      $hash=password_hash($password,PASSWORD_DEFAULT);
      query('INSERT INTO ae_users(name,email,password_hash,role,status,phone,preferred_language) VALUES(?,?,?,"user","active",?,?)',[$values['name'],$values['email'],$hash,$values['phone']?:null,current_lang()]);
      $id=db()->insert_id;
      $_SESSION['user']=['id'=>(int)$id,'name'=>$values['name'],'email'=>$values['email'],'role'=>'user','preferred_language'=>current_lang()];
      $_SESSION['lang']=current_lang();
      flash('success',localized('تم إنشاء حسابك بنجاح. مرحبًا بك في عين الأثر.','Your account has been created successfully. Welcome to AtharEye.'));
      redirect('dashboard.php');
    }
  }catch(Throwable $e){
    $errors[]=localized('تعذر إنشاء الحساب الآن. تأكد من تثبيت النظام ثم حاول مرة أخرى.','The account could not be created. Please make sure the system is installed and try again.');
  }
}
include __DIR__.'/includes/header.php';
?>
<section class="auth-page-shell register-shell" aria-labelledby="registerTitle">
  <div class="auth-card register-card">
    <div class="auth-card-brand"><img src="assets/images/logo-full.svg" alt="<?= h(t('app.name')) ?>"></div>
    <span class="auth-kicker"><i class="ti ti-user-plus"></i><?= h(localized('حساب مستخدم جديد','New User Account')) ?></span>
    <h1 id="registerTitle"><?= h(t('nav.register')) ?></h1>
    <p class="auth-lead"><?= h(localized('أنشئ حسابًا لاستعراض المواقع التراثية وإرسال البلاغات ومتابعة حالتها.','Create an account to browse heritage sites, submit reports, and track their status.')) ?></p>
    <?php if(!empty($errors)): ?><div class="form-errors"><?php foreach($errors as $error): ?><div><i class="ti ti-alert-circle"></i><?= h($error) ?></div><?php endforeach; ?></div><?php endif; ?>
    <form method="post" class="auth-form register-form" novalidate>
      <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
      <div class="form-group"><label class="form-label" for="regName"><?= h(localized('الاسم الكامل','Full Name')) ?></label><div class="field-with-icon"><i class="ti ti-user"></i><input class="form-control" id="regName" name="name" value="<?= h($values['name']) ?>" autocomplete="name" required></div></div>
      <div class="form-group"><label class="form-label" for="regEmail"><?= h(t('profile.email')) ?></label><div class="field-with-icon"><i class="ti ti-mail"></i><input class="form-control ltr" id="regEmail" type="email" name="email" value="<?= h($values['email']) ?>" autocomplete="email" required></div></div>
      <div class="form-group"><label class="form-label" for="regPhone"><?= h(t('profile.phone')) ?> <span class="optional"><?= h(localized('(اختياري)','(Optional)')) ?></span></label><div class="field-with-icon"><i class="ti ti-phone"></i><input class="form-control ltr" id="regPhone" name="phone" value="<?= h($values['phone']) ?>" autocomplete="tel"></div></div>
      <div class="register-password-grid">
        <div class="form-group"><label class="form-label" for="regPassword"><?= h(localized('كلمة المرور','Password')) ?></label><div class="field-with-icon password-field"><i class="ti ti-lock"></i><input class="form-control ltr" id="regPassword" type="password" name="password" autocomplete="new-password" required><button class="password-toggle" type="button" data-password-toggle><i class="ti ti-eye"></i></button></div></div>
        <div class="form-group"><label class="form-label" for="regConfirm"><?= h(localized('تأكيد كلمة المرور','Confirm Password')) ?></label><div class="field-with-icon password-field"><i class="ti ti-lock-check"></i><input class="form-control ltr" id="regConfirm" type="password" name="confirm_password" autocomplete="new-password" required><button class="password-toggle" type="button" data-password-toggle><i class="ti ti-eye"></i></button></div></div>
      </div>
      <label class="terms-check"><input type="checkbox" required><span><?= h(localized('أوافق على استخدام بيانات الحساب داخل النظام لأغراض المتابعة وتقديم البلاغات.','I agree to use my account data within the system for monitoring and report submission.')) ?></span></label>
      <button class="btn btn-primary auth-submit" type="submit"><i class="ti ti-user-check"></i><span><?= h(t('nav.register')) ?></span></button>
    </form>
    <div class="auth-secondary-action"><span><?= h(localized('لديك حساب بالفعل؟','Already have an account?')) ?></span><a href="login.php"><i class="ti ti-login-2"></i><?= h(t('nav.login')) ?></a></div>
  </div>
  <aside class="auth-showcase register-showcase">
    <div class="auth-showcase-content">
      <div class="auth-showcase-mark"><img src="assets/images/mark.svg" alt=""></div>
      <span class="auth-showcase-label"><?= h(localized('منصة سعودية لمتابعة التراث','A Saudi heritage monitoring platform')) ?></span>
      <h2><?= h(localized('شارك في حماية المواقع التراثية بالمعلومة الدقيقة.','Help protect heritage sites through accurate reporting.')) ?></h2>
      <p><?= h(localized('يمنحك الحساب إمكانية استعراض المواقع، إرسال البلاغات بالصور، ومتابعة حالة كل بلاغ حتى اكتمال المعالجة.','Your account lets you browse sites, submit photo reports, and follow every report until the review process is complete.')) ?></p>
      <div class="register-benefits">
        <div><i class="ti ti-map-pin"></i><b><?= h(localized('استعرض المواقع','Browse Sites')) ?></b><span><?= h(localized('تفاصيل واضحة وتقارير معتمدة.','Clear details and approved reports.')) ?></span></div>
        <div><i class="ti ti-camera-plus"></i><b><?= h(localized('أرسل بلاغًا','Submit a Report')) ?></b><span><?= h(localized('أرفق وصفًا وصورًا للمشكلة.','Attach a description and photos.')) ?></span></div>
        <div><i class="ti ti-progress-check"></i><b><?= h(localized('تابع الحالة','Track Progress')) ?></b><span><?= h(localized('اعرف مرحلة معالجة البلاغ.','Know the report review stage.')) ?></span></div>
      </div>
    </div>
  </aside>
</section>
<?php include __DIR__.'/includes/footer.php'; ?>
