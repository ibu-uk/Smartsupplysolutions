<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_login();

$user = current_user();

$filter = (string)($_GET['filter'] ?? 'due');
if (!in_array($filter, ['due', 'upcoming', 'all'], true)) {
    $filter = 'due';
}

$page = (int)($_GET['page'] ?? 1);
$per_page = 50;
if ($page < 1) {
    $page = 1;
}

$where = ['dv.follow_up_date IS NOT NULL'];
$where[] = "(dv.follow_up_status IS NULL OR dv.follow_up_status = 'next')";
$params = [];

if ($filter === 'due') {
    $where[] = 'dv.follow_up_date <= CURDATE()';
} elseif ($filter === 'upcoming') {
    $where[] = 'dv.follow_up_date > CURDATE()';
    $where[] = 'dv.follow_up_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)';
}

if (!is_admin($user)) {
    $where[] = 'dv.user_id = ?';
    $params[] = (int)$user['id'];
}

$whereSql = ' WHERE ' . implode(' AND ', $where);

$countStmt = db()->prepare('SELECT COUNT(*) AS c FROM daily_visits dv' . $whereSql);
$countStmt->execute($params);
$total = (int)($countStmt->fetch()['c'] ?? 0);

$totalPages = (int)max(1, (int)ceil($total / $per_page));
if ($page > $totalPages) {
    $page = $totalPages;
}
$offset = ($page - 1) * $per_page;

$stmt = db()->prepare(
    'SELECT dv.*, u.username FROM daily_visits dv JOIN users u ON u.id = dv.user_id' .
    $whereSql .
    ' ORDER BY dv.follow_up_date ASC, dv.id DESC LIMIT ? OFFSET ?'
);
$stmt->execute(array_merge($params, [$per_page, $offset]));
$rows = $stmt->fetchAll();

$prevUrl = null;
$nextUrl = null;
if ($page > 1) {
    $prevUrl = BASE_URL . '/reminders.php?' . http_build_query(['filter' => $filter, 'page' => $page - 1]);
}
if ($page < $totalPages) {
    $nextUrl = BASE_URL . '/reminders.php?' . http_build_query(['filter' => $filter, 'page' => $page + 1]);
}

$badge = reminders_count($user);

?><!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars(APP_NAME) ?> - Reminders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="<?= htmlspecialchars(BASE_URL) ?>/assets/app.css" rel="stylesheet">
</head>
<body class="app-bg">
<nav class="navbar navbar-expand-lg navbar-dark app-navbar">
    <div class="container position-relative">
        <div class="position-absolute top-50 start-50 translate-middle text-white fw-semibold text-center" style="font-size: 14px; width: 100%; pointer-events: none;">
            Smartsupplysolutions
        </div>
        <a class="navbar-brand position-relative" style="z-index: 1;" href="<?= htmlspecialchars(BASE_URL) ?>/daily_report.php"><?= htmlspecialchars(APP_NAME) ?></a>
        <div class="ms-auto d-flex gap-2 position-relative" style="z-index: 1;">
            <a class="btn btn-sm btn-nav" href="<?= htmlspecialchars(BASE_URL) ?>/reminders.php">Reminders<?= $badge > 0 ? ' (' . (int)$badge . ')' : '' ?></a>
            <a class="btn btn-sm btn-nav" href="<?= htmlspecialchars(BASE_URL) ?>/reports.php">Reports</a>
            <?php if (is_admin($user)): ?>
                <a class="btn btn-sm btn-nav" href="<?= htmlspecialchars(BASE_URL) ?>/users.php">Users</a>
            <?php endif; ?>
            <a class="btn btn-sm btn-nav" href="<?= htmlspecialchars(BASE_URL) ?>/logout.php">Logout</a>
        </div>
    </div>
</nav>

<div class="container py-4" style="max-width: 1200px;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h5 mb-0">المتابعات</h1>
        <div class="text-muted small"><?= htmlspecialchars($user['username'] ?? '') ?></div>
    </div>

    <div class="card shadow-sm app-card mb-3">
        <div class="card-body">
            <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between">
                <div class="d-flex flex-wrap gap-2">
                    <a class="btn btn-app-outline btn-sm" href="<?= htmlspecialchars(BASE_URL) ?>/reminders.php?filter=due">مستحقة / متأخرة</a>
                    <a class="btn btn-app-outline btn-sm" href="<?= htmlspecialchars(BASE_URL) ?>/reminders.php?filter=upcoming">قادمة (7 أيام)</a>
                    <a class="btn btn-app-outline btn-sm" href="<?= htmlspecialchars(BASE_URL) ?>/reminders.php?filter=all">الكل</a>
                </div>
                <div class="text-muted small">العدد: <?= (int)$total ?></div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm app-card">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th class="text-nowrap">تاريخ متابعة</th>
                        <th>المنطقة</th>
                        <th>اسم العيادة</th>
                        <th>اسم الشخص</th>
                        <th class="text-nowrap">رقم الموبايل</th>
                        <th class="text-nowrap">تاريخ الزيارة</th>
                        <th class="d-none d-lg-table-cell">ملاحظات</th>
                        <th class="no-print">الإجراء</th>
                        <th class="no-print">طباعة</th>
                        <?php if (is_admin($user)): ?>
                            <th class="no-print">تعديل</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $r): ?>
                        <tr>
                            <td><?= (int)$r['id'] ?></td>
                            <td class="text-nowrap"><?= htmlspecialchars((string)$r['follow_up_date']) ?></td>
                            <td><?= htmlspecialchars((string)$r['area']) ?></td>
                            <td><?= htmlspecialchars((string)$r['clinic_name']) ?></td>
                            <td><?= htmlspecialchars((string)$r['person_name']) ?></td>
                            <td class="text-nowrap"><?= htmlspecialchars((string)$r['mobile']) ?></td>
                            <td class="text-nowrap"><?= htmlspecialchars((string)$r['visit_date']) ?></td>
                            <td class="d-none d-lg-table-cell" style="min-width: 260px; max-width: 420px; white-space: normal;">
                                <?= htmlspecialchars((string)$r['notes']) ?>
                            </td>
                            <td class="no-print" style="min-width: 320px;">
                                <form method="post" action="<?= htmlspecialchars(BASE_URL) ?>/update_follow_up.php" class="d-flex flex-wrap gap-2 align-items-center">
                                    <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                                    <input type="date" name="follow_up_date" class="form-control form-control-sm" value="<?= htmlspecialchars((string)$r['follow_up_date']) ?>" style="max-width: 160px;">
                                    <input type="text" name="note" class="form-control form-control-sm" placeholder="ملاحظة" style="max-width: 220px;" value="">
                                    <button type="submit" name="action" value="next" class="btn btn-app-outline btn-sm">Next</button>
                                    <button type="submit" name="action" value="done" class="btn btn-app btn-sm">Done</button>
                                    <button type="submit" name="action" value="cancel" class="btn btn-app-outline btn-sm">Cancel</button>
                                </form>
                            </td>
                            <td class="no-print">
                                <a class="btn btn-app-outline btn-sm" target="_blank" href="<?= htmlspecialchars(BASE_URL) ?>/print.php?autoprint=1&mode=single&id=<?= (int)$r['id'] ?>">طباعة</a>
                            </td>
                            <?php if (is_admin($user)): ?>
                                <td class="no-print">
                                    <a class="btn btn-app-outline btn-sm" href="<?= htmlspecialchars(BASE_URL) ?>/edit_report.php?id=<?= (int)$r['id'] ?>">تعديل</a>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$rows): ?>
                        <tr>
                            <td colspan="11" class="text-muted">لا يوجد بيانات</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="text-muted small">Page <?= (int)$page ?> of <?= (int)$totalPages ?> (<?= (int)$total ?>)</div>
            <div class="d-flex gap-2">
                <a class="btn btn-app-outline btn-sm <?= $prevUrl ? '' : 'disabled' ?>" href="<?= $prevUrl ? htmlspecialchars($prevUrl) : '#' ?>">Previous</a>
                <a class="btn btn-app-outline btn-sm <?= $nextUrl ? '' : 'disabled' ?>" href="<?= $nextUrl ? htmlspecialchars($nextUrl) : '#' ?>">Next</a>
            </div>
        </div>
    </div>
</div>
</body>
</html>
