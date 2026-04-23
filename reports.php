<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_login();

require_once __DIR__ . '/dropdowns.php';

$user = current_user();

$flashSuccess = flash_get('success');
$from = (string)($_GET['from'] ?? '');
$to = (string)($_GET['to'] ?? '');

$name = trim((string)($_GET['name'] ?? ''));
$mobile = trim((string)($_GET['mobile'] ?? ''));
$area = trim((string)($_GET['area'] ?? ''));
$user_id = trim((string)($_GET['user_id'] ?? ''));
$weekday = trim((string)($_GET['weekday'] ?? ''));
$page = (int)($_GET['page'] ?? 1);
$month_page = (int)($_GET['month_page'] ?? 1);
$per_page = 50;
if ($page < 1) {
    $page = 1;
}
if ($month_page < 1) {
    $month_page = 1;
}

$where = [];
$params = [];

$weekdayOptions = [
    '' => 'الكل',
    '1' => 'الأحد',
    '2' => 'الإثنين',
    '3' => 'الثلاثاء',
    '4' => 'الأربعاء',
    '5' => 'الخميس',
    '6' => 'الجمعة',
    '7' => 'السبت',
];

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

if ($mobile !== '') {
    $where[] = 'dv.mobile LIKE ?';
    $params[] = '%' . $mobile . '%';
}

if ($area !== '') {
    $where[] = 'dv.area = ?';
    $params[] = $area;
}

if ($user_id !== '') {
    $where[] = 'dv.user_id = ?';
    $params[] = (int)$user_id;
}

if ($weekday !== '' && isset($weekdayOptions[$weekday])) {
    $where[] = 'DAYOFWEEK(dv.visit_date) = ?';
    $params[] = (int)$weekday;
}

$whereSql = $where ? (' WHERE ' . implode(' AND ', $where)) : '';

$usersStmt = db()->query('SELECT id, username FROM users ORDER BY username ASC');
$users = $usersStmt->fetchAll();

$countStmt = db()->prepare('SELECT COUNT(*) AS c FROM daily_visits dv' . $whereSql);
$countStmt->execute($params);
$totalCount = (int)($countStmt->fetch()['c'] ?? 0);

$totalPages = (int)max(1, (int)ceil($totalCount / $per_page));
$page = (int)min($page, $totalPages);
$offset = ($page - 1) * $per_page;

$months_per_page = 12;
$monthsWhereSql = $whereSql;

$monthsCountStmt = db()->prepare(
    "SELECT COUNT(*) AS c FROM (SELECT DATE_FORMAT(dv.visit_date, '%Y-%m') AS ym FROM daily_visits dv" . $monthsWhereSql . " GROUP BY ym) x"
);
$monthsCountStmt->execute($params);
$monthsTotal = (int)($monthsCountStmt->fetch()['c'] ?? 0);
$monthsTotalPages = (int)max(1, (int)ceil($monthsTotal / $months_per_page));
$month_page = (int)min($month_page, $monthsTotalPages);
$monthsOffset = ($month_page - 1) * $months_per_page;

$monthlyStmt = db()->prepare(
    "SELECT DATE_FORMAT(dv.visit_date, '%Y-%m') AS ym, COUNT(*) AS c
     FROM daily_visits dv" . $monthsWhereSql . "
     GROUP BY ym
     ORDER BY ym DESC
     LIMIT ? OFFSET ?"
);
$monthlyStmt->execute(array_merge($params, [$months_per_page, $monthsOffset]));
$monthlyCounts = $monthlyStmt->fetchAll();

$sql = 'SELECT dv.*, u.username, (SELECT COUNT(*) FROM daily_visit_contacts c WHERE c.daily_visit_id = dv.id) AS contacts_count
        FROM daily_visits dv JOIN users u ON u.id = dv.user_id' . $whereSql . ' ORDER BY dv.visit_date DESC, dv.id DESC LIMIT ? OFFSET ?';
$stmt = db()->prepare($sql);
$stmt->execute(array_merge($params, [$per_page, $offset]));
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

$baseQuery = [
    'from' => $from,
    'to' => $to,
    'name' => $name,
    'mobile' => $mobile,
    'area' => $area,
    'user_id' => $user_id,
    'weekday' => $weekday,
];
$prevUrl = null;
$nextUrl = null;
if ($page > 1) {
    $prevUrl = BASE_URL . '/reports.php?' . http_build_query(array_merge($baseQuery, ['page' => $page - 1]));
}
if ($page < $totalPages) {
    $nextUrl = BASE_URL . '/reports.php?' . http_build_query(array_merge($baseQuery, ['page' => $page + 1]));
}

$prevMonthUrl = null;
$nextMonthUrl = null;
if ($month_page > 1) {
    $prevMonthUrl = BASE_URL . '/reports.php?' . http_build_query(array_merge($baseQuery, ['page' => $page, 'month_page' => $month_page - 1]));
}
if ($month_page < $monthsTotalPages) {
    $nextMonthUrl = BASE_URL . '/reports.php?' . http_build_query(array_merge($baseQuery, ['page' => $page, 'month_page' => $month_page + 1]));
}

?><!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars(APP_NAME) ?> - Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link href="<?= htmlspecialchars(BASE_URL) ?>/assets/app.css" rel="stylesheet">
    <style>
        .table-sticky-header thead th {
            position: sticky;
            top: 0;
            z-index: 2;
            background: var(--bs-body-bg);
        }
        .btn-actions {
            border-color: rgba(13, 110, 253, .35);
            background: rgba(13, 110, 253, .06);
        }
        .btn-actions:hover {
            border-color: rgba(13, 110, 253, .55);
            background: rgba(13, 110, 253, .10);
        }
        .stat-card {
            border: 0;
            border-radius: 14px;
            overflow: hidden;
        }
        #backToTopBtn {
            position: fixed;
            left: 18px;
            bottom: 18px;
            z-index: 1050;
            display: none;
        }
    </style>
</head>
<body class="app-bg">
<nav class="navbar navbar-expand-lg navbar-dark app-navbar">
    <div class="container position-relative">
        <div class="position-absolute top-50 start-50 translate-middle text-white fw-semibold text-center" style="font-size: 14px; width: 100%; pointer-events: none;">
            <div class="d-inline-flex align-items-center gap-2" style="transform: translateY(-1px);">
                <img src="<?= htmlspecialchars(BASE_URL . '/assets/' . rawurlencode('Screenshot 2026-04-22 153610.png')) ?>" alt="Smartsupplysolutions" style="height: 26px; width: auto; background: #fff; border-radius: 6px; padding: 2px 6px; opacity: .95;">
                <span>Smartsupplysolutions</span>
            </div>

<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">
      <div class="modal-body text-center p-4">
        <div class="d-flex justify-content-center mb-3">
          <div class="rounded-circle border d-flex align-items-center justify-content-center" style="width: 72px; height: 72px; border-width: 3px;">
            <i class="bi bi-trash" style="font-size: 34px;"></i>
          </div>
        </div>
        <div class="fw-semibold" style="font-size: 18px;">تأكيد الحذف</div>
        <div class="text-muted mt-2">هل أنت متأكد أنك تريد حذف التقرير؟</div>
        <div class="d-flex justify-content-center gap-2 mt-4">
          <button type="button" class="btn btn-danger px-3" id="confirmDeleteBtn">نعم، حذف</button>
          <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">إلغاء</button>
        </div>
      </div>
    </div>
  </div>
</div>
        </div>
        <a class="navbar-brand position-relative" style="z-index: 1;" href="<?= htmlspecialchars(BASE_URL) ?>/daily_report.php"><?= htmlspecialchars(APP_NAME) ?></a>
        <div class="ms-auto d-flex gap-2 position-relative" style="z-index: 1;">
            <?php $reminders_count = reminders_count($user); ?>
            <a class="btn btn-sm btn-nav" href="<?= htmlspecialchars(BASE_URL) ?>/reminders.php">Reminders<?= $reminders_count > 0 ? ' (' . (int)$reminders_count . ')' : '' ?></a>
            <a class="btn btn-sm btn-nav" href="<?= htmlspecialchars(BASE_URL) ?>/daily_report.php">New</a>
            <?php if (is_admin($user)): ?>
                <a class="btn btn-sm btn-nav" href="<?= htmlspecialchars(BASE_URL) ?>/users.php">Users</a>
            <?php endif; ?>
            <a class="btn btn-sm btn-nav" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">Logout</a>
        </div>
    </div>
</nav>

<div class="container py-4" style="max-width: 1600px;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h5 mb-0">التقارير</h1>
        <div class="text-muted small"><?= htmlspecialchars($user['username'] ?? '') ?></div>
    </div>

    <?php if ($flashSuccess !== null && $flashSuccess !== ''): ?>
        <div id="flashSuccess" class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($flashSuccess) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm app-card mb-3">
        <div class="card-body">
            <form class="row g-3" method="get">
                <div class="col-md-3">
                    <label class="form-label">من تاريخ</label>
                    <input type="text" name="from" value="<?= htmlspecialchars($from) ?>" class="form-control js-date">
                </div>
                <div class="col-md-3">
                    <label class="form-label">إلى تاريخ</label>
                    <input type="text" name="to" value="<?= htmlspecialchars($to) ?>" class="form-control js-date">
                </div>
                <div class="col-md-3">
                    <label class="form-label">الاسم</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($name) ?>" class="form-control" placeholder="اسم العيادة أو اسم الشخص">
                </div>
                <div class="col-md-3">
                    <label class="form-label">الموبايل</label>
                    <input type="text" name="mobile" value="<?= htmlspecialchars($mobile) ?>" class="form-control" placeholder="رقم الموبايل">
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

                <div class="col-md-3">
                    <label class="form-label">اليوم</label>
                    <select name="weekday" class="form-select">
                        <?php foreach ($weekdayOptions as $k => $label): ?>
                            <option value="<?= htmlspecialchars((string)$k) ?>" <?= ($weekday === (string)$k) ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12 d-flex flex-wrap gap-2">
                    <button class="btn btn-app" type="submit">بحث</button>
                    <a class="btn btn-app-outline" href="<?= htmlspecialchars(BASE_URL) ?>/reports.php">مسح</a>
                    <a class="btn btn-app-outline" target="_blank" href="<?= htmlspecialchars(BASE_URL) ?>/print.php?autoprint=1&from=<?= urlencode($from) ?>&to=<?= urlencode($to) ?>&name=<?= urlencode($name) ?>&mobile=<?= urlencode($mobile) ?>&area=<?= urlencode($area) ?>&user_id=<?= urlencode($user_id) ?>&weekday=<?= urlencode($weekday) ?>">طباعة الكل</a>
                    <a class="btn btn-app-outline" href="<?= htmlspecialchars(BASE_URL) ?>/export_reports.php?from=<?= urlencode($from) ?>&to=<?= urlencode($to) ?>&name=<?= urlencode($name) ?>&mobile=<?= urlencode($mobile) ?>&area=<?= urlencode($area) ?>&user_id=<?= urlencode($user_id) ?>&weekday=<?= urlencode($weekday) ?>">تصدير Excel</a>
                    <?php if (is_admin($user)): ?>
                        <a class="btn btn-app-outline" href="<?= htmlspecialchars(BASE_URL) ?>/import_csv.php">استيراد CSV</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-lg-4">
            <div class="card shadow-sm stat-card stat-card--primary h-100">
                <div class="stat-card__inner">
                    <div class="stat-card__accent"></div>
                    <div class="d-flex align-items-center justify-content-between gap-3">
                        <div>
                            <div class="text-muted small mb-1">إجمالي السجلات</div>
                            <div class="stat-card__value"><?= (int)$totalCount ?></div>
                        </div>
                        <div class="stat-card__icon text-primary">
                            <i class="bi bi-clipboard-data" style="font-size: 22px;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card shadow-sm stat-card stat-card--success h-100">
                <div class="stat-card__inner">
                    <div class="stat-card__accent"></div>
                    <div class="d-flex align-items-center justify-content-between gap-3 mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <div class="stat-card__icon text-success">
                                <i class="bi bi-bar-chart" style="font-size: 22px;"></i>
                            </div>
                            <div>
                                <div class="text-muted small">الإجمالي حسب الشهر</div>
                                <div class="text-muted small">Page <?= (int)$month_page ?> of <?= (int)$monthsTotalPages ?> (<?= (int)$monthsTotal ?>)</div>
                            </div>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <a class="btn btn-app-outline btn-sm <?= $prevMonthUrl ? '' : 'disabled' ?>" href="<?= $prevMonthUrl ? htmlspecialchars($prevMonthUrl) : '#' ?>">Previous</a>
                            <a class="btn btn-app-outline btn-sm <?= $nextMonthUrl ? '' : 'disabled' ?>" href="<?= $nextMonthUrl ? htmlspecialchars($nextMonthUrl) : '#' ?>">Next</a>
                            <button class="btn btn-app-outline btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#dailyCountsPanel" aria-expanded="false" aria-controls="dailyCountsPanel">عرض / إخفاء</button>
                        </div>
                    </div>

                    <div class="collapse show" id="dailyCountsPanel">
                        <div class="table-responsive" style="max-height: 260px; overflow: auto;">
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>الشهر</th>
                                        <th class="text-nowrap">العدد</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($monthlyCounts as $d): ?>
                                        <tr>
                                            <td><?= htmlspecialchars((string)$d['ym']) ?></td>
                                            <td><?= (int)$d['c'] ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (!$monthlyCounts): ?>
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
    </div>

    <div class="card shadow-sm app-card">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0 align-middle table-sticky-header">
                <thead>
                    <tr>
                        <th>#</th>
                        <th class="no-print text-end">الإجراءات</th>
                        <th>المنطقة</th>
                        <th>العنوان</th>
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
                            <td class="no-print text-end text-nowrap">
                                <div class="d-flex justify-content-end">
                                    <div class="dropdown">
                                        <button class="btn btn-app-outline btn-sm btn-actions" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Actions" aria-label="Actions">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <a class="dropdown-item" href="<?= htmlspecialchars(BASE_URL) ?>/export_report.php?id=<?= (int)$r['id'] ?>">
                                                    <i class="bi bi-file-earmark-excel me-2"></i>
                                                    تصدير Excel
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" target="_blank" href="<?= htmlspecialchars(BASE_URL) ?>/print.php?autoprint=1&mode=single&id=<?= (int)$r['id'] ?>">
                                                    <i class="bi bi-printer me-2"></i>
                                                    طباعة
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="<?= htmlspecialchars(BASE_URL) ?>/edit_report.php?id=<?= (int)$r['id'] ?>">
                                                    <i class="bi bi-pencil-square me-2"></i>
                                                    تعديل
                                                </a>
                                            </li>
                                            <?php if (is_admin($user)): ?>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <form method="post" action="<?= htmlspecialchars(BASE_URL) ?>/delete_report.php" style="display:inline;" class="js-delete-report-form">
                                                        <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                                                        <button class="dropdown-item text-danger js-delete-report-btn" type="button" data-report-id="<?= (int)$r['id'] ?>">
                                                            <i class="bi bi-trash me-2"></i>
                                                            حذف
                                                        </button>
                                                    </form>
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                </div>
                            </td>
                            <td><?= htmlspecialchars((string)$r['area']) ?></td>
                            <td><?= htmlspecialchars((string)($r['address'] ?? '')) ?></td>
                            <td><?= htmlspecialchars((string)$r['clinic_name']) ?></td>
                            <td>
                                <?php $cs = $contactsByVisitId[(int)$r['id']] ?? []; ?>
                                <?php if ($cs): ?>
                                    <?php foreach ($cs as $c): ?>
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
                            <td class="d-none d-lg-table-cell">
                                <?php if ($cs): ?>
                                    <?php foreach ($cs as $c): ?>
                                        <div><?= htmlspecialchars((string)($c['job_title'] ?? '')) ?></div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <?= htmlspecialchars((string)$r['job_title']) ?>
                                <?php endif; ?>
                            </td>
                            <td class="d-none d-lg-table-cell">
                                <?php if ($cs): ?>
                                    <?php foreach ($cs as $c): ?>
                                        <div class="text-nowrap"><?= htmlspecialchars((string)($c['mobile'] ?? '')) ?></div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-nowrap"><?= htmlspecialchars((string)$r['mobile']) ?></div>
                                <?php endif; ?>
                            </td>
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
        <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="text-muted small">Page <?= (int)$page ?> of <?= (int)$totalPages ?> (<?= (int)$totalCount ?>)</div>
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
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
(() => {
  const el = document.getElementById('flashSuccess');
  if (!el) return;
  window.setTimeout(() => {
    try {
      const alert = bootstrap.Alert.getOrCreateInstance(el);
      alert.close();
    } catch (e) {
      el.remove();
    }
  }, 2500);
})();

document.querySelectorAll('.js-date').forEach((el) => {
  flatpickr(el, {
    dateFormat: 'Y-m-d',
    altInput: true,
    altFormat: 'd/m/Y',
    allowInput: true,
  });
});

(() => {
  const modalEl = document.getElementById('deleteConfirmModal');
  const confirmBtn = document.getElementById('confirmDeleteBtn');
  if (!modalEl || !confirmBtn) return;

  let pendingForm = null;

  document.querySelectorAll('.js-delete-report-btn').forEach((btn) => {
    btn.addEventListener('click', () => {
      const form = btn.closest('form');
      if (!form) return;
      pendingForm = form;
      const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
      modal.show();
    });
  });

  confirmBtn.addEventListener('click', () => {
    if (!pendingForm) return;
    pendingForm.submit();
  });
})();
</script>
</body>
</html>
