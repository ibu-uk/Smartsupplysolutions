<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_login();

$user = current_user();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/reminders.php');
    exit;
}

$id = (int)($_POST['id'] ?? 0);
$action = (string)($_POST['action'] ?? '');
$followUpDate = trim((string)($_POST['follow_up_date'] ?? ''));
$note = trim((string)($_POST['note'] ?? ''));
$filter = (string)($_POST['filter'] ?? 'due');
$page = (int)($_POST['page'] ?? 1);

if (!in_array($filter, ['due', 'upcoming', 'all'], true)) {
    $filter = 'due';
}
if ($page < 1) {
    $page = 1;
}

if ($id <= 0 || !in_array($action, ['next', 'done', 'cancel'], true)) {
    header('Location: ' . BASE_URL . '/reminders.php');
    exit;
}

$redirectUrl = BASE_URL . '/reminders.php?' . http_build_query(['filter' => $filter, 'page' => $page]);

// Ensure record exists and user has access
$params = [$id];
$sql = 'SELECT id, user_id FROM daily_visits WHERE id = ?';
if (!is_admin($user)) {
    $sql .= ' AND user_id = ?';
    $params[] = (int)$user['id'];
}

$stmt = db()->prepare($sql);
$stmt->execute($params);
$row = $stmt->fetch();

if (!$row) {
    header('Location: ' . BASE_URL . '/reminders.php');
    exit;
}

if ($action === 'next') {
    if ($followUpDate === '') {
        header('Location: ' . $redirectUrl);
        exit;
    }

    $upd = db()->prepare(
        "UPDATE daily_visits
         SET follow_up_date = ?, follow_up_status = 'next', follow_up_done_at = NULL, follow_up_action_note = ?
         WHERE id = ?"
    );
    $upd->execute([
        $followUpDate,
        ($note !== '' ? $note : null),
        $id,
    ]);
} elseif ($action === 'done') {
    $upd = db()->prepare(
        "UPDATE daily_visits
         SET follow_up_status = 'done', follow_up_done_at = NOW(), follow_up_action_note = ?
         WHERE id = ?"
    );
    $upd->execute([
        ($note !== '' ? $note : null),
        $id,
    ]);
} elseif ($action === 'cancel') {
    $upd = db()->prepare(
        "UPDATE daily_visits
         SET follow_up_status = 'cancelled', follow_up_done_at = NULL, follow_up_action_note = ?
         WHERE id = ?"
    );
    $upd->execute([
        ($note !== '' ? $note : null),
        $id,
    ]);
}

header('Location: ' . $redirectUrl);
exit;
