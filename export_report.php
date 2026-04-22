<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_login();

$id = (string)($_GET['id'] ?? '');
if ($id === '' || !ctype_digit($id)) {
    http_response_code(400);
    echo 'Invalid id';
    exit;
}

$stmt = db()->prepare('SELECT dv.*, u.username FROM daily_visits dv JOIN users u ON u.id = dv.user_id WHERE dv.id = ? LIMIT 1');
$stmt->execute([(int)$id]);
$r = $stmt->fetch();
if (!$r) {
    http_response_code(404);
    echo 'Not found';
    exit;
}

$cStmt = db()->prepare('SELECT person_name, job_title, mobile FROM daily_visit_contacts WHERE daily_visit_id = ? ORDER BY id ASC');
$cStmt->execute([(int)$id]);
$contacts = $cStmt->fetchAll();

$filename = 'report-' . (int)$r['id'] . '.csv';

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

echo "\xEF\xBB\xBF"; // UTF-8 BOM for Excel

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

if ($contacts) {
    foreach ($contacts as $c) {
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

fclose($out);
exit;
