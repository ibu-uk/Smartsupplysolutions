<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';

$user = current_user();
$username = (string)($user['username'] ?? '');

logout_user();

setcookie('reminders_popup_shown', '0', time() - 3600, '/');

if ($username !== '') {
    flash_set('logged_out', 'إلى اللقاء ' . $username . '، تم تسجيل الخروج بنجاح.');
} else {
    flash_set('logged_out', 'تم تسجيل الخروج بنجاح.');
}
header('Location: ' . BASE_URL . '/login.php');
exit;
