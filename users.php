<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_login();
require_admin();

$user = current_user();

$flashSuccess = flash_get('success');
$flashError = flash_get('error');

$stmt = db()->query('SELECT id, username, created_at FROM users ORDER BY id DESC');
$users = $stmt->fetchAll();

?><!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars(APP_NAME) ?> - Users</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
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
            <a class="btn btn-sm btn-nav" href="<?= htmlspecialchars(BASE_URL) ?>/reports.php">Reports</a>
            <a class="btn btn-sm btn-nav" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">Logout</a>
        </div>
    </div>
</nav>

<div class="container py-4" style="max-width: 1100px;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h5 mb-0">المستخدمين</h1>
        <div class="text-muted small"><?= htmlspecialchars($user['username'] ?? '') ?></div>
    </div>

    <?php if ($flashSuccess !== null && $flashSuccess !== ''): ?>
        <div class="alert alert-success"><?= htmlspecialchars($flashSuccess) ?></div>
    <?php endif; ?>
    <?php if ($flashError !== null && $flashError !== ''): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($flashError) ?></div>
    <?php endif; ?>

    <div class="row g-3">
        <div class="col-lg-5">
            <div class="card shadow-sm app-card">
                <div class="card-body">
                    <h2 class="h6 mb-3">إضافة مستخدم جديد</h2>
                    <form method="post" action="<?= htmlspecialchars(BASE_URL) ?>/save_user.php" class="row g-3">
                        <div class="col-12">
                            <label class="form-label">اسم المستخدم</label>
                            <input name="username" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">كلمة المرور</label>
                            <input type="password" name="password" class="form-control" required minlength="4">
                        </div>
                        <div class="col-12">
                            <button class="btn btn-app" type="submit">حفظ</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card shadow-sm app-card">
                <div class="card-body">
                    <h2 class="h6 mb-3">قائمة المستخدمين</h2>
                    <div class="table-responsive">
                        <table class="table mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>اسم المستخدم</th>
                                    <th>تاريخ الإنشاء</th>
                                    <th>تغيير كلمة المرور</th>
                                    <th class="text-end">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $u): ?>
                                    <tr>
                                        <td><?= (int)$u['id'] ?></td>
                                        <td><?= htmlspecialchars((string)$u['username']) ?></td>
                                        <td class="text-nowrap"><?= htmlspecialchars((string)$u['created_at']) ?></td>
                                        <td class="text-end" style="min-width: 260px;">
                                            <form method="post" action="<?= htmlspecialchars(BASE_URL) ?>/update_user.php" class="d-flex gap-2 justify-content-end">
                                                <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                                                <input type="password" name="password" class="form-control" placeholder="كلمة مرور جديدة" minlength="4" required>
                                                <button class="btn btn-app-outline btn-sm" type="submit">حفظ</button>
                                            </form>
                                        </td>
                                        <td class="text-end text-nowrap">
                                            <?php if (((string)$u['username']) !== 'admin'): ?>
                                                <div class="d-flex justify-content-end">
                                                    <form method="post" action="<?= htmlspecialchars(BASE_URL) ?>/delete_user.php" onsubmit="return confirm('حذف المستخدم؟');">
                                                        <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                                                        <button class="btn btn-danger btn-sm" type="submit" title="حذف" aria-label="حذف">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (!$users): ?>
                                    <tr>
                                        <td colspan="5" class="text-muted">لا يوجد مستخدمين</td>
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
