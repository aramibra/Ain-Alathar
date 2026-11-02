<?php
require_once __DIR__ . '/../config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

function h($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function first_char(string $v): string { return function_exists('mb_substr') ? mb_substr($v,0,1,'UTF-8') : substr($v,0,1); }
function app_base(): string {
    $dir = str_replace('\\','/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    if (preg_match('#/(admin|user|archaeologist)$#', $dir)) $dir = dirname($dir);
    return rtrim($dir === '/' ? '' : $dir, '/');
}
function url($path=''): string { return app_base() . '/' . ltrim($path, '/'); }
function current_user(): ?array { return $_SESSION['user'] ?? null; }
function user_role(): ?string { return $_SESSION['user']['role'] ?? null; }
function is_logged_in(): bool { return !empty($_SESSION['user']); }

function current_lang(): string {
    if (isset($_GET['lang']) && in_array($_GET['lang'], ['ar','en'], true)) {
        $_SESSION['lang'] = $_GET['lang'];
        if (is_logged_in()) {
            try { query('UPDATE ae_users SET preferred_language=? WHERE id=?', [$_GET['lang'], current_user()['id']]); } catch (Throwable $e) {}
            $_SESSION['user']['preferred_language'] = $_GET['lang'];
        }
    }
    if (!empty($_SESSION['lang']) && in_array($_SESSION['lang'], ['ar','en'], true)) return $_SESSION['lang'];
    if (!empty($_SESSION['user']['preferred_language']) && in_array($_SESSION['user']['preferred_language'], ['ar','en'], true)) return $_SESSION['user']['preferred_language'];
    return 'ar';
}
function is_rtl(): bool { return current_lang() === 'ar'; }
function translations(): array { static $all; if ($all === null) $all = require __DIR__ . '/i18n.php'; return $all; }
function t(string $key, array $vars=[]): string {
    $all = translations(); $lang = current_lang();
    $value = $all[$lang][$key] ?? $all['ar'][$key] ?? $key;
    foreach ($vars as $k=>$v) $value = str_replace(':' . $k, (string)$v, $value);
    return $value;
}
function lang_url(string $lang): string {
    $params = $_GET; $params['lang'] = $lang;
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: ($_SERVER['SCRIPT_NAME'] ?? 'index.php');
    return $path . '?' . http_build_query($params);
}
function localized($ar, $en): string { return current_lang()==='ar' ? $ar : $en; }
function role_label(?string $role=null): string { $role = $role ?? user_role(); return t('role.' . ($role ?: 'user')); }
function status_label(string $status): string { return t('status.' . $status); }
function priority_label(string $priority): string { return t('priority.' . $priority); }
function risk_label($value): string {
    $level = is_numeric($value) ? risk_level_from_percentage((float)$value) : (string)$value;
    return match($level){ 'مرتفع'=>t('risk.high'), 'متوسط'=>t('risk.medium'), default=>t('risk.low') };
}
function panel_label(): string { return t('header.panel.' . (user_role() ?: 'user')); }

function flash($key, $msg=null) {
    if ($msg !== null) { $_SESSION['flash'][$key] = $msg; return null; }
    $v = $_SESSION['flash'][$key] ?? null; unset($_SESSION['flash'][$key]); return $v;
}
function redirect($path): never { header('Location: ' . url($path)); exit; }
function require_login(): void { if (!is_logged_in()) { flash('warning', t('flash.login_required')); redirect('login.php'); } }
function require_role($roles): void {
    require_login(); $roles = (array)$roles;
    if (!in_array(user_role(), $roles, true)) { flash('danger', t('flash.forbidden')); redirect('dashboard.php'); }
}
function csrf_token(): string { if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(24)); return $_SESSION['csrf']; }
function verify_csrf(): void {
    if (!hash_equals((string)($_SESSION['csrf'] ?? ''), (string)($_POST['csrf'] ?? ''))) {
        flash('danger', t('flash.expired')); redirect($_SERVER['HTTP_REFERER'] ?? 'index.php');
    }
}

function query($sql, $params = [], $types = ''): mysqli_result|bool {
    $conn = db();
    if (!$params) return $conn->query($sql);
    $stmt = $conn->prepare($sql);
    if (!$types) foreach ($params as $p) $types .= is_int($p) ? 'i' : (is_float($p) ? 'd' : 's');
    $stmt->bind_param($types, ...$params); $stmt->execute();
    return $stmt->get_result() ?: true;
}
function fetch_all($sql, $params = [], $types = ''): array { $r = query($sql, $params, $types); return $r instanceof mysqli_result ? $r->fetch_all(MYSQLI_ASSOC) : []; }
function fetch_one($sql, $params = [], $types = ''): ?array { $a = fetch_all($sql, $params, $types); return $a[0] ?? null; }
function scalar($sql, $params = [], $types = '') { $row = fetch_one($sql, $params, $types); return $row ? array_values($row)[0] : null; }

function excerpt($text, $length=120): string {
    $text = trim((string)$text);
    if (function_exists('mb_strimwidth')) return mb_strimwidth($text, 0, $length, '...', 'UTF-8');
    return strlen($text) > $length ? substr($text, 0, $length) . '...' : $text;
}
function risk_level_from_percentage($p): string { $p=(float)$p; return $p>=70?'مرتفع':($p>=40?'متوسط':'منخفض'); }
function risk_class($levelOrPercent): string {
    if (is_numeric($levelOrPercent)) $levelOrPercent = risk_level_from_percentage((float)$levelOrPercent);
    return match($levelOrPercent) { 'مرتفع'=>'danger', 'متوسط'=>'warning', default=>'success' };
}
function risk_color($levelOrPercent): string { return match(risk_class($levelOrPercent)) { 'danger'=>'#d94b5f','warning'=>'#e6ad3c',default=>'#2ba879' }; }
function compute_assessment_percentage(array $d): int {
    $sum=0; foreach(['damage_severity','risk_probability','spread_speed','affected_area','heritage_impact','urgency_level'] as $k) $sum += max(1,min(5,(int)($d[$k]??1)));
    return (int)round(($sum/30)*100);
}
function upload_files($field, $dir): array {
    if (empty($_FILES[$field]['name'])) return [];
    $names = is_array($_FILES[$field]['name']) ? $_FILES[$field]['name'] : [$_FILES[$field]['name']];
    $tmp = is_array($_FILES[$field]['tmp_name']) ? $_FILES[$field]['tmp_name'] : [$_FILES[$field]['tmp_name']];
    $errors = is_array($_FILES[$field]['error']) ? $_FILES[$field]['error'] : [$_FILES[$field]['error']];
    $base = __DIR__ . '/../uploads/' . trim($dir, '/'); if (!is_dir($base)) mkdir($base, 0775, true);
    $paths=[];
    foreach ($names as $i=>$name) {
        if (($errors[$i]??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_OK) continue;
        $ext=strtolower(pathinfo($name,PATHINFO_EXTENSION)); if(!in_array($ext,['jpg','jpeg','png','webp'],true)) continue;
        $new=bin2hex(random_bytes(8)).'.'.$ext; $dest=$base.'/'.$new;
        if(move_uploaded_file($tmp[$i],$dest)) $paths[]='uploads/'.trim($dir,'/').'/'.$new;
    }
    return $paths;
}
function stats(): array {
    return [
        'sites'=>(int)scalar('SELECT COUNT(*) FROM ae_heritage_sites'),
        'high'=>(int)scalar('SELECT COUNT(*) FROM ae_heritage_sites WHERE risk_percentage>=70'),
        'medium'=>(int)scalar('SELECT COUNT(*) FROM ae_heritage_sites WHERE risk_percentage BETWEEN 40 AND 69'),
        'low'=>(int)scalar('SELECT COUNT(*) FROM ae_heritage_sites WHERE risk_percentage<40'),
        'new_reports'=>(int)scalar("SELECT COUNT(*) FROM ae_incident_reports WHERE status IN ('new','assigned','under_review')"),
        'pending_assessments'=>(int)scalar("SELECT COUNT(*) FROM ae_field_assessments WHERE status='submitted'"),
        'approved_assessments'=>(int)scalar("SELECT COUNT(*) FROM ae_field_assessments WHERE status='approved'"),
    ];
}
function site_list($where='', $params=[]): array { return fetch_all("SELECT * FROM ae_heritage_sites $where ORDER BY risk_percentage DESC,id DESC",$params); }
function site_map_data($where='', $params=[]): array {
    return array_map(fn($s)=>['id'=>(int)$s['id'],'name'=>$s['name'],'city'=>$s['city'],'lat'=>(float)$s['latitude'],'lng'=>(float)$s['longitude'],'risk'=>(int)$s['risk_percentage'],'level'=>$s['risk_level'],'type'=>$s['site_type']],site_list($where,$params));
}
function nav_items(): array {
    $profile=['profile.php',t('nav.profile'),'ti ti-user-circle'];
    $about=['about.php',t('nav.about'),'ti ti-info-circle'];
    if(user_role()==='admin') return [
        ['dashboard.php',t('nav.home'),'ti ti-home'],['admin/sites.php',t('nav.admin_sites'),'ti ti-map-pin'],['admin/reports.php',t('nav.admin_reports'),'ti ti-alert-triangle'],['admin/assessments.php',t('nav.admin_assessments'),'ti ti-clipboard-check'],['admin/users.php',t('nav.users'),'ti ti-users'],['admin/statistics.php',t('nav.statistics'),'ti ti-chart-bar'],['map.php',t('nav.map'),'ti ti-map-2'],$profile,$about
    ];
    if(user_role()==='archaeologist') return [
        ['dashboard.php',t('nav.home'),'ti ti-home'],['archaeologist/tasks.php',t('nav.tasks'),'ti ti-route'],['archaeologist/assessments.php',t('nav.my_assessments'),'ti ti-clipboard-list'],['sites.php',t('nav.sites'),'ti ti-map-pin'],['map.php',t('nav.map'),'ti ti-map-2'],$profile,$about
    ];
    return [
        ['dashboard.php',t('nav.home'),'ti ti-home'],['sites.php',t('nav.sites'),'ti ti-map-pin'],['user/report.php',t('nav.report_new'),'ti ti-message-report'],['map.php',t('nav.map'),'ti ti-map-2'],$profile,$about
    ];
}
function user_permissions(?string $role=null): array {
    $role=$role??user_role();
    return match($role){
        'admin'=>[localized('إدارة المواقع والمستخدمين','Manage sites and users'),localized('إسناد البلاغات ومراجعة التقييمات','Assign reports and review assessments'),localized('عرض التقارير والإحصائيات','View reports and analytics')],
        'archaeologist'=>[localized('استلام المهام الميدانية','Receive field tasks'),localized('إضافة التقييمات والصور','Submit assessments and photos'),localized('متابعة حالة التقييمات','Track assessment status')],
        default=>[localized('استعراض المواقع والتقارير','Browse sites and approved reports'),localized('إرسال البلاغات بالصور','Submit reports with photos'),localized('متابعة حالة البلاغات','Track submitted reports')]
    };
}
