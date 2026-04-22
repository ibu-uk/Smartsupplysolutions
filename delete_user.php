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
    header('Location: ' . BASE_URL . '/users.php?error=' . urlencode('معرف غير صحيح'));
    exit;
}

$stmt = db()->prepare('SELECT username FROM users WHERE id = ? LIMIT 1');
$stmt->execute([(int)$id]);
$row = $stmt->fetch();

if (!$row) {
    header('Location: ' . BASE_URL . '/users.php?error=' . urlencode('المستخدم غير موجود'));
    exit;
}

if (((string)$row['username']) === 'admin') {
    header('Location: ' . BASE_URL . '/users.php?error=' . urlencode('لا يمكن حذف admin'));
    exit;
}

$stmt = db()->prepare('DELETE FROM users WHERE id = ?');
$stmt->execute([(int)$id]);

header('Location: ' . BASE_URL . '/users.php?created=1');
exit;
