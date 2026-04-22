<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/daily_report.php');
    exit;
}

$user = current_user();

$visit_date = (string)($_POST['visit_date'] ?? '');
$area = trim((string)($_POST['area'] ?? ''));
$clinic_name = trim((string)($_POST['clinic_name'] ?? ''));
$visit_number = trim((string)($_POST['visit_number'] ?? ''));
$person_name = trim((string)($_POST['person_name'] ?? ''));
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
    'INSERT INTO daily_visits (user_id, visit_date, area, clinic_name, visit_number, person_name, job_title, mobile, interest, visit_type, visit_result, execution_status, notes)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
);

$stmt->execute([
    (int)$user['id'],
    $visit_date,
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
]);

header('Location: ' . BASE_URL . '/reports.php?saved=1');
exit;
