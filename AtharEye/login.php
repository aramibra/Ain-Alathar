<?php
require_once __DIR__.'/includes/helpers.php';
if(is_logged_in()) redirect('dashboard.php');
$title=t('nav.login');
$bodyClass='public-body auth-page';
if($_SERVER['REQUEST_METHOD']==='POST'){
  verify_csrf();
  $email=trim($_POST['email']??'');
  $pass=$_POST['password']??'';
  try{
    $u=fetch_one("SELECT * FROM ae_users WHERE email=? AND status='active'",[$email]);
    if($u && password_verify($pass,$u['password_hash'])){
      $_SESSION['user']=[
        'id'=>(int)$u['id'],
        'name'=>$u['name'],
        'email'=>$u['email'],
        'role'=>$u['role'],
        'preferred_language'=>$u['preferred_language']??'ar'
      ];
      $_SESSION['lang']=$u['preferred_language']??current_lang();
      redirect('dashboard.php');
    }
    flash('danger',localized('بيانات الدخول غير صحيحة.','Incorrect sign-in credentials.'));
  }catch(Throwable $e){
    flash('danger',localized('يرجى تثبيت المشروع أولًا.','Please install the project first.'));
    redirect('install.php');
  }
}
include __DIR__.'/includes/header.php';
?>
<section class="auth-page-shell" aria-labelledby="signInTitle">
  <div class="auth-card">
    <div class="auth-card-brand">
      <img src="assets/images/logo-full.svg" alt="<?= h(t('app.name')) ?>">
    </div>

    <span class="auth-kicker"><i class="ti ti-shield-lock"></i><?= h(t('app.tagline')) ?></span>
    <h1 id="signInTitle"><?= h(t('nav.login')) ?></h1>
    <p class="auth-lead"><?= h(localized(
      'أدخل بيانات حسابك للوصول إلى اللوحة المناسبة لصلاحياتك داخل النظام.',
      'Enter your account details to access the dashboard assigned to your role.'
    )) ?></p>

    <form method="post" class="auth-form" novalidate>
      <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">

      <div class="form-group">
        <label class="form-label" for="loginEmail"><?= h(t('profile.email')) ?></label>
        <div class="field-with-icon">
          <i class="ti ti-mail"></i>
          <input class="form-control ltr" id="loginEmail" type="email" name="email" autocomplete="username" placeholder="name@example.com" required>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label" for="loginPassword"><?= h(localized('كلمة المرور','Password')) ?></label>
        <div class="field-with-icon password-field">
          <i class="ti ti-lock"></i>
          <input class="form-control ltr" id="loginPassword" type="password" name="password" autocomplete="current-password" placeholder="••••••••" required>
          <button class="password-toggle" type="button" data-password-toggle aria-label="<?= h(localized('إظهار كلمة المرور','Show password')) ?>">
            <i class="ti ti-eye"></i>
          </button>
        </div>
      </div>

      <button class="btn btn-primary auth-submit" type="submit">
        <i class="ti ti-login-2"></i>
        <span><?= h(t('home.enter')) ?></span>
      </button>
    </form>

    <div class="auth-secondary-action">
      <span><?= h(localized('ليس لديك حساب؟','Do not have an account?')) ?></span>
      <a href="register.php"><i class="ti ti-user-plus"></i><?= h(t('nav.register')) ?></a>
    </div>
    <div class="auth-support-note">
      <i class="ti ti-info-circle"></i>
      <span><?= h(localized(
        'إنشاء الحساب متاح للمستخدم العادي، بينما تُدار صلاحيات المسؤول والعالم الأثري من لوحة الإدارة.',
        'Self-registration creates a standard user account. Administrator and archaeologist roles are managed from the administration panel.'
      )) ?></span>
    </div>
  </div>

  <aside class="auth-showcase" aria-label="<?= h(t('app.name')) ?>">
    <div class="auth-showcase-glow glow-one"></div>
    <div class="auth-showcase-glow glow-two"></div>

    <div class="auth-showcase-content">
      <div class="auth-showcase-mark">
        <img src="assets/images/mark.svg" alt="">
      </div>
      <span class="auth-showcase-label"><?= h(localized('منصة وطنية لحماية التراث','A national heritage protection platform')) ?></span>
      <h2><?= h(localized('رؤية أوضح للمواقع، وقرار أسرع للحماية.','Clearer site visibility. Faster protection decisions.')) ?></h2>
      <p><?= h(localized(
        'يجمع عين الأثر المواقع والبلاغات والتقييمات والتقارير في مسار واحد موثوق يساعد الجهات المختصة على تحديد الأولويات.',
        'AtharEye unifies sites, reports, assessments, and approved records in one trusted workflow that helps specialists set priorities.'
      )) ?></p>

      <div class="auth-feature-list">
        <div><i class="ti ti-map-2"></i><span><?= h(localized('خرائط تفاعلية وحرارية','Interactive maps and risk heatmaps')) ?></span></div>
        <div><i class="ti ti-user-shield"></i><span><?= h(localized('صلاحيات واضحة حسب الدور','Clear role-based permissions')) ?></span></div>
        <div><i class="ti ti-clipboard-check"></i><span><?= h(localized('تقارير لا تُنشر إلا بعد الاعتماد','Reports published only after approval')) ?></span></div>
      </div>
    </div>

    <div class="auth-risk-orbit" aria-hidden="true">
      <span class="orbit-dot low"></span>
      <span class="orbit-dot medium"></span>
      <span class="orbit-dot high"></span>
    </div>
  </aside>
</section>
<?php include __DIR__.'/includes/footer.php'; ?>
