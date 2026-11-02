<?php
require_once __DIR__ . '/helpers.php';
$title = $title ?? t('app.name');
$bodyClass = $bodyClass ?? '';
$lang = current_lang();
$dir = is_rtl() ? 'rtl' : 'ltr';
$currentScript = basename($_SERVER['SCRIPT_NAME'] ?? '');
?>
<!doctype html>
<html lang="<?= h($lang) ?>" dir="<?= h($dir) ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#07563f">
  <title><?= h($title) ?> - <?= h(t('app.name')) ?></title>
  <base href="<?= h(app_base() . '/') ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Tajawal:wght@400;500;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
  <link rel="icon" href="assets/images/mark.svg" type="image/svg+xml">
  <link rel="stylesheet" href="assets/css/app.css?v=17.0">
</head>
<body id="pageTop" class="<?= h(trim($bodyClass . ' lang-' . $lang)) ?>">
<?php if (!is_logged_in()): ?>
<header class="public-header">
  <div class="public-header-inner">
    <a class="brand" href="index.php" aria-label="<?= h(t('app.name')) ?>">
      <img class="brand-logo" src="assets/images/logo-full.svg" alt="<?= h(t('app.name')) ?>">
    </a>

    <button class="public-menu-toggle" type="button" data-public-menu-toggle aria-label="<?= h(localized('فتح القائمة','Open menu')) ?>" aria-expanded="false">
      <span class="menu-glyph" aria-hidden="true">☰</span>
    </button>

    <nav class="public-nav" data-public-nav aria-label="<?= h(t('app.name')) ?>">
      <a href="index.php" class="<?= $currentScript==='index.php'?'active':'' ?>"><?= h(t('nav.home')) ?></a>
      <a href="about.php" class="<?= $currentScript==='about.php'?'active':'' ?>"><?= h(t('nav.about')) ?></a>
      <a class="language-link" href="<?= h(lang_url($lang==='ar'?'en':'ar')) ?>" data-language-switch>
        <i class="ti ti-language"></i><span><?= h(t('header.language')) ?></span>
      </a>
      <a class="register-link <?= $currentScript==='register.php'?'active':'' ?>" href="register.php">
        <i class="ti ti-user-plus"></i><span><?= h(t('nav.register')) ?></span>
      </a>
      <a class="login-link <?= $currentScript==='login.php'?'active':'' ?>" href="login.php">
        <i class="ti ti-login-2"></i><span><?= h(t('nav.login')) ?></span>
      </a>
    </nav>
  </div>
</header>
<main class="public-main">
<?php else: ?>
<div class="app-shell">
  <?php include __DIR__ . '/sidebar.php'; ?>
  <header class="topbar">
    <div class="top-title">
      <h2><?= h($title) ?></h2>
      <small><?= h(panel_label()) ?></small>
    </div>
    <div class="top-actions">
      <button class="icon-btn mobile-menu-btn" type="button" data-sidebar-toggle aria-label="Menu"><i class="ti ti-menu-2"></i></button>
      <div class="top-search-wrap"><i class="ti ti-search"></i><input class="form-control top-search" placeholder="<?= h(t('header.search')) ?>" data-global-search></div>
      <a class="icon-btn language-toggle" href="<?= h(lang_url($lang==='ar'?'en':'ar')) ?>" title="<?= h(t('header.language')) ?>" data-language-switch><i class="ti ti-language"></i><span><?= strtoupper($lang==='ar'?'EN':'AR') ?></span></a>
      <div class="user-menu-wrap">
        <button class="user-chip" type="button" data-user-menu-toggle aria-expanded="false">
          <span class="avatar-circle"><?= h(first_char(current_user()['name'] ?? 'U')) ?></span>
          <span class="user-chip-copy"><b><?= h(current_user()['name'] ?? '') ?></b><small><?= h(role_label()) ?></small></span>
          <i class="ti ti-chevron-down"></i>
        </button>
        <div class="user-dropdown" data-user-menu>
          <a href="profile.php"><i class="ti ti-user-circle"></i><?= h(t('nav.profile')) ?></a>
          <a href="about.php"><i class="ti ti-info-circle"></i><?= h(t('nav.about')) ?></a>
          <hr>
          <a class="danger-link" href="logout.php"><i class="ti ti-logout"></i><?= h(t('nav.logout')) ?></a>
        </div>
      </div>
    </div>
  </header>
  <main class="app-content">
<?php endif; ?>
<?php foreach (['success','warning','danger'] as $f): if ($m = flash($f)): ?>
  <div class="notice <?= h($f) ?>" data-auto-dismiss><i class="ti <?= $f==='success'?'ti-circle-check':($f==='warning'?'ti-alert-circle':'ti-circle-x') ?>"></i><span><?= h($m) ?></span><button type="button" aria-label="Close">×</button></div>
<?php endif; endforeach; ?>
