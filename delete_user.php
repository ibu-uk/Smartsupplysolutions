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
if ($id === '' || !ctype_digit($id)) {
    flash_set('error', 'معرف غير صحيح');
    header('Location: ' . BASE_URL . '/users.php');
    exit;
}

$stmt = db()->prepare('SELECT username FROM users WHERE id = ? LIMIT 1');
$stmt->execute([(int)$id]);
$row = $stmt->fetch();

if (!$row) {
    flash_set('error', 'المستخدم غير موجود');
    header('Location: ' . BASE_URL . '/users.php');
    exit;
}

if (((string)$row['username']) === 'admin') {
    flash_set('error', 'لا يمكن حذف admin');
    header('Location: ' . BASE_URL . '/users.php');
    exit;
}

$stmt = db()->prepare('DELETE FROM users WHERE id = ?');
$stmt->execute([(int)$id]);

flash_set('success', 'تم الحذف');
header('Location: ' . BASE_URL . '/users.php');
exit;
