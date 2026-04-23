<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_login();

$user = current_user();

$filter = (string)($_GET['filter'] ?? 'due');
if (!in_array($filter, ['due', 'upcoming', 'all'], true)) {
    $filter = 'due';
}

$name = trim((string)($_GET['name'] ?? ''));
$mobile = trim((string)($_GET['mobile'] ?? ''));
$from = (string)($_GET['from'] ?? '');
$to = (string)($_GET['to'] ?? '');

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

if ($name !== '') {
    $where[] = '(dv.clinic_name LIKE ? OR dv.person_name LIKE ?)';
    $like = '%' . $name . '%';
    $params[] = $like;
    $params[] = $like;
}

if ($mobile !== '') {
    $where[] = 'dv.mobile LIKE ?';
    $params[] = '%' . $mobile . '%';
}

if ($from !== '') {
    $where[] = 'dv.follow_up_date >= ?';
    $params[] = $from;
}
if ($to !== '') {
    $where[] = 'dv.follow_up_date <= ?';
    $params[] = $to;
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
$baseQuery = [
    'filter' => $filter,
    'name' => $name,
    'mobile' => $mobile,
    'from' => $from,
    'to' => $to,
];
if ($page > 1) {
    $prevUrl = BASE_URL . '/reminders.php?' . http_build_query(array_merge($baseQuery, ['page' => $page - 1]));
}
if ($page < $totalPages) {
    $nextUrl = BASE_URL . '/reminders.php?' . http_build_query(array_merge($baseQuery, ['page' => $page + 1]));
}

$badge = reminders_count($user);

?><!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars(APP_NAME) ?> - Reminders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="<?= htmlspecialchars(BASE_URL) ?>/assets/app.css" rel="stylesheet">
</head>
<body class="app-bg">
<nav class="navbar navbar-expand-lg navbar-dark app-navbar">
    <div class="container position-relative">
        <div class="position-absolute top-50 start-50 translate-middle text-white fw-semibold text-center" style="font-size: 14px; width: 100%; pointer-events: none;">
            <div class="d-inline-flex align-items-center gap-2" style="transform: translateY(-1px);">
                <img src="<?= htmlspecialchars(BASE_URL . '/assets/' . rawurlencode('Screenshot 2026-04-22 153610.png')) ?>" alt="Smartsupplysolutions" style="height: 26px; width: auto; background: #fff; border-radius: 6px; padding: 2px 6px; opacity: .95;">
                <span>Smartsupplysolutions</span>
            </div>
        </div>
        <a class="navbar-brand position-relative" style="z-index: 1;" href="<?= htmlspecialchars(BASE_URL) ?>/daily_report.php"><?= htmlspecialchars(APP_NAME) ?></a>
        <div class="ms-auto d-flex gap-2 position-relative" style="z-index: 1;">
            <a class="btn btn-sm btn-nav" href="<?= htmlspecialchars(BASE_URL) ?>/reminders.php">Reminders<?= $badge > 0 ? ' (' . (int)$badge . ')' : '' ?></a>
            <a class="btn btn-sm btn-nav" href="<?= htmlspecialchars(BASE_URL) ?>/reports.php">Reports</a>
            <?php if (is_admin($user)): ?>
                <a class="btn btn-sm btn-nav" href="<?= htmlspecialchars(BASE_URL) ?>/users.php">Users</a>
            <?php endif; ?>
            <a class="btn btn-sm btn-nav" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">Logout</a>
        </div>
    </div>
</nav>

<div class="container py-4" style="max-width: 1600px;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h5 mb-0">المتابعات</h1>
        <div class="text-muted small"><?= htmlspecialchars($user['username'] ?? '') ?></div>
    </div>

    <div class="card shadow-sm app-card mb-3">
        <div class="card-body">
            <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between">
                <div class="d-flex flex-wrap gap-2">
                    <a class="btn btn-app-outline btn-sm" href="<?= htmlspecialchars(BASE_URL) ?>/reminders.php?<?= htmlspecialchars(http_build_query(array_merge($baseQuery, ['filter' => 'due', 'page' => 1]))) ?>">مستحقة / متأخرة</a>
                    <a class="btn btn-app-outline btn-sm" href="<?= htmlspecialchars(BASE_URL) ?>/reminders.php?<?= htmlspecialchars(http_build_query(array_merge($baseQuery, ['filter' => 'upcoming', 'page' => 1]))) ?>">قادمة (7 أيام)</a>
                    <a class="btn btn-app-outline btn-sm" href="<?= htmlspecialchars(BASE_URL) ?>/reminders.php?<?= htmlspecialchars(http_build_query(array_merge($baseQuery, ['filter' => 'all', 'page' => 1]))) ?>">الكل</a>
                </div>
                <div class="text-muted small">العدد: <?= (int)$total ?></div>
            </div>

            <form class="row g-2 mt-3" method="get">
                <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
                <div class="col-md-4">
                    <input type="text" name="name" value="<?= htmlspecialchars($name) ?>" class="form-control form-control-sm" placeholder="بحث بالاسم (العيادة/الشخص)">
                </div>
                <div class="col-md-3">
                    <input type="text" name="mobile" value="<?= htmlspecialchars($mobile) ?>" class="form-control form-control-sm" placeholder="بحث بالموبايل">
                </div>
                <div class="col-md-2">
                    <input type="date" name="from" value="<?= htmlspecialchars($from) ?>" class="form-control form-control-sm" placeholder="من">
                </div>
                <div class="col-md-2">
                    <input type="date" name="to" value="<?= htmlspecialchars($to) ?>" class="form-control form-control-sm" placeholder="إلى">
                </div>
                <div class="col-md-1 d-grid">
                    <button class="btn btn-app btn-sm" type="submit">بحث</button>
                </div>
                <div class="col-12 d-flex justify-content-end">
                    <a class="btn btn-app-outline btn-sm" href="<?= htmlspecialchars(BASE_URL) ?>/reminders.php?<?= htmlspecialchars(http_build_query(['filter' => $filter])) ?>">مسح</a>
                </div>
            </form>
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
                        <th class="d-none d-lg-table-cell">ملاحظات الزيارة</th>
                        <th class="d-none d-lg-table-cell">ملاحظات المتابعة</th>
                        <th class="no-print">الإجراء</th>
                        <th class="no-print text-end">الإجراءات</th>
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
                                <?= htmlspecialchars((string)($r['notes'] ?? '')) ?>
                            </td>
                            <td class="d-none d-lg-table-cell" style="min-width: 260px; max-width: 420px; white-space: normal;">
                                <?= htmlspecialchars((string)($r['follow_up_action_note'] ?? '')) ?>
                            </td>
                            <td class="no-print text-end" style="min-width: 320px;">
                                <form method="post" action="<?= htmlspecialchars(BASE_URL) ?>/update_follow_up.php" class="d-flex flex-wrap gap-2 align-items-center justify-content-end">
                                    <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                                    <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
                                    <input type="hidden" name="page" value="<?= (int)$page ?>">
                                    <input type="date" name="follow_up_date" class="form-control form-control-sm" value="<?= htmlspecialchars((string)$r['follow_up_date']) ?>" style="max-width: 160px;">
                                    <input type="text" name="note" class="form-control form-control-sm" placeholder="ملاحظة" style="max-width: 220px;" value="<?= htmlspecialchars((string)($r['follow_up_action_note'] ?? '')) ?>">
                                    <button type="submit" name="action" value="next" class="btn btn-app-outline btn-sm">Next</button>
                                    <button type="submit" name="action" value="done" class="btn btn-app btn-sm">Done</button>
                                    <button type="submit" name="action" value="cancel" class="btn btn-app-outline btn-sm">Cancel</button>
                                </form>
                            </td>
                            <td class="no-print text-end text-nowrap">
                                <div class="d-flex justify-content-end">
                                    <div class="btn-group" role="group">
                                        <a class="btn btn-app-outline btn-sm" title="طباعة" aria-label="طباعة" target="_blank" href="<?= htmlspecialchars(BASE_URL) ?>/print.php?autoprint=1&mode=single&id=<?= (int)$r['id'] ?>">
                                            <i class="bi bi-printer"></i>
                                        </a>
                                        <a class="btn btn-app-outline btn-sm" title="تعديل" aria-label="تعديل" href="<?= htmlspecialchars(BASE_URL) ?>/edit_report.php?id=<?= (int)$r['id'] ?>">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                    </div>
                                </div>
                            </td>
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

<div class="modal fade" id="logoutModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">
      <div class="modal-body text-center p-4">
        <div class="d-flex justify-content-center mb-3">
          <div class="rounded-circle border d-flex align-items-center justify-content-center" style="width: 72px; height: 72px; border-width: 3px;">
            <i class="bi bi-question-lg" style="font-size: 34px;"></i>
          </div>
        </div>
        <div class="fw-semibold" style="font-size: 18px;">تأكيد تسجيل الخروج</div>
        <div class="text-muted mt-2">هل أنت متأكد أنك تريد تسجيل الخروج؟</div>
        <div class="d-flex justify-content-center gap-2 mt-4">
          <a class="btn btn-danger px-3" href="<?= htmlspecialchars(BASE_URL) ?>/logout.php">نعم، تسجيل الخروج</a>
          <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">إلغاء</button>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
