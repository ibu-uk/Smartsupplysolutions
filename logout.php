<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';

$u = current_user();
$displayName = (string)($u['username'] ?? '');

logout_user();
if ($displayName !== '') {
    flash_set('logged_out', 'إلى اللقاء ' . $displayName . '، تم تسجيل الخروج بنجاح.');
} else {
    flash_set('logged_out', 'تم تسجيل الخروج بنجاح.');
}
header('Location: ' . BASE_URL . '/login.php');
exit;
