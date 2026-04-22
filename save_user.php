<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_login();
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/users.php');
    exit;
}

$username = trim((string)($_POST['username'] ?? ''));
$password = (string)($_POST['password'] ?? '');

if ($username === '' || $password === '') {
    flash_set('error', 'الرجاء إدخال اسم المستخدم وكلمة المرور');
    header('Location: ' . BASE_URL . '/users.php');
    exit;
}

if (mb_strlen($username) > 190) {
    flash_set('error', 'اسم المستخدم طويل');
    header('Location: ' . BASE_URL . '/users.php');
    exit;
}

$hash = password_hash($password, PASSWORD_BCRYPT);

try {
    $stmt = db()->prepare('INSERT INTO users (username, password_hash) VALUES (?, ?)');
    $stmt->execute([$username, $hash]);
} catch (Throwable $e) {
    flash_set('error', 'اسم المستخدم موجود مسبقاً');
    header('Location: ' . BASE_URL . '/users.php');
    exit;
}

flash_set('success', 'تم إنشاء المستخدم');
header('Location: ' . BASE_URL . '/users.php');
exit;
