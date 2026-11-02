<?php $path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'); ?>
<div class="sidebar-backdrop" data-sidebar-backdrop></div>
<aside class="sidebar">
  <a class="side-logo" href="dashboard.php">
    <img class="side-mark" src="assets/images/mark.svg" alt="<?= h(t('app.name')) ?>">
    <div><div class="side-title"><?= h(t('app.name')) ?></div><div class="brand-sub"><?= h(t('app.name_en')) ?></div></div>
  </a>
  <div class="side-card">
    <span class="side-role-icon"><i class="ti <?= user_role()==='admin'?'ti-shield-check':(user_role()==='archaeologist'?'ti-clipboard-search':'ti-user') ?>"></i></span>
    <div><strong><?= h(role_label()) ?></strong><br><small><?= h(t('app.tagline')) ?></small></div>
  </div>
  <div class="side-label"><?= h(localized('القائمة الرئيسية','Main Menu')) ?></div>
  <nav class="side-menu">
    <?php foreach (nav_items() as $item): [$href,$label,$icon]=$item; $active=str_ends_with($path,trim($href,'/')) || (basename($href)==='dashboard.php' && basename($_SERVER['SCRIPT_NAME'])==='dashboard.php'); ?>
      <a class="<?= $active?'active':'' ?>" href="<?= h($href) ?>"><i class="<?= h($icon) ?>"></i><span><?= h($label) ?></span></a>
    <?php endforeach; ?>
  </nav>
  <div class="side-footer"><i class="ti ti-shield-check"></i><span><?= h(localized('حماية المواقع التراثية تبدأ بالمعلومة الدقيقة.','Heritage protection begins with accurate information.')) ?></span></div>
</aside>
