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
    header('Location: ' . BASE_URL . '/users.php?error=' . urlencode('الرجاء إدخال اسم المستخدم وكلمة المرور'));
    exit;
}

if (mb_strlen($username) > 190) {
    header('Location: ' . BASE_URL . '/users.php?error=' . urlencode('اسم المستخدم طويل'));
    exit;
}

$hash = password_hash($password, PASSWORD_BCRYPT);

try {
    $stmt = db()->prepare('INSERT INTO users (username, password_hash) VALUES (?, ?)');
    $stmt->execute([$username, $hash]);
} catch (Throwable $e) {
    header('Location: ' . BASE_URL . '/users.php?error=' . urlencode('اسم المستخدم موجود مسبقاً'));
    exit;
}

header('Location: ' . BASE_URL . '/users.php?created=1');
exit;
