<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_login();
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/reports.php');
    exit;
}

$id = (string)($_POST['id'] ?? '');
if ($id === '' || !ctype_digit($id)) {
    http_response_code(400);
    echo 'Invalid id';
    exit;
}

$stmt = db()->prepare('DELETE FROM daily_visits WHERE id = ?');
$stmt->execute([(int)$id]);

header('Location: ' . BASE_URL . '/reports.php');
exit;
