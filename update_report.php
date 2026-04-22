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

$visit_date = (string)($_POST['visit_date'] ?? '');
$follow_up_date = (string)($_POST['follow_up_date'] ?? '');
$area = (string)($_POST['area'] ?? '');
$clinic_name = (string)($_POST['clinic_name'] ?? '');
$visit_number = (string)($_POST['visit_number'] ?? '');
$person_name = (string)($_POST['person_name'] ?? '');
$job_title = trim((string)($_POST['job_title'] ?? ''));
$mobile = trim((string)($_POST['mobile'] ?? ''));
$interest = trim((string)($_POST['interest'] ?? ''));
$visit_type = trim((string)($_POST['visit_type'] ?? ''));
$visit_result = trim((string)($_POST['visit_result'] ?? ''));
$execution_status = trim((string)($_POST['execution_status'] ?? ''));
$notes = trim((string)($_POST['notes'] ?? ''));

$errors = [];
if ($visit_date === '') $errors[] = 'visit_date';
if ($area === '') $errors[] = 'area';
if ($clinic_name === '') $errors[] = 'clinic_name';
if ($visit_number === '') $errors[] = 'visit_number';
if ($person_name === '') $errors[] = 'person_name';
if ($job_title === '') $errors[] = 'job_title';
if ($interest === '') $errors[] = 'interest';
if ($visit_type === '') $errors[] = 'visit_type';
if ($visit_result === '') $errors[] = 'visit_result';
if ($execution_status === '') $errors[] = 'execution_status';

if ($errors) {
    http_response_code(400);
    echo 'Missing fields: ' . htmlspecialchars(implode(', ', $errors));
    exit;
}

$stmt = db()->prepare(
    "UPDATE daily_visits
     SET visit_date = ?,
         follow_up_date = ?,
         follow_up_status = CASE
             WHEN ? IS NULL THEN NULL
             WHEN (follow_up_status IS NULL OR follow_up_status = '') THEN 'next'
             ELSE follow_up_status
         END,
         follow_up_done_at = CASE WHEN ? IS NULL THEN NULL ELSE follow_up_done_at END,
         follow_up_action_note = CASE WHEN ? IS NULL THEN NULL ELSE follow_up_action_note END,
         area = ?, clinic_name = ?, visit_number = ?, person_name = ?, job_title = ?, mobile = ?, interest = ?, visit_type = ?, visit_result = ?, execution_status = ?, notes = ?
     WHERE id = ?"
);

$fud = ($follow_up_date !== '' ? $follow_up_date : null);
$stmt->execute([
    $visit_date,
    $fud,
    $fud,
    $fud,
    $fud,
    $area,
    $clinic_name,
    $visit_number,
    $person_name,
    $job_title,
    $mobile,
    $interest,
    $visit_type,
    $visit_result,
    $execution_status,
    $notes,
    (int)$id,
]);

header('Location: ' . BASE_URL . '/reports.php');
exit;
