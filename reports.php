<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_login();

require_once __DIR__ . '/dropdowns.php';

$user = current_user();

$saved = isset($_GET['saved']);
$from = (string)($_GET['from'] ?? '');
$to = (string)($_GET['to'] ?? '');

$name = trim((string)($_GET['name'] ?? ''));
$area = trim((string)($_GET['area'] ?? ''));
$user_id = trim((string)($_GET['user_id'] ?? ''));

$where = [];
$params = [];

if ($from !== '') {
    $where[] = 'visit_date >= ?';
    $params[] = $from;
}
if ($to !== '') {
    $where[] = 'visit_date <= ?';
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

$usersStmt = db()->query('SELECT id, username FROM users ORDER BY username ASC');
$users = $usersStmt->fetchAll();

$countStmt = db()->prepare('SELECT COUNT(*) AS c FROM daily_visits dv' . $whereSql);
$countStmt->execute($params);
$totalCount = (int)($countStmt->fetch()['c'] ?? 0);

$dailyStmt = db()->prepare('SELECT dv.visit_date, COUNT(*) AS c FROM daily_visits dv' . $whereSql . ' GROUP BY dv.visit_date ORDER BY dv.visit_date DESC');
$dailyStmt->execute($params);
$dailyCounts = $dailyStmt->fetchAll();

$sql = 'SELECT dv.*, u.username FROM daily_visits dv JOIN users u ON u.id = dv.user_id' . $whereSql . ' ORDER BY dv.visit_date DESC, dv.id DESC LIMIT 500';
$stmt = db()->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

?><!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars(APP_NAME) ?> - Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="<?= htmlspecialchars(BASE_URL) ?>/assets/app.css" rel="stylesheet">
</head>
<body class="app-bg">
<nav class="navbar navbar-expand-lg navbar-dark app-navbar">
    <div class="container">
        <a class="navbar-brand" href="<?= htmlspecialchars(BASE_URL) ?>/daily_report.php"><?= htmlspecialchars(APP_NAME) ?></a>
        <div class="ms-auto d-flex gap-2">
            <a class="btn btn-sm btn-nav" href="<?= htmlspecialchars(BASE_URL) ?>/daily_report.php">New</a>
            <?php if (is_admin($user)): ?>
                <a class="btn btn-sm btn-nav" href="<?= htmlspecialchars(BASE_URL) ?>/users.php">Users</a>
            <?php endif; ?>
            <a class="btn btn-sm btn-nav" href="<?= htmlspecialchars(BASE_URL) ?>/logout.php">Logout</a>
        </div>
    </div>
</nav>

<div class="container py-4" style="max-width: 1200px;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h5 mb-0">التقارير</h1>
        <div class="text-muted small"><?= htmlspecialchars($user['username'] ?? '') ?></div>
    </div>

    <?php if ($saved): ?>
        <div class="alert alert-success">تم الحفظ</div>
    <?php endif; ?>

    <div class="card shadow-sm app-card mb-3">
        <div class="card-body">
            <form class="row g-3" method="get">
                <div class="col-md-3">
                    <label class="form-label">من تاريخ</label>
                    <input type="date" name="from" value="<?= htmlspecialchars($from) ?>" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">إلى تاريخ</label>
                    <input type="date" name="to" value="<?= htmlspecialchars($to) ?>" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">الاسم</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($name) ?>" class="form-control" placeholder="اسم العيادة أو اسم الشخص">
                </div>
                <div class="col-md-3">
                    <label class="form-label">المنطقة</label>
                    <select name="area" class="form-select">
                        <option value="">الكل</option>
                        <?php foreach ($DROPDOWNS['areas'] as $v): ?>
                            <option value="<?= htmlspecialchars($v) ?>" <?= ($area === $v) ? 'selected' : '' ?>><?= htmlspecialchars($v) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">المستخدم</label>
                    <select name="user_id" class="form-select">
                        <option value="">الكل</option>
                        <?php foreach ($users as $u): ?>
                            <option value="<?= (int)$u['id'] ?>" <?= ($user_id !== '' && (int)$user_id === (int)$u['id']) ? 'selected' : '' ?>><?= htmlspecialchars((string)$u['username']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-12 d-flex gap-2 align-self-end">
                    <button class="btn btn-app" type="submit">بحث</button>
                    <a class="btn btn-app-outline" href="<?= htmlspecialchars(BASE_URL) ?>/reports.php">مسح</a>
                    <a class="btn btn-app-outline" target="_blank" href="<?= htmlspecialchars(BASE_URL) ?>/print.php?autoprint=1&from=<?= urlencode($from) ?>&to=<?= urlencode($to) ?>&name=<?= urlencode($name) ?>&area=<?= urlencode($area) ?>&user_id=<?= urlencode($user_id) ?>">طباعة الكل</a>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-lg-4">
            <div class="card shadow-sm app-card h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">إجمالي السجلات</div>
                    <div class="fs-3 fw-semibold"><?= (int)$totalCount ?></div>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card shadow-sm app-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="text-muted small">الإجمالي حسب التاريخ</div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>التاريخ</th>
                                    <th class="text-nowrap">العدد</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($dailyCounts as $d): ?>
                                    <tr>
                                        <td><?= htmlspecialchars((string)$d['visit_date']) ?></td>
                                        <td><?= (int)$d['c'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (!$dailyCounts): ?>
                                    <tr>
                                        <td colspan="2" class="text-muted">لا يوجد بيانات</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm app-card">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th class="no-print">طباعة</th>
                        <th>المنطقة</th>
                        <th>اسم العيادة</th>
                        <th>اسم الشخص</th>
                        <th class="d-none d-lg-table-cell">المسمى الوظيفي</th>
                        <th class="d-none d-lg-table-cell">رقم الموبايل</th>
                        <th class="text-nowrap">تاريخ الزيارة</th>
                        <th class="text-nowrap">رقم الزيارة</th>
                        <th class="d-none d-lg-table-cell">مستوى الاهتمام</th>
                        <th class="d-none d-lg-table-cell">نوع الزيارة</th>
                        <th class="d-none d-lg-table-cell">نتيجة الزيارة</th>
                        <th class="d-none d-lg-table-cell">حالة التنفيذ</th>
                        <th class="d-none d-xl-table-cell">ملاحظات</th>
                        <th class="d-none d-lg-table-cell">المستخدم</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $r): ?>
                        <tr>
                            <td><?= (int)$r['id'] ?></td>
                            <td class="no-print">
                                <a class="btn btn-app-outline btn-sm" target="_blank" href="<?= htmlspecialchars(BASE_URL) ?>/print.php?autoprint=1&mode=single&id=<?= (int)$r['id'] ?>">طباعة</a>
                            </td>
                            <td><?= htmlspecialchars((string)$r['area']) ?></td>
                            <td><?= htmlspecialchars((string)$r['clinic_name']) ?></td>
                            <td><?= htmlspecialchars((string)$r['person_name']) ?></td>
                            <td class="d-none d-lg-table-cell"><?= htmlspecialchars((string)$r['job_title']) ?></td>
                            <td class="d-none d-lg-table-cell text-nowrap"><?= htmlspecialchars((string)$r['mobile']) ?></td>
                            <td class="text-nowrap"><?= htmlspecialchars((string)$r['visit_date']) ?></td>
                            <td class="text-nowrap"><?= htmlspecialchars((string)$r['visit_number']) ?></td>
                            <td class="d-none d-lg-table-cell"><?= htmlspecialchars((string)$r['interest']) ?></td>
                            <td class="d-none d-lg-table-cell"><?= htmlspecialchars((string)$r['visit_type']) ?></td>
                            <td class="d-none d-lg-table-cell"><?= htmlspecialchars((string)$r['visit_result']) ?></td>
                            <td class="d-none d-lg-table-cell"><?= htmlspecialchars((string)$r['execution_status']) ?></td>
                            <td class="d-none d-xl-table-cell" style="min-width: 260px; max-width: 420px; white-space: normal;">
                                <?= htmlspecialchars((string)$r['notes']) ?>
                            </td>
                            <td class="d-none d-lg-table-cell"><?= htmlspecialchars((string)$r['username']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
