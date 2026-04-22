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
    header('Location: ' . BASE_URL . '/users.php?error=' . urlencode('معرف غير صحيح'));
    exit;
}

if ($password === '' || mb_strlen($password) < 4) {
    header('Location: ' . BASE_URL . '/users.php?error=' . urlencode('كلمة المرور قصيرة'));
    exit;
}

$hash = password_hash($password, PASSWORD_BCRYPT);

$stmt = db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
$stmt->execute([$hash, (int)$id]);

header('Location: ' . BASE_URL . '/users.php?created=1');
exit;
