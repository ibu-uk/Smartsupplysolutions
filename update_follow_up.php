<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_login();

$user = current_user();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/reminders.php');
    exit;
}

$reminderId = (int)($_POST['reminder_id'] ?? 0);
$visitId = (int)($_POST['daily_visit_id'] ?? 0);
$action = (string)($_POST['action'] ?? '');
$followUpDate = trim((string)($_POST['follow_up_date'] ?? ''));
$note = trim((string)($_POST['note'] ?? ''));
$filter = (string)($_POST['filter'] ?? 'due');
$status = (string)($_POST['status'] ?? 'active');
$name = trim((string)($_POST['name'] ?? ''));
$mobile = trim((string)($_POST['mobile'] ?? ''));
$from = (string)($_POST['from'] ?? '');
$to = (string)($_POST['to'] ?? '');
$page = (int)($_POST['page'] ?? 1);

if (!in_array($filter, ['due', 'upcoming', 'all'], true)) {
    $filter = 'due';
}
if (!in_array($status, ['active', 'done', 'cancelled', 'closed'], true)) {
    $status = 'active';
}
if ($page < 1) {
    $page = 1;
}

if (($reminderId <= 0 && $visitId <= 0) || !in_array($action, ['next', 'done', 'cancel'], true)) {
    header('Location: ' . BASE_URL . '/reminders.php');
    exit;
}

$redirectUrl = BASE_URL . '/reminders.php?' . http_build_query([
    'filter' => $filter,
    'status' => $status,
    'name' => $name,
    'mobile' => $mobile,
    'from' => $from,
    'to' => $to,
    'page' => $page,
]);

if ($reminderId > 0) {
    // Ensure reminder exists and user has access via linked report
    $params = [$reminderId];
    $sql = 'SELECT r.id, r.daily_visit_id, dv.user_id FROM reminders r JOIN daily_visits dv ON dv.id = r.daily_visit_id WHERE r.id = ?';
    if (!is_admin($user)) {
        $sql .= ' AND dv.user_id = ?';
        $params[] = (int)$user['id'];
    }
    $sql .= ' LIMIT 1';
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
        $upd = db()->prepare("UPDATE reminders SET follow_up_date = ?, status = 'next', done_at = NULL, action_note = ? WHERE id = ?");
        $upd->execute([$followUpDate, ($note !== '' ? $note : null), $reminderId]);
    } elseif ($action === 'done') {
        $upd = db()->prepare("UPDATE reminders SET status = 'done', done_at = NOW(), action_note = ? WHERE id = ?");
        $upd->execute([($note !== '' ? $note : null), $reminderId]);
    } elseif ($action === 'cancel') {
        $upd = db()->prepare("UPDATE reminders SET status = 'cancelled', done_at = NULL, action_note = ? WHERE id = ?");
        $upd->execute([($note !== '' ? $note : null), $reminderId]);
    }
} else {
    // Legacy: update follow-up fields in daily_visits
    $params = [$visitId];
    $sql = 'SELECT id, user_id FROM daily_visits WHERE id = ?';
    if (!is_admin($user)) {
        $sql .= ' AND user_id = ?';
        $params[] = (int)$user['id'];
    }
    $sql .= ' LIMIT 1';
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
        $upd->execute([$followUpDate, ($note !== '' ? $note : null), $visitId]);
    } elseif ($action === 'done') {
        $upd = db()->prepare(
            "UPDATE daily_visits
             SET follow_up_status = 'done', follow_up_done_at = NOW(), follow_up_action_note = ?
             WHERE id = ?"
        );
        $upd->execute([($note !== '' ? $note : null), $visitId]);
    } elseif ($action === 'cancel') {
        $upd = db()->prepare(
            "UPDATE daily_visits
             SET follow_up_status = 'cancelled', follow_up_done_at = NULL, follow_up_action_note = ?
             WHERE id = ?"
        );
        $upd->execute([($note !== '' ? $note : null), $visitId]);
    }
}

header('Location: ' . $redirectUrl);
exit;
