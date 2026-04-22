<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_login();

$user = current_user();

$from = (string)($_GET['from'] ?? '');
$to = (string)($_GET['to'] ?? '');
$name = trim((string)($_GET['name'] ?? ''));
$area = trim((string)($_GET['area'] ?? ''));
$user_id = trim((string)($_GET['user_id'] ?? ''));
$weekday = trim((string)($_GET['weekday'] ?? ''));

$where = [];
$params = [];

if ($from !== '') {
    $where[] = 'dv.visit_date >= ?';
    $params[] = $from;
}
if ($to !== '') {
    $where[] = 'dv.visit_date <= ?';
    $params[] = $to;
}

if ($name !== '') {
    $where[] = '(dv.clinic_name LIKE ? OR dv.person_name LIKE ?)';
    $like = '%' . $name . '%';
    $params[] = $like;
    $params[] = $like;
}

if ($area !== '') {
    $where[] = 'dv.area = ?';
    $params[] = $area;
}

if ($user_id !== '' && ctype_digit($user_id)) {
    $where[] = 'dv.user_id = ?';
    $params[] = (int)$user_id;
}

if ($weekday !== '' && ctype_digit($weekday)) {
    $where[] = 'DAYOFWEEK(dv.visit_date) = ?';
    $params[] = (int)$weekday;
}

if (!is_admin($user)) {
    $where[] = 'dv.user_id = ?';
    $params[] = (int)$user['id'];
}

$whereSql = $where ? (' WHERE ' . implode(' AND ', $where)) : '';

$stmt = db()->prepare('SELECT dv.*, u.username FROM daily_visits dv JOIN users u ON u.id = dv.user_id' . $whereSql . ' ORDER BY dv.visit_date DESC, dv.id DESC');
$stmt->execute($params);
$rows = $stmt->fetchAll();

$contactsByVisitId = [];
if ($rows) {
    $ids = array_map(static fn($r) => (int)$r['id'], $rows);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $cStmt = db()->prepare('SELECT daily_visit_id, person_name, job_title, mobile FROM daily_visit_contacts WHERE daily_visit_id IN (' . $placeholders . ') ORDER BY id ASC');
    $cStmt->execute($ids);
    $cRows = $cStmt->fetchAll();
    foreach ($cRows as $c) {
        $vid = (int)$c['daily_visit_id'];
        if (!isset($contactsByVisitId[$vid])) {
            $contactsByVisitId[$vid] = [];
        }
        $contactsByVisitId[$vid][] = $c;
    }
}

$filename = 'reports-' . date('Y-m-d') . '.csv';

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

echo "\xEF\xBB\xBF";

$out = fopen('php://output', 'w');

$headers = [
    'تاريخ الزيارة',
    'تاريخ متابعة',
    'المنطقة',
    'العنوان',
    'اسم العيادة',
    'رقم الزيارة',
    'اسم الشخص',
    'المسمى الوظيفي',
    'رقم الموبايل',
    'مستوى الاهتمام',
    'نوع الزيارة',
    'نتيجة الزيارة',
    'حالة التنفيذ',
    'ملاحظات',
    'المستخدم',
];

fputcsv($out, $headers);

foreach ($rows as $r) {
    $baseRow = [
        (string)($r['visit_date'] ?? ''),
        (string)($r['follow_up_date'] ?? ''),
        (string)($r['area'] ?? ''),
        (string)($r['address'] ?? ''),
        (string)($r['clinic_name'] ?? ''),
        (string)($r['visit_number'] ?? ''),
    ];

    $tailRow = [
        (string)($r['interest'] ?? ''),
        (string)($r['visit_type'] ?? ''),
        (string)($r['visit_result'] ?? ''),
        (string)($r['execution_status'] ?? ''),
        (string)($r['notes'] ?? ''),
        (string)($r['username'] ?? ''),
    ];

    $cs = $contactsByVisitId[(int)$r['id']] ?? [];
    if ($cs) {
        foreach ($cs as $c) {
            $row = array_merge(
                $baseRow,
                [
                    (string)($c['person_name'] ?? ''),
                    (string)($c['job_title'] ?? ''),
                    (string)($c['mobile'] ?? ''),
                ],
                $tailRow
            );
            fputcsv($out, $row);
        }
    } else {
        $row = array_merge(
            $baseRow,
            [
                (string)($r['person_name'] ?? ''),
                (string)($r['job_title'] ?? ''),
                (string)($r['mobile'] ?? ''),
            ],
            $tailRow
        );
        fputcsv($out, $row);
    }
}

fclose($out);
exit;
