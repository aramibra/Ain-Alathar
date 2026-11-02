<?php
define('ATHAREYE_INSTALLING', true);
require_once __DIR__ . '/config.php';
$done=false; $error=null; $mode=null;
function run_sql_file(mysqli $conn,string $file): void {
  $sql=file_get_contents($file);
  foreach(array_filter(array_map('trim',explode(';',$sql))) as $stmt) if($stmt!=='') $conn->query($stmt);
}
function table_exists(mysqli $conn,string $table): bool { $e=$conn->real_escape_string($table); $r=$conn->query("SHOW TABLES LIKE '$e'"); return $r && $r->num_rows>0; }
function column_exists(mysqli $conn,string $table,string $column): bool { $t=$conn->real_escape_string($table); $c=$conn->real_escape_string($column); $r=$conn->query("SHOW COLUMNS FROM `$t` LIKE '$c'"); return $r && $r->num_rows>0; }
function seed_data(mysqli $conn): void {
  $users=[['مسؤول النظام','admin@heritage.gov.sa','admin123','admin'],['د. سالم الحربي','archaeologist@heritage.gov.sa','archaeologist123','archaeologist'],['مستخدم تجريبي','user@heritage.gov.sa','user123','user']];
  $st=$conn->prepare('INSERT INTO ae_users(name,email,password_hash,role,status,preferred_language) VALUES(?,?,?,?,"active","ar")');
  foreach($users as $u){$hash=password_hash($u[2],PASSWORD_DEFAULT);$st->bind_param('ssss',$u[0],$u[1],$hash,$u[3]);$st->execute();}
  $riskTypes=['إهمال','تشققات','سيول','زحف عمراني','رطوبة','تخريب','تآكل','انهيار جزئي']; $st=$conn->prepare('INSERT INTO ae_risk_types(name) VALUES(?)'); foreach($riskTypes as $rt){$st->bind_param('s',$rt);$st->execute();}
  $sites=[
    ['قلعة الزريب','تبوك','تبوك','قلعة تاريخية','قلعة عثمانية تقع على طريق الحج القديم وتعاني من تشققات وتآكل في بعض الجدران.',28.3132,35.7594,88],
    ['قلعة المويلح','ضباء','تبوك','قلعة ساحلية','قلعة ساحلية تاريخية تأثرت بالرطوبة والأمطار وتحتاج إلى معالجة وتصريف للمياه.',27.6545,35.4577,76],
    ['منطقة حسمي والنقوش الصخرية','تبوك','تبوك','نقوش صخرية','منطقة واسعة تضم نقوشًا ورسومًا صخرية من فترات تاريخية متعددة.',28.9704,35.3088,72],
    ['محطة سكة حديد الحجاز','تبوك','تبوك','محطة تاريخية','محطة تاريخية من خط الحجاز تضم مباني ومستودعات ذات أهمية معمارية.',28.3998,36.5715,48],
    ['قلعة المعظم','المعظم','تبوك','قلعة تاريخية','قلعة تاريخية كانت تخدم طريق الحج الشامي وتحتاج إلى عناية دورية.',27.7329,37.9571,55],
    ['معيار شعيب','البدع','تبوك','معلم أثري','واجهات وممرات صخرية منحوتة تعود لفترات قديمة في محافظة البدع.',28.4896,35.0032,58],
    ['بئر هداج','تيماء','تبوك','بئر تاريخي','من أشهر آبار الجزيرة وتحيط به عناصر عمرانية واقتصادية وتاريخية.',27.6327,38.5517,22],
    ['قلعة تبوك','تبوك','تبوك','قلعة تاريخية','معلم تراثي داخل مدينة تبوك يرتبط بتاريخ الحج الشامي ويضم آثارًا متعددة.',28.3838,36.5543,24],
    ['وادي الديسة','الديسة','تبوك','موقع طبيعي تراثي','منطقة طبيعية وتراثية تضم تكوينات صخرية ومواقع نقوش ومسارات ماء.',27.6611,36.4207,46],
    ['مسجد التوبة','تبوك','تبوك','مسجد تاريخي','مسجد تاريخي ارتبط بسيرة الرسول صلى الله عليه وسلم في المنطقة.',28.3830,36.5610,18],
  ];
  $st=$conn->prepare('INSERT INTO ae_heritage_sites(name,city,region,site_type,description,latitude,longitude,risk_percentage,risk_level,status,created_by) VALUES(?,?,?,?,?,?,?,?,?,"active",1)');
  foreach($sites as $s){$level=$s[7]>=70?'مرتفع':($s[7]>=40?'متوسط':'منخفض');$st->bind_param('sssssddis',$s[0],$s[1],$s[2],$s[3],$s[4],$s[5],$s[6],$s[7],$level);$st->execute();}
  $conn->query("INSERT INTO ae_incident_reports(site_id,user_id,title,description,status,priority) VALUES (1,3,'تشققات واضحة في الجدار الشمالي','توجد تشققات كبيرة وتآكل في أجزاء من الجدار الشمالي للقلعة.','assigned','high'),(2,3,'تجمع مياه قرب الأساسات','تجمع المياه بعد الأمطار قرب الأساسات ويحتاج إلى فحص ميداني.','new','medium')");
  $conn->query("INSERT INTO ae_report_assignments(report_id,archaeologist_id,assigned_by,status,notes) VALUES(1,2,1,'assigned','زيارة الموقع وتحديد مستوى الخطورة بدقة')");
  $conn->query("INSERT INTO ae_field_assessments(report_id,site_id,archaeologist_id,visit_date,risk_type,damage_severity,risk_probability,spread_speed,affected_area,heritage_impact,urgency_level,risk_percentage,risk_level,notes,recommendations,status,reviewed_by,reviewed_at) VALUES (1,1,2,'2026-07-15','تشققات',5,5,4,4,5,5,93,'مرتفع','أظهر التقييم وجود تشققات وتآكل واضح في الجدار الشمالي.','إغلاق الجزء المتضرر وبدء ترميم عاجل.','approved',1,NOW()),(NULL,2,2,'2026-07-12','رطوبة',4,4,4,3,4,4,77,'مرتفع','يوجد أثر رطوبة وتسرب للمياه في بعض أجزاء الأساسات.','تحسين تصريف المياه ومعالجة الأحجار المتأثرة.','approved',1,NOW()),(NULL,8,2,'2026-06-20','إهمال',2,2,1,1,2,1,30,'منخفض','حالة الموقع مستقرة مع احتياج إلى متابعة وقائية.','استمرار الصيانة الدورية كل ستة أشهر.','approved',1,NOW())");
  $conn->query("UPDATE ae_heritage_sites SET risk_percentage=93,risk_level='مرتفع' WHERE id=1"); $conn->query("UPDATE ae_heritage_sites SET risk_percentage=77,risk_level='مرتفع' WHERE id=2");
}
if($_SERVER['REQUEST_METHOD']==='POST'){
  try{
    $server=db(true); $server->query('CREATE DATABASE IF NOT EXISTS `'.DB_NAME.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    $conn=new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);$conn->set_charset('utf8mb4');
    $reset=($_POST['reset']??'')==='1';
    if($reset || !table_exists($conn,'ae_schema_meta')){
      run_sql_file($conn,__DIR__.'/database/schema.sql'); seed_data($conn); $mode='fresh';
    } else {
      if(!column_exists($conn,'ae_users','phone')) $conn->query('ALTER TABLE ae_users ADD COLUMN phone VARCHAR(30) NULL AFTER status');
      if(!column_exists($conn,'ae_users','preferred_language')) $conn->query("ALTER TABLE ae_users ADD COLUMN preferred_language ENUM('ar','en') NOT NULL DEFAULT 'ar' AFTER phone");
      if(!column_exists($conn,'ae_users','updated_at')) $conn->query('ALTER TABLE ae_users ADD COLUMN updated_at DATETIME NULL AFTER created_at');
      $conn->query("UPDATE ae_schema_meta SET schema_version='16',installed_at=CURRENT_TIMESTAMP WHERE id=1"); $mode='upgrade';
    }
    $done=true;
  }catch(Throwable $e){$error='تعذر إكمال التثبيت. تأكد من تشغيل MySQL وصحة إعدادات الاتصال ثم حاول مرة أخرى.';}
}
?>
<!doctype html><html lang="ar" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>تثبيت عين الأثر</title><link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;700;800;900&display=swap" rel="stylesheet"><link rel="stylesheet" href="assets/css/app.css?v=16.0"></head><body class="public-body"><main class="auth-wrap"><section class="auth-panel"><img class="install-logo" src="assets/images/logo-full.svg" alt="عين الأثر"><h1 style="color:var(--green);margin:18px 0 8px">تثبيت عين الأثر</h1><p class="muted">يستخدم النظام قاعدة <b>tabuk_heritage</b> وجداول مستقلة تبدأ بـ <b>ae_</b>.</p><?php if($error):?><div class="notice danger"><?= htmlspecialchars($error) ?></div><?php endif;?><?php if($done):?><div class="notice success"><?= $mode==='upgrade'?'تمت ترقية المشروع مع الحفاظ على البيانات الحالية.':'تم تثبيت المشروع وإنشاء البيانات التجريبية بنجاح.' ?></div><a class="btn btn-primary" href="index.php">فتح النظام</a><?php else:?><form method="post"><button class="btn btn-primary" type="submit">تثبيت أو ترقية المشروع بأمان</button><label style="display:flex;gap:8px;margin-top:18px;align-items:flex-start"><input type="checkbox" name="reset" value="1"><span class="mini">إعادة إنشاء جداول عين الأثر والبيانات التجريبية من الصفر. لا تستخدم هذا الخيار إذا كانت لديك بيانات تريد الاحتفاظ بها.</span></label></form><?php endif;?><p class="mini" style="margin-top:18px">الترقية الآمنة تضيف الأعمدة الجديدة دون حذف المواقع أو البلاغات أو التقييمات الحالية.</p></section></main></body></html>
