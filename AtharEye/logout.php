<?php
require_once __DIR__.'/includes/helpers.php';
unset($_SESSION['user']); session_regenerate_id(true); flash('success',localized('تم تسجيل الخروج بنجاح.','You have signed out successfully.')); redirect('index.php');
