<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_login();

$mode = (string)($_GET['mode'] ?? '');
$id = (string)($_GET['id'] ?? '');
$autoprint = (string)($_GET['autoprint'] ?? '') === '1';

$from = (string)($_GET['from'] ?? '');
$to = (string)($_GET['to'] ?? '');
$name = trim((string)($_GET['name'] ?? ''));
$area = trim((string)($_GET['area'] ?? ''));
$user_id = trim((string)($_GET['user_id'] ?? ''));

$rows = [];
$title = 'طباعة';

if ($mode === 'single' || $id !== '') {
    if ($id === '' || !ctype_digit($id)) {
        http_response_code(400);
        echo 'Invalid id';
        exit;
    }

    $stmt = db()->prepare('SELECT dv.*, u.username FROM daily_visits dv JOIN users u ON u.id = dv.user_id WHERE dv.id = ? LIMIT 1');
    $stmt->execute([(int)$id]);
    $row = $stmt->fetch();
    if (!$row) {
        http_response_code(404);
        echo 'Not found';
        exit;
    }

    $rows = [$row];
    $title = 'طباعة زيارة رقم ' . (string)$row['id'];
} else {
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
    if ($user_id !== '') {
        $where[] = 'dv.user_id = ?';
        $params[] = (int)$user_id;
    }

    $whereSql = $where ? (' WHERE ' . implode(' AND ', $where)) : '';
    $stmt = db()->prepare('SELECT dv.*, u.username FROM daily_visits dv JOIN users u ON u.id = dv.user_id' . $whereSql . ' ORDER BY dv.visit_date DESC, dv.id DESC');
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    $title = 'طباعة التقرير';
}

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

?><!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars(APP_NAME) ?> - <?= htmlspecialchars($title) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="<?= htmlspecialchars(BASE_URL) ?>/assets/app.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: #fff !important; }
            .app-card { box-shadow: none !important; }
            .container { max-width: none !important; }
            .table-responsive { overflow: visible !important; }
            table { font-size: 11px; table-layout: fixed; width: 100%; }
            th, td { padding: .35rem .4rem !important; }
            th { white-space: nowrap; }
            .col-notes { width: 16%; }
            .col-username { width: 7%; }
            .col-username, .col-username * { white-space: nowrap; }
            .col-notes { white-space: normal; }
        }

        @page {
            size: A4 landscape;
            margin: 10mm;
        }
    </style>
</head>
<body class="app-bg">
<div class="container py-3" style="max-width: 1100px;">
    <div class="d-flex justify-content-between align-items-center mb-3 no-print">
        <div class="h6 mb-0"><?= htmlspecialchars(APP_NAME) ?></div>
        <div class="d-flex gap-2">
            <a class="btn btn-app-outline" href="<?= htmlspecialchars(BASE_URL) ?>/reports.php">رجوع</a>
            <button class="btn btn-app" onclick="window.print()" type="button">طباعة</button>
        </div>
    </div>

    <div class="text-center mb-3">
        <div class="fw-semibold" style="font-size: 18px;">SmartSupplySolutions</div>
        <div class="text-muted" style="font-size: 14px;">Daily Report - تقرير الزيارات اليومي</div>
        <div class="text-muted small"><?= htmlspecialchars($title) ?></div>
    </div>

    <div class="card shadow-sm app-card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h1 class="h6 mb-0"><?= htmlspecialchars($title) ?></h1>
                <div class="text-muted small">العدد: <?= (int)count($rows) ?></div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>تاريخ الزيارة</th>
                            <th>تاريخ متابعة</th>
                            <th>المنطقة</th>
                            <th>اسم العيادة</th>
                            <th>رقم الزيارة</th>
                            <th>اسم الشخص</th>
                            <th>المسمى الوظيفي</th>
                            <th>رقم الموبايل</th>
                            <th>مستوى الاهتمام</th>
                            <th>نوع الزيارة</th>
                            <th>نتيجة الزيارة</th>
                            <th>حالة التنفيذ</th>
                            <th class="col-notes">ملاحظات</th>
                            <th class="col-username">المستخدم</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $r): ?>
                            <tr>
                                <td><?= (int)$r['id'] ?></td>
                                <td class="text-nowrap"><?= htmlspecialchars((string)$r['visit_date']) ?></td>
                                <td class="text-nowrap"><?= htmlspecialchars((string)($r['follow_up_date'] ?? '')) ?></td>
                                <td><?= htmlspecialchars((string)$r['area']) ?></td>
                                <td><?= htmlspecialchars((string)$r['clinic_name']) ?></td>
                                <td class="text-nowrap"><?= htmlspecialchars((string)$r['visit_number']) ?></td>
                                <td>
                                    <?php $cs = $contactsByVisitId[(int)$r['id']] ?? []; ?>
                                    <?php if ($cs): ?>
                                        <?php foreach ($cs as $idx => $c): ?>
                                            <div>
                                                <?= htmlspecialchars((string)($c['person_name'] ?? '')) ?>
                                                <?php if ((string)($c['job_title'] ?? '') !== ''): ?>
                                                    <span class="text-muted">(<?= htmlspecialchars((string)$c['job_title']) ?>)</span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <?= htmlspecialchars((string)$r['person_name']) ?>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars((string)$r['job_title']) ?></td>
                                <td>
                                    <?php if ($cs): ?>
                                        <?php foreach ($cs as $idx => $c): ?>
                                            <div class="text-nowrap"><?= htmlspecialchars((string)($c['mobile'] ?? '')) ?></div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="text-nowrap"><?= htmlspecialchars((string)$r['mobile']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars((string)$r['interest']) ?></td>
                                <td><?= htmlspecialchars((string)$r['visit_type']) ?></td>
                                <td><?= htmlspecialchars((string)$r['visit_result']) ?></td>
                                <td><?= htmlspecialchars((string)$r['execution_status']) ?></td>
                                <td class="col-notes" style="white-space: normal;">
                                    <?php if ((string)($r['follow_up_action_note'] ?? '') !== ''): ?>
                                        <div><?= htmlspecialchars((string)$r['follow_up_action_note']) ?></div>
                                    <?php endif; ?>
                                    <?php if ((string)($r['notes'] ?? '') !== ''): ?>
                                        <div><?= htmlspecialchars((string)$r['notes']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="col-username"><?= htmlspecialchars((string)$r['username']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$rows): ?>
                            <tr>
                                <td colspan="15" class="text-muted">لا يوجد بيانات</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php if ($autoprint): ?>
<script>
window.addEventListener('load', () => {
    setTimeout(() => {
        window.print();
    }, 150);
});
window.addEventListener('afterprint', () => {
    setTimeout(() => {
        window.close();
    }, 150);
});
</script>
<?php endif; ?>
</body>
</html>
