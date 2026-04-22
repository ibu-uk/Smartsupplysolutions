<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';

logout_user();
flash_set('logged_out', 'تم تسجيل الخروج بنجاح.');
header('Location: ' . BASE_URL . '/login.php');
exit;
