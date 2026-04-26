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
$follow_up_date = (string)($_POST['follow_up_date'] ?? '');
$area = trim((string)($_POST['area'] ?? ''));
$address = trim((string)($_POST['address'] ?? ''));
$clinic_name = trim((string)($_POST['clinic_name'] ?? ''));
$visit_number = trim((string)($_POST['visit_number'] ?? ''));
$contacts_name = $_POST['contacts_name'] ?? [];
$contacts_job_title = $_POST['contacts_job_title'] ?? [];
$contacts_mobile = $_POST['contacts_mobile'] ?? [];
$interest = trim((string)($_POST['interest'] ?? ''));
$visit_type = trim((string)($_POST['visit_type'] ?? ''));
$visit_result = trim((string)($_POST['visit_result'] ?? ''));
$execution_status = trim((string)($_POST['execution_status'] ?? ''));
$notes = trim((string)($_POST['notes'] ?? ''));

if (!is_array($contacts_name)) {
    $contacts_name = [];
}
if (!is_array($contacts_job_title)) {
    $contacts_job_title = [];
}
if (!is_array($contacts_mobile)) {
    $contacts_mobile = [];
}

$normalizedContacts = [];
$max = max(count($contacts_name), count($contacts_mobile), count($contacts_job_title));
for ($i = 0; $i < $max; $i++) {
    $n = trim((string)($contacts_name[$i] ?? ''));
    $t = trim((string)($contacts_job_title[$i] ?? ''));
    $m = trim((string)($contacts_mobile[$i] ?? ''));
    if ($n === '' && $m === '') {
        continue;
    }
    if ($n === '') {
        continue;
    }
    if ($t === '') {
        continue;
    }
    $normalizedContacts[] = ['name' => $n, 'job_title' => $t, 'mobile' => ($m !== '' ? $m : null)];
}

$primaryPersonName = (string)($normalizedContacts[0]['name'] ?? '');
$primaryJobTitle = (string)($normalizedContacts[0]['job_title'] ?? '');
$primaryMobile = $normalizedContacts[0]['mobile'] ?? null;

$errors = [];
if ($visit_date === '') $errors[] = 'visit_date';
if ($area === '') $errors[] = 'area';
if ($clinic_name === '') $errors[] = 'clinic_name';
if ($visit_number === '') $errors[] = 'visit_number';
if ($primaryPersonName === '') $errors[] = 'person_name';
if ($primaryJobTitle === '') $errors[] = 'job_title';
if ($interest === '') $errors[] = 'interest';
if ($visit_type === '') $errors[] = 'visit_type';
if ($visit_result === '') $errors[] = 'visit_result';
if ($execution_status === '') $errors[] = 'execution_status';

if ($errors) {
    http_response_code(400);
    echo 'Missing fields: ' . htmlspecialchars(implode(', ', $errors));
    exit;
}

$pdo = db();
$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare(
        "INSERT INTO daily_visits (user_id, visit_date, follow_up_date, follow_up_status, follow_up_done_at, follow_up_action_note, area, address, clinic_name, visit_number, person_name, job_title, mobile, interest, visit_type, visit_result, execution_status, notes)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );

    $hasFollowUp = ($follow_up_date !== '');
    $stmt->execute([
        (int)$user['id'],
        $visit_date,
        ($hasFollowUp ? $follow_up_date : null),
        ($hasFollowUp ? 'next' : null),
        null,
        null,
        $area,
        ($address !== '' ? $address : null),
        $clinic_name,
        $visit_number,
        $primaryPersonName,
        $primaryJobTitle,
        $primaryMobile,
        $interest,
        $visit_type,
        $visit_result,
        $execution_status,
        $notes,
    ]);

    $visitId = (int)$pdo->lastInsertId();
    if ($normalizedContacts) {
        $cStmt = $pdo->prepare('INSERT INTO daily_visit_contacts (daily_visit_id, person_name, job_title, mobile) VALUES (?, ?, ?, ?)');
        foreach ($normalizedContacts as $c) {
            $cStmt->execute([$visitId, $c['name'], $c['job_title'], $c['mobile']]);
        }
    }

    if ($hasFollowUp) {
        $rStmt = $pdo->prepare("INSERT INTO reminders (daily_visit_id, follow_up_date, status) VALUES (?, ?, 'next')");
        $rStmt->execute([$visitId, $follow_up_date]);
    }

    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo 'DB error';
    exit;
}

flash_set('success', 'تم الحفظ');
header('Location: ' . BASE_URL . '/reports.php');
exit;
