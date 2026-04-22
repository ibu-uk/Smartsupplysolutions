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
$password = (string)($_POST['password'] ?? '');

if ($id === '' || !ctype_digit($id)) {
    flash_set('error', 'معرف غير صحيح');
    header('Location: ' . BASE_URL . '/users.php');
    exit;
}

if ($password === '' || mb_strlen($password) < 4) {
    flash_set('error', 'كلمة المرور قصيرة');
    header('Location: ' . BASE_URL . '/users.php');
    exit;
}

$hash = password_hash($password, PASSWORD_BCRYPT);

$stmt = db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
$stmt->execute([$hash, (int)$id]);

flash_set('success', 'تم حفظ كلمة المرور');
header('Location: ' . BASE_URL . '/users.php');
exit;
