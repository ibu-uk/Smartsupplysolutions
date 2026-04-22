<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_login();
require_admin();

require_once __DIR__ . '/dropdowns.php';

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

$user = current_user();

?><!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars(APP_NAME) ?> - Edit Report</title>
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
            <a class="btn btn-sm btn-nav" href="<?= htmlspecialchars(BASE_URL) ?>/reports.php">Reports</a>
            <a class="btn btn-sm btn-nav" href="<?= htmlspecialchars(BASE_URL) ?>/logout.php">Logout</a>
        </div>
    </div>
</nav>

<div class="container py-4" style="max-width: 980px;">
    <div class="card shadow-sm app-card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="h5 mb-0 app-title">تعديل التقرير #<?= (int)$r['id'] ?></h1>
                <div class="text-muted small"><?= htmlspecialchars($user['username'] ?? '') ?></div>
            </div>

            <form method="post" action="<?= htmlspecialchars(BASE_URL) ?>/update_report.php" class="row g-3">
                <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">

                <div class="col-md-4">
                    <label class="form-label">تاريخ الزيارة</label>
                    <input type="date" name="visit_date" class="form-control" required value="<?= htmlspecialchars((string)$r['visit_date']) ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label">تاريخ متابعة</label>
                    <input type="date" name="follow_up_date" class="form-control" value="<?= htmlspecialchars((string)($r['follow_up_date'] ?? '')) ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label">المنطقة</label>
                    <select name="area" class="form-select" required>
                        <option value="" disabled>اختر</option>
                        <?php foreach ($DROPDOWNS['areas'] as $v): ?>
                            <option value="<?= htmlspecialchars($v) ?>" <?= ((string)$r['area'] === $v) ? 'selected' : '' ?>><?= htmlspecialchars($v) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">اسم العيادة</label>
                    <input type="text" name="clinic_name" class="form-control" required value="<?= htmlspecialchars((string)$r['clinic_name']) ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label">رقم الزيارة</label>
                    <select name="visit_number" class="form-select" required>
                        <option value="" disabled>اختر</option>
                        <?php foreach ($DROPDOWNS['visit_numbers'] as $v): ?>
                            <option value="<?= htmlspecialchars($v) ?>" <?= ((string)$r['visit_number'] === (string)$v) ? 'selected' : '' ?>><?= htmlspecialchars($v) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">اسم الشخص</label>
                    <input type="text" name="person_name" class="form-control" required value="<?= htmlspecialchars((string)$r['person_name']) ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label">المسمى الوظيفي</label>
                    <select name="job_title" class="form-select" required>
                        <option value="" disabled>اختر</option>
                        <?php foreach ($DROPDOWNS['job_titles'] as $v): ?>
                            <option value="<?= htmlspecialchars($v) ?>" <?= ((string)$r['job_title'] === $v) ? 'selected' : '' ?>><?= htmlspecialchars($v) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">رقم الموبايل</label>
                    <input type="tel" name="mobile" class="form-control" inputmode="tel" value="<?= htmlspecialchars((string)$r['mobile']) ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label">مستوى الاهتمام</label>
                    <select name="interest" class="form-select" required>
                        <option value="" disabled>اختر</option>
                        <?php foreach ($DROPDOWNS['interest'] as $v): ?>
                            <option value="<?= htmlspecialchars($v) ?>" <?= ((string)$r['interest'] === $v) ? 'selected' : '' ?>><?= htmlspecialchars($v) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">نوع الزيارة</label>
                    <select name="visit_type" class="form-select" required>
                        <option value="" disabled>اختر</option>
                        <?php foreach ($DROPDOWNS['visit_type'] as $v): ?>
                            <option value="<?= htmlspecialchars($v) ?>" <?= ((string)$r['visit_type'] === $v) ? 'selected' : '' ?>><?= htmlspecialchars($v) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">نتيجة الزيارة</label>
                    <select name="visit_result" class="form-select" required>
                        <option value="" disabled>اختر</option>
                        <?php foreach ($DROPDOWNS['visit_result'] as $v): ?>
                            <option value="<?= htmlspecialchars($v) ?>" <?= ((string)$r['visit_result'] === $v) ? 'selected' : '' ?>><?= htmlspecialchars($v) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">حالة التنفيذ</label>
                    <select name="execution_status" class="form-select" required>
                        <option value="" disabled>اختر</option>
                        <?php foreach ($DROPDOWNS['status'] as $v): ?>
                            <option value="<?= htmlspecialchars($v) ?>" <?= ((string)$r['execution_status'] === $v) ? 'selected' : '' ?>><?= htmlspecialchars($v) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label">ملاحظات</label>
                    <textarea name="notes" class="form-control" rows="3"><?= htmlspecialchars((string)$r['notes']) ?></textarea>
                </div>

                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-app">حفظ التعديل</button>
                    <a class="btn btn-app-outline" href="<?= htmlspecialchars(BASE_URL) ?>/reports.php">رجوع</a>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
