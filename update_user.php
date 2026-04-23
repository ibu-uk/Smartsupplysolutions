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
$mode = (string)($_POST['mode'] ?? 'password');
$username = trim((string)($_POST['username'] ?? ''));
$password = (string)($_POST['password'] ?? '');

if ($id === '' || !ctype_digit($id)) {
    flash_set('error', 'معرف غير صحيح');
    header('Location: ' . BASE_URL . '/users.php');
    exit;
}

$stmt = db()->prepare('SELECT id, username FROM users WHERE id = ? LIMIT 1');
$stmt->execute([(int)$id]);
$existing = $stmt->fetch();
if (!$existing) {
    flash_set('error', 'المستخدم غير موجود');
    header('Location: ' . BASE_URL . '/users.php');
    exit;
}

if (!in_array($mode, ['password', 'username'], true)) {
    $mode = 'password';
}

if ($mode === 'password') {
    if ($password === '' || mb_strlen($password) < 4) {
        flash_set('error', 'كلمة المرور قصيرة');
        header('Location: ' . BASE_URL . '/users.php');
        exit;
    }

    $hash = password_hash($password, PASSWORD_BCRYPT);
    $upd = db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
    $upd->execute([$hash, (int)$id]);
    flash_set('success', 'تم حفظ كلمة المرور');
} else {
    if ($username === '' || mb_strlen($username) > 190) {
        flash_set('error', 'اسم المستخدم غير صحيح');
        header('Location: ' . BASE_URL . '/users.php');
        exit;
    }
    if (((string)$existing['username']) === 'admin') {
        flash_set('error', 'لا يمكن تعديل admin');
        header('Location: ' . BASE_URL . '/users.php');
        exit;
    }

    try {
        $upd = db()->prepare('UPDATE users SET username = ? WHERE id = ?');
        $upd->execute([$username, (int)$id]);
    } catch (Throwable $e) {
        flash_set('error', 'اسم المستخدم موجود مسبقاً');
        header('Location: ' . BASE_URL . '/users.php');
        exit;
    }

    flash_set('success', 'تم تعديل اسم المستخدم');
}

header('Location: ' . BASE_URL . '/users.php');
exit;
