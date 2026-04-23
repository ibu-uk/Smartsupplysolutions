<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_login();
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/users.php');
    exit;
}

$id = (string)($_POST['id'] ?? '');
$makeAdmin = (string)($_POST['make_admin'] ?? '');

if ($id === '' || !ctype_digit($id)) {
    flash_set('error', 'معرف غير صحيح');
    header('Location: ' . BASE_URL . '/users.php');
    exit;
}

if (!in_array($makeAdmin, ['0', '1'], true)) {
    flash_set('error', 'قيمة غير صحيحة');
    header('Location: ' . BASE_URL . '/users.php');
    exit;
}

$current = current_user();
$targetId = (int)$id;

if ($current && (int)$current['id'] === $targetId && $makeAdmin === '0') {
    flash_set('error', 'لا يمكن إزالة صلاحية المدير من حسابك');
    header('Location: ' . BASE_URL . '/users.php');
    exit;
}

$stmt = db()->prepare('SELECT id, username FROM users WHERE id = ? LIMIT 1');
$stmt->execute([$targetId]);
$target = $stmt->fetch();

if (!$target) {
    flash_set('error', 'المستخدم غير موجود');
    header('Location: ' . BASE_URL . '/users.php');
    exit;
}

if (((string)$target['username']) === 'admin' && $makeAdmin === '0') {
    flash_set('error', 'لا يمكن إزالة صلاحية المدير من admin');
    header('Location: ' . BASE_URL . '/users.php');
    exit;
}

$upd = db()->prepare('UPDATE users SET is_admin = ? WHERE id = ?');
$upd->execute([(int)$makeAdmin, $targetId]);

flash_set('success', 'تم تحديث صلاحيات المستخدم');
header('Location: ' . BASE_URL . '/users.php');
exit;
